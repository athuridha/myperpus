<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reshelving extends Model
{
    use HasFactory;

    protected $table = 'reshelving';

    protected $fillable = [
        'book_id',
        'borrowing_id',
        'returned_to_counter_at',
        'reshelved_at',
        'processed_by',
    ];

    protected $casts = [
        'returned_to_counter_at' => 'datetime',
        'reshelved_at' => 'datetime',
    ];

    /**
     * Get the book being reshelved
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the associated borrowing
     */
    public function borrowing()
    {
        return $this->belongsTo(Borrowing::class);
    }

    /**
     * Get the staff who processed this
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Alias for processor relationship to match view naming (processedBy)
     */
    public function processedBy()
    {
        return $this->processor();
    }

    /**
     * Check if book has been reshelved
     */
    public function isReshelved()
    {
        return !is_null($this->reshelved_at);
    }

    /**
     * Mark as reshelved
     */
    public function markAsReshelved()
    {
        $this->reshelved_at = now();
        $this->save();
        return $this;
    }

    /**
     * Get duration in counter (waiting to be reshelved)
     */
    public function getWaitingDurationAttribute()
    {
        if ($this->isReshelved()) {
            return $this->reshelved_at->diffInMinutes($this->returned_to_counter_at);
        }
        return now()->diffInMinutes($this->returned_to_counter_at);
    }

    /**
     * Scope for pending reshelving
     */
    public function scopePending($query)
    {
        return $query->whereNull('reshelved_at');
    }

    /**
     * Scope for completed reshelving
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('reshelved_at');
    }

    /**
     * Get status
     */
    public function getStatusAttribute()
    {
        return $this->isReshelved() ? 'Sudah di Rak' : 'Di Counter';
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        return $this->isReshelved() ? 'success' : 'warning';
    }
}
