<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FineController extends Controller
{
    /**
     * Display user's fines
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = $user->fines()->with('borrowing.book');

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'unpaid') {
                $query->unpaid();
            } elseif ($request->status === 'paid') {
                $query->paid();
            }
        }

        $fines = $query->latest()->paginate(10);
        $totalUnpaid = $user->total_unpaid_fines;

        return view('member.fines.index', compact('fines', 'totalUnpaid'));
    }

    /**
     * Show payment form
     */
    public function showPaymentForm(Fine $fine)
    {
        $user = auth()->user();

        // Validate ownership
        if ($fine->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if already paid
        if ($fine->status === 'paid') {
            return back()->with('error', 'Denda ini sudah lunas.');
        }

        return view('member.fines.payment', compact('fine'));
    }

    /**
     * Submit payment proof
     */
    public function submitPayment(Request $request, Fine $fine)
    {
        $user = auth()->user();

        // Validate ownership
        if ($fine->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if already paid
        if ($fine->status === 'paid') {
            return back()->with('error', 'Denda ini sudah lunas.');
        }

        $request->validate([
            'payment_amount' => ['required', 'numeric', 'min:' . $fine->remaining_amount, 'max:' . $fine->remaining_amount],
            'payment_method' => ['required', 'in:transfer,cash,ewallet'],
            'payment_proof' => ['required', 'image', 'max:2048'], // Max 2MB
        ], [
            'payment_amount.required' => 'Jumlah pembayaran wajib diisi.',
            'payment_amount.min' => 'Jumlah pembayaran harus sama dengan sisa denda.',
            'payment_amount.max' => 'Jumlah pembayaran tidak boleh lebih dari sisa denda.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_proof.required' => 'Bukti pembayaran wajib diunggah.',
            'payment_proof.image' => 'Bukti pembayaran harus berupa gambar.',
            'payment_proof.max' => 'Ukuran bukti pembayaran maksimal 2MB.',
        ]);

        try {
            // Store payment proof
            $proofPath = $request->file('payment_proof')->store('payment-proofs', 'public');

            // Update fine with payment submission
            $fine->update([
                'payment_proof' => $proofPath,
                'status' => 'pending_verification',
            ]);

            return redirect()->route('member.fines.index')
                ->with('success', 'Bukti pembayaran berhasil dikirim. Menunggu verifikasi dari petugas.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
