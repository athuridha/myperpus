<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display member dashboard
     */
    public function index()
    {
        $user = auth()->user();

        $data = [
            'activeBorrowings' => $user->activeBorrowings()->with('book')->get(),
            'totalBorrowed' => $user->borrowings()->count(),
            'activeBorrowCount' => $user->active_borrow_count,
            'remainingQuota' => $user->remaining_borrow_quota,
            'unpaidFines' => $user->unpaidFines()->with('borrowing.book')->get(),
            'totalUnpaidFines' => $user->total_unpaid_fines,
            'recentNotifications' => $user->notifications()->limit(5)->get(),
            'unreadNotificationCount' => $user->unreadNotifications()->count(),
        ];

        return view('member.dashboard', $data);
    }
}
