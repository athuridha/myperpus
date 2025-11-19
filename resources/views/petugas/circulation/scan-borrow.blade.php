@extends('layouts.app')

@section('title', 'Scan Peminjaman')
@section('page-title', 'Scan QR - Peminjaman Buku')

@section('sidebar')
<nav class="nav flex-column">
    <a href="{{ route('petugas.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('petugas.circulation.scan-borrow') }}" class="nav-link active">
        <i class="bi bi-qr-code-scan"></i> Scan Pinjam
    </a>
    <a href="{{ route('petugas.circulation.scan-return') }}" class="nav-link">
        <i class="bi bi-qr-code"></i> Scan Kembali
    </a>
    <a href="{{ route('petugas.circulation.index') }}" class="nav-link">
        <i class="bi bi-list-ul"></i> Daftar Sirkulasi
    </a>
    <a href="{{ route('petugas.fines.index') }}" class="nav-link">
        <i class="bi bi-cash"></i> Kelola Denda
    </a>
    <a href="{{ route('petugas.reshelving.index') }}" class="nav-link">
        <i class="bi bi-archive"></i> Reshelving
    </a>
</nav>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-qr-code-scan me-2"></i>Scan QR Code - Peminjaman Buku</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('petugas.circulation.process-borrow') }}" method="POST">
                    @csrf

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Petunjuk:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Scan QR code kartu anggota/member</li>
                            <li>Scan QR code buku yang akan dipinjam</li>
                            <li>Klik tombol "Proses Peminjaman"</li>
                        </ol>
                    </div>

                    <!-- User QR -->
                    <div class="mb-4">
                        <label for="user_qr" class="form-label fw-bold">1. QR Code Member</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                            <input type="text" class="form-control form-control-lg @error('user_qr') is-invalid @enderror"
                                   id="user_qr" name="user_qr" placeholder="Scan QR Code Member..." required>
                        </div>
                        @error('user_qr')
                        <div class="text-danger small">{{ $message }}</div>
                        @enderror
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openQRScanner('user_qr')">
                            <i class="bi bi-camera"></i> Buka Scanner
                        </button>
                    </div>

                    <!-- Book QR -->
                    <div class="mb-4">
                        <label for="book_qr" class="form-label fw-bold">2. QR Code Buku</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="bi bi-book"></i></span>
                            <input type="text" class="form-control form-control-lg @error('book_qr') is-invalid @enderror"
                                   id="book_qr" name="book_qr" placeholder="Scan QR Code Buku..." required>
                        </div>
                        @error('book_qr')
                        <div class="text-danger small">{{ $message }}</div>
                        @enderror
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openQRScanner('book_qr')">
                            <i class="bi bi-camera"></i> Buka Scanner
                        </button>
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Proses Peminjaman
                        </button>
                        <a href="{{ route('petugas.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Manual Input Help -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="text-muted">Untuk Testing Manual:</h6>
                <p class="small mb-0">Format QR Code:</p>
                <ul class="small">
                    <li><strong>Member:</strong> {"id":1,"nim_nip":"123456","name":"John Doe","type":"user"}</li>
                    <li><strong>Buku:</strong> {"id":1,"isbn":"978-xxx","title":"Judul Buku","type":"book"}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openQRScanner(targetInput) {
    alert('QR Scanner akan dibuka. Untuk implementasi lengkap, gunakan library seperti html5-qrcode atau instascan.');
    // Implementasi QR scanner menggunakan camera akan ditambahkan dengan library external
}

// Auto focus ke input berikutnya setelah scan
document.getElementById('user_qr').addEventListener('change', function() {
    if(this.value) {
        document.getElementById('book_qr').focus();
    }
});
</script>
@endpush
@endsection
