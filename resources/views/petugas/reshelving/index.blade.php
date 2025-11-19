@extends('layouts.app')

@section('title', 'Manajemen Reshelving')
@section('page-title', 'Manajemen Reshelving')

@section('sidebar')
<nav class="nav flex-column">
    <a href="{{ route('petugas.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('petugas.circulation.index') }}" class="nav-link">
        <i class="bi bi-arrow-left-right"></i> Sirkulasi
    </a>
    <a href="{{ route('petugas.fines.index') }}" class="nav-link">
        <i class="bi bi-cash"></i> Denda
    </a>
    <a href="{{ route('petugas.reshelving.index') }}" class="nav-link active">
        <i class="bi bi-arrow-clockwise"></i> Reshelving
    </a>
</nav>
@endsection

@section('content')
<!-- Info Alert -->
<div class="alert alert-info mb-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Tentang Reshelving:</strong> Buku yang dikembalikan perlu ditandai "sudah kembali ke rak" untuk memastikan akurasi stok dan lokasi.
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Menunggu Reshelving</div>
                        <div class="h2">{{ $pendingCount }}</div>
                    </div>
                    <i class="bi bi-hourglass-split fs-1 text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Selesai Hari Ini</div>
                        <div class="h2">{{ $completedToday }}</div>
                    </div>
                    <i class="bi bi-check-circle fs-1 text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Total Bulan Ini</div>
                        <div class="h2">{{ $monthlyTotal }}</div>
                    </div>
                    <i class="bi bi-calendar-check fs-1 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions -->
@if($pendingReshelving->count() > 0)
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('petugas.reshelving.bulk-reshelve') }}" method="POST" id="bulkForm">
            @csrf
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <button type="button" class="btn btn-secondary" onclick="toggleAllCheckboxes()">
                        <i class="bi bi-check-square"></i> Pilih Semua
                    </button>
                    <span class="ms-3 text-muted">
                        <span id="selectedCount">0</span> buku dipilih
                    </span>
                </div>
                <button type="submit" class="btn btn-success" onclick="return confirm('Tandai semua buku yang dipilih sudah kembali ke rak?')">
                    <i class="bi bi-check-circle"></i> Tandai Sudah Reshelve
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Pending Reshelving List -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-list"></i> Buku Menunggu Reshelving ({{ $pendingReshelving->count() }})
    </div>
    <div class="card-body">
        @if($pendingReshelving->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="checkAll" onclick="toggleAllCheckboxes()">
                        </th>
                        <th>Buku</th>
                        <th>Lokasi Rak</th>
                        <th>Dikembalikan</th>
                        <th>Waktu Tunggu</th>
                        <th>Peminjam</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingReshelving as $item)
                    <tr>
                        <td>
                            <input type="checkbox" name="reshelving_ids[]" value="{{ $item->id }}" 
                                   class="reshelve-checkbox" form="bulkForm" onchange="updateSelectedCount()">
                        </td>
                        <td>
                            <strong>{{ $item->book->title }}</strong><br>
                            <small class="text-muted">{{ $item->book->author }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                {{ $item->book->location->code }} - {{ $item->book->location->name }}
                            </span>
                        </td>
                        <td>{{ $item->returned_to_counter_at->format('d M Y H:i') }}</td>
                        <td>
                            @php
                                $hours = $item->returned_to_counter_at->diffInHours(now());
                            @endphp
                            @if($hours < 1)
                                <span class="badge bg-success">Baru saja</span>
                            @elseif($hours < 4)
                                <span class="badge bg-info">{{ $hours }} jam</span>
                            @elseif($hours < 24)
                                <span class="badge bg-warning">{{ $hours }} jam</span>
                            @else
                                <span class="badge bg-danger">{{ floor($hours/24) }} hari</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $item->borrowing->user->name }}</small>
                        </td>
                        <td>
                            <form action="{{ route('petugas.reshelving.mark-reshelved', $item) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" 
                                        onclick="return confirm('Tandai buku ini sudah kembali ke rak?')">
                                    <i class="bi bi-check"></i> Reshelve
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-check-circle display-1 text-success"></i>
            <h5 class="mt-3">Tidak Ada Buku yang Perlu Direshelve</h5>
            <p class="text-muted">Semua buku sudah kembali ke rak</p>
        </div>
        @endif
    </div>
</div>

<!-- Recently Completed -->
@if($recentlyCompleted->count() > 0)
<div class="card mt-4">
    <div class="card-header bg-success text-white">
        <i class="bi bi-check-circle me-2"></i>Baru Selesai Direshelve
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Buku</th>
                        <th>Lokasi</th>
                        <th>Dikembalikan</th>
                        <th>Direshelve</th>
                        <th>Waktu Proses</th>
                        <th>Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentlyCompleted as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->book->title }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $item->book->location->code }}</span>
                        </td>
                        <td>{{ $item->returned_to_counter_at->format('d/m H:i') }}</td>
                        <td>{{ $item->reshelved_at->format('d/m H:i') }}</td>
                        <td>
                            @php
                                $processingTime = $item->returned_to_counter_at->diffInMinutes($item->reshelved_at);
                            @endphp
                            @if($processingTime < 60)
                                {{ $processingTime }} menit
                            @else
                                {{ floor($processingTime / 60) }} jam {{ $processingTime % 60 }} menit
                            @endif
                        </td>
                        <td>
                            <small>{{ $item->processedBy->name }}</small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function toggleAllCheckboxes() {
    const checkAll = document.getElementById('checkAll');
    const checkboxes = document.querySelectorAll('.reshelve-checkbox');
    checkboxes.forEach(cb => cb.checked = checkAll.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkedCount = document.querySelectorAll('.reshelve-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = checkedCount;
    
    // Update "check all" checkbox state
    const totalCheckboxes = document.querySelectorAll('.reshelve-checkbox').length;
    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.checked = checkedCount === totalCheckboxes && totalCheckboxes > 0;
    }
}

// Initialize count on page load
document.addEventListener('DOMContentLoaded', updateSelectedCount);
</script>
@endpush
