<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\UserAddress;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Store a new address for the authenticated user.
     */
    public function storeAddress(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'street' => 'required|string|max:255',
            'house_number' => 'required|string|max:20',
            'postal_code' => 'required|string|max:10',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        $user = $request->user();

        // Check if useralready has an address.
        if ($user->address) {
            return response()->json([
                'message' => 'User already has an address. Use PUT to update.',
                'errors' => ['general' => ['Er bestaat al een adres voor deze gebruiker.']]
            ], 422);
        }

        $address = $user->address()->create($validated);

        return response()->json([
            'message' => 'Adres succesvol toegevoegd.',
            'address' => $address
        ], 201);
    }

    /**
     * Update the user's existing address.
     */
    public function updateAddress(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'street' => 'required|string|max:255',
            'house_number' => 'required|string|max:20',
            'postal_code' => 'required|string|max:10',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        $user = $request->user();

        if (!$user->address) {
            // Create new address if none exists.
            $address = $user->address()->create($validated);

            return response()->json([
                'message' => 'Adres succesvol aangemaakt.',
                'address' => $address
            ], 201);
        }

        // Update exisiting address.
        $user->address->update($validated);

        return response()->json([
            'message' => 'Adres succesvol bijgewerkt.',
            'address' => $user->address->fresh()
        ]);
    }
    
    /**
     * Get the user's address.
     */
    public function getAddress(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->address) {
            return response()->json([
                'message' => 'Geen adres gevonden.',
                'address' => null
            ], 404);
        }

        return response()->json([
            'address' => $user->address
        ]);
    }
}
