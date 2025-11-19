<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Borrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'borrow_date',
        'due_date',
        'return_date',
        'status',
        'fine_amount',
        'notes',
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'fine_amount' => 'decimal:2',
    ];

    /**
     * Get the user who borrowed this book
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the borrowed book
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the fine associated with this borrowing
     */
    public function fine()
    {
        return $this->hasOne(Fine::class);
    }

    /**
     * Get reshelving record
     */
    public function reshelving()
    {
        return $this->hasOne(Reshelving::class);
    }

    /**
     * Check if borrowing is overdue
     */
    public function isOverdue()
    {
        if ($this->return_date || !$this->due_date) {
            return false; // Already returned or no due date yet
        }
        return Carbon::now()->greaterThan($this->due_date);
    }

    /**
     * Get days overdue
     */
    public function getDaysOverdueAttribute()
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return Carbon::now()->diffInDays($this->due_date);
    }

    /**
     * Get days until due
     */
    public function getDaysUntilDueAttribute()
    {
        if ($this->return_date || $this->isOverdue() || !$this->due_date) {
            return 0;
        }
        return $this->due_date->diffInDays(Carbon::now());
    }

    /**
     * Calculate fine amount
     */
    public function calculateFine()
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        $finePerDay = config('app.fine_amount_per_day', env('FINE_AMOUNT_PER_DAY', 1000));
        $daysOverdue = $this->days_overdue;

        return $daysOverdue * $finePerDay;
    }

    /**
     * Update status to overdue if past due date
     */
    public function checkAndUpdateOverdue()
    {
        if ($this->isOverdue() && $this->status !== 'returned') {
            $this->update([
                'status' => 'overdue',
                'fine_amount' => $this->calculateFine()
            ]);
            return true;
        }
        return false;
    }

    /**
     * Scope for active borrowings
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['borrowed', 'booked', 'overdue'])
            ->whereNull('return_date');
    }

    /**
     * Scope for overdue borrowings
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->whereNull('return_date');
    }

    /**
     * Scope for returned borrowings
     */
    public function scopeReturned($query)
    {
        return $query->where('status', 'returned')
            ->whereNotNull('return_date');
    }

    /**
     * Scope for user's borrowings
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
            'booked' => 'info',
            'borrowed' => 'primary',
            'overdue' => 'danger',
            'returned' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Get status in Indonesian
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'booked' => 'Dipesan',
            'borrowed' => 'Dipinjam',
            'overdue' => 'Terlambat',
            'returned' => 'Dikembalikan',
            default => 'Unknown',
        };
    }
}
