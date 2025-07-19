<?php

/**
 * Bestandsnaam: SessionExpiredController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-06-20
 * Tijd: 23:57:21
 * Doel: Controller voor sessie vervaldatum afhandeling. Toont sessie verlopen meldingen via modals of pagina's en behandelt verschillende acties die gebruikers kunnen ondernemen na sessie verloop.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class SessionExpiredController extends Controller
{
    /**
     * Toon sessie verlopen pagina of modal data
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Inertia\Response
     */
    public function show(Request $request): JsonResponse|Response
    {
        // Als het een AJAX verzoek is (bijv. vanuit checkout), return JSON voor modal weergave
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'session_expired' => true,
                'message' => 'Je sessie is verlopen voor je veiligheid.',
                'show_modal' => true
            ]);
        }

        // Voor normale verzoeken, toon dedicated sessie verlopen pagina
        return Inertia::render('Auth/SessionExpired');
    }

    /**
     * Behandel sessie verloop acties en redirect gebruiker naar juiste bestemming
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request): RedirectResponse
    {
        // Haal gewenste actie op uit request
        $action = $request->input('action');

        // Behandel verschillende gebruikersacties na sessie verloop
        switch ($action) {
            case 'login':
                // Sla checkout op als terugkeer bestemming voor naadloze ervaring
                if ($request->has('from_checkout')) {
                    return redirect()->route('login', ['return_to' => 'checkout']);
                }
                return redirect()->route('login');

            case 'continue_shopping':
                // Redirect naar alle categorieën voor verder winkelen
                return redirect()->route('AllCategories');

            case 'view_categories':
                // Redirect naar categorieën overzicht
                return redirect()->route('AllCategories');

            case 'view_cart':
                // Redirect naar winkelwagen (cart blijft mogelijk behouden in storage)
                return redirect()->route('cart.index');

            case 'view_deals':
                // Redirect naar deals/aanbiedingen pagina
                return redirect('/'); // of naar je specifieke deals pagina

            default:
                // Fallback naar homepage voor onbekende acties
                return redirect('/');
        }
    }
}