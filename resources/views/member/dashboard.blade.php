@extends('layouts.app')

@section('title', 'Dashboard Member')
@section('page-title', 'Dashboard')

@section('sidebar')
<nav class="nav flex-column">
    <a href="{{ route('member.dashboard') }}" class="nav-link active">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('member.books.index') }}" class="nav-link">
        <i class="bi bi-book"></i> Katalog Buku
    </a>
    <a href="{{ route('member.borrowings.index') }}" class="nav-link">
        <i class="bi bi-clock-history"></i> Riwayat Peminjaman
    </a>
    <a href="{{ route('member.fines.index') }}" class="nav-link">
        <i class="bi bi-cash"></i> Denda
    </a>
</nav>
@endsection

@section('content')
<!-- User Status Alert -->
@if(auth()->user()->isPending())
<div class="alert alert-warning" role="alert">
    <i class="bi bi-hourglass-split me-2"></i>
    <strong>Akun Anda sedang menunggu persetujuan administrator.</strong>
    Anda dapat melihat katalog buku, tetapi belum dapat melakukan peminjaman.
</div>
@endif

@if(auth()->user()->unpaidFines()->exists())
<div class="alert alert-danger" role="alert">
    <i class="bi bi-exclamation-circle me-2"></i>
    <strong>Anda memiliki denda yang belum dibayar sebesar Rp {{ number_format($totalUnpaidFines, 0, ',', '.') }}</strong>
    Silakan lunasi denda untuk dapat melakukan peminjaman.
    <a href="{{ route('member.fines.index') }}" class="alert-link">Lihat Denda</a>
</div>
@endif

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Peminjaman Aktif</div>
                        <div class="h2 mb-0">{{ $activeBorrowCount }}</div>
                    </div>
                    <div class="text-primary">
                        <i class="bi bi-book fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Kuota Tersisa</div>
                        <div class="h2 mb-0">{{ $remainingQuota }}</div>
                    </div>
                    <div class="text-success">
                        <i class="bi bi-check-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Total Peminjaman</div>
                        <div class="h2 mb-0">{{ $totalBorrowed }}</div>
                    </div>
                    <div class="text-warning">
                        <i class="bi bi-clock-history fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Denda Belum Bayar</div>
                        <div class="h5 mb-0">Rp {{ number_format($totalUnpaidFines, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-danger">
                        <i class="bi bi-cash fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Borrowings -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-book me-2"></i>Peminjaman Aktif</span>
        <a href="{{ route('member.borrowings.index') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
    </div>
    <div class="card-body">
        @if($activeBorrowings->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeBorrowings as $borrowing)
                    <tr>
                        <td>
                            <strong>{{ $borrowing->book->title }}</strong><br>
                            <small class="text-muted">{{ $borrowing->book->author }}</small>
                        </td>
                        <td>{{ $borrowing->borrow_date ? $borrowing->borrow_date->format('d/m/Y') : '-' }}</td>
                        <td>{{ $borrowing->due_date ? $borrowing->due_date->format('d/m/Y') : '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $borrowing->status_color }}">
                                {{ $borrowing->status_label }}
                            </span>
                            @if($borrowing->isOverdue())
                            <br><small class="text-danger">Terlambat {{ $borrowing->days_overdue }} hari</small>
                            @endif
                        </td>
                        <td>
                            @if($borrowing->status === 'booked')
                            <form action="{{ route('member.borrowings.cancel', $borrowing) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Yakin ingin membatalkan booking?')">
                                    Batal
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-center text-muted mb-0">Tidak ada peminjaman aktif</p>
        @endif
    </div>
</div>

<!-- Unpaid Fines -->
@if($unpaidFines->count() > 0)
<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-cash me-2"></i>Denda Belum Dibayar</span>
        <a href="{{ route('member.fines.index') }}" class="btn btn-sm btn-danger">Bayar Sekarang</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Buku</th>
                        <th>Jumlah Denda</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($unpaidFines as $fine)
                    <tr>
                        <td>
                            <strong>{{ $fine->borrowing->book->title }}</strong><br>
                            <small class="text-muted">Terlambat {{ $fine->borrowing->days_overdue }} hari</small>
                        </td>
                        <td class="fw-bold text-danger">Rp {{ number_format($fine->amount, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-{{ $fine->status === 'pending_verification' ? 'warning' : 'danger' }}">
                                {{ $fine->status === 'pending_verification' ? 'Menunggu Verifikasi' : 'Belum Dibayar' }}
                            </span>
                        </td>
                        <td>
                            @if($fine->status === 'unpaid')
                            <a href="{{ route('member.fines.payment', $fine) }}" class="btn btn-sm btn-primary">
                                Bayar
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

<!-- Recent Notifications -->
<div class="card mt-3">
    <div class="card-header">
        <i class="bi bi-bell me-2"></i>Notifikasi Terbaru
        @if($unreadNotificationCount > 0)
        <span class="badge bg-danger ms-2">{{ $unreadNotificationCount }}</span>
        @endif
    </div>
    <div class="card-body">
        @if($recentNotifications->count() > 0)
        @foreach($recentNotifications as $notification)
        <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
            <i class="bi bi-{{ $notification->icon }} text-{{ $notification->color }} fs-4 me-3"></i>
            <div class="flex-grow-1">
                <h6 class="mb-1">{{ $notification->title }}</h6>
                <p class="mb-1 small">{{ $notification->message }}</p>
                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
            </div>
        </div>
        @endforeach
        @else
        <p class="text-center text-muted mb-0">Tidak ada notifikasi</p>
        @endif
    </div>
</div>
@endsection
