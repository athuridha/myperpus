<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display book catalog
     */
    public function index(Request $request)
    {
        $query = Book::with(['category', 'location']);

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by location
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        // Filter by availability
        if ($request->filled('available') && $request->available == '1') {
            $query->available();
        }

        $books = $query->paginate(12);
        $categories = Category::all();
        $locations = Location::all();

        return view('member.books.index', compact('books', 'categories', 'locations'));
    }

    /**
     * Display book details
     */
    public function show(Book $book)
    {
        $book->load(['category', 'location']);

        // Get related books from same category
        $relatedBooks = Book::where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->available()
            ->limit(4)
            ->get();

        return view('member.books.show', compact('book', 'relatedBooks'));
    }
}
