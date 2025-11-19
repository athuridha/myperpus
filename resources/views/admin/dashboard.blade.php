@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Admin')

@section('sidebar')
<nav class="nav flex-column">
    <a href="{{ route('admin.dashboard') }}" class="nav-link active">
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
    <a href="{{ route('admin.reports.index') }}" class="nav-link">
        <i class="bi bi-bar-chart"></i> Laporan
    </a>
</nav>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Total Buku</div>
                        <div class="h2">{{ $stats['total_books'] }}</div>
                        <small class="text-success">{{ $stats['available_books'] }} tersedia</small>
                    </div>
                    <i class="bi bi-book fs-1 text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Total Member</div>
                        <div class="h2">{{ $stats['total_users'] }}</div>
                        <small class="text-warning">{{ $stats['pending_users'] }} pending</small>
                    </div>
                    <i class="bi bi-people fs-1 text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Peminjaman Aktif</div>
                        <div class="h2">{{ $stats['active_borrowings'] }}</div>
                        <small class="text-danger">{{ $stats['overdue_borrowings'] }} terlambat</small>
                    </div>
                    <i class="bi bi-clock-history fs-1 text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Denda Belum Dibayar</div>
                        <div class="h5">Rp {{ number_format($stats['unpaid_fines'], 0, ',', '.') }}</div>
                        <small>dari Rp {{ number_format($stats['total_fines'], 0, ',', '.') }}</small>
                    </div>
                    <i class="bi bi-cash fs-1 text-danger"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart me-2"></i>Statistik Peminjaman (6 Bulan Terakhir)
            </div>
            <div class="card-body">
                <canvas id="borrowingsChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-star me-2"></i>Buku Terpopuler
            </div>
            <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                @foreach($popularBooks as $book)
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <strong>{{ Str::limit($book->title, 30) }}</strong><br>
                        <small class="text-muted">{{ $book->author }}</small>
                    </div>
                    <span class="badge bg-primary">{{ $book->borrowings_count }}x</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Pending Approvals & Recent Activity -->
<div class="row">
    @if($pendingApprovals->count() > 0)
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-warning">
                <i class="bi bi-hourglass-split me-2"></i>Pending Approval User ({{ $pendingApprovals->count() }})
            </div>
            <div class="card-body">
                @foreach($pendingApprovals as $user)
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <strong>{{ $user->name }}</strong><br>
                        <small class="text-muted">
                            {{ $user->email }} | {{ $user->nim_nip }}<br>
                            Mendaftar {{ $user->created_at->diffForHumans() }}
                        </small>
                    </div>
                    <div>
                        <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                <i class="bi bi-check"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.users.reject', $user) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger" title="Reject"
                                    onclick="return confirm('Yakin reject user ini?')">
                                <i class="bi bi-x"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="col-md-{{ $pendingApprovals->count() > 0 ? '6' : '12' }} mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history me-2"></i>Peminjaman Terbaru
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                @foreach($recentBorrowings as $b)
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <strong>{{ $b->book->title }}</strong><br>
                        <small class="text-muted">
                            {{ $b->user->name }} | {{ $b->created_at->diffForHumans() }}
                        </small>
                    </div>
                    <span class="badge bg-{{ $b->status_color }}">{{ $b->status_label }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('borrowingsChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($borrowingsChart['labels']),
        datasets: [{
            label: 'Jumlah Peminjaman',
            data: @json($borrowingsChart['data']),
            borderColor: 'rgb(78, 115, 223)',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>
@endpush
