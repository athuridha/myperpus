<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Fine;
use App\Models\Reshelving;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display petugas dashboard
     */
    public function index()
    {
        $data = [
            'todayBorrowings' => Borrowing::whereDate('borrow_date', today())
                ->with('user', 'book')
                ->latest()
                ->limit(10)
                ->get(),
            'todayReturns' => Borrowing::whereDate('return_date', today())
                ->with('user', 'book')
                ->latest()
                ->limit(10)
                ->get(),
            'activeBookings' => Borrowing::where('status', 'booked')
                ->with('user', 'book')
                ->latest()
                ->limit(10)
                ->get(),
            'overdueBooks' => Borrowing::overdue()
                ->with('user', 'book')
                ->latest()
                ->limit(10)
                ->get(),
            'pendingReshelving' => Reshelving::pending()
                ->with('book', 'borrowing.user')
                ->latest()
                ->limit(10)
                ->get(),
            'pendingFinePayments' => Fine::where('status', 'pending_verification')
                ->with('user', 'borrowing.book')
                ->latest()
                ->limit(10)
                ->get(),
            'stats' => [
                'total_active_borrowings' => Borrowing::whereIn('status', ['borrowed', 'booked', 'overdue'])->count(),
                'total_overdue' => Borrowing::overdue()->count(),
                'total_pending_reshelving' => Reshelving::pending()->count(),
                'total_pending_payments' => Fine::where('status', 'pending_verification')->count(),
            ],
        ];

        return view('petugas.dashboard', $data);
    }
}
