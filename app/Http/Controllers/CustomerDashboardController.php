<?php

/**
 * Bestandsnaam: CustomerDashboardController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-07-01
 * Tijd: 01:25:04
 * Doel: Controller voor het klanten dashboard. Toont een overzicht van actieve bestellingen en bestelhistorie voor ingelogde klanten.
 */

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class CustomerDashboardController extends Controller
{
    /**
     * Toon het klanten dashboard met actieve bestellingen en bestelhistorie
     * 
     * @return \Inertia\Response
     */
    public function index(): Response
    {
        // Haal de huidige ingelogde gebruiker op
        $user = auth()->user();

        return Inertia::render('Dashboard', [
            // Haal actieve bestellingen op (pending en processing status)
            'activeOrders' => $user->orders()
                ->with(['items.product', 'deliverySlot']) // Laad gerelateerde data voor complete informatie
                ->whereIn('status', ['pending', 'processing'])
                ->latest() // Nieuwste eerst
                ->get(),

            // Haal bestelhistorie op (alleen voltooide bestellingen)
            'orderHistory' => $user->orders()
                ->with(['items.product', 'deliverySlot']) // Laad gerelateerde data voor complete informatie
                ->where('status', 'completed')
                ->latest() // Nieuwste eerst
                ->paginate(10) // Pagineer voor betere performance bij veel bestellingen
        ]);
    }
}