@extends('layouts.app')

@section('title', 'Katalog Buku')
@section('page-title', 'Katalog Buku')

@section('sidebar')
<nav class="nav flex-column">
    <a href="{{ route('member.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('member.books.index') }}" class="nav-link active">
        <i class="bi bi-book"></i> Katalog Buku
    </a>
    <a href="{{ route('member.borrowings.index') }}" class="nav-link">
        <i class="bi bi-clock-history"></i> Riwayat Peminjaman
    </a>
    <a href="{{ route('member.fines.index') }}" class="nav-link">
        <i class="bi bi-cash"></i> Denda
    </a>
</nav>
@endsection

@section('content')
<!-- Search & Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('member.books.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Cari judul, penulis, ISBN..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="category_id" class="form-select">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="available" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('available') == '1' ? 'selected' : '' }}>Tersedia</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Cari
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Books Grid -->
@if($books->count() > 0)
<div class="row">
    @foreach($books as $book)
    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <div class="card h-100">
            <img src="{{ $book->cover_url }}" class="card-img-top" alt="{{ $book->title }}"
                 style="height: 250px; object-fit: cover;">
            <div class="card-body d-flex flex-column">
                <h6 class="card-title">{{ Str::limit($book->title, 50) }}</h6>
                <p class="card-text small text-muted mb-2">
                    <i class="bi bi-person"></i> {{ $book->author }}<br>
                    <i class="bi bi-building"></i> {{ $book->publisher }}
                </p>
                <div class="mt-auto">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-{{ $book->isAvailable() ? 'success' : 'secondary' }}">
                            {{ $book->isAvailable() ? 'Tersedia' : 'Tidak Tersedia' }}
                        </span>
                        <small class="text-muted">{{ $book->available_stock }}/{{ $book->total_stock }}</small>
                    </div>
                    <a href="{{ route('member.books.show', $book) }}" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-eye"></i> Detail
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center">
    {{ $books->links() }}
</div>
@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
        <h5 class="text-muted">Tidak ada buku ditemukan</h5>
        <p class="text-muted">Coba ubah filter atau kata kunci pencarian Anda</p>
    </div>
</div>
@endif
@endsection
