<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'isbn',
        'author',
        'publisher',
        'year',
        'category_id',
        'location_id',
        'description',
        'cover_image',
        'total_stock',
        'available_stock',
        'book_condition',
        'qr_code',
    ];

    protected $casts = [
        'year' => 'integer',
        'total_stock' => 'integer',
        'available_stock' => 'integer',
    ];

    /**
     * Get the category of this book
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the location of this book
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get all borrowings of this book
     */
    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    /**
     * Get active borrowings (borrowed but not returned)
     */
    public function activeBorrowings()
    {
        return $this->hasMany(Borrowing::class)
            ->whereIn('status', ['borrowed', 'overdue'])
            ->whereNull('return_date');
    }

    /**
     * Get reshelving records
     */
    public function reshelvings()
    {
        return $this->hasMany(Reshelving::class);
    }

    /**
     * Check if book is available for borrowing
     */
    public function isAvailable()
    {
        return $this->available_stock > 0 && $this->book_condition === 'baik';
    }

    /**
     * Get cover image URL
     */
    public function getCoverUrlAttribute()
    {
        if ($this->cover_image) {
            return asset('storage/covers/' . $this->cover_image);
        }
        return asset('images/no-cover.png');
    }

    /**
     * Get QR code URL
     */
    public function getQrCodeUrlAttribute()
    {
        if ($this->qr_code) {
            return asset('storage/qrcodes/' . $this->qr_code);
        }
        return null;
    }

    /**
     * Get full book info for QR code
     */
    public function getQrDataAttribute()
    {
        return json_encode([
            'id' => $this->id,
            'isbn' => $this->isbn,
            'title' => $this->title,
            'type' => 'book'
        ]);
    }

    /**
     * Scope for available books
     */
    public function scopeAvailable($query)
    {
        return $query->where('available_stock', '>', 0)
            ->where('book_condition', 'baik');
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('isbn', 'like', "%{$search}%")
                ->orWhere('author', 'like', "%{$search}%")
                ->orWhere('publisher', 'like', "%{$search}%");
        });
    }

    /**
     * Decrease available stock when borrowed
     */
    public function decrementStock()
    {
        if ($this->available_stock > 0) {
            $this->decrement('available_stock');
            return true;
        }
        return false;
    }

    /**
     * Increase available stock when returned
     */
    public function incrementStock()
    {
        if ($this->available_stock < $this->total_stock) {
            $this->increment('available_stock');
            return true;
        }
        return false;
    }
}
