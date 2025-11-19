<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    /**
     * Display registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'nim_nip' => ['required', 'string', 'max:20', 'unique:users'],
            'phone' => ['required', 'string', 'max:15'],
            'address' => ['required', 'string'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'nim_nip.required' => 'NIM/NIP wajib diisi.',
            'nim_nip.unique' => 'NIM/NIP sudah terdaftar.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'address.required' => 'Alamat wajib diisi.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nim_nip' => $request->nim_nip,
            'phone' => $request->phone,
            'address' => $request->address,
            'role' => 'member',
            'status' => 'pending', // Requires admin approval
            'max_borrow_limit' => config('app.max_borrow_limit', 3),
        ]);

        event(new Registered($user));

        // Log registration
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'register',
            'description' => "User {$user->name} registered",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('login')
            ->with('success', 'Registrasi berhasil! Silakan tunggu persetujuan dari administrator dan verifikasi email Anda.');
    }
}
