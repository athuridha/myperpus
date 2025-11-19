<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display categories list
     */
    public function index()
    {
        $categories = Category::withCount('books')->latest()->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show create category form
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store new category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories'],
            'description' => ['nullable', 'string'],
        ]);

        try {
            $category = Category::create($request->all());

            // Log action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'create_category',
                'description' => "Created category: {$category->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.categories.index')
                ->with('success', 'Kategori berhasil ditambahkan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show edit category form
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update category
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,' . $category->id],
            'description' => ['nullable', 'string'],
        ]);

        try {
            $category->update($request->all());

            // Log action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'update_category',
                'description' => "Updated category: {$category->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.categories.index')
                ->with('success', 'Kategori berhasil diupdate.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete category
     */
    public function destroy(Category $category)
    {
        // Check if category has books
        if ($category->books()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus kategori yang memiliki buku.');
        }

        try {
            $categoryName = $category->name;
            $category->delete();

            // Log action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete_category',
                'description' => "Deleted category: {$categoryName}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.categories.index')
                ->with('success', 'Kategori berhasil dihapus.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
