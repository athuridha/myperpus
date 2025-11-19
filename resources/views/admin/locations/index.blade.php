@extends('layouts.app')

@section('title', 'Kelola Lokasi Rak')
@section('page-title', 'Kelola Lokasi Rak')

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
    <a href="{{ route('admin.locations.index') }}" class="nav-link active">
        <i class="bi bi-geo-alt"></i> Lokasi Rak
    </a>
    <a href="{{ route('admin.reports.index') }}" class="nav-link">
        <i class="bi bi-bar-chart"></i> Laporan
    </a>
</nav>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Form Add/Edit Location -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-{{ isset($editLocation) ? 'pencil' : 'plus-circle' }} me-2"></i>
                {{ isset($editLocation) ? 'Edit' : 'Tambah' }} Lokasi Rak
            </div>
            <div class="card-body">
                <form action="{{ isset($editLocation) ? route('admin.locations.update', $editLocation) : route('admin.locations.store') }}" method="POST">
                    @csrf
                    @if(isset($editLocation))
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Kode Rak <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                               value="{{ old('code', $editLocation->code ?? '') }}" required 
                               placeholder="Contoh: A1, B2, C3">
                        @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Kode unik untuk identifikasi rak</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $editLocation->name ?? '') }}" required
                               placeholder="Contoh: Rak A-1">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror" 
                                  placeholder="Contoh: Lantai 1 - Bagian Kiri">{{ old('description', $editLocation->description ?? '') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2">
                        @if(isset($editLocation))
                        <a href="{{ route('admin.locations.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Lokasi
                        </button>
                        @else
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Lokasi
                        </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Locations List -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-list"></i> Daftar Lokasi Rak ({{ $locations->count() }})
            </div>
            <div class="card-body">
                @if($locations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="80">ID</th>
                                <th>Kode</th>
                                <th>Nama Lokasi</th>
                                <th>Deskripsi</th>
                                <th>Jumlah Buku</th>
                                <th width="150">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($locations as $location)
                            <tr>
                                <td>#{{ $location->id }}</td>
                                <td><span class="badge bg-secondary">{{ $location->code }}</span></td>
                                <td><strong>{{ $location->name }}</strong></td>
                                <td>{{ $location->description ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $location->books_count }} buku</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.locations.edit', $location) }}" class="btn btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger" 
                                                onclick="confirmDelete({{ $location->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-form-{{ $location->id }}" 
                                          action="{{ route('admin.locations.destroy', $location) }}" 
                                          method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted">Belum ada lokasi rak</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(locationId) {
    if (confirm('Apakah Anda yakin ingin menghapus lokasi ini?\n\nPerhatian: Buku dengan lokasi ini akan terpengaruh.')) {
        document.getElementById('delete-form-' + locationId).submit();
    }
}
</script>
@endpush
