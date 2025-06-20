<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class SessionExpiredController extends Controller
{
    /**
     * Show session expired page or modal data
     */
    public function show(Request $request): JsonResponse|Response
    {
        // If it's an AJAX request from checkout, return JSON for modal
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'session_expired' => true,
                'message' => 'Je sessie is verlopen voor je veiligheid.',
                'show_modal' => true
            ]);
        }

        return Inertia::render('Auth/SessionExpired');
    }

    /**
     * Handle session expiry actions
     */
    public function handle(Request $request): RedirectResponse
    {
        $action = $request->input('action');

        switch ($action) {
            case 'login':
                // Store checkout as return destination
                if ($request->has('from_checkout')) {
                    return redirect()->route('login', ['return_to' => 'checkout']);
                }
                return redirect()->route('login');

            case 'continue_shopping':
                return redirect()->route('AllCategories');

            case 'view_categories':
                return redirect()->route('AllCategories');

            case 'view_cart':
                return redirect()->route('cart.index');

            case 'view_deals':
                return redirect('/'); // or your deals page

            default:
                return redirect('/');
        }
    }
}