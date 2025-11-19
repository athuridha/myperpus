<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Get the user who owns this notification
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if notification is read
     */
    public function isRead()
    {
        return !is_null($this->read_at);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        if (!$this->isRead()) {
            $this->read_at = now();
            $this->save();
        }
        return $this;
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread()
    {
        $this->read_at = null;
        $this->save();
        return $this;
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope for user's notifications
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for notification type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get icon based on type
     */
    public function getIconAttribute()
    {
        return match ($this->type) {
            'deadline' => 'calendar-warning',
            'overdue' => 'alert-triangle',
            'approved' => 'check-circle',
            'rejected' => 'x-circle',
            'payment' => 'credit-card',
            'reminder' => 'bell',
            'info' => 'info',
            default => 'bell',
        };
    }

    /**
     * Get color based on type
     */
    public function getColorAttribute()
    {
        return match ($this->type) {
            'deadline' => 'warning',
            'overdue' => 'danger',
            'approved' => 'success',
            'rejected' => 'danger',
            'payment' => 'info',
            'reminder' => 'primary',
            'info' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Create notification for user
     */
    public static function createForUser($userId, $type, $title, $message)
    {
        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
        ]);
    }

    /**
     * Create deadline reminder notification
     */
    public static function createDeadlineReminder($userId, $borrowing)
    {
        return static::createForUser(
            $userId,
            'deadline',
            'Pengingat Deadline Pengembalian',
            "Buku '{$borrowing->book->title}' harus dikembalikan pada {$borrowing->due_date->format('d M Y')}"
        );
    }

    /**
     * Create overdue notification
     */
    public static function createOverdueNotification($userId, $borrowing)
    {
        return static::createForUser(
            $userId,
            'overdue',
            'Buku Terlambat Dikembalikan',
            "Buku '{$borrowing->book->title}' sudah melewati deadline. Denda: Rp " . number_format($borrowing->fine_amount, 0, ',', '.')
        );
    }

    /**
     * Create approval notification
     */
    public static function createApprovalNotification($userId, $approved = true)
    {
        if ($approved) {
            return static::createForUser(
                $userId,
                'approved',
                'Akun Disetujui',
                'Selamat! Akun Anda telah disetujui. Anda sekarang dapat meminjam buku.'
            );
        } else {
            return static::createForUser(
                $userId,
                'rejected',
                'Akun Ditolak',
                'Maaf, pendaftaran akun Anda ditolak. Silakan hubungi admin untuk informasi lebih lanjut.'
            );
        }
    }

    /**
     * Create payment confirmation notification
     */
    public static function createPaymentConfirmation($userId, $fine)
    {
        return static::createForUser(
            $userId,
            'payment',
            'Pembayaran Denda Berhasil',
            "Pembayaran denda sebesar Rp " . number_format($fine->paid_amount, 0, ',', '.') . " telah diterima."
        );
    }
}
