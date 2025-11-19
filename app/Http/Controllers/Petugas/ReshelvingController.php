<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Reshelving;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReshelvingController extends Controller
{
    /**
     * Display reshelving queue
     */
    public function index(Request $request)
    {
        $query = Reshelving::with(['book.location', 'borrowing.user']);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->pending();
            } elseif ($request->status === 'completed') {
                $query->completed();
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('book', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        $reshelvings = $query->latest()->paginate(20);

        // Dashboard/summary variables expected by the view
        $pendingCount = Reshelving::pending()->count();
        $completedToday = Reshelving::completed()->whereDate('reshelved_at', now())->count();
        $monthlyTotal = Reshelving::completed()->whereYear('reshelved_at', now()->year)
            ->whereMonth('reshelved_at', now()->month)
            ->count();

        // Collections used by the view
        $pendingReshelving = Reshelving::pending()->with(['book.location', 'borrowing.user'])->latest()->get();
        $recentlyCompleted = Reshelving::completed()->with(['book.location', 'borrowing.user', 'processedBy'])
            ->whereNotNull('reshelved_at')
            ->latest('reshelved_at')
            ->take(10)
            ->get();

        return view('petugas.reshelving.index', compact(
            'reshelvings',
            'pendingCount',
            'completedToday',
            'monthlyTotal',
            'pendingReshelving',
            'recentlyCompleted'
        ));
    }

    /**
     * Mark book as reshelved
     */
    public function markAsReshelved(Reshelving $reshelving)
    {
        if ($reshelving->isReshelved()) {
            return back()->with('error', 'Buku sudah dikembalikan ke rak.');
        }

        try {
            DB::beginTransaction();

            // Mark as reshelved
            $reshelving->markAsReshelved();

            // Increment book stock
            $reshelving->book->incrementStock();

            // Log action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'reshelve_book',
                'description' => "Book '{$reshelving->book->title}' reshelved to location {$reshelving->book->location->full_name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            return back()->with('success', "Buku '{$reshelving->book->title}' berhasil dikembalikan ke rak {$reshelving->book->location->full_name}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Bulk mark as reshelved
     */
    public function bulkReshelve(Request $request)
    {
        $request->validate([
            'reshelving_ids' => ['required', 'array'],
            'reshelving_ids.*' => ['exists:reshelvings,id'],
        ]);

        try {
            DB::beginTransaction();

            $count = 0;
            foreach ($request->reshelving_ids as $id) {
                $reshelving = Reshelving::find($id);
                if ($reshelving && !$reshelving->isReshelved()) {
                    $reshelving->markAsReshelved();
                    $reshelving->book->incrementStock();
                    $count++;
                }
            }

            // Log action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'bulk_reshelve',
                'description' => "Bulk reshelved {$count} books",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return back()->with('success', "{$count} buku berhasil dikembalikan ke rak.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
