<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\User;
use App\Models\Borrowing;
use App\Models\Fine;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index()
    {
        return view('admin.reports.index');
    }

    /**
     * Borrowing report
     */
    public function borrowings(Request $request)
    {
        $query = Borrowing::with(['user', 'book']);

        // Date range filter
        if ($request->filled('start_date')) {
            $query->whereDate('borrow_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('borrow_date', '<=', $request->end_date);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $borrowings = $query->latest('borrow_date')->paginate(50);

        // Statistics
        $totalBorrowings = Borrowing::count();
        $activeBorrowings = Borrowing::whereIn('status', ['borrowed', 'booked', 'overdue'])->count();
        $returnedBorrowings = Borrowing::where('status', 'returned')->count();
        $overdueBorrowings = Borrowing::where('status', 'overdue')->count();

        return view('admin.reports.borrowings', compact(
            'borrowings', 
            'totalBorrowings', 
            'activeBorrowings', 
            'returnedBorrowings', 
            'overdueBorrowings'
        ));
    }

    /**
     * Fine report
     */
    public function fines(Request $request)
    {
        $query = Fine::with(['user', 'borrowing.book']);

        // Date range filter
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'unpaid') {
                $query->unpaid();
            } elseif ($request->status === 'paid') {
                $query->paid();
            }
        }

        $fines = $query->latest()->paginate(50);

        // Statistics
        $stats = [
            'total_amount' => (clone $query)->sum('amount'),
            'paid_amount' => (clone $query)->where('status', 'paid')->sum('paid_amount'),
            'unpaid_amount' => (clone $query)->where('status', 'unpaid')->sum('amount'),
            'total_fines' => (clone $query)->count(),
        ];

        return view('admin.reports.fines', compact('fines', 'stats'));
    }

    /**
     * Book popularity report
     */
    public function bookPopularity(Request $request)
    {
        $query = Book::withCount('borrowings')
            ->with('category');

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $books = $query->orderBy('borrowings_count', 'desc')
            ->paginate(50);

        return view('admin.reports.book-popularity', compact('books'));
    }

    /**
     * User activity report
     */
    public function userActivity(Request $request)
    {
        $query = User::where('role', 'member')
            ->withCount(['borrowings', 'fines']);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('borrowings_count', 'desc')
            ->paginate(50);

        return view('admin.reports.user-activity', compact('users'));
    }

    /**
     * Overdue books report
     */
    public function overdueBooks()
    {
        $overdueBooks = Borrowing::overdue()
            ->with(['user', 'book'])
            ->orderBy('due_date', 'asc')
            ->get();

        $stats = [
            'total_overdue' => $overdueBooks->count(),
            'total_potential_fines' => $overdueBooks->sum(function ($borrowing) {
                return $borrowing->calculateFine();
            }),
        ];

        return view('admin.reports.overdue-books', compact('overdueBooks', 'stats'));
    }

    /**
     * Monthly statistics
     */
    public function monthlyStats(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $months = [];
        $borrowingsData = [];
        $returnsData = [];
        $finesData = [];

        for ($month = 1; $month <= 12; $month++) {
            $months[] = Carbon::create($year, $month, 1)->format('M');

            // Borrowings count
            $borrowingsData[] = Borrowing::whereYear('borrow_date', $year)
                ->whereMonth('borrow_date', $month)
                ->count();

            // Returns count
            $returnsData[] = Borrowing::whereYear('return_date', $year)
                ->whereMonth('return_date', $month)
                ->count();

            // Fines amount
            $finesData[] = Fine::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('amount');
        }

        $chartData = [
            'labels' => $months,
            'borrowings' => $borrowingsData,
            'returns' => $returnsData,
            'fines' => $finesData,
        ];

        // Year-to-date stats
        $ytdStats = [
            'total_borrowings' => Borrowing::whereYear('borrow_date', $year)->count(),
            'total_returns' => Borrowing::whereYear('return_date', $year)->whereNotNull('return_date')->count(),
            'total_fines' => Fine::whereYear('created_at', $year)->sum('amount'),
            'new_members' => User::where('role', 'member')->whereYear('created_at', $year)->count(),
        ];

        return view('admin.reports.monthly-stats', compact('chartData', 'ytdStats', 'year'));
    }

    /**
     * Audit log report
     */
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user');

        // Date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Action filter
        if ($request->filled('action')) {
            $query->action($request->action);
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        $logs = $query->latest()->paginate(50);

        return view('admin.reports.audit-logs', compact('logs'));
    }

    /**
     * Export borrowings report to Excel
     */
    public function exportBorrowings(Request $request)
    {
        // This would use a package like maatwebsite/excel
        // Implementation depends on the package being installed
        return back()->with('info', 'Export feature akan diimplementasikan dengan package maatwebsite/excel');
    }

    /**
     * Export fines report to Excel
     */
    public function exportFines(Request $request)
    {
        // This would use a package like maatwebsite/excel
        return back()->with('info', 'Export feature akan diimplementasikan dengan package maatwebsite/excel');
    }
}
