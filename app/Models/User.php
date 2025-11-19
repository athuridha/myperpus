<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'nim_nip',
        'phone',
        'address',
        'role',
        'status',
        'email_verified_at',
        'max_borrow_limit',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all borrowings of this user
     */
    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    /**
     * Get active borrowings
     */
    public function activeBorrowings()
    {
        return $this->hasMany(Borrowing::class)
            ->whereIn('status', ['borrowed', 'booked', 'overdue'])
            ->whereNull('return_date');
    }

    /**
     * Get all fines of this user
     */
    public function fines()
    {
        return $this->hasMany(Fine::class);
    }

    /**
     * Get unpaid fines
     */
    public function unpaidFines()
    {
        return $this->hasMany(Fine::class)
            ->where('status', 'unpaid');
    }

    /**
     * Get notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get unread notifications
     */
    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get audit logs
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is petugas
     */
    public function isPetugas()
    {
        return $this->role === 'petugas';
    }

    /**
     * Check if user is member
     */
    public function isMember()
    {
        return $this->role === 'member';
    }

    /**
     * Check if user is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if user is blocked
     */
    public function isBlocked()
    {
        return $this->status === 'blocked';
    }

    /**
     * Check if user is pending approval
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if user can borrow
     */
    public function canBorrow()
    {
        if (!$this->isActive() || !$this->isMember()) {
            return false;
        }

        // Check if has unpaid fines
        if ($this->unpaidFines()->exists()) {
            return false;
        }

        // Check borrow limit
        $activeBorrowCount = $this->activeBorrowings()->count();
        return $activeBorrowCount < $this->max_borrow_limit;
    }

    /**
     * Get total unpaid fines amount
     */
    public function getTotalUnpaidFinesAttribute()
    {
        return $this->unpaidFines()->sum('amount');
    }

    /**
     * Get active borrow count
     */
    public function getActiveBorrowCountAttribute()
    {
        return $this->activeBorrowings()->count();
    }

    /**
     * Get remaining borrow quota
     */
    public function getRemainingBorrowQuotaAttribute()
    {
        return max(0, $this->max_borrow_limit - $this->active_borrow_count);
    }

    /**
     * Block user
     */
    public function block($reason = null)
    {
        $this->status = 'blocked';
        $this->save();

        AuditLog::logUserStatusChange($this, 'blocked');

        if ($reason) {
            Notification::createForUser(
                $this->id,
                'info',
                'Akun Diblokir',
                "Akun Anda telah diblokir. Alasan: {$reason}"
            );
        }

        return $this;
    }

    /**
     * Unblock user
     */
    public function unblock()
    {
        $this->status = 'active';
        $this->save();

        AuditLog::logUserStatusChange($this, 'active');

        Notification::createForUser(
            $this->id,
            'info',
            'Akun Dibuka Kembali',
            'Akun Anda telah dibuka kembali. Anda sekarang dapat meminjam buku.'
        );

        return $this;
    }

    /**
     * Approve user
     */
    public function approve()
    {
        $this->status = 'active';
        $this->save();

        AuditLog::logUserApproval($this, true);
        Notification::createApprovalNotification($this->id, true);

        return $this;
    }

    /**
     * Reject user
     */
    public function reject()
    {
        $this->status = 'blocked';
        $this->save();

        AuditLog::logUserApproval($this, false);
        Notification::createApprovalNotification($this->id, false);

        return $this;
    }

    /**
     * Get QR data for user identification
     */
    public function getQrDataAttribute()
    {
        return json_encode([
            'id' => $this->id,
            'nim_nip' => $this->nim_nip,
            'name' => $this->name,
            'type' => 'user'
        ]);
    }

    /**
     * Scope for role
     */
    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope for status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for pending users
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get role label in Indonesian
     */
    public function getRoleLabelAttribute()
    {
        return match ($this->role) {
            'admin' => 'Administrator',
            'petugas' => 'Petugas Sirkulasi',
            'member' => 'Anggota',
            default => 'Unknown',
        };
    }

    /**
     * Get status label in Indonesian
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'active' => 'Aktif',
            'pending' => 'Menunggu Persetujuan',
            'blocked' => 'Diblokir',
            default => 'Unknown',
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'active' => 'success',
            'pending' => 'warning',
            'blocked' => 'danger',
            default => 'secondary',
        };
    }
}
