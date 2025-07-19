/**
 * Bestandsnaam: Index.vue (Pages/Editor/News)
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-06-01
 * Tijd: 20:06:40
 * Doel: Overzichtspagina voor nieuwsartikelen management in de editor interface. Bevat een tabel weergave
 *       van alle artikelen met status informatie, publicatie data, en directe acties voor edit/delete.
 *       Inclusief navigatie naar create pagina en lege staat handling voor gebruiksvriendelijke experience.
 */

<script setup>
// Inertia.js imports voor navigatie en routing
import { Head, Link } from '@inertiajs/vue3';

// Heroicons imports voor UI iconen
import { PencilIcon, TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';

// Layout en component imports
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

// ========== PROPS DEFINITIE ==========

// Props van server - artikelen data voor overzicht weergave
defineProps({
    articles: Array                            // Array van artikel objecten met id, title, published_at, is_published
});
</script>

<template>
    <!-- Editor Layout Wrapper -->
    <EditorLayout>
        <!-- Page Title voor Browser Tab -->
        <Head title="Nieuwsartikelen" />

        <!-- Main Container met Padding -->
        <div class="p-6">
            
            <!-- Page Header Sectie met Titel en Nieuwe Artikel Knop -->
            <div class="flex justify-between items-center mb-6">
                <!-- Hoofdtitel van de Pagina -->
                <h1 class="text-2xl font-semibold">Nieuwsartikelen</h1>
                <!-- Navigatie naar Create Pagina -->
                <Link
                    :href="route('editor.news.create')"
                    class="inline-flex items-center"
                >
                    <!-- Nieuw Artikel Actie Knop -->
                    <PrimaryButton>
                        <PlusIcon class="h-5 w-5 mr-2" />
                        Nieuw Artikel
                    </PrimaryButton>
                </Link>
            </div>

            <!-- Artikelen Overzicht Tabel Container -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <!-- Artikelen Data Tabel -->
                <table class="min-w-full divide-y divide-gray-200">
                    
                    <!-- Tabel Header met Kolom Titels -->
                    <thead class="bg-gray-50">
                        <tr>
                            <!-- Titel Kolom Header -->
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Titel
                            </th>
                            <!-- Publicatie Datum Kolom Header -->
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Gepubliceerd op
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
                    
                    <!-- Tabel Body met Artikel Data -->
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Artikel Rij (herhaalt voor elk artikel) -->
                        <tr v-for="article in articles" :key="article.id">
                            <!-- Artikel Titel Kolom -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ article.title }}
                            </td>
                            <!-- Publicatie Datum Kolom met Fallback -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ article.published_at ? new Date(article.published_at).toLocaleString() : 'Niet gepubliceerd' }}
                            </td>
                            <!-- Status Badge Kolom -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <!-- Dynamische Status Badge met Kleur Logica -->
                                <span
                                    :class="[
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        article.is_published ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                                    ]"
                                >
                                    {{ article.is_published ? 'Gepubliceerd' : 'Concept' }}
                                </span>
                            </td>
                            <!-- Acties Kolom met Edit en Delete Links -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <!-- Edit Artikel Link -->
                                <Link
                                    :href="route('editor.news.edit', article.id)"
                                    class="text-orange-600 hover:text-orange-800"
                                >
                                    <PencilIcon class="h-5 w-5" />
                                </Link>
                                <!-- Delete Artikel Link met Confirmation -->
                                <Link
                                    :href="route('editor.news.destroy', article.id)"
                                    method="delete"
                                    class="text-red-600 hover:text-red-800"
                                >
                                    <TrashIcon class="h-5 w-5" />
                                </Link>
                            </td>
                        </tr>
                        
                        <!-- Lege Staat Rij (alleen zichtbaar als geen artikelen) -->
                        <tr v-if="articles.length === 0">
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500 ">
                                Geen nieuwsartikelen gevonden
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </EditorLayout>
</template>