@extends('layouts.app')

@section('title', 'Statistik Bulanan')
@section('page-title', 'Statistik Bulanan')

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
<div class="mb-3 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <form action="{{ route('admin.reports.monthly-stats') }}" method="GET" class="d-flex gap-2">
        <select name="year" class="form-select" style="width: 120px;">
            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-search"></i> Lihat
        </button>
    </form>
</div>

<!-- Year to Date Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="text-muted small">Total Peminjaman</div>
                <div class="h2">{{ $ytdStats['total_borrowings'] }}</div>
                <small class="text-muted">Tahun {{ $year }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="text-muted small">Total Pengembalian</div>
                <div class="h2">{{ $ytdStats['total_returns'] }}</div>
                <small class="text-muted">Tahun {{ $year }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="text-muted small">Total Denda</div>
                <div class="h5">Rp {{ number_format($ytdStats['total_fines'], 0, ',', '.') }}</div>
                <small class="text-muted">Tahun {{ $year }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card info">
            <div class="card-body">
                <div class="text-muted small">Member Baru</div>
                <div class="h2">{{ $ytdStats['new_members'] }}</div>
                <small class="text-muted">Tahun {{ $year }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Chart -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-bar-chart"></i> Grafik Bulanan Tahun {{ $year }}
    </div>
    <div class="card-body">
        <canvas id="monthlyChart" height="80"></canvas>
    </div>
</div>

<!-- Monthly Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-table"></i> Data Detail Per Bulan
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-center">Peminjaman</th>
                        <th class="text-center">Pengembalian</th>
                        <th class="text-center">Denda (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chartData['labels'] as $index => $month)
                    <tr>
                        <td><strong>{{ $month }}</strong></td>
                        <td class="text-center">
                            <span class="badge bg-primary">{{ $chartData['borrowings'][$index] }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success">{{ $chartData['returns'][$index] }}</span>
                        </td>
                        <td class="text-center text-danger">
                            {{ number_format($chartData['fines'][$index], 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="table-primary fw-bold">
                        <td>TOTAL</td>
                        <td class="text-center">{{ array_sum($chartData['borrowings']) }}</td>
                        <td class="text-center">{{ array_sum($chartData['returns']) }}</td>
                        <td class="text-center">{{ number_format(array_sum($chartData['fines']), 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('monthlyChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartData['labels']),
            datasets: [
                {
                    label: 'Peminjaman',
                    data: @json($chartData['borrowings']),
                    borderColor: 'rgb(13, 110, 253)',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.3
                },
                {
                    label: 'Pengembalian',
                    data: @json($chartData['returns']),
                    borderColor: 'rgb(25, 135, 84)',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush
@endsection
