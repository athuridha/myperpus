<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display users list
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by role
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->status($request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nim_nip', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(20);

        // Statistics
        $totalUsers = User::count();
        $pendingUsers = User::where('status', 'pending')->count();
        $approvedUsers = User::where('status', 'approved')->count();
        $blockedUsers = User::where('status', 'blocked')->count();

        return view('admin.users.index', compact('users', 'totalUsers', 'pendingUsers', 'approvedUsers', 'blockedUsers'));
    }

    /**
     * Show create user form
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'nim_nip' => ['required', 'string', 'max:20', 'unique:users'],
            'phone' => ['required', 'string', 'max:15'],
            'address' => ['required', 'string'],
            'role' => ['required', 'in:admin,petugas,member'],
            'status' => ['required', 'in:active,pending,blocked'],
            'max_borrow_limit' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'nim_nip' => $request->nim_nip,
                'phone' => $request->phone,
                'address' => $request->address,
                'role' => $request->role,
                'status' => $request->status,
                'max_borrow_limit' => $request->max_borrow_limit,
                'email_verified_at' => now(), // Auto-verify admin-created users
            ]);

            // Log action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'create_user',
                'description' => "Created user: {$user->name} ({$user->role})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil ditambahkan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show edit user form
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'nim_nip' => ['required', 'string', 'max:20', 'unique:users,nim_nip,' . $user->id],
            'phone' => ['required', 'string', 'max:15'],
            'address' => ['required', 'string'],
            'role' => ['required', 'in:admin,petugas,member'],
            'status' => ['required', 'in:active,pending,blocked'],
            'max_borrow_limit' => ['required', 'integer', 'min:1', 'max:10'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $data = $request->except('password');

            // Update password if provided
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            // Log action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'update_user',
                'description' => "Updated user: {$user->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil diupdate.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Approve pending user
     */
    public function approve(User $user)
    {
        if (!$user->isPending()) {
            return back()->with('error', 'User tidak dalam status pending.');
        }

        try {
            $user->approve();

            return redirect()->route('admin.users.index')
                ->with('success', "User {$user->name} berhasil disetujui.");

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Reject pending user
     */
    public function reject(User $user)
    {
        if (!$user->isPending()) {
            return back()->with('error', 'User tidak dalam status pending.');
        }

        try {
            $user->reject();

            return redirect()->route('admin.users.index')
                ->with('success', "User {$user->name} ditolak.");

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Block user
     */
    public function block(Request $request, User $user)
    {
        if ($user->isBlocked()) {
            return back()->with('error', 'User sudah diblokir.');
        }

        $request->validate([
            'reason' => ['required', 'string'],
        ]);

        try {
            $user->block($request->reason);

            return back()->with('success', "User {$user->name} berhasil diblokir.");

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Unblock user
     */
    public function unblock(User $user)
    {
        if (!$user->isBlocked()) {
            return back()->with('error', 'User tidak dalam status blocked.');
        }

        try {
            $user->unblock();

            return back()->with('success', "User {$user->name} berhasil dibuka kembali.");

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display user details
     */
    public function show(User $user)
    {
        $user->load([
            'borrowings.book',
            'fines.borrowing.book',
            'notifications',
            'auditLogs'
        ]);

        return view('admin.users.show', compact('user'));
    }
}
