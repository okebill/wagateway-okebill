<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();
        
        // Check if user status is inactive
        if ($user->status === 'inactive') {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.');
        }
        
        // Check if user account is expired
        if ($user->isExpired()) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Akun Anda telah kedaluwarsa pada ' . $user->account_expires_at->format('d M Y') . '. Silakan hubungi administrator untuk perpanjangan.');
        }

        $request->session()->regenerate();

        // Check if account will expire soon (within 7 days)
        if ($user->account_expires_at && $user->account_expires_at->diffInDays(now()) <= 7) {
            session()->flash('warning', 'Peringatan: Akun Anda akan kedaluwarsa pada ' . $user->account_expires_at->format('d M Y') . ' (' . $user->account_expires_at->diffForHumans() . ').');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
