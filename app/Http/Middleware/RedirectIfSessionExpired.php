<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfSessionExpired
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only redirect to session expired if:
        // 1. User is not authenticated AND
        // 2. There's evidence they were previously logged in (session data exists)
        // 3. They're not already on auth-related pages
        
        if (!Auth::check()) {
            // Check if user was previously authenticated in this session
            $wasAuthenticated = Session::has('_previous_url') || 
                               Session::has('login.intended') || 
                               $request->session()->previousUrl();
            
            // Don't redirect if they're already on auth pages or session expired page
            $authRoutes = ['login', 'register', 'password.request', 'session.expired'];
            $currentRoute = $request->route()?->getName();
            
            if ($wasAuthenticated && !in_array($currentRoute, $authRoutes)) {
                // Store the intended URL so they can continue after login
                if (!$request->expectsJson()) {
                    Session::put('login.intended', $request->url());
                }
                
                return redirect()->route('session.expired');
            }
        }
        
        return $next($request);
    }
}