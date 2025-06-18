<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = Auth::user();
            
        if ($user && $user->hasRole('admin')) {
            // Force redirect by removing any intended URL
            session()->forget('url.intended');
        
            // Get admin dashboard route
            return redirect(route('admin.dashboard'));
        }

        // Redirect to dashboard if not admin
        return redirect(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/')->with('logout_success', true);
    }

    /**
     * Show session expired page
     */
    public function sessionExpired(): Response
    {
        return Inertia::render('Auth/SessionExpired');
    }

    /**
     * Handle session expiry redirect
     */
    public function handleSessionExpiry(Request $request): RedirectResponse
    {
        // Clear any existing session data
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to session expired page
        return redirect()->route('session.expired');
    }
}