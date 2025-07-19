<?php

/**
 * Bestandsnaam: SettingsController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-01-22
 * Tijd: 21:59:49
 * Doel: Controller voor systeeminstellingen beheer. Toont verschillende instellingen pagina's op basis van gebruikersrol (Admin/Editor) en behandelt wachtwoord wijzigingen met juiste validatie en beveiliging.
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    /**
     * Toon instellingen pagina op basis van gebruikersrol
     * 
     * @return \Inertia\Response|null
     */
    public function index()
    {
        // Toon admin instellingen voor systeembeheerders
        if (auth()->user()->isSystemAdmin()) {
            return Inertia::render('Admin/Settings/Index');
        } 
        // Toon editor instellingen voor editors
        else if (auth()->user()->isEditor()) {
            return Inertia::render('Editor/Settings/Index');
        }

        // Geen instellingen pagina voor andere rollen (return null)
    }

    /**
     * Werk het wachtwoord van de gebruiker bij
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        // Valideer wachtwoord gegevens met sterke beveiligingseisen
        $request->validate([
            'current_password' => ['required', 'current_password'], // Verificeer huidige wachtwoord
            'password' => ['required', Password::defaults(), 'confirmed'], // Nieuw wachtwoord met confirmatie
        ]);

        // Werk gebruiker wachtwoord bij met secure hashing
        $request->user()->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Password updated successfully!');
    }
}