@extends('layouts.app')

@section('title', 'Popularitas Buku')
@section('page-title', 'Popularitas Buku')

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

<div class="card">
    <div class="card-header bg-success text-white">
        <i class="bi bi-trophy"></i> Ranking Popularitas Buku
    </div>
    <div class="card-body">
        @if($books->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 50px;">Rank</th>
                        <th>Judul Buku</th>
                        <th>Penulis</th>
                        <th>ISBN</th>
                        <th>Kategori</th>
                        <th class="text-center">Dipinjam</th>
                        <th class="text-center">Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($books as $index => $book)
                    <tr>
                        <td>
                            <span class="badge bg-{{ $index < 3 ? 'success' : 'secondary' }}" style="width: 35px;">
                                #{{ $books->firstItem() + $index }}
                            </span>
                        </td>
                        <td><strong>{{ $book->title }}</strong></td>
                        <td>{{ $book->author }}</td>
                        <td><code class="small">{{ $book->isbn }}</code></td>
                        <td>
                            <span class="badge bg-info">{{ $book->category->name ?? '-' }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary rounded-pill" style="font-size: 1rem;">
                                {{ $book->borrowings_count }}
                            </span>
                        </td>
                        <td class="text-center">{{ $book->stock }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $books->links() }}
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <p class="text-muted mt-3">Belum ada data peminjaman</p>
        </div>
        @endif
    </div>
</div>
@endsection
