@extends('layouts.app')

@section('title', 'Detail Buku')
@section('page-title', 'Detail Buku')

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
<div class="mb-3">
    <a href="{{ route('member.books.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <img src="{{ $book->cover_url }}" class="card-img-top" alt="{{ $book->title }}">
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if(auth()->user()->canBorrow() && $book->isAvailable())
                    <form action="{{ route('member.borrowings.book', $book) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100"
                                onclick="return confirm('Yakin ingin booking buku ini?')">
                            <i class="bi bi-bookmark"></i> Booking Buku
                        </button>
                    </form>
                    @else
                    <button class="btn btn-secondary w-100" disabled>
                        @if(!auth()->user()->canBorrow())
                            @if(!auth()->user()->isActive())
                            Akun Tidak Aktif
                            @elseif(auth()->user()->unpaidFines()->exists())
                            Lunasi Denda Dulu
                            @else
                            Kuota Penuh
                            @endif
                        @else
                        Tidak Tersedia
                        @endif
                    </button>
                    @endif
                </div>

                <hr>

                <div class="mb-2">
                    <strong>Ketersediaan:</strong>
                    <span class="badge bg-{{ $book->isAvailable() ? 'success' : 'secondary' }} float-end">
                        {{ $book->available_stock }}/{{ $book->total_stock }}
                    </span>
                </div>
                <div class="mb-2">
                    <strong>Kondisi:</strong>
                    <span class="badge bg-{{ $book->book_condition === 'baik' ? 'success' : 'warning' }} float-end">
                        {{ ucfirst($book->book_condition) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h3 class="mb-3">{{ $book->title }}</h3>

                <table class="table table-borderless">
                    <tr>
                        <th width="200">ISBN</th>
                        <td>{{ $book->isbn }}</td>
                    </tr>
                    <tr>
                        <th>Penulis</th>
                        <td>{{ $book->author }}</td>
                    </tr>
                    <tr>
                        <th>Penerbit</th>
                        <td>{{ $book->publisher }}</td>
                    </tr>
                    <tr>
                        <th>Tahun Terbit</th>
                        <td>{{ $book->year }}</td>
                    </tr>
                    <tr>
                        <th>Kategori</th>
                        <td>
                            <span class="badge bg-primary">{{ $book->category->name }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Lokasi Rak</th>
                        <td>
                            <i class="bi bi-geo-alt"></i> {{ $book->location->full_name }}
                        </td>
                    </tr>
                </table>

                @if($book->description)
                <hr>
                <h5>Deskripsi</h5>
                <p>{{ $book->description }}</p>
                @endif
            </div>
        </div>

        <!-- Related Books -->
        @if($relatedBooks->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <strong>Buku Terkait</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($relatedBooks as $related)
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <img src="{{ $related->cover_url }}" class="card-img-top" alt="{{ $related->title }}"
                                 style="height: 150px; object-fit: cover;">
                            <div class="card-body p-2">
                                <small class="fw-bold">{{ Str::limit($related->title, 30) }}</small>
                                <a href="{{ route('member.books.show', $related) }}" class="btn btn-sm btn-outline-primary w-100 mt-2">
                                    Lihat
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
