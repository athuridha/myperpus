@extends('layouts.app')

@section('title', 'Edit Kategori')
@section('page-title', 'Edit Kategori: ' . $category->name)

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
    <a href="{{ route('admin.categories.index') }}" class="nav-link active">
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
    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil me-2"></i>Form Edit Kategori
            </div>
            <div class="card-body">
                <form action="{{ route('admin.categories.update', $category) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $category->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Kategori
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-book me-2"></i>Buku dengan Kategori Ini ({{ $category->books->count() }})
            </div>
            <div class="card-body">
                @if($category->books->count() > 0)
                <div class="list-group">
                    @foreach($category->books->take(10) as $book)
                    <a href="{{ route('admin.books.show', $book) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $book->title }}</strong><br>
                                <small class="text-muted">{{ $book->author }}</small>
                            </div>
                            <span class="badge bg-primary">{{ $book->available_stock }}/{{ $book->total_stock }}</span>
                        </div>
                    </a>
                    @endforeach
                </div>
                @if($category->books->count() > 10)
                <p class="text-muted mt-2 mb-0">Dan {{ $category->books->count() - 10 }} buku lainnya...</p>
                @endif
                @else
                <p class="text-center text-muted mb-0">Belum ada buku dengan kategori ini</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
