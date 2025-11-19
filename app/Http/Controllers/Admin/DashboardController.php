<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Fine;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard with statistics and charts
     */
    public function index()
    {
        // Overview statistics
        $stats = [
            'total_books' => Book::count(),
            'available_books' => Book::where('available_stock', '>', 0)->count(),
            'total_users' => User::where('role', 'member')->count(),
            'active_users' => User::where('role', 'member')->where('status', 'active')->count(),
            'pending_users' => User::where('role', 'member')->where('status', 'pending')->count(),
            'total_borrowings' => Borrowing::count(),
            'active_borrowings' => Borrowing::whereIn('status', ['borrowed', 'booked', 'overdue'])->count(),
            'overdue_borrowings' => Borrowing::overdue()->count(),
            'total_fines' => Fine::sum('amount'),
            'unpaid_fines' => Fine::unpaid()->sum('amount'),
        ];

        // Recent activities
        $recentBorrowings = Borrowing::with(['user', 'book'])
            ->latest()
            ->limit(10)
            ->get();

        $recentUsers = User::where('role', 'member')
            ->latest()
            ->limit(10)
            ->get();

        $pendingApprovals = User::pending()
            ->latest()
            ->get();

        // Chart data - Borrowings per month (last 6 months)
        $borrowingsChart = $this->getBorrowingsChartData();

        // Popular books
        $popularBooks = Book::withCount('borrowings')
            ->orderBy('borrowings_count', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentBorrowings',
            'recentUsers',
            'pendingApprovals',
            'borrowingsChart',
            'popularBooks'
        ));
    }

    /**
     * Get borrowings chart data for last 6 months
     */
    private function getBorrowingsChartData()
    {
        $months = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');

            $count = Borrowing::whereYear('borrow_date', $date->year)
                ->whereMonth('borrow_date', $date->month)
                ->count();

            $data[] = $count;
        }

        return [
            'labels' => $months,
            'data' => $data,
        ];
    }
}
