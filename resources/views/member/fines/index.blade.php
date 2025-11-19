@extends('layouts.app')

@section('title', 'Denda')
@section('page-title', 'Denda')

@section('sidebar')
<nav class="nav flex-column">
    <a href="{{ route('member.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('member.books.index') }}" class="nav-link">
        <i class="bi bi-book"></i> Katalog Buku
    </a>
    <a href="{{ route('member.borrowings.index') }}" class="nav-link">
        <i class="bi bi-clock-history"></i> Riwayat Peminjaman
    </a>
    <a href="{{ route('member.fines.index') }}" class="nav-link active">
        <i class="bi bi-cash"></i> Denda
    </a>
</nav>
@endsection

@section('content')
<!-- Summary Card -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted">Total Denda Belum Dibayar</div>
                        <div class="h2 mb-0 text-danger">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-danger">
                        <i class="bi bi-exclamation-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('member.fines.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Belum Dibayar</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Fines Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-cash me-2"></i>Daftar Denda
    </div>
    <div class="card-body">
        @if($fines->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Buku</th>
                        <th>Terlambat</th>
                        <th>Jumlah Denda</th>
                        <th>Dibayar</th>
                        <th>Sisa</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fines as $fine)
                    <tr>
                        <td>
                            <strong>{{ $fine->borrowing->book->title }}</strong><br>
                            <small class="text-muted">{{ $fine->borrowing->book->author }}</small>
                        </td>
                        <td>{{ $fine->borrowing->days_overdue }} hari</td>
                        <td class="fw-bold">{{ $fine->formatted_amount }}</td>
                        <td>{{ $fine->formatted_paid_amount }}</td>
                        <td class="fw-bold text-danger">Rp {{ number_format($fine->remaining_amount, 0, ',', '.') }}</td>
                        <td>
                            @if($fine->status === 'paid')
                            <span class="badge bg-success">Lunas</span>
                            @elseif($fine->status === 'pending_verification')
                            <span class="badge bg-warning">Menunggu Verifikasi</span>
                            @else
                            <span class="badge bg-danger">Belum Dibayar</span>
                            @endif
                        </td>
                        <td>
                            @if($fine->status === 'unpaid')
                            <a href="{{ route('member.fines.payment', $fine) }}" class="btn btn-sm btn-primary">
                                Bayar
                            </a>
                            @elseif($fine->status === 'pending_verification')
                            <span class="badge bg-warning">Verifikasi</span>
                            @else
                            <span class="badge bg-success">✓ Lunas</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $fines->links() }}
        </div>
        @else
        <p class="text-center text-muted mb-0">Tidak ada denda</p>
        @endif
    </div>
</div>
@endsection
