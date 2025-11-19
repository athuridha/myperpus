<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get all books in this category
     */
    public function books()
    {
        return $this->hasMany(Book::class);
    }

    /**
     * Get count of books in this category
     */
    public function getBooksCountAttribute()
    {
        return $this->books()->count();
    }

    /**
     * Get available books in this category
     */
    public function getAvailableBooksCountAttribute()
    {
        return $this->books()->where('available_stock', '>', 0)->count();
    }
}
