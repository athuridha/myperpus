@extends('layouts.app')

@section('title', 'Detail Buku')
@section('page-title', $book->title)

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
<div class="mb-3">
    <a href="{{ route('admin.books.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-warning">
        <i class="bi bi-pencil"></i> Edit
    </a>
    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
        <i class="bi bi-trash"></i> Hapus
    </button>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                @if($book->cover_image)
                <img src="{{ asset('storage/covers/' . $book->cover_image) }}" alt="{{ $book->title }}" 
                     class="img-fluid mb-3" style="max-height: 400px;">
                @else
                <div class="bg-secondary text-white py-5 mb-3">
                    <i class="bi bi-book fs-1"></i>
                    <p>Tidak ada cover</p>
                </div>
                @endif

                @if($book->qr_code)
                <hr>
                <h6>QR Code Buku</h6>
                <img src="{{ asset('storage/qrcodes/' . $book->qr_code) }}" alt="QR Code" class="img-fluid" style="max-width: 200px;">
                <br>
                <a href="{{ asset('storage/qrcodes/' . $book->qr_code) }}" download class="btn btn-sm btn-primary mt-2">
                    <i class="bi bi-download"></i> Download QR
                </a>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Informasi Buku
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Judul</th>
                        <td>: {{ $book->title }}</td>
                    </tr>
                    <tr>
                        <th>ISBN</th>
                        <td>: {{ $book->isbn ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Penulis</th>
                        <td>: {{ $book->author }}</td>
                    </tr>
                    <tr>
                        <th>Penerbit</th>
                        <td>: {{ $book->publisher ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tahun Terbit</th>
                        <td>: {{ $book->year ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Kategori</th>
                        <td>: <span class="badge bg-info">{{ $book->category->name }}</span></td>
                    </tr>
                    <tr>
                        <th>Lokasi Rak</th>
                        <td>: <span class="badge bg-secondary">{{ $book->location->code }} - {{ $book->location->name }}</span></td>
                    </tr>
                    <tr>
                        <th>Stok Total</th>
                        <td>: {{ $book->total_stock }} eksemplar</td>
                    </tr>
                    <tr>
                        <th>Stok Tersedia</th>
                        <td>: <span class="badge bg-primary">{{ $book->available_stock }} tersedia</span></td>
                    </tr>
                    <tr>
                        <th>Kondisi</th>
                        <td>: 
                            @if($book->book_condition === 'baik')
                            <span class="badge bg-success">Baik</span>
                            @elseif($book->book_condition === 'rusak')
                            <span class="badge bg-warning">Rusak</span>
                            @else
                            <span class="badge bg-danger">Hilang</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Deskripsi</th>
                        <td>: {{ $book->description ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Dibuat</th>
                        <td>: {{ $book->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Terakhir Update</th>
                        <td>: {{ $book->updated_at->format('d M Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-clock-history me-2"></i>Riwayat Peminjaman
            </div>
            <div class="card-body">
                @if($book->borrowings->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Peminjam</th>
                                <th>Tgl Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($book->borrowings->take(10) as $b)
                            <tr>
                                <td>{{ $b->user->name }}</td>
                                <td>{{ $b->borrow_date->format('d M Y') }}</td>
                                <td>{{ $b->due_date->format('d M Y') }}</td>
                                <td>{{ $b->return_date ? $b->return_date->format('d M Y') : '-' }}</td>
                                <td><span class="badge bg-{{ $b->status_color }}">{{ $b->status_label }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-muted mb-0">Total dipinjam: {{ $book->borrowings->count() }} kali</p>
                @else
                <p class="text-muted mb-0">Belum pernah dipinjam</p>
                @endif
            </div>
        </div>
    </div>
</div>

<form id="delete-form" action="{{ route('admin.books.destroy', $book) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('Apakah Anda yakin ingin menghapus buku ini?\n\nPerhatian: Data riwayat peminjaman akan tetap tersimpan.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endpush
