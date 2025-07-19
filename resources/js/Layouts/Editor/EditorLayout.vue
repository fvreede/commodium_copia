/**
 * Bestandsnaam: EditorLayout.vue
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-07-03
 * Tijd: 00:21:59
 * Doel: Hoofd layout component voor content editor dashboard met responsive sidebar management en optimale workflow voor redacteuren en content managers. Bevat desktop statische sidebar, mobile overlay panel, intelligent breakpoint detection en gestroomlijnde interface voor dagelijkse content editing taken. Geïntegreerd met editor-specifieke navbar en sidebar componenten.
 */

<!-- resources/js/Layouts/Editor/EditorLayout.vue -->
<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Navbar from '@/Components/Editor/Layout/Navbar.vue';
import Sidebar from '@/Components/Editor/Layout/Sidebar.vue';

/**
 * SIDEBAR STATE MANAGEMENT
 * Reactive state voor responsive sidebar gedrag en mobile/desktop content editing
 */
const isSidebarOpen = ref(false);  // Mobile sidebar open/gesloten staat voor editor interface
const isMobile = ref(false);       // Device type detectie voor editor workflow optimalisatie

/**
 * MOBILE DEVICE DETECTIE
 * Controleert schermgrootte voor optimale content editing ervaring
 */
const checkMobile = () => {
    isMobile.value = window.innerWidth < 768; // md breakpoint (768px) voor editor interface
    
    // Auto-sluit sidebar op mobile bij resize naar desktop voor content editor UX
    if (!isMobile.value) {
        isSidebarOpen.value = false;
    }
};

/**
 * SIDEBAR TOGGLE FUNCTIONALITEIT
 * Schakelt mobile sidebar voor content editor navigatie tussen verschillende content types
 */
const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

/**
 * SIDEBAR SLUITEN
 * Sluit mobile sidebar programmatisch na content navigatie voor clean editor interface
 */
const closeSidebar = () => {
    isSidebarOpen.value = false;
};

/**
 * LIFECYCLE HOOKS
 * Setup en cleanup van responsive behavior voor content editor dashboard
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
        EDITOR DASHBOARD WRAPPER
        Hoofdcontainer voor volledig scherm content editor interface
    -->
    <div class="flex h-screen overflow-hidden bg-slate-100">
        
        <!-- Desktop Editor Sidebar - Altijd zichtbaar voor desktop editing -->
        <!-- Permanente sidebar voor consistente content type navigatie -->
        <div class="hidden md:flex">
            <Sidebar :isOpen="true" :isMobile="false" />
        </div>
        
        <!-- Mobile Editor Sidebar - Overlay voor mobiele content editing -->
        <!-- Touch-vriendelijke sidebar voor onderweg content management -->
        <div class="md:hidden">
            <Sidebar
                :isOpen="isSidebarOpen"
                :isMobile="true"
                @close="closeSidebar"
            />
        </div>
        
        <!-- Editor Content Area -->
        <!-- Hoofdwerkruimte voor content creation en editing -->
        <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
            
            <!-- Editor Header/Navbar -->
            <!-- Responsive header met editor actions en mobile hamburger -->
            <Navbar @toggle-sidebar="toggleSidebar" :is-mobile="isMobile" />
            
            <!-- Editor Main Content -->
            <!-- Scrollbare content workspace met responsive padding voor editing comfort -->
            <main>
                <div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
                    <!-- Slot voor editor pagina-specifieke content -->
                    <slot></slot>
                </div>
            </main>
        </div>
    </div>
</template>