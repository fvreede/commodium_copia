/**
 * Bestandsnaam: Index.vue (Pages/Editor/Banners)
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-06-30
 * Tijd: 20:50:14
 * Doel: Overzichtspagina voor het beheren van categorie banners in de editor interface. Toont een responsive
 *       grid van categorie cards met banner previews, laatste update informatie, en directe edit links voor
 *       elke categorie. Bevat placeholder states voor categorieën zonder banner.
 */

<script setup>
// Inertia.js imports voor navigatie en routing
import { Head, Link } from '@inertiajs/vue3';

// Heroicons imports voor UI iconen
import { PencilIcon } from '@heroicons/vue/24/outline';

// Layout component import
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';

// ========== PROPS DEFINITIE ==========

// Props van server - categorieën data voor banner management
const props = defineProps({
    categories: Array                          // Array van categorie objecten met id, name, banner_path en update_at
});
</script>

<template>
    <!-- Editor Layout Wrapper -->
    <EditorLayout>
        <!-- Page Title voor Browser Tab -->
        <Head title="Categorie Banners" />

        <!-- Main Container met Padding -->
        <div class="p-6">
            
            <!-- Page Header Sectie -->
            <div class="mb-6">
                <!-- Hoofdtitel van de Pagina -->
                <h1 class="text-2xl font-semibold">
                    Categorie Banners
                </h1>
            </div>

            <!-- Responsive Grid Container voor Categorie Cards -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                
                <!-- Categorie Card (herhaalt voor elke categorie) -->
                <div v-for="category in categories" :key="category.id" class="bg-white rounded-lg shadow overflow-hidden">
                    
                    <!-- Banner Preview Sectie -->
                    <div class="aspect-w-16 aspect-h-9 bg-gray-200">
                        <!-- Bestaande Banner Afbeelding -->
                        <img 
                            v-if="category.banner_path"
                            :src="`/storage/${category.banner_path}`" 
                            :alt="`${category.name} banner`"
                            class="w-full h-full object-cover"
                        />
                        <!-- Placeholder voor Geen Banner -->
                        <div v-else class="flex items-center justify-center h-full text-gray-400">
                            Geen banner
                        </div>
                    </div>
                
                    <!-- Categorie Informatie Sectie -->
                    <div class="p-4">
                        <!-- Header met Naam en Edit Knop -->
                        <div class="flex justify-between items-center">
                            <!-- Categorie Naam -->
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ category.name }}
                            </h3>
                            <!-- Edit Link naar Banner Edit Pagina -->
                            <Link
                                :href="route('editor.banners.edit', category.id)"
                                class="p-2 text-orange-600 hover:text-orange-900 rounded-full hover:bg-orange-50"
                            >
                                <PencilIcon class="h-5 w-5" />
                            </Link>
                        </div>

                        <!-- Metadata Details Sectie -->
                        <dl class="mt-2 text-sm text-gray-500">
                            <!-- Laatste Update Informatie -->
                            <div class="mt-1">
                                <dt class="inline">Laatst bijgewerkt: </dt>
                                <dd class="inline">{{ category.update_at ? new Date(category.update_at).toLocaleDateString() : 'Nooit' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </EditorLayout>
</template>