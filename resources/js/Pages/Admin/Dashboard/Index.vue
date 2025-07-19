/**
 * Bestandsnaam: Index.vue (Pages/Admin/Dashboard)
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-01-13
 * Tijd: 19:19:05
 * Doel: Dit component toont het hoofddashboard van de admin interface met een eenvoudige welkomst
 *       boodschap. Gebruikt een responsieve grid layout structuur die klaar is voor dashboard content.
 */

<script setup>
// Layout component voor consistente admin interface
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';

// Inertia.js Head component voor page title management
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <!-- Page Title voor Browser Tab -->
    <Head title="Admin Dashboard" />

    <!-- Admin Layout Wrapper -->
    <AdminLayout>
        
        <!-- Hoofd Dashboard Grid Container -->
        <!-- Responsieve grid: 1 kolom op mobiel, 2 op tablet, 4 op desktop -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5">
            
            <!-- Welkomst Card -->
            <div class="flex flex-col items-center justify-center text-center py-12 px-6 bg-white rounded-md shadow-sm">
                <h1 class="text-2xl font-bold">Dashboard</h1>
                <p>Welcome to your admin dashboard.</p>
            </div>
            
        </div>

        <!-- Secundaire Dashboard Grid -->
        <!-- 12-kolom grid systeem met responsive spacing -->
        <div class="mt-4 grid grid-cols-12 gap-4 md:mt-6 md:gap-6 2xl:mt7.5 2xl:gap-7.5">
            <!-- Toekomstige Dashboard Componenten Placeholder -->
            <!-- Hier komen dashboard widgets zoals statistieken, recente activiteiten, snelle acties, etc. -->
            
        </div>
        
    </AdminLayout>
</template>