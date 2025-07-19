/**
 * Bestandsnaam: GuestLayout.vue
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-01-04
 * Tijd: 14:25:31
 * Doel: Minimalistische guest layout component voor niet-geauthenticeerde gebruikers met focus op login, registratie en wachtwoord reset pagina's. Bevat gecentreerde layout, logo branding, clean card interface en subtle hover effecten voor professionele authenticatie ervaring zonder afleiding.
 */

<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <!-- 
        GUEST LAYOUT CONTAINER
        Volledig scherm centered layout voor authenticatie workflows
    -->
    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 pt-6 sm:pt-0">
        
        <!-- Logo Sectie -->
        <!-- Merkidentificatie met link naar homepage en subtiele hover animatie -->
        <div>
            <Link href="/" class="transition-transform duration-300 hover:scale-105">
                <ApplicationLogo />
            </Link>
        </div>

        <!-- Authenticatie Card Container -->
        <!-- Clean, moderne card interface voor login/register formulieren -->
        <div class="mt-6 w-full max-w-sm bg-white px-6 py-8 shadow-lg rounded-lg border border-gray-200">
            <!-- Content Slot voor authenticatie formulieren -->
            <slot />
        </div>
    </div>
</template>