@extends('layouts.app')

@section('title', 'Detail Denda')
@section('page-title', 'Detail & Verifikasi Denda')

@section('sidebar')
<nav class="nav flex-column">
    <a href="{{ route('petugas.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('petugas.circulation.index') }}" class="nav-link">
        <i class="bi bi-arrow-left-right"></i> Sirkulasi
    </a>
    <a href="{{ route('petugas.fines.index') }}" class="nav-link active">
        <i class="bi bi-cash"></i> Denda
    </a>
    <a href="{{ route('petugas.reshelving.index') }}" class="nav-link">
        <i class="bi bi-arrow-clockwise"></i> Reshelving
    </a>
</nav>
@endsection

@section('content')
<div class="mb-3">
    <a href="{{ route('petugas.fines.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Fine Details -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Informasi Denda
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">ID Denda</th>
                        <td>: #{{ $fine->id }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>: 
                            @if($fine->status === 'paid')
                            <span class="badge bg-success">Lunas</span>
                            @elseif($fine->status === 'pending_verification')
                            <span class="badge bg-warning">Pending Verifikasi</span>
                            @elseif($fine->status === 'reduced')
                            <span class="badge bg-info">Dikurangi</span>
                            @else
                            <span class="badge bg-danger">Belum Dibayar</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Jumlah Denda</th>
                        <td>: <span class="h5 text-danger">Rp {{ number_format($fine->amount, 0, ',', '.') }}</span></td>
                    </tr>
                    <tr>
                        <th>Sudah Dibayar</th>
                        <td>: Rp {{ number_format($fine->paid_amount, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Sisa</th>
                        <td>: <span class="fw-bold {{ ($fine->amount - $fine->paid_amount) > 0 ? 'text-danger' : 'text-success' }}">
                            Rp {{ number_format($fine->amount - $fine->paid_amount, 0, ',', '.') }}
                        </span></td>
                    </tr>
                    <tr>
                        <th>Metode Pembayaran</th>
                        <td>: 
                            @if($fine->payment_method)
                            <span class="badge bg-{{ $fine->payment_method == 'cash' ? 'success' : 'info' }}">
                                {{ $fine->payment_method == 'cash' ? 'Tunai' : 'QRIS' }}
                            </span>
                            @else
                            <span class="text-muted">Belum ada</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Tanggal Pembayaran</th>
                        <td>: {{ $fine->payment_date ? $fine->payment_date->format('d M Y H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Catatan</th>
                        <td>: {{ $fine->notes ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Dibuat</th>
                        <td>: {{ $fine->created_at->format('d M Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- User Info -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="bi bi-person me-2"></i>Informasi Peminjam
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Nama</th>
                        <td>: {{ $fine->user->name }}</td>
                    </tr>
                    <tr>
                        <th>NIM/NIP</th>
                        <td>: {{ $fine->user->nim_nip }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>: {{ $fine->user->email }}</td>
                    </tr>
                    <tr>
                        <th>Telepon</th>
                        <td>: {{ $fine->user->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status Akun</th>
                        <td>: <span class="badge bg-{{ $fine->user->status === 'active' ? 'success' : 'warning' }}">
                            {{ ucfirst($fine->user->status) }}
                        </span></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Borrowing Info -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="bi bi-book me-2"></i>Informasi Peminjaman
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Buku</th>
                        <td>: <strong>{{ $fine->borrowing->book->title }}</strong></td>
                    </tr>
                    <tr>
                        <th>Penulis</th>
                        <td>: {{ $fine->borrowing->book->author }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Pinjam</th>
                        <td>: {{ $fine->borrowing->borrow_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <th>Jatuh Tempo</th>
                        <td>: {{ $fine->borrowing->due_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Kembali</th>
                        <td>: {{ $fine->borrowing->return_date ? $fine->borrowing->return_date->format('d M Y') : 'Belum dikembalikan' }}</td>
                    </tr>
                    <tr>
                        <th>Keterlambatan</th>
                        <td>: <span class="badge bg-danger">{{ $fine->borrowing->days_overdue }} hari</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Payment Proof -->
        @if($fine->payment_proof)
        <div class="card mb-3">
            <div class="card-header">
                <i class="bi bi-image me-2"></i>Bukti Pembayaran
            </div>
            <div class="card-body text-center">
                <img src="{{ asset('storage/payment-proofs/' . $fine->payment_proof) }}" 
                     alt="Bukti Pembayaran" 
                     class="img-fluid mb-3"
                     style="max-height: 400px;">
                <br>
                <a href="{{ asset('storage/payment-proofs/' . $fine->payment_proof) }}" 
                   target="_blank" 
                   class="btn btn-sm btn-primary">
                    <i class="bi bi-arrows-fullscreen"></i> Lihat Ukuran Penuh
                </a>
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-gear me-2"></i>Aksi
            </div>
            <div class="card-body">
                @if($fine->status === 'pending_verification')
                <!-- Verify Payment -->
                <form action="{{ route('petugas.fines.verify', $fine) }}" method="POST" class="mb-3">
                    @csrf
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Verifikasi Pembayaran</strong><br>
                        Pastikan bukti pembayaran valid sebelum memverifikasi.
                    </div>
                    <button type="submit" class="btn btn-success w-100" 
                            onclick="return confirm('Yakin verifikasi pembayaran ini?')">
                        <i class="bi bi-check-circle"></i> Verifikasi & Approve
                    </button>
                </form>
                @endif

                @if($fine->status === 'unpaid')
                <!-- Cash Payment -->
                <form action="{{ route('petugas.fines.cash-payment', $fine) }}" method="POST" class="mb-3">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Jumlah Pembayaran Tunai</label>
                        <input type="number" name="amount" class="form-control" 
                               value="{{ $fine->amount }}" min="1" max="{{ $fine->amount }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-cash"></i> Input Pembayaran Tunai
                    </button>
                </form>

                <hr>

                <!-- Reduce Fine -->
                <form action="{{ route('petugas.fines.reduce', $fine) }}" method="POST">
                    @csrf
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Pengurangan Denda</strong><br>
                        Hanya untuk kasus tertentu dengan persetujuan.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Pengurangan</label>
                        <input type="number" name="reduction_amount" class="form-control" 
                               min="1" max="{{ $fine->amount }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Pengurangan</label>
                        <textarea name="reason" class="form-control" rows="2" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning w-100"
                            onclick="return confirm('Yakin mengurangi denda?')">
                        <i class="bi bi-dash-circle"></i> Kurangi Denda
                    </button>
                </form>
                @endif

                @if($fine->status === 'paid')
                <div class="alert alert-success text-center">
                    <i class="bi bi-check-circle display-1 mb-3"></i>
                    <h5>Denda Sudah Lunas</h5>
                    <p class="mb-0">Dibayar pada {{ $fine->payment_date->format('d M Y H:i') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
