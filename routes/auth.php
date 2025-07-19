<?php

/**
 * Bestandsnaam: auth.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2024-12-25
 * Tijd: 16:57:50
 * Doel: Route definities voor alle authenticatie gerelateerde functionaliteit. Bevat registratie, login, logout, wachtwoord reset, email verificatie en wachtwoord bevestiging routes met juiste middleware beveiliging.
 */

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

/**
 * GUEST ROUTES - Voor niet-ingelogde gebruikers
 * 
 * Deze routes zijn alleen toegankelijk voor gasten (niet-geauthenticeerde gebruikers)
 * Middleware 'guest' zorgt ervoor dat ingelogde gebruikers worden omgeleid
 */
Route::middleware('guest')->group(function () {
    
    // GEBRUIKER REGISTRATIE
    // GET route toont registratie formulier
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
    
    // POST route verwerkt registratie gegevens en maakt nieuw account aan
    Route::post('register', [RegisteredUserController::class, 'store']);

    // GEBRUIKER LOGIN
    // GET route toont login formulier
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    
    // POST route verwerkt login credentials en start gebruiker sessie
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // WACHTWOORD RESET AANVRAAG
    // GET route toont "wachtwoord vergeten" formulier
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    
    // POST route verstuurt wachtwoord reset email naar gebruiker
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    // WACHTWOORD RESET UITVOERING
    // GET route toont nieuw wachtwoord formulier (via email link met token)
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    
    // POST route verwerkt nieuw wachtwoord en werkt account bij
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

/**
 * AUTHENTICATED ROUTES - Voor ingelogde gebruikers
 * 
 * Deze routes zijn alleen toegankelijk voor geauthenticeerde gebruikers
 * Middleware 'auth' zorgt ervoor dat gasten worden doorverwezen naar login
 */
Route::middleware('auth')->group(function () {
    
    // EMAIL VERIFICATIE MANAGEMENT
    // GET route toont email verificatie prompt/herinnering
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');
    
    // GET route verwerkt email verificatie via link uit email
    // {id} = gebruiker ID, {hash} = verificatie hash voor beveiliging
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])  // signed = URL signing, throttle = 6 pogingen per minuut
        ->name('verification.verify');
    
    // POST route verstuurt nieuwe verificatie email
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')               // Rate limiting: 6 emails per minuut
        ->name('verification.send');

    // WACHTWOORD BEVESTIGING (voor gevoelige acties)
    // GET route toont wachtwoord bevestiging formulier
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    
    // POST route valideert huidige wachtwoord voor gevoelige acties
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // WACHTWOORD WIJZIGEN (vanuit profiel instellingen)
    // PUT route voor het wijzigen van wachtwoord door ingelogde gebruiker
    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update');

    // GEBRUIKER LOGOUT
    // POST route voor uitloggen en sessie beëindigen
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

/**
 * MIDDLEWARE UITLEG:
 * 
 * - guest: Alleen toegankelijk voor niet-ingelogde gebruikers
 * - auth: Alleen toegankelijk voor ingelogde gebruikers
 * - signed: URL moet cryptografisch ondertekend zijn (beveiliging tegen tampering)
 * - throttle:6,1: Rate limiting - maximaal 6 verzoeken per 1 minuut per IP/gebruiker
 * 
 * BEVEILIGING OVERWEGINGEN:
 * 
 * - Email verificatie URLs zijn signed om tampering te voorkomen
 * - Rate limiting voorkomt spam en brute force aanvallen
 * - Password confirmation vereist voor gevoelige account wijzigingen
 * - Guest/auth middleware voorkomt toegang tot verkeerde routes
 * 
 * ROUTE NAMEN:
 * 
 * Named routes maken het mogelijk om URLs te genereren vanuit code:
 * - route('login') genereert login URL
 * - route('password.reset', $token) genereert reset URL met token
 * - Dit maakt de applicatie flexibeler en makkelijker te onderhouden
 */