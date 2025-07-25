<?php

/**
 * Bestandsnaam: ProfileController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.6
 * Datum: 2025-07-25
 * Tijd: 16:32
 * Doel: Controller voor gebruikersprofiel beheer. Behandelt profiel weergave, bijwerken van 
 *       gebruikersgegevens, account verwijdering en volledig adresbeheer (aanmaken, bijwerken, 
 *       ophalen) voor checkout en bezorgfunctionaliteit. Ondersteunt zowel web als API endpoints.
 */

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\UserAddress;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

// TEST
use Illuminate\Support\Facades\DB;        // ← NIEUWE IMPORT

/**
 * ProfileController
 *
 * Behandelt alle gebruikersprofiel gerelateerde functionaliteit:
 * - Profiel informatie weergave en bijwerken
 * - Account verwijdering met beveiligingsvalidatie
 * - Adresbeheer voor checkout en bezorging
 * - API endpoints voor address CRUD operaties
 *
 * @uses App\Http\Requests\ProfileUpdateRequest Voor profiel validatie
 * @uses App\Models\UserAddress Voor adres model operaties
 * @uses Inertia\Inertia Voor frontend view rendering
 */
class ProfileController extends Controller
{
    /**
     * ========== PROFIEL MANAGEMENT METHODES ==========
     */

    /**
     * Toon het profiel bewerk formulier van de gebruiker
     * 
     * Rendert de profiel edit pagina met gebruikersgegevens en email verificatie status.
     * Gebruikt Inertia.js voor Vue.js frontend integratie.
     * 
     * @param \Illuminate\Http\Request $request Het HTTP request object
     * @return \Inertia\Response Inertia response met profiel data
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Werk de profiel informatie van de gebruiker bij
     * 
     * Behandelt het bijwerken van basis gebruikersgegevens zoals naam en email.
     * Reset email verificatie als email adres wordt gewijzigd voor beveiligingsdoeleinden.
     * 
     * @param \App\Http\Requests\ProfileUpdateRequest $request Gevalideerd profiel update request
     * @return \Illuminate\Http\RedirectResponse Redirect naar profiel edit pagina
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Vul gebruiker model met gevalideerde gegevens
        $request->user()->fill($request->validated());

        // Als email is gewijzigd, reset email verificatie om beveiliging te waarborgen
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
            
            Log::info('Email address changed for user', [
                'user_id' => $request->user()->id,
                'old_email' => $request->user()->getOriginal('email'),
                'new_email' => $request->user()->email
            ]);
        }

        // Sla wijzigingen op in database
        $request->user()->save();

        Log::info('Profile updated successfully', [
            'user_id' => $request->user()->id
        ]);

        return Redirect::route('profile.edit')->with('status', 'Profiel succesvol bijgewerkt.');
    }

    /**
     * Verwijder het gebruikersaccount
     * 
     * Behandelt volledige account verwijdering na wachtwoord bevestiging.
     * Logt gebruiker uit en invalideert sessie voor beveiligingsdoeleinden.
     * 
     * @param \Illuminate\Http\Request $request Het HTTP request object
     * @return \Illuminate\Http\RedirectResponse Redirect naar homepage
     * @throws \Illuminate\Validation\ValidationException Als wachtwoord incorrect is
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Valideer huidige wachtwoord voor beveiliging
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $userId = $user->id;

        Log::info('User account deletion initiated', [
            'user_id' => $userId,
            'email' => $user->email
        ]);

        // Log gebruiker uit voordat account wordt verwijderd
        Auth::logout();

        // Verwijder gebruikersaccount (cascade delete zal gerelateerde data verwijderen)
        $user->delete();

        // Invalideer sessie voor beveiliging
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('User account deleted successfully', [
            'deleted_user_id' => $userId
        ]);

        return Redirect::to('/')->with('status', 'Je account is succesvol verwijderd.');
    }

    /**
     * ========== ADRESBEHEER API METHODES ==========
     */

    /**
     * Sla een nieuw adres op of werk bestaand adres bij voor de geauthenticeerde gebruiker
     * 
     * Deze methode behandelt zowel nieuwe adres creatie als het bijwerken van bestaande adressen.
     * Gebruikt Nederlandse postcode validatie en ondersteunt volledige adres informatie
     * voor checkout en bezorging functionaliteit.
     * 
     * @param \Illuminate\Http\Request $request Het HTTP request object met adres data
     * @return \Illuminate\Http\JsonResponse JSON response met adres data of error informatie
     * @throws \Illuminate\Validation\ValidationException Bij ongeldige input data
     */
    public function storeAddress(Request $request): JsonResponse
    {
        try {
            $this->ensureSessionConsistency($request);

            // ========== ENHANCED DEBUG LOGGING - START ==========
            Log::info('=== ADDRESS CREATION ATTEMPT STARTED ===', [
                'timestamp' => now()->toISOString(),
                'auth_check' => Auth::check(),
                'auth_id' => Auth::id(),
                'session_id' => session()->getId(),
                'csrf_token' => $request->header('X-CSRF-TOKEN') ? 'Present' : 'Missing',
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
                'request_method' => $request->method(),
                'request_url' => $request->url(),
                'session_data_keys' => array_keys(session()->all()),
                'has_login_web_session' => session()->has('login.web'),
                'session_lifetime' => config('session.lifetime')
            ]);

            // Valideer adres gegevens met Nederlandse postcode format en vereiste velden
            $validated = $request->validate([
                'street' => 'required|string|max:255',                                    // Straatnaam (verplicht)
                'house_number' => 'required|string|max:50',                               // Huisnummer met mogelijke toevoeging
                'postal_code' => 'required|string|regex:/^[0-9]{4}\s?[A-Za-z]{2}$/|max:10', // Nederlandse postcode format (1234AB)
                'city' => 'required|string|max:255',                                      // Woonplaats (verplicht)
                'country' => 'required|string|max:100',                                   // Land (standaard Nederland)
            ]);

            Log::info('Address validation passed', [
                'validated_fields' => array_keys($validated)
            ]);

            $user = $request->user();
            
            // ========== ENHANCED USER AUTHENTICATION DEBUG ==========
            if (!$user) {
                Log::error('=== NO AUTHENTICATED USER FOUND ===', [
                    'auth_check' => Auth::check(),
                    'auth_id' => Auth::id(),
                    'session_id' => session()->getId(),
                    'session_all' => session()->all(),
                    'request_headers' => $request->headers->all(),
                    'middleware_passed' => 'auth middleware should have blocked this'
                ]);
                
                return response()->json([
                    'message' => 'DEBUG - Geen geauthenticeerde gebruiker gevonden',
                    'auth_check' => Auth::check(),
                    'auth_id' => Auth::id(),
                    'session_id' => session()->getId()
                ], 401);
            }

            // ========== USER DATABASE VERIFICATION ==========
            Log::info('User found in session, verifying database consistency', [
                'session_user_id' => $user->id,
                'session_user_email' => $user->email,
                'user_object_type' => get_class($user),
                'user_created_at' => $user->created_at,
                'user_updated_at' => $user->updated_at
            ]);

            // Controleer of user echt bestaat in database
            $dbUser = \App\Models\User::find($user->id);
            if (!$dbUser) {
                Log::error('=== USER EXISTS IN SESSION BUT NOT IN DATABASE ===', [
                    'session_user_id' => $user->id,
                    'session_user_email' => $user->email,
                    'db_query_result' => 'null',
                    'possible_cause' => 'Database transaction not committed or user deleted'
                ]);
                
                return response()->json([
                    'message' => 'DEBUG - Gebruiker bestaat in sessie maar niet in database',
                    'session_user_id' => $user->id,
                    'user_email' => $user->email
                ], 500);
            }

            Log::info('User database verification successful', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'db_user_created_at' => $dbUser->created_at,
                'session_vs_db_match' => $user->email === $dbUser->email
            ]);

            // ========== ADDRESS RELATIONSHIP DEBUG ==========
            Log::info('Checking existing address relationship', [
                'user_id' => $user->id
            ]);

            // Controleer of gebruiker al een adres heeft (met extra debug info)
            try {
                $existingAddress = $user->address;
                Log::info('Address relationship query completed', [
                    'has_existing_address' => $existingAddress !== null,
                    'existing_address_id' => $existingAddress?->id,
                    'existing_address_data' => $existingAddress ? [
                        'street' => $existingAddress->street,
                        'city' => $existingAddress->city,
                        'created_at' => $existingAddress->created_at
                    ] : null
                ]);
            } catch (\Exception $relationError) {
                Log::error('Error accessing user address relationship', [
                    'user_id' => $user->id,
                    'error' => $relationError->getMessage(),
                    'trace' => $relationError->getTraceAsString()
                ]);
                throw $relationError;
            }
            
            if ($existingAddress) {
                // ========== UPDATE EXISTING ADDRESS ==========
                Log::info('Updating existing address', [
                    'user_id' => $user->id,
                    'address_id' => $existingAddress->id,
                    'old_data' => [
                        'street' => $existingAddress->street,
                        'house_number' => $existingAddress->house_number,
                        'city' => $existingAddress->city
                    ],
                    'new_data' => $validated
                ]);

                $existingAddress->update($validated);
                $address = $existingAddress;
                
                Log::info('Address updated successfully', [
                    'user_id' => $user->id,
                    'address_id' => $address->id,
                    'updated_fields' => array_keys($validated)
                ]);
                
                $message = 'Adres succesvol bijgewerkt';
                $statusCode = 200;
            } else {
                // ========== CREATE NEW ADDRESS ==========
                Log::info('Creating new address for user', [
                    'user_id' => $user->id,
                    'address_data' => $validated
                ]);

                try {
                    $address = UserAddress::create([
                        'user_id' => $user->id,
                        ...$validated
                    ]);
                    
                    Log::info('New address created successfully', [
                        'user_id' => $user->id,
                        'address_id' => $address->id,
                        'address_created_at' => $address->created_at
                    ]);
                } catch (\Exception $createError) {
                    Log::error('Error creating new address', [
                        'user_id' => $user->id,
                        'error' => $createError->getMessage(),
                        'sql_error' => $createError instanceof \Illuminate\Database\QueryException ? $createError->getSql() : 'Not SQL error',
                        'trace' => $createError->getTraceAsString(),
                        'address_data' => $validated
                    ]);
                    throw $createError;
                }
                
                $message = 'Adres succesvol aangemaakt';
                $statusCode = 201;
            }

            // ========== FINAL VERIFICATION ==========
            Log::info('Address operation completed, performing final verification', [
                'user_id' => $user->id,
                'address_id' => $address->id,
                'message' => $message,
                'status_code' => $statusCode
            ]);

            // Refresh het adres object met relaties voor complete response
            $address->load('user');

            Log::info('=== ADDRESS CREATION ATTEMPT COMPLETED SUCCESSFULLY ===', [
                'user_id' => $user->id,
                'address_id' => $address->id,
                'final_status' => 'success'
            ]);

            return response()->json([
                'message' => $message,
                'address' => $address
            ], $statusCode);

        } catch (ValidationException $e) {
            Log::warning('=== ADDRESS VALIDATION FAILED ===', [
                'user_id' => Auth::id(),
                'errors' => $e->errors(),
                'input_data' => $request->except(['_token']),
                'auth_check' => Auth::check()
            ]);
            
            return response()->json([
                'message' => 'Validatie fout',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('=== CRITICAL ERROR SAVING ADDRESS ===', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'input_data' => $request->except(['_token']),
                'auth_check' => Auth::check(),
                'session_id' => session()->getId(),
                'csrf_token' => $request->header('X-CSRF-TOKEN') ? 'Present' : 'Missing',
                'database_connection' => DB::connection()->getPdo() ? 'Connected' : 'Disconnected'
            ]);

            // TEMPORARY DEBUG: Return detailed error information
            return response()->json([
                'message' => 'DEBUG - Critical Error: ' . $e->getMessage(),
                'error_type' => get_class($e),
                'file' => basename($e->getFile()),
                'line' => $e->getLine(),
                'user_authenticated' => Auth::check(),
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
                'sql_error' => $e instanceof \Illuminate\Database\QueryException ? $e->getSql() : 'Not SQL error'
            ], 500);
        }
    }

    /**
     * Werk het bestaande adres van de gebruiker bij
     * 
     * Deze methode is specifiek voor PUT requests en behandelt het bijwerken van 
     * bestaande adressen. Als geen adres bestaat, wordt automatisch een nieuw adres aangemaakt.
     * 
     * @param \Illuminate\Http\Request $request Het HTTP request object met bijgewerkte adres data
     * @return \Illuminate\Http\JsonResponse JSON response met bijgewerkte adres data
     * @throws \Illuminate\Validation\ValidationException Bij ongeldige input data
     */
    public function updateAddress(Request $request): JsonResponse
    {
        try {
            // Valideer adres gegevens met Nederlandse standaarden
            $validated = $request->validate([
                'street' => 'required|string|max:255',
                'house_number' => 'required|string|max:50',
                'postal_code' => 'required|string|regex:/^[0-9]{4}\s?[A-Za-z]{2}$/|max:10',
                'city' => 'required|string|max:255',
                'country' => 'required|string|max:100',
            ]);

            $user = $request->user();
            
            // Controleer authenticatie
            if (!$user) {
                Log::warning('Unauthorized address update attempt');
                return response()->json([
                    'message' => 'Niet geautoriseerd'
                ], 401);
            }

            // Zoek het bestaande adres van de gebruiker
            $address = $user->address;
            
            if (!$address) {
                // Geen bestaand adres gevonden, maak een nieuwe aan
                $address = UserAddress::create([
                    'user_id' => $user->id,
                    ...$validated
                ]);
                
                Log::info('New address created via update method for user', [
                    'user_id' => $user->id,
                    'address_id' => $address->id
                ]);
                
                return response()->json([
                    'message' => 'Adres succesvol aangemaakt',
                    'address' => $address
                ], 201);
            }

            // Update het bestaande adres met nieuwe gegevens
            $address->update($validated);
            
            Log::info('Address updated via PUT method for user', [
                'user_id' => $user->id,
                'address_id' => $address->id,
                'updated_fields' => array_keys($validated)
            ]);

            // Refresh het adres object om laatste wijzigingen op te halen
            $address->refresh();

            return response()->json([
                'message' => 'Adres succesvol bijgewerkt',
                'address' => $address
            ], 200);

        } catch (ValidationException $e) {
            Log::warning('Address update validation failed', [
                'user_id' => Auth::id(),
                'errors' => $e->errors()
            ]);
            
            return response()->json([
                'message' => 'Validatie fout',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error updating address', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Er is een onverwachte fout opgetreden bij het bijwerken van het adres'
            ], 500);
        }
    }

    /**
     * Haal het huidige adres van de gebruiker op
     * 
     * Retourneert het bezorgadres van de geauthenticeerde gebruiker voor gebruik
     * in checkout proces, profiel weergave en order management.
     * 
     * @param \Illuminate\Http\Request $request Het HTTP request object
     * @return \Illuminate\Http\JsonResponse JSON response met adres data of null
     */
    public function getAddress(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Controleer authenticatie status
            if (!$user) {
                Log::warning('Unauthorized address fetch attempt');
                return response()->json([
                    'message' => 'Niet geautoriseerd'
                ], 401);
            }

            // Haal adres op van gebruiker
            $address = $user->address;

            if (!$address) {
                Log::info('No address found for user', [
                    'user_id' => $user->id
                ]);
                
                return response()->json([
                    'message' => 'Geen adres gevonden',
                    'address' => null
                ], 200); // 200 status omdat het geen error is, maar een lege response
            }

            Log::info('Address retrieved successfully for user', [
                'user_id' => $user->id,
                'address_id' => $address->id
            ]);

            return response()->json([
                'address' => $address
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching address', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Er is een fout opgetreden bij het ophalen van het adres'
            ], 500);
        }
    }

    /**
     * Verwijder het adres van de huidige gebruiker
     * 
     * Deze methode verwijdert het bezorgadres van de gebruiker. Vooral nuttig
     * voor privacy doeleinden of als gebruiker wil dat adres data wordt gewist.
     * 
     * @param \Illuminate\Http\Request $request Het HTTP request object
     * @return \Illuminate\Http\JsonResponse JSON response met success of error message
     */
    public function deleteAddress(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Controleer authenticatie
            if (!$user) {
                Log::warning('Unauthorized address deletion attempt');
                return response()->json([
                    'message' => 'Niet geautoriseerd'
                ], 401);
            }

            $address = $user->address;
            
            // Controleer of er een adres bestaat om te verwijderen
            if (!$address) {
                Log::warning('Address deletion attempted but no address found', [
                    'user_id' => $user->id
                ]);
                
                return response()->json([
                    'message' => 'Geen adres gevonden om te verwijderen'
                ], 404);
            }

            $addressId = $address->id;
            
            // Verwijder het adres uit database
            $address->delete();
            
            Log::info('Address deleted successfully', [
                'user_id' => $user->id,
                'deleted_address_id' => $addressId
            ]);

            return response()->json([
                'message' => 'Adres succesvol verwijderd'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error deleting address', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Er is een fout opgetreden bij het verwijderen van het adres'
            ], 500);
        }
    }


     /**
     * ========== SESSIE TIMING FIX METHODE ==========
     */

    /**
     * Zorg voor sessie consistentie na gebruiker registratie
     * 
     * Lost timing probleem op waarbij gebruiker wel geauthenticeerd is maar login.web 
     * sessie marker ontbreekt. Dit gebeurt soms direct na registratie wanneer sessie
     * regeneratie nog niet volledig voltooid is.
     * 
     * @param \Illuminate\Http\Request $request Het HTTP request object
     * @return void
     */
    private function ensureSessionConsistency(Request $request): void
    {
        $user = Auth::user();
        if (!$user) return;
        
        // Kijk of er een geldige login sessie bestaat (Laravel maakt dynamische keys)
        $hasValidLoginSession = collect(session()->all())
            ->keys()
            ->contains(function ($key) {
                return str_starts_with($key, 'login_web_');
            });
        
        if (!$hasValidLoginSession) {
            Log::info('Sessie fix toegepast voor gebruiker', [
                'user_id' => $user->id,
                'session_keys_voor' => array_keys(session()->all()),
                'session_id_voor' => session()->getId()
            ]);
            
            // Probeer logout/login cyclus om sessie te fixen
            Auth::logout(); 
            Auth::login($user, true); 
            
            // Check of het werkte
            $hasValidLoginSessionAfter = collect(session()->all())
                ->keys()
                ->contains(function ($key) {
                    return str_starts_with($key, 'login_web_');
                });
            
            Log::info('Sessie fix klaar', [
                'user_id' => $user->id,
                'session_keys_na' => array_keys(session()->all()),
                'session_id_na' => session()->getId(),
                'heeft_login_sessie_na' => $hasValidLoginSessionAfter,
                'fix_gelukt' => $hasValidLoginSessionAfter
            ]);
            
            // Als het nog steeds niet werkt, regenereer de hele sessie
            if (!$hasValidLoginSessionAfter) {
                Log::warning('Sessie fix werkte niet, probeer sessie regeneratie', [
                    'user_id' => $user->id
                ]);
                
                $request->session()->regenerate();
                Auth::login($user, true);
                
                // Let op: dit breekt CSRF tokens
                Log::warning('Sessie geregenereerd - CSRF tokens mogelijk kapot', [
                    'user_id' => $user->id
                ]);
            }
        }
    }
}

/**
 * CONTROLLER FUNCTIONALITEIT UITLEG:
 * 
 * PROFIEL MANAGEMENT:
 * - edit(): Toont profiel bewerk formulier met Inertia.js
 * - update(): Werkt profiel gegevens bij, reset email verificatie bij email wijziging
 * - destroy(): Verwijdert volledig gebruikersaccount na wachtwoord verificatie
 * 
 * ADRESBEHEER API:
 * - storeAddress(): Uniforme POST methode voor zowel create als update operaties
 * - updateAddress(): Specifieke PUT methode voor adres updates
 * - getAddress(): Haalt huidig gebruikersadres op voor frontend weergave
 * - deleteAddress(): Verwijdert gebruikersadres voor privacy doeleinden
 * 
 * BEVEILIGING & LOGGING:
 * - Alle methodes controleren authenticatie status
 * - Uitgebreide logging voor audit trails en debugging
 * - Proper error handling met gestructureerde JSON responses
 * - Nederlandse postcode validatie (1234AB format)
 * - CSRF bescherming via middleware
 * 
 * API RESPONSE FORMATEN:
 * - Success: 200/201 met data object
 * - Validation Error: 422 met errors object  
 * - Unauthorized: 401 met message
 * - Not Found: 404 met message
 * - Server Error: 500 met generic message (details in logs)
 */