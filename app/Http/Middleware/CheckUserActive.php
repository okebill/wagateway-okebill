<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user is not approved (except admin)
            if (!$user->isAdmin() && !$user->isApproved()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')->with('error', 'Akun Anda belum disetujui oleh administrator. Silakan hubungi admin untuk persetujuan.');
            }
            
            // Check if user status is inactive
            if ($user->status === 'inactive') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.');
            }
            
            // Check if user account is expired
            if ($user->isExpired()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')->with('error', 'Akun Anda telah kedaluwarsa pada ' . $user->account_expires_at->format('d M Y') . '. Silakan hubungi administrator untuk perpanjangan.');
            }
        }
        
        return $next($request);
    }
}
