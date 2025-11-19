<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\BookController as MemberBookController;
use App\Http\Controllers\Member\BorrowingController as MemberBorrowingController;
use App\Http\Controllers\Member\FineController as MemberFineController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use App\Http\Controllers\Petugas\CirculationController;
use App\Http\Controllers\Petugas\FineController as PetugasFineController;
use App\Http\Controllers\Petugas\ReshelvingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    // Authentication
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Email verification routes
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function () {
        // Email verification handler
        return redirect()->route('member.dashboard')->with('success', 'Email berhasil diverifikasi!');
    })->middleware(['signed'])->name('verification.verify');
});

/*
|--------------------------------------------------------------------------
| Member Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'user.status', 'role:member'])->prefix('member')->name('member.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [MemberDashboardController::class, 'index'])->name('dashboard');

    // Books catalog
    Route::get('/books', [MemberBookController::class, 'index'])->name('books.index');
    Route::get('/books/{book}', [MemberBookController::class, 'show'])->name('books.show');

    // Borrowings
    Route::get('/borrowings', [MemberBorrowingController::class, 'index'])->name('borrowings.index');
    Route::post('/borrowings/book/{book}', [MemberBorrowingController::class, 'book'])->name('borrowings.book');
    Route::post('/borrowings/{borrowing}/cancel', [MemberBorrowingController::class, 'cancelBooking'])->name('borrowings.cancel');

    // Fines
    Route::get('/fines', [MemberFineController::class, 'index'])->name('fines.index');
    Route::get('/fines/{fine}/payment', [MemberFineController::class, 'showPaymentForm'])->name('fines.payment');
    Route::post('/fines/{fine}/payment', [MemberFineController::class, 'submitPayment'])->name('fines.submit-payment');
});

/*
|--------------------------------------------------------------------------
| Petugas Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'user.status', 'role:petugas,admin'])->prefix('petugas')->name('petugas.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->name('dashboard');

    // Circulation Management
    Route::get('/circulation', [CirculationController::class, 'index'])->name('circulation.index');
    Route::get('/circulation/scan-borrow', [CirculationController::class, 'scanBorrow'])->name('circulation.scan-borrow');
    Route::post('/circulation/process-borrow', [CirculationController::class, 'processBorrow'])->name('circulation.process-borrow');
    Route::get('/circulation/scan-return', [CirculationController::class, 'scanReturn'])->name('circulation.scan-return');
    Route::post('/circulation/process-return', [CirculationController::class, 'processReturn'])->name('circulation.process-return');

    // Fine Management
    Route::get('/fines', [PetugasFineController::class, 'index'])->name('fines.index');
    Route::get('/fines/{fine}', [PetugasFineController::class, 'show'])->name('fines.show');
    Route::post('/fines/{fine}/verify', [PetugasFineController::class, 'verifyPayment'])->name('fines.verify');
    Route::post('/fines/{fine}/cash-payment', [PetugasFineController::class, 'processCashPayment'])->name('fines.cash-payment');
    Route::post('/fines/{fine}/reduce', [PetugasFineController::class, 'reduceFine'])->name('fines.reduce');

    // Reshelving Management
    Route::get('/reshelving', [ReshelvingController::class, 'index'])->name('reshelving.index');
    Route::post('/reshelving/{reshelving}/mark-reshelved', [ReshelvingController::class, 'markAsReshelved'])->name('reshelving.mark-reshelved');
    Route::post('/reshelving/bulk-reshelve', [ReshelvingController::class, 'bulkReshelve'])->name('reshelving.bulk-reshelve');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'user.status', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Book Management
    Route::resource('books', AdminBookController::class);
    Route::post('/books/import', [AdminBookController::class, 'import'])->name('books.import');

    // User Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/reject', [UserController::class, 'reject'])->name('users.reject');
    Route::post('/users/{user}/block', [UserController::class, 'block'])->name('users.block');
    Route::post('/users/{user}/unblock', [UserController::class, 'unblock'])->name('users.unblock');

    // Category Management
    Route::resource('categories', CategoryController::class)->except(['show']);

    // Location Management
    Route::resource('locations', LocationController::class)->except(['show']);

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/borrowings', [ReportController::class, 'borrowings'])->name('reports.borrowings');
    Route::get('/reports/fines', [ReportController::class, 'fines'])->name('reports.fines');
    Route::get('/reports/book-popularity', [ReportController::class, 'bookPopularity'])->name('reports.book-popularity');
    Route::get('/reports/user-activity', [ReportController::class, 'userActivity'])->name('reports.user-activity');
    Route::get('/reports/overdue-books', [ReportController::class, 'overdueBooks'])->name('reports.overdue-books');
    Route::get('/reports/monthly-stats', [ReportController::class, 'monthlyStats'])->name('reports.monthly-stats');
    Route::get('/reports/audit-logs', [ReportController::class, 'auditLogs'])->name('reports.audit-logs');
    Route::post('/reports/export-borrowings', [ReportController::class, 'exportBorrowings'])->name('reports.export-borrowings');
    Route::post('/reports/export-fines', [ReportController::class, 'exportFines'])->name('reports.export-fines');
});
