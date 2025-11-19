<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\Notification;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FineController extends Controller
{
    /**
     * Display all fines
     */
    public function index(Request $request)
    {
        $query = Fine::with(['user', 'borrowing.book']);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'unpaid') {
                $query->unpaid();
            } elseif ($request->status === 'paid') {
                $query->paid();
            } elseif ($request->status === 'pending_verification') {
                $query->where('status', 'pending_verification');
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nim_nip', 'like', "%{$search}%");
            });
        }

        $fines = $query->latest()->paginate(20);

        // Summary statistics for the fines dashboard
        $totalFines = Fine::sum('amount');
        $unpaidFines = Fine::where('status', 'unpaid')->sum('amount');
        $paidFines = Fine::where('status', 'paid')->sum('paid_amount');
        $pendingVerification = Fine::where('status', 'pending_verification')->count();

        // Collection of fines that need manual verification (pending_verification)
        $finesNeedVerification = Fine::with(['user', 'borrowing.book'])
            ->where('status', 'pending_verification')
            ->latest()
            ->get();

        return view('petugas.fines.index', compact(
            'fines',
            'totalFines',
            'unpaidFines',
            'paidFines',
            'pendingVerification',
            'finesNeedVerification'
        ));
    }

    /**
     * Show fine details with payment proof
     */
    public function show(Fine $fine)
    {
        $fine->load(['user', 'borrowing.book']);

        return view('petugas.fines.show', compact('fine'));
    }

    /**
     * Verify payment
     */
    public function verifyPayment(Request $request, Fine $fine)
    {
        if ($fine->status !== 'pending_verification') {
            return back()->with('error', 'Denda ini tidak memerlukan verifikasi.');
        }

        $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'rejection_reason' => ['required_if:action,reject', 'string'],
        ]);

        try {
            DB::beginTransaction();

            if ($request->action === 'approve') {
                // Approve payment
                $fine->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'paid_amount' => $fine->amount,
                ]);

                // Create notification
                Notification::createPaymentConfirmation($fine->user_id, $fine);

                $message = 'Pembayaran berhasil diverifikasi.';

            } else {
                // Reject payment
                $fine->update([
                    'status' => 'unpaid',
                    'payment_proof' => null,
                ]);

                // Create notification
                Notification::createForUser(
                    $fine->user_id,
                    'error',
                    'Pembayaran Ditolak',
                    "Pembayaran denda untuk buku '{$fine->borrowing->book->title}' ditolak. Alasan: {$request->rejection_reason}"
                );

                $message = 'Pembayaran ditolak.';
            }

            // Log action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'verify_payment',
                'description' => "Payment verification for fine #{$fine->id}: {$request->action}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('petugas.fines.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Process cash payment
     */
    public function processCashPayment(Request $request, Fine $fine)
    {
        if ($fine->status === 'paid') {
            return back()->with('error', 'Denda ini sudah lunas.');
        }

        $request->validate([
            'payment_amount' => ['required', 'numeric', 'min:' . $fine->remaining_amount],
            'payment_method' => ['required', 'in:cash,transfer,ewallet'],
        ]);

        try {
            DB::beginTransaction();

            $fine->addPayment(
                $request->payment_amount,
                $request->payment_method,
                null // No proof for cash payment
            );

            // Create notification
            Notification::createPaymentConfirmation($fine->user_id, $fine);

            // Log action
            AuditLog::logFinePayment($fine);

            DB::commit();

            return redirect()->route('petugas.fines.index')
                ->with('success', 'Pembayaran berhasil diproses.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Reduce fine amount (admin/petugas privilege)
     */
    public function reduceFine(Request $request, Fine $fine)
    {
        $request->validate([
            'new_amount' => ['required', 'numeric', 'min:0', 'max:' . $fine->amount],
            'reason' => ['required', 'string'],
        ]);

        try {
            DB::beginTransaction();

            $oldAmount = $fine->amount;
            $fine->reduceFine($request->new_amount, $request->reason);

            // Create notification
            Notification::createForUser(
                $fine->user_id,
                'success',
                'Denda Dikurangi',
                "Denda Anda dikurangi dari Rp " . number_format($oldAmount, 0, ',', '.') .
                " menjadi Rp " . number_format($request->new_amount, 0, ',', '.') .
                ". Alasan: {$request->reason}"
            );

            // Log action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'reduce_fine',
                'description' => "Reduced fine #{$fine->id} from {$oldAmount} to {$request->new_amount}. Reason: {$request->reason}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return back()->with('success', 'Denda berhasil dikurangi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
