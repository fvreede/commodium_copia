/**
 * Bestandsnaam: AdminLayout.vue
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-07-03
 * Tijd: 00:09:21
 * Doel: Responsive admin layout component voor content editor dashboard met adaptive sidebar management. Bevat desktop statische sidebar, mobile overlay panel, intelligent breakpoint detection en optimale workflow voor content editors en redacteuren. Geïntegreerd met navbar en sidebar componenten voor complete admin interface experience.
 */

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Navbar from '@/Components/Admin/Layout/Navbar.vue';
import Sidebar from '@/Components/Admin/Layout/Sidebar.vue';

/**
 * SIDEBAR STATE MANAGEMENT
 * Reactive state voor responsive sidebar gedrag en mobile/desktop switching
 */
const isSidebarOpen = ref(false);  // Mobile sidebar open/gesloten staat
const isMobile = ref(false);       // Huidige device type detectie

/**
 * MOBILE DEVICE DETECTIE
 * Controleert schermgrootte en past layout dienovereenkomstig aan
 */
const checkMobile = () => {
    isMobile.value = window.innerWidth < 768; // md breakpoint (768px)
    
    // Auto-sluit sidebar op mobile bij resize naar desktop voor UX consistency
    if (!isMobile.value) {
        isSidebarOpen.value = false;
    }
};

/**
 * SIDEBAR TOGGLE FUNCTIONALITEIT
 * Schakelt mobile sidebar open/gesloten staat voor editor workflow
 */
const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

/**
 * SIDEBAR SLUITEN
 * Sluit mobile sidebar programmatisch voor navigatie cleanup
 */
const closeSidebar = () => {
    isSidebarOpen.value = false;
};

/**
 * OUTSIDE CLICK HANDLER
 * Behandelt clicks buiten sidebar op mobile voor intuitive UX
 * 
 * @param {Event} event - Click event voor outside detection
 */
const handleOutsideClick = (event) => {
    // Voor volledig scherm mobile sidebar, vertrouwen we hoofdzakelijk op X knop en navigatie
    // Maar behoud backdrop click functionaliteit voor toegankelijkheid
};

/**
 * LIFECYCLE HOOKS
 * Setup en cleanup van event listeners voor responsive behavior
 */
onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
});
</script>

<template>
    <!-- 
        PAGINA WRAPPER
        Hoofdcontainer voor volledig scherm admin interface met overflow management
    -->
    <div class="flex h-screen overflow-hidden bg-slate-100">
        
        <!-- Desktop Sidebar - Altijd zichtbaar op desktop -->
        <!-- Statische sidebar voor consistente editor workflow op desktop -->
        <div class="hidden md:flex">
            <Sidebar :isOpen="true" :isMobile="false" />
        </div>
        
        <!-- Mobile Sidebar Overlay - Alleen op mobile -->
        <!-- Overlay panel voor mobile editor interface -->
        <div class="md:hidden">
            <Sidebar
                :isOpen="isSidebarOpen"
                :isMobile="true"
                @close="closeSidebar"
            />
        </div>
        
        <!-- Content Area -->
        <!-- Hoofdcontent gebied met responsive layout en scroll management -->
        <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
            
            <!-- Header/Navbar -->
            <!-- Responsive header met mobile hamburger en desktop actions -->
            <Navbar @toggle-sidebar="toggleSidebar" :is-mobile="isMobile" />
            
            <!-- Main Content -->
            <!-- Scrollbare content area met responsive padding voor editor werkruimte -->
            <main>
                <div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
                    <!-- Slot voor pagina-specifieke content -->
                    <slot></slot>
                </div>
            </main>
        </div>
    </div>
</template>