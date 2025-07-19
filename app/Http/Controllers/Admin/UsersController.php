<?php

/**
 * Bestandsnaam: UsersController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-01-20
 * Tijd: 16:39:00
 * Doel: Deze controller beheert gebruikers in het admin panel inclusief rollen, permissies, 
 *       status beheer, zoekfunctionaliteit en bescherming van system admin accounts.
 */

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
    /**
     * Toont een overzicht van alle gebruikers met paginatie
     * Laadt gebruikers met hun rollen en controleert gebruiker permissies
     * 
     * @return \Inertia\Response
     */
    public function index()
    {
        // Haal gebruikers op met hun rollen en pagineer resultaten
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

    /**
     * Slaat een nieuwe gebruiker op in de database
     * Valideert invoer, hasht wachtwoord en wijst rol toe
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Valideer alle gebruiker gegevens inclusief wachtwoord sterkte
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|exists:roles,name',
        ]);

        // Maak nieuwe gebruiker aan met gehashed wachtwoord
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => User::STATUS_ACTIVE,
        ]);

        // Wijs de geselecteerde rol toe aan de gebruiker
        $user->assignRole($request->role);

        return redirect()->back()->with('success', 'User created successfully.');
    }

    /**
     * Werkt een bestaande gebruiker bij
     * Beschermt system admin account tegen modificaties
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        // Bescherm system administrator tegen modificaties
        if ($user->isSystemAdmin()) {
            return redirect()->back()->with('error', 'System administrator account cannot be modified.');
        }

        // Valideer invoer, negeer huidige gebruiker bij email unieke controle
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name',
        ]);

        try {
            // Werk gebruiker gegevens bij
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // Synchroniseer rollen (verwijdert oude en voegt nieuwe toe)
            $user->syncRoles([$request->role]);

            return redirect()->back()->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while updating the user.' . $e->getMessage());
        }
    }

    /**
     * Verwijdert een gebruiker uit de database
     * Beschermt admin gebruiker tegen verwijdering
     * 
     * @param \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        // Voorkom verwijdering van system administrator
        if ($user->isSystemAdmin()) {
            return redirect()->back()->with('error', 'Cannot delete admin user.');
        }

        // Verwijder gebruiker (soft delete indien geconfigureerd)
        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    /**
     * Schakelt de status van een gebruiker om (actief/opgeschort)
     * Beschermt admin gebruiker tegen status wijzigingen
     * 
     * @param \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus(User $user)
    {
        // Voorkom status wijziging van system administrator
        if ($user->isSystemAdmin()) {
            return redirect()->back()->with('error', 'Cannot modify admin status.');
        }

        // Wissel status tussen actief en opgeschort
        $user->update([
            'status' => $user->status === User::STATUS_ACTIVE
                ? User::STATUS_SUSPENDED
                : User::STATUS_ACTIVE
        ]);

        return redirect()->back()->with('success', 'User status updated successfully.');
    }

    /**
     * Zoekt gebruikers op basis van naam of email
     * Retourneert JSON resultaten voor AJAX verzoeken
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            // Haal zoekterm op uit request
            $query = $request->input('query');

            // Zoek gebruikers op naam of email met gelimiteerde resultaten
            $users = User::with('roles')
                ->where('name', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%")
                ->limit(10)
                ->get()
                ->map(function ($user) {
                    // Transform resultaten naar gewenste format
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
            // Retourneer foutmelding bij problemen
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}