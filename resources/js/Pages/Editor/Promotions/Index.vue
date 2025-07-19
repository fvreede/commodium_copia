/**
 * Bestandsnaam: Index.vue (Pages/Editor/Promotions)
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-07-04
 * Tijd: 16:58:38
 * Doel: Overzichtspagina voor promoties/aanbiedingen management in de editor interface. Bevat responsive
 *       weergave met desktop tabel en mobile cards, status indicatoren, vervaldatum weergave, en complete
 *       CRUD functionaliteiten. Inclusief lege staat handling, directe edit/delete acties, en navigatie
 *       naar create pagina voor optimaal promotie beheer en marketing campagne overzicht.
 */

<script setup>
// Inertia.js imports voor navigatie en routing
import { Head, Link } from '@inertiajs/vue3';

// Heroicons imports voor UI iconen
import { TrashIcon, PencilIcon, PlusIcon } from '@heroicons/vue/24/outline';

// Layout en component imports
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

// ========== PROPS DEFINITIE ==========

// Props van server - promoties data voor overzicht weergave
const props = defineProps({
    promotions: Array                         // Array van promotie objecten met id, title, valid_until, is_active
});
</script>

<template>
    <!-- Editor Layout Wrapper -->
    <EditorLayout>
        <!-- Page Title voor Browser Tab -->
        <Head title="Aanbiedingsacties" />

        <!-- Main Container met Responsive Padding -->
        <div class="p-4 sm:p-6">
            
            <!-- Page Header Sectie met Responsive Layout -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 space-y-4 sm:space-y-0">
                <!-- Hoofdtitel van de Pagina -->
                <h1 class="text-xl sm:text-2xl font-semibold">Aanbiedingen</h1>
                <!-- Navigatie naar Create Pagina met Conditionele Visibility -->
                <Link 
                    :href="route('editor.promotions.create')" 
                    :class="[
                        'w-full sm:w-auto',
                        promotions.length === 0 ? 'hidden sm:block' : 'block'  // Verberg op mobile bij lege staat
                    ]"
                >
                    <!-- Nieuwe Aanbieding Knop -->
                    <PrimaryButton class="w-full sm:w-auto justify-center">
                        <PlusIcon class="h-5 w-5 mr-2" />
                        Nieuwe Aanbieding
                    </PrimaryButton>
                </Link>
            </div>

            <!-- Desktop Tabel Weergave (verborgen op tablet/mobile) -->
            <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
                <!-- Promoties Data Tabel -->
                <table class="min-w-full divide-y divide-gray-200">
                    
                    <!-- Tabel Header -->
                    <thead class="bg-gray-50">
                        <tr>
                            <!-- Titel Kolom Header -->
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Title
                            </th>
                            <!-- Vervaldatum Kolom Header -->
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Geldig tot
                            </th>
                            <!-- Status Kolom Header -->
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <!-- Acties Kolom Header -->
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acties
                            </th>
                        </tr>
                    </thead>
                    
                    <!-- Tabel Body met Promotie Data -->
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Promotie Rij (herhaalt voor elke promotie) -->
                        <tr v-for="promotion in promotions" :key="promotion.id">
                            <!-- Promotie Titel Kolom -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ promotion.title }}
                            </td>
                            <!-- Vervaldatum Kolom met Datum Formatting -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ new Date(promotion.valid_until).toLocaleDateString() }}
                            </td>
                            <!-- Status Badge Kolom -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <!-- Dynamische Status Badge met Kleur Logica -->
                                <span
                                    :class="[
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        promotion.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    ]"
                                >
                                    {{ promotion.is_active ? 'Actief' : 'Inactief' }}
                                </span>
                            </td>
                            <!-- Acties Kolom met Edit en Delete Links -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <!-- Actie Knoppen Container -->
                                <div class="flex items-center space-x-2">
                                    <!-- Edit Promotie Link -->
                                    <Link 
                                        :href="route('editor.promotions.edit', promotion.id)"
                                        class="inline-flex text-orange-600 hover:text-orange-900"
                                    >
                                        <PencilIcon class="w-5 h-5" />
                                    </Link>
                                    <!-- Delete Promotie Button met Directe Inertia Call -->
                                    <button 
                                        @click="$inertia.delete(route('editor.promotions.destroy', promotion.id))"
                                        class="inline-flex text-red-600 hover:text-red-900"
                                    >
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card Weergave (alleen zichtbaar op tablet/mobile) -->
            <div class="md:hidden space-y-4">
                <!-- Promotie Card (herhaalt voor elke promotie) -->
                <div v-for="promotion in promotions" :key="promotion.id" 
                     class="bg-white rounded-lg shadow p-4 border border-gray-200">
                    
                    <!-- Titel en Status Rij -->
                    <div class="flex justify-between items-start mb-3">
                        <!-- Promotie Titel -->
                        <h3 class="text-lg font-medium text-gray-900 flex-1 mr-3">
                            {{ promotion.title }}
                        </h3>
                        <!-- Status Badge voor Mobile -->
                        <span
                            :class="[
                                'px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap',
                                promotion.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                            ]"
                        >
                            {{ promotion.is_active ? 'Actief' : 'Inactief' }}
                        </span>
                    </div>

                    <!-- Vervaldatum Informatie -->
                    <div class="mb-4">
                        <span class="text-sm text-gray-500">Geldig tot: </span>
                        <span class="text-sm font-medium text-gray-900">
                            {{ new Date(promotion.valid_until).toLocaleDateString() }}
                        </span>
                    </div>

                    <!-- Mobile Actie Knoppen -->
                    <div class="flex justify-end space-x-3">
                        <!-- Edit Link met Enhanced Mobile Styling -->
                        <Link 
                            :href="route('editor.promotions.edit', promotion.id)"
                            class="p-2 text-orange-600 hover:text-orange-900 hover:bg-orange-50 rounded-md transition-colors duration-200"
                        >
                            <PencilIcon class="w-5 h-5" />
                        </Link>
                        <!-- Delete Button met Enhanced Mobile Styling -->
                        <button 
                            @click="$inertia.delete(route('editor.promotions.destroy', promotion.id))"
                            class="p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-md transition-colors duration-200"
                        >
                            <TrashIcon class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <!-- Lege Staat voor Mobile -->
                <div v-if="promotions.length === 0" class="text-center py-12">
                    <!-- Icon Container -->
                    <div class="text-gray-400 mb-4">
                        <PlusIcon class="w-12 h-12 mx-auto" />
                    </div>
                    <!-- Lege Staat Titel -->
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Geen aanbiedingen</h3>
                    <!-- Lege Staat Beschrijving -->
                    <p class="text-gray-500 mb-6">Begin met het maken van je eerste aanbieding.</p>
                    <!-- Call-to-Action voor Eerste Promotie -->
                    <div class="flex justify-center">
                        <Link :href="route('editor.promotions.create')">
                            <PrimaryButton class="w-full sm:w-auto">
                                <PlusIcon class="h-5 w-5 mr-2" />
                                Eerste Aanbieding Maken
                            </PrimaryButton>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </EditorLayout>
</template>