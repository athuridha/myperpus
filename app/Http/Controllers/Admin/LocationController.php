<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display locations list
     */
    public function index()
    {
        $locations = Location::withCount('books')->latest()->paginate(20);

        return view('admin.locations.index', compact('locations'));
    }

    /**
     * Show create location form
     */
    public function create()
    {
        return view('admin.locations.create');
    }

    /**
     * Store new location
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:locations'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        try {
            $location = Location::create($request->all());

            // Log action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'create_location',
                'description' => "Created location: {$location->full_name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.locations.index')
                ->with('success', 'Lokasi berhasil ditambahkan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show edit location form
     */
    public function edit(Location $location)
    {
        return view('admin.locations.edit', compact('location'));
    }

    /**
     * Update location
     */
    public function update(Request $request, Location $location)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:locations,code,' . $location->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        try {
            $location->update($request->all());

            // Log action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'update_location',
                'description' => "Updated location: {$location->full_name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.locations.index')
                ->with('success', 'Lokasi berhasil diupdate.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete location
     */
    public function destroy(Location $location)
    {
        // Check if location has books
        if ($location->books()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus lokasi yang memiliki buku.');
        }

        try {
            $locationName = $location->full_name;
            $location->delete();

            // Log action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete_location',
                'description' => "Deleted location: {$locationName}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.locations.index')
                ->with('success', 'Lokasi berhasil dihapus.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
