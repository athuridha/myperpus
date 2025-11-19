@extends('layouts.app')

@section('title', 'Laporan Denda')
@section('page-title', 'Laporan Denda')

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
<div class="mb-3">
    <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="text-muted small">Total Denda</div>
                <div class="h5">Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="text-muted small">Belum Dibayar</div>
                <div class="h5">Rp {{ number_format($stats['unpaid_amount'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="text-muted small">Sudah Dibayar</div>
                <div class="h5">Rp {{ number_format($stats['paid_amount'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card info">
            <div class="card-body">
                <div class="text-muted small">Total Record</div>
                <div class="h2">{{ $stats['total_fines'] }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.reports.fines') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="date" name="start_date" class="form-control" placeholder="Tanggal Mulai" 
                       value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="end_date" class="form-control" placeholder="Tanggal Akhir" 
                       value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Belum Dibayar</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Sudah Dibayar</option>
                    <option value="pending_verification" {{ request('status') == 'pending_verification' ? 'selected' : '' }}>Pending</option>
                    <option value="reduced" {{ request('status') == 'reduced' ? 'selected' : '' }}>Dikurangi</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Fines Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-list"></i> Data Denda ({{ $fines->total() }} record)
    </div>
    <div class="card-body">
        @if($fines->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Peminjam</th>
                        <th>Buku</th>
                        <th>Jumlah</th>
                        <th>Dibayar</th>
                        <th>Sisa</th>
                        <th>Status</th>
                        <th>Metode</th>
                        <th>Tgl Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fines as $fine)
                    <tr>
                        <td>#{{ $fine->id }}</td>
                        <td>{{ $fine->created_at->format('d/m/Y') }}</td>
                        <td>
                            <strong>{{ $fine->user->name }}</strong><br>
                            <small class="text-muted">{{ $fine->user->nim_nip }}</small>
                        </td>
                        <td>{{ $fine->borrowing->book->title }}</td>
                        <td class="fw-bold">Rp {{ number_format($fine->amount, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($fine->paid_amount, 0, ',', '.') }}</td>
                        <td class="text-danger">Rp {{ number_format($fine->amount - $fine->paid_amount, 0, ',', '.') }}</td>
                        <td>
                            @if($fine->status === 'paid')
                            <span class="badge bg-success">Lunas</span>
                            @elseif($fine->status === 'pending_verification')
                            <span class="badge bg-warning">Pending</span>
                            @elseif($fine->status === 'reduced')
                            <span class="badge bg-info">Dikurangi</span>
                            @else
                            <span class="badge bg-danger">Belum Bayar</span>
                            @endif
                        </td>
                        <td>
                            @if($fine->payment_method)
                            <span class="badge bg-{{ $fine->payment_method == 'cash' ? 'success' : 'info' }}">
                                {{ $fine->payment_method == 'cash' ? 'Tunai' : 'QRIS' }}
                            </span>
                            @else
                            -
                            @endif
                        </td>
                        <td>{{ $fine->payment_date ? $fine->payment_date->format('d/m/Y') : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $fines->links() }}
        </div>
        @else
        <p class="text-center text-muted mb-0">Tidak ada data</p>
        @endif
    </div>
</div>
@endsection
