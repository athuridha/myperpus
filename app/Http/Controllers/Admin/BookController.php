<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\Location;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BookController extends Controller
{
    /**
     * Display books list
     */
    public function index(Request $request)
    {
        $query = Book::with(['category', 'location']);

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by location
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        $books = $query->latest()->paginate(20);
        $categories = Category::all();
        $locations = Location::all();

        return view('admin.books.index', compact('books', 'categories', 'locations'));
    }

    /**
     * Show create book form
     */
    public function create()
    {
        $categories = Category::all();
        $locations = Location::all();

        return view('admin.books.create', compact('categories', 'locations'));
    }

    /**
     * Store new book
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'string', 'max:20', 'unique:books'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'category_id' => ['required', 'exists:categories,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'total_stock' => ['required', 'integer', 'min:1'],
            'book_condition' => ['required', 'in:baik,rusak,hilang'],
        ]);

        try {
            $data = $request->except('cover_image');
            $data['available_stock'] = $request->total_stock;

            // Handle cover image upload
            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
            }

            // Create book
            $book = Book::create($data);

            // Generate QR code
            $qrFileName = 'book_' . $book->id . '_' . time() . '.png';
            $qrPath = storage_path('app/public/qrcodes/' . $qrFileName);

            // Ensure directory exists
            if (!file_exists(storage_path('app/public/qrcodes'))) {
                mkdir(storage_path('app/public/qrcodes'), 0755, true);
            }

            QrCode::format('png')
                ->size(300)
                ->generate($book->qr_data, $qrPath);

            $book->update(['qr_code' => $qrFileName]);

            // Log action
            AuditLog::logBookCreated($book);

            return redirect()->route('admin.books.index')
                ->with('success', 'Buku berhasil ditambahkan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show edit book form
     */
    public function edit(Book $book)
    {
        $categories = Category::all();
        $locations = Location::all();

        return view('admin.books.edit', compact('book', 'categories', 'locations'));
    }

    /**
     * Update book
     */
    public function update(Request $request, Book $book)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'string', 'max:20', 'unique:books,isbn,' . $book->id],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'category_id' => ['required', 'exists:categories,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'total_stock' => ['required', 'integer', 'min:' . ($book->total_stock - $book->available_stock)],
            'book_condition' => ['required', 'in:baik,rusak,hilang'],
        ]);

        try {
            $data = $request->except('cover_image');

            // Update available stock proportionally
            $borrowedCount = $book->total_stock - $book->available_stock;
            $data['available_stock'] = $request->total_stock - $borrowedCount;

            // Handle cover image upload
            if ($request->hasFile('cover_image')) {
                // Delete old cover
                if ($book->cover_image) {
                    Storage::disk('public')->delete($book->cover_image);
                }
                $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
            }

            $book->update($data);

            // Log action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'update_book',
                'description' => "Updated book: {$book->title}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.books.index')
                ->with('success', 'Buku berhasil diupdate.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete book (soft delete)
     */
    public function destroy(Book $book)
    {
        // Check if book has active borrowings
        if ($book->activeBorrowings()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus buku yang masih dipinjam.');
        }

        try {
            $book->delete();

            // Log action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete_book',
                'description' => "Deleted book: {$book->title}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.books.index')
                ->with('success', 'Buku berhasil dihapus.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Import books from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'], // Max 5MB
        ]);

        try {
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Skip header row
            array_shift($rows);

            $imported = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because array is 0-indexed and we skip header

                // Validate required fields
                if (empty($row[0]) || empty($row[1])) {
                    $errors[] = "Row {$rowNumber}: Title and ISBN are required";
                    continue;
                }

                // Find category and location by name
                $category = Category::where('name', $row[5])->first();
                $location = Location::where('code', $row[6])->first();

                if (!$category || !$location) {
                    $errors[] = "Row {$rowNumber}: Invalid category or location";
                    continue;
                }

                // Check if ISBN already exists
                if (Book::where('isbn', $row[1])->exists()) {
                    $errors[] = "Row {$rowNumber}: ISBN {$row[1]} already exists";
                    continue;
                }

                // Create book
                $book = Book::create([
                    'title' => $row[0],
                    'isbn' => $row[1],
                    'author' => $row[2] ?? 'Unknown',
                    'publisher' => $row[3] ?? 'Unknown',
                    'year' => $row[4] ?? date('Y'),
                    'category_id' => $category->id,
                    'location_id' => $location->id,
                    'description' => $row[7] ?? null,
                    'total_stock' => $row[8] ?? 1,
                    'available_stock' => $row[8] ?? 1,
                    'book_condition' => $row[9] ?? 'baik',
                ]);

                // Generate QR code
                $qrFileName = 'book_' . $book->id . '_' . time() . '.png';
                $qrPath = storage_path('app/public/qrcodes/' . $qrFileName);

                QrCode::format('png')
                    ->size(300)
                    ->generate($book->qr_data, $qrPath);

                $book->update(['qr_code' => $qrFileName]);

                $imported++;
            }

            // Log action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'import_books',
                'description' => "Imported {$imported} books from Excel",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $message = "{$imported} buku berhasil diimport.";
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " baris gagal: " . implode(', ', $errors);
            }

            return redirect()->route('admin.books.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
