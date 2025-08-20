<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::with('approvedBy')
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);
        
        $stats = [
            'total' => User::count(),
            'approved' => User::approved()->count(),
            'pending' => User::pendingApproval()->count(),
            'active' => User::active()->count(),
            'admins' => User::admins()->count(),
        ];
        
        return view('users.index', compact('users', 'stats'));
    }

    /**
     * Display pending approval users
     */
    public function pendingApproval()
    {
        $users = User::pendingApproval()
                    ->users() // Only regular users, not admins
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);
        
        return view('users.pending-approval', compact('users'));
    }

    /**
     * Approve a user
     */
    public function approve(User $user)
    {
        if ($user->isApproved()) {
            return redirect()->back()->with('warning', 'User sudah disetujui sebelumnya.');
        }

        $user->update([
            'is_approved' => true,
            'approved_at' => Carbon::now(),
            'approved_by' => auth()->id(),
            'status' => 'active', // Auto activate when approved
        ]);

        return redirect()->back()->with('success', "User {$user->name} berhasil disetujui!");
    }

    /**
     * Reject/Disapprove a user
     */
    public function reject(User $user)
    {
        if (!$user->isApproved()) {
            return redirect()->back()->with('warning', 'User belum disetujui sebelumnya.');
        }

        // Prevent rejecting admin
        if ($user->isAdmin()) {
            return redirect()->back()->with('error', 'Tidak dapat menolak akses administrator.');
        }

        $user->update([
            'is_approved' => false,
            'approved_at' => null,
            'approved_by' => null,
            'status' => 'inactive', // Auto deactivate when rejected
        ]);

        return redirect()->back()->with('success', "Persetujuan user {$user->name} berhasil dicabut!");
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,user'],
            'status' => ['required', 'in:active,inactive'],
            'limit_device' => ['required', 'integer', 'min:1', 'max:999'],
            'account_expires_at' => ['nullable', 'date', 'after:today'],
            'is_approved' => ['boolean'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        // Auto approve if admin creates the user
        if ($request->boolean('is_approved', false)) {
            $validated['approved_at'] = Carbon::now();
            $validated['approved_by'] = auth()->id();
        }

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan!');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['approvedBy', 'whatsappDevices']);
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,user'],
            'status' => ['required', 'in:active,inactive'],
            'limit_device' => ['required', 'integer', 'min:1', 'max:999'],
            'account_expires_at' => ['nullable', 'date', 'after:today'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User berhasil diupdate!');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun sendiri!');
        }

        // Prevent deleting other admins (unless you're the main admin)
        if ($user->isAdmin() && !auth()->user()->isAdmin()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus administrator lain!');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', "User {$userName} berhasil dihapus!");
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(User $user)
    {
        // Prevent self-deactivation
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menonaktifkan akun sendiri!');
        }

        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active'
        ]);

        $status = $user->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->route('users.index')->with('success', "User {$user->name} berhasil {$status}!");
    }
}
