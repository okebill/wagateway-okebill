<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user', // Default role
            'is_approved' => false, // Requires admin approval
            'status' => 'inactive', // Inactive until approved
            'limit_device' => 1, // Default device limit
        ]);

        event(new Registered($user));

        // Don't auto-login, redirect to pending approval page
        return redirect()->route('auth.pending-approval')->with('success', 
            'Registrasi berhasil! Akun Anda sedang menunggu persetujuan administrator. Anda akan mendapat notifikasi via email ketika akun disetujui.'
        );
    }

    /**
     * Display pending approval page
     */
    public function pendingApproval(): View
    {
        return view('auth.pending-approval');
    }
}
