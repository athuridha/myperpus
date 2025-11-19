@extends('layouts.app')

@section('title', 'Aktivitas User')
@section('page-title', 'Aktivitas User')

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

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="text-muted small">Total Member</div>
                <div class="h2">{{ $users->total() }}</div>
                <small class="text-muted">Terdaftar</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="text-muted small">Aktif Meminjam</div>
                <div class="h2">{{ $users->where('borrowings_count', '>', 0)->count() }}</div>
                <small class="text-muted">Member</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="text-muted small">Belum Pinjam</div>
                <div class="h2">{{ $users->where('borrowings_count', 0)->count() }}</div>
                <small class="text-muted">Member</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="text-muted small">Punya Denda</div>
                <div class="h2">{{ $users->where('fines_count', '>', 0)->count() }}</div>
                <small class="text-muted">Member</small>
            </div>
        </div>
    </div>
</div>

<!-- Active Members -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <i class="bi bi-people"></i> Daftar Member ({{ $users->total() }} total)
    </div>
    <div class="card-body">
        @if($users->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Nama</th>
                        <th>NIM/NIP</th>
                        <th>Status</th>
                        <th class="text-center">Total Pinjam</th>
                        <th class="text-center">Denda</th>
                        <th class="text-center">Terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $index => $user)
                    <tr>
                        <td>
                            <span class="badge bg-{{ $index < 3 ? 'success' : 'secondary' }}">
                                #{{ $users->firstItem() + $index }}
                            </span>
                        </td>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->nim_nip }}</td>
                        <td>
                            <span class="badge bg-{{ $user->status == 'approved' ? 'success' : 'warning' }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary rounded-pill">{{ $user->borrowings_count }}</span>
                        </td>
                        <td class="text-center">
                            @if($user->fines_count > 0)
                            <span class="badge bg-danger">{{ $user->fines_count }}</span>
                            @else
                            <span class="text-success">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <small>{{ $user->created_at->format('d M Y') }}</small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $users->links() }}
        </div>
        @else
        <p class="text-center text-muted mb-0">Belum ada data member</p>
        @endif
    </div>
</div>
@endsection
