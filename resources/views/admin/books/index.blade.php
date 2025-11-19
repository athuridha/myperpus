@extends('layouts.app')

@section('title', 'Kelola Buku')
@section('page-title', 'Kelola Buku')

@section('sidebar')
<nav class="nav flex-column">
    <a href="{{ route('admin.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('admin.books.index') }}" class="nav-link active">
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
    <a href="{{ route('admin.reports.index') }}" class="nav-link">
        <i class="bi bi-bar-chart"></i> Laporan
    </a>
</nav>
@endsection

@section('content')
<!-- Action Bar -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.books.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Buku
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.books.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari judul, ISBN, penulis..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="location_id" class="form-select">
                    <option value="">Semua Lokasi</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                        {{ $loc->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Books Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-list"></i> Daftar Buku ({{ $books->total() }} buku)
    </div>
    <div class="card-body">
        @if($books->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Judul</th>
                        <th>ISBN</th>
                        <th>Penulis</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Stok</th>
                        <th>Kondisi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($books as $book)
                    <tr>
                        <td>
                            @if($book->cover_image)
                            <img src="{{ asset('storage/covers/' . $book->cover_image) }}" 
                                 alt="{{ $book->title }}" 
                                 style="width: 50px; height: 70px; object-fit: cover;">
                            @else
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" 
                                 style="width: 50px; height: 70px;">
                                <i class="bi bi-book"></i>
                            </div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $book->title }}</strong><br>
                            <small class="text-muted">{{ $book->publisher }} ({{ $book->year }})</small>
                        </td>
                        <td>{{ $book->isbn ?? '-' }}</td>
                        <td>{{ $book->author }}</td>
                        <td>
                            <span class="badge bg-info">{{ $book->category->name }}</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $book->location->code }}</span>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $book->available_stock }}/{{ $book->total_stock }}</span>
                        </td>
                        <td>
                            @if($book->book_condition === 'baik')
                            <span class="badge bg-success">Baik</span>
                            @elseif($book->book_condition === 'rusak')
                            <span class="badge bg-warning">Rusak</span>
                            @else
                            <span class="badge bg-danger">Hilang</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.books.show', $book) }}" class="btn btn-info" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-danger" title="Hapus"
                                        onclick="confirmDelete({{ $book->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <form id="delete-form-{{ $book->id }}" 
                                  action="{{ route('admin.books.destroy', $book) }}" 
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

        <!-- Pagination -->
        <div class="mt-3">
            {{ $books->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-inbox fs-1 text-muted"></i>
            <p class="text-muted">Tidak ada buku ditemukan</p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(bookId) {
    if (confirm('Apakah Anda yakin ingin menghapus buku ini?')) {
        document.getElementById('delete-form-' + bookId).submit();
    }
}
</script>
@endpush
