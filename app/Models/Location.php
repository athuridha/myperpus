<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    /**
     * Get all books in this location
     */
    public function books()
    {
        return $this->hasMany(Book::class);
    }

    /**
     * Get full location name with code
     */
    public function getFullNameAttribute()
    {
        return $this->code . ' - ' . $this->name;
    }

    /**
     * Get count of books in this location
     */
    public function getBooksCountAttribute()
    {
        return $this->books()->count();
    }
}
