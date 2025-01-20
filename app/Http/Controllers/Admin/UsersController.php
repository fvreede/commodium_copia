<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::with('roles')
            ->paginate(10);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => Role::all(),
            'can' => [
                'create' => Auth::user()->can('create users'),
                'edit' => Auth::user()->can('edit users'),
                'delete' => Auth::user()->can('delete users'),
                'block' => Auth::user()->can('block users'),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => User::STATUS_ACTIVE,
        ]);

        $user->assignRole($request->role);

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        if ($user->isSystemAdmin()) {
            return redirect()->back()->with('error', 'System administrator account cannot be modified.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name',
        ]);

        try {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            $user->syncRoles([$request->role]);

            return redirect()->back()->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while updating the user.' . $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        if ($user->isSystemAdmin()) {
            return redirect()->back()->with('error', 'Cannot delete admin user.');
        }

        $user->delete();
        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->isSystemAdmin()) {
            return redirect()->back()->with('error', 'Cannot modify admin status.');
        }

        $user->update([
            'status' => $user->status === User::STATUS_ACTIVE 
                ? User::STATUS_SUSPENDED 
                : User::STATUS_ACTIVE
        ]);

        return redirect()->back()->with('success', 'User status updated successfully.');
    }

    // Search query for users
    public function search(Request $request)
    {
        try {
            $query = $request->input('query');
    
            $users = User::with('roles')
                ->where('name', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%")
                ->limit(10)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->roles->first()?->name ?? 'No role',
                        'status' => $user->status
                    ];
                });
    
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}