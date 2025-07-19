<?php

/**
 * Bestandsnaam: AdminController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-01-22
 * Tijd: 21:57:27
 * Doel: Controller voor het admin dashboard. Verzamelt en toont statistieken en overzichten voor beheerders van het systeem.
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

class AdminController extends Controller
{
    /**
     * Toon het admin dashboard met statistieken
     * 
     * @return \Inertia\Response
     */
    public function dashboard()
    {
        // Verzamel belangrijke statistieken voor het dashboard
        $stats = [
            'users' => User::count(), // Totaal aantal gebruikers in het systeem
        ];

        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => $stats,
        ]);
    }
}