@extends('layouts.app')

@section('title', 'Tambah Lokasi')
@section('page-title', 'Tambah Lokasi Rak')

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
<div class="mb-3">
    <a href="{{ route('admin.locations.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-plus-circle"></i> Form Tambah Lokasi Rak
            </div>
            <div class="card-body">
                <form action="{{ route('admin.locations.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="code" class="form-label">Kode Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" 
                               id="code" name="code" value="{{ old('code') }}" required autofocus 
                               placeholder="Contoh: A1, B2, C3">
                        @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Kode unik untuk lokasi rak (max 10 karakter)</small>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required
                               placeholder="Contoh: Lantai 1 - Rak A">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Deskripsi detail lokasi rak">{{ old('description') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Opsional - Informasi tambahan tentang lokasi</small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Lokasi
                        </button>
                        <a href="{{ route('admin.locations.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-info-circle"></i> Panduan</h6>
                <ul class="mb-0">
                    <li><strong>Kode Lokasi:</strong> Singkatan/kode unik (contoh: A1, B2, LT1-RAK3)</li>
                    <li><strong>Nama Lokasi:</strong> Deskripsi lengkap lokasi rak</li>
                    <li>Kode harus unik dan belum digunakan</li>
                    <li>Gunakan sistem pengkodean yang konsisten</li>
                    <li>Contoh format:
                        <ul>
                            <li>A1 - Lantai 1 Rak A</li>
                            <li>B2 - Lantai 2 Rak B</li>
                            <li>FIK-01 - Fiksi Rak 01</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-lightbulb"></i> Tips
            </div>
            <div class="card-body">
                <p class="mb-0">
                    <small class="text-muted">
                        Rencanakan sistem pengkodean lokasi dengan baik untuk memudahkan pencarian buku. 
                        Pertimbangkan untuk menggunakan kode yang mencerminkan lantai, area, atau jenis koleksi.
                    </small>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
