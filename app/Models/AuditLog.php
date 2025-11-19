<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the user who performed this action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an action
     */
    public static function logAction($action, $description, $userId = null)
    {
        return static::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log login
     */
    public static function logLogin($userId)
    {
        return static::logAction('login', 'User logged in', $userId);
    }

    /**
     * Log logout
     */
    public static function logLogout($userId)
    {
        return static::logAction('logout', 'User logged out', $userId);
    }

    /**
     * Log borrowing
     */
    public static function logBorrowing($borrowing)
    {
        return static::logAction(
            'borrow',
            "Borrowed book: {$borrowing->book->title} (ID: {$borrowing->book_id})",
            $borrowing->user_id
        );
    }

    /**
     * Log return
     */
    public static function logReturn($borrowing)
    {
        return static::logAction(
            'return',
            "Returned book: {$borrowing->book->title} (ID: {$borrowing->book_id})",
            $borrowing->user_id
        );
    }

    /**
     * Log book creation
     */
    public static function logBookCreated($book)
    {
        return static::logAction(
            'add_book',
            "Added new book: {$book->title} (ISBN: {$book->isbn})"
        );
    }

    /**
     * Log book update
     */
    public static function logBookUpdated($book)
    {
        return static::logAction(
            'edit_book',
            "Updated book: {$book->title} (ID: {$book->id})"
        );
    }

    /**
     * Log book deletion
     */
    public static function logBookDeleted($book)
    {
        return static::logAction(
            'delete_book',
            "Deleted book: {$book->title} (ID: {$book->id})"
        );
    }

    /**
     * Log user approval
     */
    public static function logUserApproval($user, $approved = true)
    {
        $status = $approved ? 'approved' : 'rejected';
        return static::logAction(
            'user_approval',
            "User {$user->name} (ID: {$user->id}) was {$status}"
        );
    }

    /**
     * Log user status change
     */
    public static function logUserStatusChange($user, $newStatus)
    {
        return static::logAction(
            'user_status_change',
            "User {$user->name} (ID: {$user->id}) status changed to {$newStatus}"
        );
    }

    /**
     * Log fine payment
     */
    public static function logFinePayment($fine)
    {
        return static::logAction(
            'fine_payment',
            "Fine payment received: Rp " . number_format($fine->paid_amount, 0, ',', '.') . " for borrowing ID: {$fine->borrowing_id}",
            $fine->user_id
        );
    }

    /**
     * Scope for specific action
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for user's logs
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get action label in Indonesian
     */
    public function getActionLabelAttribute()
    {
        return match ($this->action) {
            'login' => 'Login',
            'logout' => 'Logout',
            'borrow' => 'Meminjam Buku',
            'return' => 'Mengembalikan Buku',
            'add_book' => 'Menambah Buku',
            'edit_book' => 'Mengedit Buku',
            'delete_book' => 'Menghapus Buku',
            'user_approval' => 'Persetujuan User',
            'user_status_change' => 'Perubahan Status User',
            'fine_payment' => 'Pembayaran Denda',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Get action color
     */
    public function getActionColorAttribute()
    {
        return match ($this->action) {
            'login' => 'success',
            'logout' => 'secondary',
            'borrow' => 'primary',
            'return' => 'info',
            'add_book' => 'success',
            'edit_book' => 'warning',
            'delete_book' => 'danger',
            'user_approval' => 'success',
            'user_status_change' => 'warning',
            'fine_payment' => 'success',
            default => 'secondary',
        };
    }
}
