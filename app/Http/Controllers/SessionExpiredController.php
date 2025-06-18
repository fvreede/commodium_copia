<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SessionExpiredController extends Controller
{
    /**
     * Show the session expired page.
     */
    public function show()
    {
        return Inertia::render('SessionExpired');
    }

    /**
     * Handle actions from the session expired page.
     */
    public function handle(Request $request)
    {
        $action = $request->input('action');

        switch ($action) {
            case 'login':
                return redirect()->route('login');
            case 'continue_shopping':
                return redirect()->route('/');
            case 'view_cart':
                return redirect()->route('cart.index');
            default:
                return redirect()->route('/');
        }
    }
}
