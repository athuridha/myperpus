@extends('layouts.app')

@section('title', 'Riwayat Peminjaman')
@section('page-title', 'Riwayat Peminjaman')

@section('sidebar')
<nav class="nav flex-column">
    <a href="{{ route('member.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('member.books.index') }}" class="nav-link">
        <i class="bi bi-book"></i> Katalog Buku
    </a>
    <a href="{{ route('member.borrowings.index') }}" class="nav-link active">
        <i class="bi bi-clock-history"></i> Riwayat Peminjaman
    </a>
    <a href="{{ route('member.fines.index') }}" class="nav-link">
        <i class="bi bi-cash"></i> Denda
    </a>
</nav>
@endsection

@section('content')
<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('member.borrowings.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="booked" {{ request('status') == 'booked' ? 'selected' : '' }}>Booking</option>
                        <option value="borrowed" {{ request('status') == 'borrowed' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Terlambat</option>
                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Dikembalikan</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
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

<!-- Borrowings Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-list-ul me-2"></i>Daftar Peminjaman
    </div>
    <div class="card-body">
        @if($borrowings->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Buku</th>
                        <th>Tanggal Booking</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Tanggal Kembali</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($borrowings as $borrowing)
                    <tr>
                        <td>
                            <strong>{{ $borrowing->book->title }}</strong><br>
                            <small class="text-muted">{{ $borrowing->book->author }}</small>
                        </td>
                        <td>{{ $borrowing->created_at->format('d/m/Y') }}</td>
                        <td>{{ $borrowing->borrow_date ? $borrowing->borrow_date->format('d/m/Y') : '-' }}</td>
                        <td>{{ $borrowing->due_date ? $borrowing->due_date->format('d/m/Y') : '-' }}</td>
                        <td>{{ $borrowing->return_date ? $borrowing->return_date->format('d/m/Y') : '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $borrowing->status_color }}">
                                {{ $borrowing->status_label }}
                            </span>
                            @if($borrowing->isOverdue() && !$borrowing->return_date)
                            <br><small class="text-danger">{{ $borrowing->days_overdue }} hari</small>
                            @endif
                        </td>
                        <td>
                            @if($borrowing->status === 'booked')
                            <form action="{{ route('member.borrowings.cancel', $borrowing) }}" method="POST">
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

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $borrowings->links() }}
        </div>
        @else
        <p class="text-center text-muted mb-0">Tidak ada riwayat peminjaman</p>
        @endif
    </div>
</div>
@endsection
