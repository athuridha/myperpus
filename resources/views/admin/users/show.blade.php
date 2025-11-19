@extends('layouts.app')

@section('title', 'Detail User')
@section('page-title', 'Detail User: ' . $user->name)

@section('sidebar')
<nav class="nav flex-column">
    <a href="{{ route('admin.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('admin.books.index') }}" class="nav-link">
        <i class="bi bi-book"></i> Kelola Buku
    </a>
    <a href="{{ route('admin.users.index') }}" class="nav-link active">
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
<div class="mb-3">
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
        <i class="bi bi-pencil"></i> Edit
    </a>
</div>

<div class="row">
    <div class="col-md-4">
        <!-- User Info Card -->
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-person-circle display-1 text-primary mb-3"></i>
                <h4>{{ $user->name }}</h4>
                <p class="text-muted">{{ $user->email }}</p>
                
                <div class="d-flex justify-content-center gap-2 mb-3">
                    @if($user->role === 'admin')
                    <span class="badge bg-danger">Admin</span>
                    @elseif($user->role === 'petugas')
                    <span class="badge bg-info">Petugas</span>
                    @else
                    <span class="badge bg-primary">Member</span>
                    @endif

                    @if($user->status === 'active')
                    <span class="badge bg-success">Active</span>
                    @elseif($user->status === 'pending')
                    <span class="badge bg-warning">Pending</span>
                    @else
                    <span class="badge bg-danger">Blocked</span>
                    @endif
                </div>

                <hr>

                <!-- Quick Actions -->
                <div class="d-grid gap-2">
                    @if($user->status === 'pending')
                    <form action="{{ route('admin.users.approve', $user) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Approve User
                        </button>
                    </form>
                    <form action="{{ route('admin.users.reject', $user) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100"
                                onclick="return confirm('Yakin reject user ini?')">
                            <i class="bi bi-x-circle"></i> Reject User
                        </button>
                    </form>
                    @endif

                    @if($user->status === 'active' && $user->id !== auth()->id())
                    <form action="{{ route('admin.users.block', $user) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100"
                                onclick="return confirm('Yakin blokir user ini?')">
                            <i class="bi bi-lock"></i> Blokir User
                        </button>
                    </form>
                    @endif

                    @if($user->status === 'blocked')
                    <form action="{{ route('admin.users.unblock', $user) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-unlock"></i> Buka Blokir
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistics Card -->
        @if($user->role === 'member')
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-bar-chart me-2"></i>Statistik
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td>Total Peminjaman</td>
                        <td class="text-end"><strong>{{ $user->borrowings->count() }}</strong></td>
                    </tr>
                    <tr>
                        <td>Peminjaman Aktif</td>
                        <td class="text-end"><strong>{{ $user->activeBorrowings()->count() }}</strong></td>
                    </tr>
                    <tr>
                        <td>Kuota Tersisa</td>
                        <td class="text-end"><strong>{{ $user->remainingBorrowQuota() }}</strong></td>
                    </tr>
                    <tr>
                        <td>Total Denda</td>
                        <td class="text-end text-danger"><strong>Rp {{ number_format($user->fines->sum('amount'), 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Denda Belum Bayar</td>
                        <td class="text-end text-danger"><strong>Rp {{ number_format($user->unpaidFines()->sum('amount'), 0, ',', '.') }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        <!-- User Details -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Informasi User
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">ID User</th>
                        <td>: #{{ $user->id }}</td>
                    </tr>
                    <tr>
                        <th>Nama Lengkap</th>
                        <td>: {{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>: {{ $user->email }}
                            @if($user->email_verified_at)
                            <span class="badge bg-success">Verified</span>
                            @else
                            <span class="badge bg-warning">Not Verified</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>NIM/NIP</th>
                        <td>: {{ $user->nim_nip ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Telepon</th>
                        <td>: {{ $user->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td>: {{ $user->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td>: 
                            @if($user->role === 'admin')
                            <span class="badge bg-danger">Admin</span>
                            @elseif($user->role === 'petugas')
                            <span class="badge bg-info">Petugas</span>
                            @else
                            <span class="badge bg-primary">Member</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>: 
                            @if($user->status === 'active')
                            <span class="badge bg-success">Active</span>
                            @elseif($user->status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                            @else
                            <span class="badge bg-danger">Blocked</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Batas Peminjaman</th>
                        <td>: {{ $user->max_borrow_limit }} buku</td>
                    </tr>
                    <tr>
                        <th>Bergabung</th>
                        <td>: {{ $user->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Terakhir Update</th>
                        <td>: {{ $user->updated_at->format('d M Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Borrowing History (for members) -->
        @if($user->role === 'member' && $user->borrowings->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-clock-history me-2"></i>Riwayat Peminjaman (10 Terakhir)
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Buku</th>
                                <th>Tgl Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                                <th>Denda</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->borrowings->take(10) as $borrowing)
                            <tr>
                                <td>{{ $borrowing->book->title }}</td>
                                <td>{{ $borrowing->borrow_date->format('d M Y') }}</td>
                                <td>{{ $borrowing->due_date->format('d M Y') }}</td>
                                <td>{{ $borrowing->return_date ? $borrowing->return_date->format('d M Y') : '-' }}</td>
                                <td><span class="badge bg-{{ $borrowing->status_color }}">{{ $borrowing->status_label }}</span></td>
                                <td>
                                    @if($borrowing->fine_amount > 0)
                                    <span class="text-danger">Rp {{ number_format($borrowing->fine_amount, 0, ',', '.') }}</span>
                                    @else
                                    -
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
    </div>
</div>
@endsection
