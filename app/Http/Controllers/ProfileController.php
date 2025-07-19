<?php

/**
 * Bestandsnaam: ProfileController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2024-12-25
 * Tijd: 16:57:50
 * Doel: Controller voor gebruikersprofiel beheer. Behandelt profiel weergave, bijwerken van gebruikersgegevens, account verwijdering en volledig adresbeheer (aanmaken, bijwerken, ophalen).
 */

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Toon het profiel bewerk formulier van de gebruiker
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Werk de profiel informatie van de gebruiker bij
     * 
     * @param \App\Http\Requests\ProfileUpdateRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Vul gebruiker model met gevalideerde gegevens
        $request->user()->fill($request->validated());

        // Als email is gewijzigd, reset email verificatie
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // Sla wijzigingen op
        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Verwijder het gebruikersaccount
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Valideer huidige wachtwoord voor beveiliging
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Log gebruiker uit en verwijder account
        Auth::logout();
        $user->delete();

        // Invalideer sessie voor beveiliging
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Sla een nieuw adres op voor de geauthenticeerde gebruiker
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeAddress(Request $request): JsonResponse
    {
        // Valideer adres gegevens
        $validated = $request->validate([
            'street' => 'required|string|max:255',
            'house_number' => 'required|string|max:20',
            'postal_code' => 'required|string|max:10',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        $user = $request->user();

        // Controleer of gebruiker al een adres heeft
        if ($user->address) {
            return response()->json([
                'message' => 'User already has an address. Use PUT to update.',
                'errors' => ['general' => ['Er bestaat al een adres voor deze gebruiker.']]
            ], 422);
        }

        // Maak nieuw adres aan via relatie
        $address = $user->address()->create($validated);

        return response()->json([
            'message' => 'Adres succesvol toegevoegd.',
            'address' => $address
        ], 201);
    }

    /**
     * Werk het bestaande adres van de gebruiker bij
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAddress(Request $request): JsonResponse
    {
        // Valideer adres gegevens
        $validated = $request->validate([
            'street' => 'required|string|max:255',
            'house_number' => 'required|string|max:20',
            'postal_code' => 'required|string|max:10',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        $user = $request->user();

        // Als geen adres bestaat, maak er een aan
        if (!$user->address) {
            $address = $user->address()->create($validated);
            return response()->json([
                'message' => 'Adres succesvol aangemaakt.',
                'address' => $address
            ], 201);
        }

        // Werk bestaand adres bij
        $user->address->update($validated);

        return response()->json([
            'message' => 'Adres succesvol bijgewerkt.',
            'address' => $user->address->fresh() // Haal bijgewerkte versie op
        ]);
    }

    /**
     * Haal het adres van de gebruiker op
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAddress(Request $request): JsonResponse
    {
        $user = $request->user();

        // Controleer of gebruiker een adres heeft
        if (!$user->address) {
            return response()->json([
                'message' => 'Geen adres gevonden.',
                'address' => null
            ], 404);
        }

        return response()->json([
            'address' => $user->address
        ]);
    }
}