@extends('layouts.app')

@section('title', 'Audit Log')
@section('page-title', 'Audit Log')

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

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.reports.audit-logs') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <select name="action" class="form-select">
                    <option value="">Semua Aksi</option>
                    <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Create</option>
                    <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Update</option>
                    <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Delete</option>
                    <option value="approve" {{ request('action') == 'approve' ? 'selected' : '' }}>Approve</option>
                    <option value="reject" {{ request('action') == 'reject' ? 'selected' : '' }}>Reject</option>
                    <option value="borrow" {{ request('action') == 'borrow' ? 'selected' : '' }}>Borrow</option>
                    <option value="return" {{ request('action') == 'return' ? 'selected' : '' }}>Return</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="model" class="form-select">
                    <option value="">Semua Model</option>
                    <option value="User" {{ request('model') == 'User' ? 'selected' : '' }}>User</option>
                    <option value="Book" {{ request('model') == 'Book' ? 'selected' : '' }}>Book</option>
                    <option value="Borrowing" {{ request('model') == 'Borrowing' ? 'selected' : '' }}>Borrowing</option>
                    <option value="Fine" {{ request('model') == 'Fine' ? 'selected' : '' }}>Fine</option>
                    <option value="Category" {{ request('model') == 'Category' ? 'selected' : '' }}>Category</option>
                    <option value="Location" {{ request('model') == 'Location' ? 'selected' : '' }}>Location</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Audit Logs Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-clock-history"></i> Activity Log ({{ $logs->total() }} records)
    </div>
    <div class="card-body">
        @if($logs->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th style="width: 140px;">Waktu</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Model</th>
                        <th>Deskripsi</th>
                        <th style="width: 100px;">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td>
                            <small>{{ $log->created_at->format('d/m/Y H:i') }}</small>
                        </td>
                        <td>
                            @if($log->user)
                            <strong>{{ $log->user->name }}</strong><br>
                            <small class="text-muted">{{ $log->user->nim_nip }}</small>
                            @else
                            <span class="text-muted">System</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ 
                                $log->action == 'create' ? 'success' : 
                                ($log->action == 'update' ? 'primary' : 
                                ($log->action == 'delete' ? 'danger' : 'info')) 
                            }}">
                                {{ strtoupper($log->action) }}
                            </span>
                        </td>
                        <td>
                            <code>{{ class_basename($log->auditable_type) }}</code>
                        </td>
                        <td>{{ $log->description }}</td>
                        <td><small class="text-muted">{{ $log->ip_address }}</small></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $logs->links() }}
        </div>
        @else
        <p class="text-center text-muted mb-0">Tidak ada log yang sesuai filter</p>
        @endif
    </div>
</div>
@endsection
