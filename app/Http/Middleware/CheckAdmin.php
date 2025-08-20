<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Anda harus login terlebih dahulu.');
        }

        $user = auth()->user();

        // Check if user is approved
        if (!$user->isApproved()) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun Anda belum disetujui oleh administrator. Silakan hubungi admin.');
        }

        // Check if user is admin
        if (!$user->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Hanya administrator yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}
