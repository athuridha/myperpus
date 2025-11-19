@extends('layouts.app')

@section('title', 'Laporan & Statistik')
@section('page-title', 'Laporan & Statistik')

@section('sidebar')
<nav class="nav flex-column">
    <a href="{{ route('admin.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('admin.books.index') }}" class="nav-link">
        <i class="bi bi-book"></i> Kelola Buku
    </a>
    <a href="{{ route('admin.users.index') }}" class="nav-link">
        <i class="bi bi-people"></i> Kelola User
    </a>
    <a href="{{ route('admin.categories.index') }}" class="nav-link">
        <i class="bi bi-tags"></i> Kategori
    </a>
    <a href="{{ route('admin.locations.index') }}" class="nav-link">
        <i class="bi bi-geo-alt"></i> Lokasi Rak
    </a>
    <a href="{{ route('admin.reports.index') }}" class="nav-link active">
        <i class="bi bi-bar-chart"></i> Laporan
    </a>
</nav>
@endsection

@section('content')
<div class="row">
    <!-- Borrowing Reports -->
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-book display-1 text-primary mb-3"></i>
                <h5 class="card-title">Laporan Peminjaman</h5>
                <p class="card-text text-muted">Statistik dan data peminjaman buku</p>
                <a href="{{ route('admin.reports.borrowings') }}" class="btn btn-primary">
                    <i class="bi bi-file-earmark-text"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>

    <!-- Fine Reports -->
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-cash display-1 text-danger mb-3"></i>
                <h5 class="card-title">Laporan Denda</h5>
                <p class="card-text text-muted">Data denda dan pembayaran</p>
                <a href="{{ route('admin.reports.fines') }}" class="btn btn-danger">
                    <i class="bi bi-file-earmark-text"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>

    <!-- Book Popularity -->
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-star display-1 text-warning mb-3"></i>
                <h5 class="card-title">Popularitas Buku</h5>
                <p class="card-text text-muted">Buku terpopuler & jarang dipinjam</p>
                <a href="{{ route('admin.reports.book-popularity') }}" class="btn btn-warning">
                    <i class="bi bi-file-earmark-text"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>

    <!-- User Activity -->
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-people display-1 text-success mb-3"></i>
                <h5 class="card-title">Aktivitas User</h5>
                <p class="card-text text-muted">Member aktif & tidak aktif</p>
                <a href="{{ route('admin.reports.user-activity') }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-text"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>

    <!-- Overdue Books -->
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 border-danger">
            <div class="card-body text-center">
                <i class="bi bi-exclamation-triangle display-1 text-danger mb-3"></i>
                <h5 class="card-title">Buku Terlambat</h5>
                <p class="card-text text-muted">Daftar buku yang terlambat dikembalikan</p>
                <a href="{{ route('admin.reports.overdue-books') }}" class="btn btn-danger">
                    <i class="bi bi-file-earmark-text"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>

    <!-- Monthly Statistics -->
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-calendar-month display-1 text-info mb-3"></i>
                <h5 class="card-title">Statistik Bulanan</h5>
                <p class="card-text text-muted">Ringkasan statistik per bulan</p>
                <a href="{{ route('admin.reports.monthly-stats') }}" class="btn btn-info">
                    <i class="bi bi-file-earmark-text"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>

    <!-- Audit Logs -->
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-shield-lock display-1 text-secondary mb-3"></i>
                <h5 class="card-title">Audit Log</h5>
                <p class="card-text text-muted">Log aktivitas sistem</p>
                <a href="{{ route('admin.reports.audit-logs') }}" class="btn btn-secondary">
                    <i class="bi bi-file-earmark-text"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Export -->
<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-download me-2"></i>Export Laporan
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <h6>Export Data Peminjaman</h6>
                <form action="{{ route('admin.reports.export-borrowings') }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-md-4">
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-file-excel"></i> Export Excel
                        </button>
                    </div>
                </form>
            </div>

            <div class="col-md-6 mb-3">
                <h6>Export Data Denda</h6>
                <form action="{{ route('admin.reports.export-fines') }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-md-4">
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-file-excel"></i> Export Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
