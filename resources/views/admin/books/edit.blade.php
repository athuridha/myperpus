@extends('layouts.app')

@section('title', 'Edit Buku')
@section('page-title', 'Edit Buku: ' . $book->title)

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
    <a href="{{ route('admin.books.show', $book) }}" class="btn btn-info">
        <i class="bi bi-eye"></i> Lihat Detail
    </a>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-pencil me-2"></i>Form Edit Buku
    </div>
    <div class="card-body">
        <form action="{{ route('admin.books.update', $book) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-8">
                    <!-- Title -->
                    <div class="mb-3">
                        <label class="form-label">Judul Buku <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title', $book->title) }}" required>
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- ISBN -->
                    <div class="mb-3">
                        <label class="form-label">ISBN</label>
                        <input type="text" name="isbn" class="form-control @error('isbn') is-invalid @enderror" 
                               value="{{ old('isbn', $book->isbn) }}">
                        @error('isbn')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Author -->
                    <div class="mb-3">
                        <label class="form-label">Penulis <span class="text-danger">*</span></label>
                        <input type="text" name="author" class="form-control @error('author') is-invalid @enderror" 
                               value="{{ old('author', $book->author) }}" required>
                        @error('author')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <!-- Publisher -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penerbit</label>
                            <input type="text" name="publisher" class="form-control @error('publisher') is-invalid @enderror" 
                                   value="{{ old('publisher', $book->publisher) }}">
                            @error('publisher')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Year -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tahun Terbit</label>
                            <input type="number" name="year" class="form-control @error('year') is-invalid @enderror" 
                                   value="{{ old('year', $book->year) }}" min="1900" max="{{ date('Y') }}">
                            @error('year')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <!-- Category -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $book->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Location -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lokasi Rak <span class="text-danger">*</span></label>
                            <select name="location_id" class="form-select @error('location_id') is-invalid @enderror" required>
                                <option value="">Pilih Lokasi</option>
                                @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('location_id', $book->location_id) == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->code }} - {{ $loc->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('location_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <!-- Total Stock -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jumlah Stok Total <span class="text-danger">*</span></label>
                            <input type="number" name="total_stock" class="form-control @error('total_stock') is-invalid @enderror" 
                                   value="{{ old('total_stock', $book->total_stock) }}" min="1" required>
                            @error('total_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Available Stock -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stok Tersedia</label>
                            <input type="number" name="available_stock" class="form-control @error('available_stock') is-invalid @enderror" 
                                   value="{{ old('available_stock', $book->available_stock) }}" min="0">
                            @error('available_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Condition -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kondisi <span class="text-danger">*</span></label>
                            <select name="book_condition" class="form-select @error('book_condition') is-invalid @enderror" required>
                                <option value="baik" {{ old('book_condition', $book->book_condition) == 'baik' ? 'selected' : '' }}>Baik</option>
                                <option value="rusak" {{ old('book_condition', $book->book_condition) == 'rusak' ? 'selected' : '' }}>Rusak</option>
                                <option value="hilang" {{ old('book_condition', $book->book_condition) == 'hilang' ? 'selected' : '' }}>Hilang</option>
                            </select>
                            @error('book_condition')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $book->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Current Cover -->
                    <div class="mb-3">
                        <label class="form-label">Cover Saat Ini</label>
                        @if($book->cover_image)
                        <img src="{{ asset('storage/covers/' . $book->cover_image) }}" alt="{{ $book->title }}" 
                             class="img-thumbnail mb-2" style="max-width: 100%;">
                        @else
                        <div class="bg-secondary text-white text-center py-5">
                            <i class="bi bi-book fs-1"></i>
                            <p>Tidak ada cover</p>
                        </div>
                        @endif
                    </div>

                    <!-- New Cover Image -->
                    <div class="mb-3">
                        <label class="form-label">Ganti Cover Buku</label>
                        <input type="file" name="cover_image" class="form-control @error('cover_image') is-invalid @enderror" 
                               accept="image/*" onchange="previewImage(this)">
                        @error('cover_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Format: JPG, PNG (Max 2MB)</small>
                    </div>

                    <div class="mb-3">
                        <img id="preview" src="" alt="Preview" style="max-width: 100%; display: none;" class="img-thumbnail">
                    </div>

                    <!-- QR Code -->
                    @if($book->qr_code)
                    <div class="mb-3">
                        <label class="form-label">QR Code</label>
                        <img src="{{ asset('storage/qrcodes/' . $book->qr_code) }}" alt="QR Code" class="img-thumbnail">
                    </div>
                    @endif
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.books.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update Buku
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
