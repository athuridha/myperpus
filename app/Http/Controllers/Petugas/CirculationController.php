<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\Reshelving;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CirculationController extends Controller
{
    /**
     * Show QR scan page for borrowing
     */
    public function scanBorrow()
    {
        return view('petugas.circulation.scan-borrow');
    }

    /**
     * Show QR scan page for returning
     */
    public function scanReturn()
    {
        return view('petugas.circulation.scan-return');
    }

    /**
     * Process borrowing via QR code
     */
    public function processBorrow(Request $request)
    {
        $request->validate([
            'user_qr' => ['required', 'string'],
            'book_qr' => ['required', 'string'],
        ]);

        try {
            // Decode QR data
            $userData = json_decode($request->user_qr, true);
            $bookData = json_decode($request->book_qr, true);

            if (!$userData || !$bookData) {
                return back()->with('error', 'QR Code tidak valid.');
            }

            $user = User::find($userData['id']);
            $book = Book::find($bookData['id']);

            if (!$user || !$book) {
                return back()->with('error', 'User atau buku tidak ditemukan.');
            }

            // Validate user can borrow
            if (!$user->canBorrow()) {
                return back()->with('error', "User {$user->name} tidak dapat meminjam. Periksa status, denda, atau kuota.");
            }

            // Check if book is available
            if (!$book->isAvailable()) {
                return back()->with('error', 'Buku tidak tersedia.');
            }

            DB::beginTransaction();

            // Check if there's existing booking
            $existingBooking = Borrowing::where('user_id', $user->id)
                ->where('book_id', $book->id)
                ->where('status', 'booked')
                ->first();

            if ($existingBooking) {
                // Convert booking to borrowing
                $borrowing = $existingBooking;
                $borrowing->update([
                    'status' => 'borrowed',
                    'borrow_date' => now(),
                    'due_date' => now()->addDays(config('app.borrow_duration_days', 14)),
                ]);
            } else {
                // Create new borrowing
                $borrowing = Borrowing::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'status' => 'borrowed',
                    'borrow_date' => now(),
                    'due_date' => now()->addDays(config('app.borrow_duration_days', 14)),
                ]);

                // Decrease stock if not from booking
                $book->decrementStock();
            }

            // Create notification
            Notification::createDeadlineReminder($user->id, $borrowing);

            // Log action
            AuditLog::logBorrowing($borrowing);

            DB::commit();

            return back()->with('success', "Peminjaman berhasil! User: {$user->name}, Buku: {$book->title}, Jatuh tempo: {$borrowing->due_date->format('d/m/Y')}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Process return via QR code
     */
    public function processReturn(Request $request)
    {
        $request->validate([
            'user_qr' => ['required', 'string'],
            'book_qr' => ['required', 'string'],
        ]);

        try {
            // Decode QR data
            $userData = json_decode($request->user_qr, true);
            $bookData = json_decode($request->book_qr, true);

            if (!$userData || !$bookData) {
                return back()->with('error', 'QR Code tidak valid.');
            }

            $user = User::find($userData['id']);
            $book = Book::find($bookData['id']);

            if (!$user || !$book) {
                return back()->with('error', 'User atau buku tidak ditemukan.');
            }

            // Find active borrowing
            $borrowing = Borrowing::where('user_id', $user->id)
                ->where('book_id', $book->id)
                ->whereIn('status', ['borrowed', 'overdue'])
                ->whereNull('return_date')
                ->first();

            if (!$borrowing) {
                return back()->with('error', 'Tidak ada peminjaman aktif untuk user dan buku ini.');
            }

            DB::beginTransaction();

            // Update borrowing
            $borrowing->update([
                'return_date' => now(),
                'status' => 'returned',
            ]);

            // Check if overdue and create fine
            $fine = null;
            if ($borrowing->isOverdue()) {
                $fineAmount = $borrowing->calculateFine();
                $fine = $borrowing->createFine($fineAmount);
            }

            // Create reshelving record
            $reshelving = Reshelving::create([
                'book_id' => $book->id,
                'borrowing_id' => $borrowing->id,
                'status' => 'pending',
            ]);

            // Create notification
            if ($fine) {
                Notification::createForUser(
                    $user->id,
                    'warning',
                    'Buku Dikembalikan - Ada Denda',
                    "Buku '{$book->title}' telah dikembalikan. Denda keterlambatan: Rp " . number_format($fine->amount, 0, ',', '.')
                );
            } else {
                Notification::createForUser(
                    $user->id,
                    'success',
                    'Buku Dikembalikan',
                    "Buku '{$book->title}' telah dikembalikan tepat waktu. Terima kasih!"
                );
            }

            // Log action
            AuditLog::logReturn($borrowing);

            DB::commit();

            $message = "Pengembalian berhasil! User: {$user->name}, Buku: {$book->title}";
            if ($fine) {
                $message .= ", Denda: Rp " . number_format($fine->amount, 0, ',', '.');
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display all borrowings
     */
    public function index(Request $request)
    {
        $query = Borrowing::with(['user', 'book']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nim_nip', 'like', "%{$search}%");
            })->orWhereHas('book', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        $borrowings = $query->latest()->paginate(20);

        // Additional dashboard statistics used by the view
        $today = Carbon::today();
        $todayBorrows = Borrowing::whereDate('borrow_date', $today)->count();
        $todayReturns = Borrowing::whereDate('return_date', $today)->count();
        $activeBookings = Borrowing::where('status', 'booked')->count();
        $overdueCount = Borrowing::overdue()->count();

        // Recent transactions (borrow or return) happened today
        $recentTransactions = Borrowing::with(['user', 'book'])
            ->whereDate('borrow_date', $today)
            ->orWhereDate('return_date', $today)
            ->latest()
            ->take(10)
            ->get();

        // Overdue list for action
        $overdueBooks = Borrowing::overdue()->with(['user', 'book'])->get();

        return view('petugas.circulation.index', compact(
            'borrowings',
            'todayBorrows',
            'todayReturns',
            'activeBookings',
            'overdueCount',
            'recentTransactions',
            'overdueBooks'
        ));
    }
}
