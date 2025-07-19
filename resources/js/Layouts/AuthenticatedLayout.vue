/**
 * Bestandsnaam: AuthenticatedLayout.vue
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-07-02
 * Tijd: 00:15:49
 * Doel: Geauthenticeerde gebruiker layout component voor klanten dashboard met responsive navigatie, gebruiker account management en clean interface. Bevat hoofdnavigatie met logo, gebruiker dropdown menu, mobile hamburger interface en flexible content areas voor persoonlijke gebruiker pagina's zoals dashboard, bestellingen en account instellingen.
 */

<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

/**
 * RESPONSIVE NAVIGATIE STATE
 * State management voor mobile hamburger menu toggle functionaliteit
 */
const showingNavigationDropdown = ref(false);  // Mobile navigatie dropdown zichtbaarheid
</script>

<template>
    <div>
        <!-- 
            GEAUTHENTICEERDE LAYOUT CONTAINER
            Hoofdcontainer voor volledige pagina layout met minimum height
        -->
        <div class="min-h-screen bg-gray-100">
            
            <!-- 
                HOOFDNAVIGATIE
                Responsive navigatiebalk met logo, hoofdnavigatie en gebruiker menu
            -->
            <nav class="border-b border-gray-100 bg-white">
                
                <!-- Primaire Navigatie Menu -->
                <!-- Desktop en mobile navigatie container met responsive padding -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        
                        <!-- Linkerzijde: Logo en Hoofdnavigatie -->
                        <div class="flex">
                            
                            <!-- Logo Sectie -->
                            <!-- Merkidentificatie met link terug naar homepage -->
                            <div class="flex shrink-0 items-center">
                                <Link href="/" title="Terug naar de homepage">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800"
                                    />
                                </Link>
                            </div>

                            <!-- Desktop Navigatie Links -->
                            <!-- Hoofdnavigatie items alleen zichtbaar op desktop -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Overzicht
                                </NavLink>
                            </div>
                        </div>

                        <!-- Rechterzijde: Desktop Gebruiker Menu -->
                        <!-- Account beheer en instellingen dropdown voor desktop -->
                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            
                            <!-- Gebruiker Account Dropdown -->
                            <!-- Settings en logout opties voor geauthenticeerde gebruikers -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    
                                    <!-- Dropdown Trigger -->
                                    <!-- Gebruiker naam button met chevron indicator -->
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <!-- Dropdown Chevron Icoon -->
                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <!-- Dropdown Content -->
                                    <!-- Account management opties en logout functionaliteit -->
                                    <template #content>
                                        <!-- Account Instellingen Link -->
                                        <DropdownLink :href="route('profile.edit')">
                                            Accountinstellingen
                                        </DropdownLink>
                                        
                                        <!-- Uitloggen Link -->
                                        <!-- POST method voor secure logout -->
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Uitloggen
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Mobile Hamburger Menu -->
                        <!-- Toggle knop voor mobile navigatie alleen zichtbaar op mobile -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <!-- Hamburger/Close Icon SVG -->
                                <!-- Toont hamburger of X gebaseerd op dropdown staat -->
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <!-- Hamburger Lines (default) -->
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <!-- Close X (when open) -->
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Mobile Navigatie Menu -->
                <!-- Uitklapbaar mobile menu met navigatie en account opties -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <!-- Mobile Navigatie Links -->
                    <!-- Hoofdnavigatie items voor mobile interface -->
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Overzicht
                        </ResponsiveNavLink>
                    </div>

                    <!-- Mobile Account Instellingen -->
                    <!-- Gebruiker informatie en account opties voor mobile -->
                    <div class="border-t border-gray-200 pb-1 pt-4">
                        
                        <!-- Gebruiker Informatie Display -->
                        <!-- Naam en email weergave in mobile menu -->
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <!-- Mobile Account Acties -->
                        <!-- Account management links voor mobile interface -->
                        <div class="mt-3 space-y-1">
                            <!-- Mobile Account Instellingen -->
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Accountinstellingen
                            </ResponsiveNavLink>
                            
                            <!-- Mobile Uitloggen -->
                            <!-- Secure POST logout voor mobile -->
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Uitloggen
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Pagina Header (Optioneel) -->
            <!-- Conditionele header sectie voor pagina-specifieke titels -->
            <header
                class="bg-white shadow"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <!-- Header Slot voor pagina titels en breadcrumbs -->
                    <slot name="header" />
                </div>
            </header>

            <!-- Hoofdinhoud -->
            <!-- Primaire content area voor pagina-specifieke inhoud -->
            <main>
                <!-- Content Slot voor pagina body -->
                <slot />
            </main>
        </div>
    </div>
</template>