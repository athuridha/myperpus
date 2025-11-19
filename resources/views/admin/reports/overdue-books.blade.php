@extends('layouts.app')

@section('title', 'Buku Terlambat')
@section('page-title', 'Buku Terlambat')

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

<div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>{{ $stats['total_overdue'] }} Buku Terlambat</strong> - Total denda yang harus dibayar: 
    <strong>Rp {{ number_format($stats['total_potential_fines'], 0, ',', '.') }}</strong>
</div>

<!-- Overdue Books Table -->
<div class="card">
    <div class="card-header bg-danger text-white">
        <i class="bi bi-exclamation-triangle me-2"></i>Daftar Buku Terlambat
    </div>
    <div class="card-body">
        @if($overdueBooks->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Peminjam</th>
                        <th>Kontak</th>
                        <th>Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Keterlambatan</th>
                        <th>Denda</th>
                        <th>Status Denda</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overdueBooks as $borrowing)
                    <tr>
                        <td>
                            <strong>{{ $borrowing->user->name }}</strong><br>
                            <small class="text-muted">{{ $borrowing->user->nim_nip }}</small>
                        </td>
                        <td>
                            @if($borrowing->user->phone)
                            <a href="tel:{{ $borrowing->user->phone }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-telephone"></i>
                            </a>
                            @endif
                            @if($borrowing->user->email)
                            <a href="mailto:{{ $borrowing->user->email }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-envelope"></i>
                            </a>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $borrowing->book->title }}</strong><br>
                            <small class="text-muted">{{ $borrowing->book->author }}</small>
                        </td>
                        <td>{{ $borrowing->borrow_date->format('d M Y') }}</td>
                        <td>{{ $borrowing->due_date->format('d M Y') }}</td>
                        <td>
                            <span class="badge bg-danger">{{ $borrowing->days_overdue }} hari</span>
                        </td>
                        <td class="text-danger fw-bold">
                            Rp {{ number_format($borrowing->calculateFine(), 0, ',', '.') }}
                        </td>
                        <td>
                            @if($borrowing->fine)
                            <span class="badge bg-{{ $borrowing->fine->status === 'paid' ? 'success' : 'warning' }}">
                                {{ $borrowing->fine->status === 'paid' ? 'Lunas' : 'Belum Bayar' }}
                            </span>
                            @else
                            <span class="badge bg-secondary">Belum Ada</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-check-circle display-1 text-success"></i>
            <h5 class="mt-3">Tidak Ada Buku Terlambat</h5>
            <p class="text-muted">Semua buku dikembalikan tepat waktu</p>
        </div>
        @endif
    </div>
</div>
@endsection
