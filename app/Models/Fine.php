<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'borrowing_id',
        'amount',
        'paid_amount',
        'status',
        'payment_method',
        'payment_proof',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    /**
     * Get the user who has this fine
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the borrowing associated with this fine
     */
    public function borrowing()
    {
        return $this->belongsTo(Borrowing::class);
    }

    /**
     * Get remaining amount to be paid
     */
    public function getRemainingAmountAttribute()
    {
        return $this->amount - $this->paid_amount;
    }

    /**
     * Check if fine is fully paid
     */
    public function isPaid()
    {
        return $this->status === 'paid' || $this->paid_amount >= $this->amount;
    }

    /**
     * Check if fine is partially paid
     */
    public function isPartiallyPaid()
    {
        return $this->paid_amount > 0 && $this->paid_amount < $this->amount;
    }

    /**
     * Add payment
     */
    public function addPayment($amount, $method = 'cash', $proof = null)
    {
        $this->paid_amount += $amount;

        if ($this->paid_amount >= $this->amount) {
            $this->status = 'paid';
            $this->payment_date = now();
        }

        $this->payment_method = $method;
        if ($proof) {
            $this->payment_proof = $proof;
        }

        $this->save();

        return $this;
    }

    /**
     * Reduce fine amount (admin/petugas action)
     */
    public function reduceFine($newAmount, $reason)
    {
        if ($newAmount < $this->amount) {
            $this->amount = $newAmount;
            $this->status = 'reduced';
            $this->notes = $reason;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Get payment proof URL
     */
    public function getPaymentProofUrlAttribute()
    {
        if ($this->payment_proof) {
            return asset('storage/payment_proofs/' . $this->payment_proof);
        }
        return null;
    }

    /**
     * Scope for unpaid fines
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    /**
     * Scope for paid fines
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for user's fines
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'unpaid' => 'danger',
            'paid' => 'success',
            'reduced' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Get status label in Indonesian
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'unpaid' => 'Belum Dibayar',
            'paid' => 'Lunas',
            'reduced' => 'Dikurangi',
            default => 'Unknown',
        };
    }

    /**
     * Format amount in Rupiah
     */
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Format paid amount in Rupiah
     */
    public function getFormattedPaidAmountAttribute()
    {
        return 'Rp ' . number_format($this->paid_amount, 0, ',', '.');
    }

    /**
     * Format remaining amount in Rupiah
     */
    public function getFormattedRemainingAmountAttribute()
    {
        return 'Rp ' . number_format($this->remaining_amount, 0, ',', '.');
    }
}
