@extends('layouts.app')

@section('title', 'Dashboard Petugas')
@section('page-title', 'Dashboard Petugas')

@section('sidebar')
<nav class="nav flex-column">
    <a href="{{ route('petugas.dashboard') }}" class="nav-link active">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('petugas.circulation.scan-borrow') }}" class="nav-link">
        <i class="bi bi-qr-code-scan"></i> Scan Pinjam
    </a>
    <a href="{{ route('petugas.circulation.scan-return') }}" class="nav-link">
        <i class="bi bi-qr-code"></i> Scan Kembali
    </a>
    <a href="{{ route('petugas.circulation.index') }}" class="nav-link">
        <i class="bi bi-list-ul"></i> Daftar Sirkulasi
    </a>
    <a href="{{ route('petugas.fines.index') }}" class="nav-link">
        <i class="bi bi-cash"></i> Kelola Denda
    </a>
    <a href="{{ route('petugas.reshelving.index') }}" class="nav-link">
        <i class="bi bi-archive"></i> Reshelving
    </a>
</nav>
@endsection

@section('content')
<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Peminjaman Aktif</div>
                        <div class="h2">{{ $stats['total_active_borrowings'] }}</div>
                    </div>
                    <i class="bi bi-book fs-1 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Terlambat</div>
                        <div class="h2">{{ $stats['total_overdue'] }}</div>
                    </div>
                    <i class="bi bi-exclamation-circle fs-1 text-danger"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Pending Reshelving</div>
                        <div class="h2">{{ $stats['total_pending_reshelving'] }}</div>
                    </div>
                    <i class="bi bi-archive fs-1 text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Pembayaran Pending</div>
                        <div class="h2">{{ $stats['total_pending_payments'] }}</div>
                    </div>
                    <i class="bi bi-cash-stack fs-1 text-info"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Today's Borrowings -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-check me-2"></i>Peminjaman Hari Ini
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                @forelse($todayBorrowings as $b)
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <div class="flex-grow-1">
                        <strong>{{ $b->book->title }}</strong><br>
                        <small class="text-muted">{{ $b->user->name }} ({{ $b->user->nim_nip }})</small>
                    </div>
                    <span class="badge bg-{{ $b->status_color }}">{{ $b->status_label }}</span>
                </div>
                @empty
                <p class="text-center text-muted">Belum ada peminjaman hari ini</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Today's Returns -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-arrow-return-left me-2"></i>Pengembalian Hari Ini
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                @forelse($todayReturns as $b)
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <div class="flex-grow-1">
                        <strong>{{ $b->book->title }}</strong><br>
                        <small class="text-muted">{{ $b->user->name }} ({{ $b->user->nim_nip }})</small>
                    </div>
                    <span class="badge bg-success">Dikembalikan</span>
                </div>
                @empty
                <p class="text-center text-muted">Belum ada pengembalian hari ini</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Active Bookings -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bookmark me-2"></i>Booking Aktif
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                @forelse($activeBookings as $b)
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <div class="flex-grow-1">
                        <strong>{{ $b->book->title }}</strong><br>
                        <small class="text-muted">{{ $b->user->name }} ({{ $b->user->nim_nip }})</small><br>
                        <small class="text-muted">{{ $b->created_at->diffForHumans() }}</small>
                    </div>
                    <span class="badge bg-warning">Booking</span>
                </div>
                @empty
                <p class="text-center text-muted">Tidak ada booking</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Overdue Books -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle me-2"></i>Buku Terlambat
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                @forelse($overdueBooks as $b)
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <div class="flex-grow-1">
                        <strong>{{ $b->book->title }}</strong><br>
                        <small class="text-muted">{{ $b->user->name }} ({{ $b->user->nim_nip }})</small><br>
                        <small class="text-danger">Terlambat {{ $b->days_overdue }} hari</small>
                    </div>
                </div>
                @empty
                <p class="text-center text-muted">Tidak ada buku terlambat</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Pending Reshelving & Payment Verification -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-archive me-2"></i>Pending Reshelving
            </div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                @forelse($pendingReshelving as $r)
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <div class="flex-grow-1">
                        <strong>{{ $r->book->title }}</strong><br>
                        <small class="text-muted">Lokasi: {{ $r->book->location->full_name }}</small>
                    </div>
                    <a href="{{ route('petugas.reshelving.index') }}" class="btn btn-sm btn-primary">Proses</a>
                </div>
                @empty
                <p class="text-center text-muted">Tidak ada buku pending reshelving</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-cash me-2"></i>Pembayaran Pending Verifikasi
            </div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                @forelse($pendingFinePayments as $f)
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <div class="flex-grow-1">
                        <strong>{{ $f->user->name }}</strong><br>
                        <small class="text-muted">{{ $f->borrowing->book->title }}</small><br>
                        <strong class="text-danger">Rp {{ number_format($f->amount, 0, ',', '.') }}</strong>
                    </div>
                    <a href="{{ route('petugas.fines.show', $f) }}" class="btn btn-sm btn-warning">Verifikasi</a>
                </div>
                @empty
                <p class="text-center text-muted">Tidak ada pembayaran pending</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
