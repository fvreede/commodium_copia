/**
 * Bestandsnaam: Navbar.vue
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-07-03
 * Tijd: 00:21:59
 * Doel: Responsive navbar component voor editor dashboard met hamburger menu, logo weergave, site preview link en logout functionaliteit. Bevat sticky positioning voor constante toegankelijkheid en mobile-first responsive design voor optimale editor workflow op alle apparaten.
 */

<!-- resources/js/Components/Editor/Layout/Navbar.vue -->
<script setup>
import { Disclosure } from '@headlessui/vue';
import { router } from '@inertiajs/vue3';
import {
 ArrowTopRightOnSquareIcon,
 Bars3Icon
} from '@heroicons/vue/24/outline';

/**
 * COMPONENT EIGENSCHAPPEN
 * Configuratie voor responsive gedrag en layout aanpassingen
 */
const props = defineProps({
    isMobile: {
        type: Boolean,
        default: false
        // Bepaalt of mobile layout getoond moet worden (hamburger menu, compacte tekst)
    }
});

/**
 * COMPONENT EVENTS
 * Events die naar parent component worden uitgezonden voor layout controle
 */
const emit = defineEmits(['toggle-sidebar']);
// toggle-sidebar: Wordt uitgezonden om mobile sidebar te openen/sluiten

/**
 * NAVIGATIE METHODEN
 * Behandelt gebruiker navigatie en sessie management
 */

// Logout functionaliteit met Inertia route handling
const logout = () => {
    router.post(route('logout'));
};

// Sidebar toggle voor mobile responsive layout
const toggleSidebar = () => {
    emit('toggle-sidebar');
};
</script>

<template>
    <!-- 
        HOOFDNAVIGATIE CONTAINER
        Sticky navbar met HeadlessUI Disclosure voor accessibility
    -->
    <Disclosure as="nav" class="bg-white shadow-lg w-full sticky top-0 z-40">
        <div class="max-w-full mx-auto px-4 sm:px-6">
            <div class="flex justify-between items-center h-16">
                
                <!-- Linkerzijde: Hamburger Menu (Mobile) + Logo -->
                <!-- Mobile-first layout met hamburger menu en responsive logo -->
                <div class="flex items-center">
                    
                    <!-- Mobile Hamburger Menu -->
                    <!-- Alleen zichtbaar op mobile voor sidebar toggle functionaliteit -->
                    <button
                        v-if="isMobile"
                        @click="toggleSidebar"
                        class="mr-3 p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-gray-500 transition-colors"
                        aria-label="Open sidebar"
                    >
                        <Bars3Icon class="h-6 w-6" />
                    </button>
                    
                    <!-- Logo/Titel Weergave -->
                    <!-- Responsive tekst die aanpast aan schermgrootte -->
                    <span class="text-xl sm:text-2xl font-bold text-gray-800">
                        <span class="hidden sm:inline">Editor Dashboard</span>
                        <span class="sm:hidden">Editor</span>
                    </span>
                </div>
                
                <!-- Rechterzijde: Actie Knoppen -->
                <!-- Navigatie acties met responsive spacing en tekst -->
                <div class="flex items-center space-x-3 sm:space-x-6">
                    
                    <!-- Site Bekijken Link -->
                    <!-- Nieuwe tab link naar publieke website met responsive tekst -->
                    <a
                        href="/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center space-x-1 sm:space-x-2 px-2 sm:px-3 py-2 text-xs sm:text-sm font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 hover:text-gray-900 transition-colors duration-150 uppercase"
                        title="View Public Site"
                    >
                        <ArrowTopRightOnSquareIcon class="h-4 w-4 sm:h-5 sm:w-5" />
                        <span class="hidden sm:inline">Bekijk site</span>
                        <span class="sm:hidden">Site</span>
                    </a>
                    
                    <!-- Uitloggen Knop -->
                    <!-- Prominente logout actie met responsive tekst en focus states -->
                    <button
                        @click="logout"
                        class="px-3 sm:px-4 py-2 border border-transparent text-xs sm:text-sm font-medium rounded-md text-white bg-zinc-700 hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 transition-colors duration-150 uppercase"
                    >
                        <span class="hidden sm:inline">Uitloggen</span>
                        <span class="sm:hidden">Uit</span>
                    </button>
                </div>
            </div>
        </div>
    </Disclosure>
</template>