/**
 * Bestandsnaam: Navbar.vue
 * Auteur: Fabio Vreede
 * Versie: v1.0.13
 * Datum: 2025-07-03
 * Tijd: 00:09:21
 * Doel: Admin dashboard navbar component. Bevat responsive navigatie met hamburger menu voor mobile, logo, "bekijk site" link en logout functionaliteit. Sticky positioning voor consistente toegang tot navigatie.
 */

<!-- resources/js/Components/Admin/Layout/Navbar.vue -->
<script setup>
import { Disclosure } from '@headlessui/vue';
import { router } from '@inertiajs/vue3';
import {
  ArrowTopRightOnSquareIcon,
  Bars3Icon
} from '@heroicons/vue/24/outline';

/**
 * COMPONENT PROPS
 * Configuratie opties voor de navbar component
 */
const props = defineProps({
  isMobile: {
    type: Boolean,
    default: false
    // Bepaalt of mobile layout getoond wordt (hamburger menu zichtbaar)
  }
});

/**
 * COMPONENT EVENTS
 * Events die dit component kan uitzenden naar parent components
 */
const emit = defineEmits(['toggle-sidebar']);
// toggle-sidebar: Wordt uitgezonden wanneer hamburger menu geklikt wordt

/**
 * LOGOUT FUNCTIONALITEIT
 * Handelt gebruiker uitloggen af via Inertia POST request
 */
const logout = () => {
  router.post(route('logout'));
  // Gebruikt Laravel's logout route om gebruiker sessie te beëindigen
};

/**
 * SIDEBAR TOGGLE FUNCTIONALITEIT
 * Stuurt event naar parent component om sidebar te openen/sluiten
 */
const toggleSidebar = () => {
  emit('toggle-sidebar');
  // Parent component (meestal AdminLayout) handelt sidebar state af
};
</script>

<template>
  <!-- 
    HOOFDNAVIGATIE CONTAINER
    Sticky navbar met shadow en responsive design
  -->
  <Disclosure as="nav" class="bg-white shadow-lg w-full sticky top-0 z-40">
    <div class="max-w-full mx-auto px-4 sm:px-6">
      <div class="flex justify-between items-center h-16">
        
        <!-- 
          LINKERZIJDE: Hamburger Menu (Mobile) + Logo
          Bevat mobile navigation toggle en admin dashboard branding
        -->
        <div class="flex items-center">
          
          <!-- Mobile hamburger menu button -->
          <!-- Alleen zichtbaar op mobile devices wanneer isMobile prop true is -->
          <button
            v-if="isMobile"
            @click="toggleSidebar"
            class="mr-3 p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-gray-500 transition-colors"
            aria-label="Open sidebar"
          >
            <Bars3Icon class="h-6 w-6" />
          </button>
          
          <!-- Logo/Title met responsive tekst -->
          <!-- Toont volledige titel op desktop, verkorte versie op mobile -->
          <span class="text-xl sm:text-2xl font-bold text-gray-800">
            <span class="hidden sm:inline">Admin Dashboard</span>
            <span class="sm:hidden">Admin</span>
          </span>
        </div>
        
        <!-- 
          RECHTERZIJDE: Navigatie Acties
          Bevat site bekijken link en logout functionaliteit
        -->
        <div class="flex items-center space-x-3 sm:space-x-6">
          
          <!-- "Bekijk Site" Link -->
          <!-- Opent publieke website in nieuwe tab voor admin preview -->
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
          
          <!-- Logout Button -->
          <!-- Zorgt voor veilige gebruiker uitlog via POST request -->
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