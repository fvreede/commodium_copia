<!--resources/js/Pages/Editor/Dashboard.vue-->

/**
 * Bestandsnaam: Dashboard.vue (Pages/Editor)
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: [DATUM_INVOEGEN]
 * Tijd: [TIJD_INVOEGEN]
 * Doel: Hoofddashboard pagina voor de editor interface. Bevat een welkomstbericht en responsive grid layouts
 *       voor toekomstige dashboard widgets en componenten. Dient als startpagina voor editor gebruikers met
 *       uitbreidbare sectie voor statistieken en snelle acties.
 */

<script setup>
// Layout component import
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';

// Inertia.js imports voor page title management
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <!-- Editor Layout Wrapper -->
    <EditorLayout>
        <!-- Page Title voor Browser Tab -->
        <Head title="Editor Dashboard" />

        <!-- Hoofdwelkomst Sectie Grid -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5">
            <!-- Welkomst Card -->
            <div class="flex flex-col items-center justify-center text-center py-12 px-6 bg-white rounded-md shadow-sm">
                <!-- Dashboard Hoofdtitel -->
                <h1 class="text-2xl font-bold">Dashboard</h1>
                <!-- Welkomstbericht -->
                <p>Welcome to your editor dashboard.</p>
            </div>
        </div>
        
        <!-- Dashboard Widgets en Componenten Grid Sectie -->
        <div class="mt-4 grid grid-cols-12 gap-4 md:mt-6 md:gap-6 2xl:mt7.5 2xl:gap-7.5">
            <!-- Toekomstige Dashboard Componenten Placeholder -->
            <!-- Hier komen dashboard widgets zoals statistieken, recente activiteiten, snelle acties, etc. -->
            
        </div>
    </EditorLayout>
</template>