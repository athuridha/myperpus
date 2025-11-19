@extends('layouts.app')

@section('title', 'Manajemen Sirkulasi')
@section('page-title', 'Manajemen Sirkulasi')

@section('sidebar')
<nav class="nav flex-column">
    <a href="{{ route('petugas.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('petugas.circulation.index') }}" class="nav-link active">
        <i class="bi bi-arrow-left-right"></i> Sirkulasi
    </a>
    <a href="{{ route('petugas.fines.index') }}" class="nav-link">
        <i class="bi bi-cash"></i> Denda
    </a>
    <a href="{{ route('petugas.reshelving.index') }}" class="nav-link">
        <i class="bi bi-arrow-clockwise"></i> Reshelving
    </a>
</nav>
@endsection

@section('content')
<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card stat-card primary h-100">
            <div class="card-body text-center">
                <i class="bi bi-qr-code-scan display-1 text-primary mb-3"></i>
                <h5 class="card-title">Scan Peminjaman</h5>
                <p class="card-text text-muted">Scan QR code untuk proses peminjaman buku</p>
                <a href="{{ route('petugas.circulation.scan-borrow') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-camera"></i> Mulai Scan Pinjam
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card stat-card success h-100">
            <div class="card-body text-center">
                <i class="bi bi-arrow-return-left display-1 text-success mb-3"></i>
                <h5 class="card-title">Scan Pengembalian</h5>
                <p class="card-text text-muted">Scan QR code untuk proses pengembalian buku</p>
                <a href="{{ route('petugas.circulation.scan-return') }}" class="btn btn-success btn-lg">
                    <i class="bi bi-camera"></i> Mulai Scan Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Dipinjam Hari Ini</div>
                        <div class="h2">{{ $todayBorrows }}</div>
                    </div>
                    <i class="bi bi-box-arrow-right fs-1 text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Dikembalikan Hari Ini</div>
                        <div class="h2">{{ $todayReturns }}</div>
                    </div>
                    <i class="bi bi-box-arrow-in-left fs-1 text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Booking Aktif</div>
                        <div class="h2">{{ $activeBookings }}</div>
                    </div>
                    <i class="bi bi-bookmark fs-1 text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Terlambat</div>
                        <div class="h2">{{ $overdueCount }}</div>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-clock-history me-2"></i>Transaksi Terbaru Hari Ini
    </div>
    <div class="card-body">
        @if($recentTransactions->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Jenis</th>
                        <th>Peminjam</th>
                        <th>Buku</th>
                        <th>Status</th>
                        <th>Denda</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTransactions as $transaction)
                    <tr>
                        <td>{{ $transaction->created_at->format('H:i') }}</td>
                        <td>
                            @if($transaction->return_date)
                            <span class="badge bg-success">Pengembalian</span>
                            @else
                            <span class="badge bg-primary">Peminjaman</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $transaction->user->name }}</strong><br>
                            <small class="text-muted">{{ $transaction->user->nim_nip }}</small>
                        </td>
                        <td>
                            <strong>{{ $transaction->book->title }}</strong><br>
                            <small class="text-muted">{{ $transaction->book->author }}</small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $transaction->status_color }}">
                                {{ $transaction->status_label }}
                            </span>
                        </td>
                        <td>
                            @if($transaction->fine_amount > 0)
                            <span class="text-danger fw-bold">Rp {{ number_format($transaction->fine_amount, 0, ',', '.') }}</span>
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-center text-muted mb-0">Belum ada transaksi hari ini</p>
        @endif
    </div>
</div>

<!-- Overdue Books Alert -->
@if($overdueBooks->count() > 0)
<div class="card mt-3 border-danger">
    <div class="card-header bg-danger text-white">
        <i class="bi bi-exclamation-triangle me-2"></i>Buku Terlambat yang Perlu Ditindaklanjuti
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Peminjam</th>
                        <th>Buku</th>
                        <th>Jatuh Tempo</th>
                        <th>Keterlambatan</th>
                        <th>Denda</th>
                        <th>Kontak</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overdueBooks as $overdue)
                    <tr>
                        <td>
                            <strong>{{ $overdue->user->name }}</strong><br>
                            <small class="text-muted">{{ $overdue->user->nim_nip }}</small>
                        </td>
                        <td>{{ $overdue->book->title }}</td>
                        <td>{{ $overdue->due_date->format('d M Y') }}</td>
                        <td>
                            <span class="badge bg-danger">{{ $overdue->days_overdue }} hari</span>
                        </td>
                        <td class="text-danger fw-bold">
                            Rp {{ number_format($overdue->calculateFine(), 0, ',', '.') }}
                        </td>
                        <td>
                            @if($overdue->user->phone)
                            <a href="tel:{{ $overdue->user->phone }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-telephone"></i>
                            </a>
                            @endif
                            @if($overdue->user->email)
                            <a href="mailto:{{ $overdue->user->email }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-envelope"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
