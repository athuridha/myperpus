<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Notification;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BorrowingController extends Controller
{
    /**
     * Display borrowing history
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = $user->borrowings()->with('book');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $borrowings = $query->latest()->paginate(10);

        return view('member.borrowings.index', compact('borrowings'));
    }

    /**
     * Book a book for borrowing
     */
    public function book(Book $book)
    {
        $user = auth()->user();

        // Validate user can borrow
        if (!$user->canBorrow()) {
            return back()->with('error', 'Anda tidak dapat melakukan peminjaman. Periksa status akun, denda, atau kuota peminjaman Anda.');
        }

        // Check if book is available
        if (!$book->isAvailable()) {
            return back()->with('error', 'Buku tidak tersedia untuk dipinjam.');
        }

        // Check if user already has active booking/borrowing for this book
        $existingBorrowing = $user->activeBorrowings()
            ->where('book_id', $book->id)
            ->first();

        if ($existingBorrowing) {
            return back()->with('error', 'Anda sudah memiliki peminjaman aktif untuk buku ini.');
        }

        try {
            DB::beginTransaction();

            // Create booking
            $borrowing = Borrowing::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'status' => 'booked',
                'borrow_date' => null,
                'due_date' => null,
                'return_date' => null,
            ]);

            // Decrease book stock
            $book->decrementStock();

            // Create notification
            Notification::createForUser(
                $user->id,
                'success',
                'Booking Berhasil',
                "Booking buku '{$book->title}' berhasil. Silakan ambil buku di perpustakaan dalam 2x24 jam."
            );

            // Log action
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'book_borrowing',
                'description' => "User {$user->name} booked book: {$book->title}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('member.borrowings.index')
                ->with('success', 'Booking berhasil! Silakan ambil buku di perpustakaan dalam 2x24 jam.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Cancel booking
     */
    public function cancelBooking(Borrowing $borrowing)
    {
        $user = auth()->user();

        // Validate ownership
        if ($borrowing->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        // Only allow canceling booked items
        if ($borrowing->status !== 'booked') {
            return back()->with('error', 'Hanya booking yang dapat dibatalkan.');
        }

        try {
            DB::beginTransaction();

            // Return book stock
            $borrowing->book->incrementStock();

            // Update borrowing status
            $borrowing->update(['status' => 'cancelled']);

            // Create notification
            Notification::createForUser(
                $user->id,
                'info',
                'Booking Dibatalkan',
                "Booking buku '{$borrowing->book->title}' telah dibatalkan."
            );

            // Log action
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'cancel_booking',
                'description' => "User {$user->name} cancelled booking for: {$borrowing->book->title}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            return back()->with('success', 'Booking berhasil dibatalkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
