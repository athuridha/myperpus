@extends('layouts.app')

@section('title', 'Manajemen Denda')
@section('page-title', 'Manajemen Denda')

@section('sidebar')
<nav class="nav flex-column">
    <a href="{{ route('petugas.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('petugas.circulation.index') }}" class="nav-link">
        <i class="bi bi-arrow-left-right"></i> Sirkulasi
    </a>
    <a href="{{ route('petugas.fines.index') }}" class="nav-link active">
        <i class="bi bi-cash"></i> Denda
    </a>
    <a href="{{ route('petugas.reshelving.index') }}" class="nav-link">
        <i class="bi bi-arrow-clockwise"></i> Reshelving
    </a>
</nav>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Total Denda</div>
                        <div class="h5">Rp {{ number_format($totalFines, 0, ',', '.') }}</div>
                    </div>
                    <i class="bi bi-cash-stack fs-1 text-danger"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Belum Dibayar</div>
                        <div class="h5">Rp {{ number_format($unpaidFines, 0, ',', '.') }}</div>
                    </div>
                    <i class="bi bi-exclamation-circle fs-1 text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card stat-card info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Pending Verifikasi</div>
                        <div class="h2">{{ $pendingVerification }}</div>
                    </div>
                    <i class="bi bi-hourglass-split fs-1 text-info"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Sudah Dibayar</div>
                        <div class="h5">Rp {{ number_format($paidFines, 0, ',', '.') }}</div>
                    </div>
                    <i class="bi bi-check-circle fs-1 text-success"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('petugas.fines.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari nama/NIM/NIP..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Belum Dibayar</option>
                    <option value="pending_verification" {{ request('status') == 'pending_verification' ? 'selected' : '' }}>Pending Verifikasi</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Sudah Dibayar</option>
                    <option value="reduced" {{ request('status') == 'reduced' ? 'selected' : '' }}>Dikurangi</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="payment_method" class="form-select">
                    <option value="">Semua Metode</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
                    <option value="qris" {{ request('payment_method') == 'qris' ? 'selected' : '' }}>QRIS</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Pending Verification Alert -->
@if($finesNeedVerification->count() > 0)
<div class="card mb-4 border-warning">
    <div class="card-header bg-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>Denda yang Perlu Diverifikasi ({{ $finesNeedVerification->count() }})
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Peminjam</th>
                        <th>Buku</th>
                        <th>Jumlah</th>
                        <th>Metode</th>
                        <th>Bukti</th>
                        <th>Upload</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($finesNeedVerification as $fine)
                    <tr>
                        <td>
                            <strong>{{ $fine->user->name }}</strong><br>
                            <small class="text-muted">{{ $fine->user->nim_nip }}</small>
                        </td>
                        <td>{{ $fine->borrowing->book->title }}</td>
                        <td class="fw-bold">Rp {{ number_format($fine->amount, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-{{ $fine->payment_method == 'cash' ? 'success' : 'info' }}">
                                {{ $fine->payment_method == 'cash' ? 'Tunai' : 'QRIS' }}
                            </span>
                        </td>
                        <td>
                            @if($fine->payment_proof)
                            <a href="{{ asset('storage/payment-proofs/' . $fine->payment_proof) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-image"></i> Lihat
                            </a>
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $fine->payment_date ? $fine->payment_date->diffForHumans() : '-' }}</small>
                        </td>
                        <td>
                            <a href="{{ route('petugas.fines.show', $fine) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i> Verifikasi
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Fines List -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-list"></i> Daftar Denda ({{ $fines->total() }} record)
    </div>
    <div class="card-body">
        @if($fines->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Peminjam</th>
                        <th>Buku</th>
                        <th>Jumlah</th>
                        <th>Dibayar</th>
                        <th>Sisa</th>
                        <th>Status</th>
                        <th>Metode</th>
                        <th>Tanggal Bayar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fines as $fine)
                    <tr>
                        <td>#{{ $fine->id }}</td>
                        <td>
                            <strong>{{ $fine->user->name }}</strong><br>
                            <small class="text-muted">{{ $fine->user->nim_nip }}</small>
                        </td>
                        <td>
                            <strong>{{ $fine->borrowing->book->title }}</strong><br>
                            <small class="text-muted">{{ $fine->borrowing->book->author }}</small>
                        </td>
                        <td class="fw-bold">Rp {{ number_format($fine->amount, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($fine->paid_amount, 0, ',', '.') }}</td>
                        <td class="text-danger">Rp {{ number_format($fine->amount - $fine->paid_amount, 0, ',', '.') }}</td>
                        <td>
                            @if($fine->status === 'paid')
                            <span class="badge bg-success">Lunas</span>
                            @elseif($fine->status === 'pending_verification')
                            <span class="badge bg-warning">Pending Verifikasi</span>
                            @elseif($fine->status === 'reduced')
                            <span class="badge bg-info">Dikurangi</span>
                            @else
                            <span class="badge bg-danger">Belum Dibayar</span>
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
                        <td>{{ $fine->payment_date ? $fine->payment_date->format('d M Y') : '-' }}</td>
                        <td>
                            <a href="{{ route('petugas.fines.show', $fine) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $fines->links() }}
        </div>
        @else
        <p class="text-center text-muted mb-0">Tidak ada data denda</p>
        @endif
    </div>
</div>
@endsection
