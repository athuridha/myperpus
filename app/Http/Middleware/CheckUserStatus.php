<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if user is blocked
        if ($user->isBlocked()) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Akun Anda telah diblokir. Silakan hubungi administrator.');
        }

        // Check if user is pending approval (only for members)
        if ($user->isMember() && $user->isPending()) {
            // Allow access to dashboard to see pending status, but restrict other actions
            if (!$request->routeIs('member.dashboard')) {
                return redirect()->route('member.dashboard')
                    ->with('warning', 'Akun Anda masih menunggu persetujuan administrator.');
            }
        }

        // Check if email is verified
        if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('warning', 'Silakan verifikasi email Anda terlebih dahulu.');
        }

        return $next($request);
    }
}
