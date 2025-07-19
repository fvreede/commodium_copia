<?php

/**
 * Bestandsnaam: MigrateSessionCartToDatabase.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-06-24
 * Tijd: 21:01:32
 * Doel: Event Listener die winkelwagen items migreert van sessie naar database wanneer een gebruiker inlogt. Zorgt voor naadloze overgang van anonieme naar geauthenticeerde winkelervaring.
 */

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;

class MigrateSessionCartToDatabase
{
    protected CartService $cartService;

    /**
     * Constructor - Injecteer de CartService dependency
     * 
     * @param \App\Services\CartService $cartService
     */
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Behandel het Login event door winkelwagen te migreren
     * Wordt automatisch aangeroepen wanneer een gebruiker inlogt
     * 
     * @param \Illuminate\Auth\Events\Login $event
     * @return void
     */
    public function handle(Login $event): void
    {
        // Migreer winkelwagen items van sessie naar database voor de ingelogde gebruiker
        // Dit zorgt ervoor dat items die toegevoegd zijn voordat inloggen behouden blijven
        $this->cartService->migrateSessionToDatabase();
    }
}