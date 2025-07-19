/**
 * Bestandsnaam: Index.vue (Pages/Admin/Subcategories)
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-02-02
 * Tijd: 00:33:31
 * Doel: Dit component toont een overzicht van alle subcategorieën in de admin interface in 
 *       tabel format. Bevat functionaliteiten voor het bewerken en verwijderen van subcategorieën,
 *       plus een link naar het aanmaken van nieuwe subcategorieën.
 */

<script setup>
// Inertia.js imports voor navigatie en routing
import { Head, Link, router } from '@inertiajs/vue3';

// Layout component voor consistente admin interface
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';

// Heroicons imports voor UI iconen
import { PencilIcon, TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';

// ========== PROPS DEFINITIE ==========

// Props van server - subcategorieën data
const props = defineProps({
    subcategories: Array,                       // Array van subcategorie objecten met bijbehorende categorie en product counts
});

// ========== DELETE FUNCTIONALITEIT ==========

/**
 * Verwijder subcategorie na bevestiging van gebruiker
 * Toont confirm dialog en stuurt DELETE request bij bevestiging
 * @param {number} id - ID van te verwijderen subcategorie
 */
const deleteSubcategory = (id) => {
    if (confirm('Are you sure you want to delete this subcategory?')) {
        router.delete(route('admin.subcategories.destroy', id));
    }
};
</script>

<template>
    <!-- Page Title voor Browser Tab -->
    <Head title="Subcategories management" />
    
    <AdminLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Page Header met Titel en Add Knop -->
                <div class="mb-6 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-900">Subcategories</h2>
                    <!-- Add Subcategory Knop -->
                    <Link
                        :href="route('admin.subcategories.create')"
                        class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 flex items-center gap-2"
                    >
                        <PlusIcon class="h-5 w-5" />
                        Add Subcategory
                    </Link>
                </div>

                <!-- Subcategorieën Tabel Container -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            
                            <!-- Subcategorieën Tabel -->
                            <table class="min-w-full divide-y divide-gray-200">
                                
                                <!-- Tabel Header -->
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                
                                <!-- Tabel Body -->
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <!-- Subcategorie Rij Loop -->
                                    <tr v-for="subcategory in subcategories" :key="subcategory.id">
                                        
                                        <!-- Subcategorie Naam Kolom -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ subcategory.name }}</div>
                                        </td>
                                        
                                        <!-- Parent Categorie Naam Kolom -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ subcategory.category.name }}</div>
                                        </td>
                                        
                                        <!-- Aantal Producten Kolom -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ subcategory.products_count }}</div>
                                        </td>
                                        
                                        <!-- Acties Kolom -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex gap-3">
                                                <!-- Edit Link -->
                                                <Link 
                                                    :href="route('admin.subcategories.edit', subcategory.id)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    <PencilIcon class="h-5 w-5" />
                                                </Link>
                                                <!-- Delete Knop -->
                                                <button 
                                                    @click="deleteSubcategory(subcategory.id)"
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    <TrashIcon class="h-5 w-5" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>