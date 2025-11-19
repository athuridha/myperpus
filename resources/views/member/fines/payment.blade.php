@extends('layouts.app')

@section('title', 'Pembayaran Denda')
@section('page-title', 'Pembayaran Denda')

@section('sidebar')
<nav class="nav flex-column">
    <a href="{{ route('member.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('member.books.index') }}" class="nav-link">
        <i class="bi bi-book"></i> Katalog Buku
    </a>
    <a href="{{ route('member.borrowings.index') }}" class="nav-link">
        <i class="bi bi-clock-history"></i> Riwayat Peminjaman
    </a>
    <a href="{{ route('member.fines.index') }}" class="nav-link active">
        <i class="bi bi-cash"></i> Denda
    </a>
</nav>
@endsection

@section('content')
<div class="mb-3">
    <a href="{{ route('member.fines.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-cash me-2"></i>Pembayaran Denda</h5>
            </div>
            <div class="card-body">
                <!-- Fine Details -->
                <div class="alert alert-info">
                    <h6>Detail Peminjaman:</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td width="150">Buku</td>
                            <td>: <strong>{{ $fine->borrowing->book->title }}</strong></td>
                        </tr>
                        <tr>
                            <td>Terlambat</td>
                            <td>: {{ $fine->borrowing->days_overdue }} hari</td>
                        </tr>
                        <tr>
                            <td>Jumlah Denda</td>
                            <td>: <strong class="text-danger fs-5">{{ $fine->formatted_amount }}</strong></td>
                        </tr>
                    </table>
                </div>

                <!-- Payment Form -->
                <form action="{{ route('member.fines.submit-payment', $fine) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="payment_amount" class="form-label">Jumlah Pembayaran</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control @error('payment_amount') is-invalid @enderror"
                                   id="payment_amount" name="payment_amount"
                                   value="{{ $fine->remaining_amount }}" readonly>
                        </div>
                        @error('payment_amount')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Pembayaran harus sesuai dengan jumlah denda</small>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Metode Pembayaran</label>
                        <select class="form-select @error('payment_method') is-invalid @enderror"
                                id="payment_method" name="payment_method" required>
                            <option value="">Pilih metode pembayaran</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="ewallet">E-Wallet (GoPay, OVO, DANA)</option>
                            <option value="cash">Tunai di Perpustakaan</option>
                        </select>
                        @error('payment_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="payment_proof" class="form-label">Bukti Pembayaran</label>
                        <input type="file" class="form-control @error('payment_proof') is-invalid @enderror"
                               id="payment_proof" name="payment_proof" accept="image/*" required>
                        @error('payment_proof')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Upload foto/screenshot bukti pembayaran (max 2MB)</small>
                    </div>

                    <!-- Payment Instructions -->
                    <div class="alert alert-warning">
                        <h6><i class="bi bi-info-circle me-2"></i>Informasi Pembayaran:</h6>
                        <ul class="mb-0 small">
                            <li><strong>Transfer Bank:</strong> BCA 1234567890 a.n Perpustakaan MyPerpus</li>
                            <li><strong>E-Wallet:</strong> 08123456789 a.n Perpustakaan MyPerpus</li>
                            <li><strong>Tunai:</strong> Bayar langsung di loket perpustakaan</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-send"></i> Kirim Bukti Pembayaran
                        </button>
                        <a href="{{ route('member.fines.index') }}" class="btn btn-outline-secondary">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
