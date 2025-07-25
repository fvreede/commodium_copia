/**
 * Bestandsnaam: Index.vue (Pages/Admin/Categories)
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-07-04
 * Tijd: 16:58:38
 * Doel: Dit component toont een overzicht van alle categorieën in de admin interface met zoekfunctionaliteit,
 *       responsive design (mobiele cards vs desktop tabel), en delete functionaliteit met bevestigingsmodal.
 *       Ondersteunt real-time filtering en optimale UX voor verschillende apparaatgroottes.
 */

<script setup>
// Vue compositie API imports
import { ref, computed } from 'vue';

// Inertia.js imports voor navigatie en routing
import { Head, Link, router } from '@inertiajs/vue3';

// Heroicons imports voor UI iconen
import { 
    PencilIcon, 
    TrashIcon, 
    PlusIcon, 
    MagnifyingGlassIcon, 
    FolderIcon, 
    ExclamationTriangleIcon 
} from '@heroicons/vue/24/outline';

// Component imports
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';

// ========== PROPS DEFINITIE ==========

// Props van server - categorieën data
const props = defineProps({
    categories: Array               // Array van categorie objecten met naam, ID en subcategorie count
});

// ========== REACTIVE STATE MANAGEMENT ==========

// Zoekfunctionaliteit state
const searchQuery = ref('');                        // Huidige zoekterm van gebruiker

// Delete modal state
const showDeleteModal = ref(false);                 // Of delete bevestiging modal zichtbaar is
const selectedCategory = ref(null);                 // Geselecteerde categorie voor verwijdering

// ========== COMPUTED PROPERTIES ==========

/**
 * Gefilterde categorieën gebaseerd op zoekterm
 * Filtert real-time op categorie naam (case-insensitive)
 * @returns {Array} Gefilterde array van categorieën
 */
const filteredCategories = computed(() => {
    if (!searchQuery.value) return props.categories;
    
    const query = searchQuery.value.toLowerCase();
    return props.categories.filter(category => 
        category.name.toLowerCase().includes(query)
    );
});

// ========== DELETE FUNCTIONALITEIT ==========

/**
 * Open delete bevestiging modal voor specifieke categorie
 * @param {Object} category - Categorie object om te verwijderen
 */
const openDeleteModal = (category) => {
    selectedCategory.value = category;
    showDeleteModal.value = true;
};

/**
 * Bevestig en voer categorie verwijdering uit
 * Gebruikt Inertia.js DELETE request naar server
 */
const confirmDelete = () => {
    if (selectedCategory.value) {
        router.delete(route('admin.categories.destroy', selectedCategory.value.id), {
            onSuccess: () => {
                // Reset modal state bij succesvolle verwijdering
                showDeleteModal.value = false;
                selectedCategory.value = null;
            }
        });
    }
};

/**
 * Sluit delete modal zonder actie te ondernemen
 * Reset alle modal gerelateerde state
 */
const closeDeleteModal = () => {
    showDeleteModal.value = false;
    selectedCategory.value = null;
};
</script>

<template>
    <Head title="Categories" />

    <AdminLayout>
        <div class="py-4 sm:py-6 lg:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-4 sm:p-6">
                        
                        <!-- Header Sectie met Zoeken en Toevoegen Knop -->
                        <div class="flex flex-col space-y-4 mb-6">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                                <!-- Page Titel -->
                                <h2 class="text-xl sm:text-2xl font-semibold text-gray-800">Categories</h2>
                                <!-- Add Category Knop -->
                                <Link
                                    :href="route('admin.categories.create')"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-3 sm:py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium"
                                >
                                    <PlusIcon class="h-4 w-4 mr-2" />
                                    Add Category
                                </Link>
                            </div>
                            
                            <!-- Zoekbalk Sectie -->
                            <div class="relative w-full">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search categories..."
                                    class="w-full pr-10 pl-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:border-transparent text-base"
                                />
                                <!-- Zoek Icoon -->
                                <MagnifyingGlassIcon class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                            </div>
                        </div>

                        <!-- Mobiele Card Weergave (zichtbaar op kleine schermen) -->
                        <div class="block lg:hidden space-y-4">
                            <!-- Categorie Card -->
                            <div 
                                v-for="category in filteredCategories" 
                                :key="category.id"
                                class="bg-gray-50 rounded-lg p-4 border border-gray-200"
                            >
                                <div class="flex flex-col space-y-3">
                                    <!-- Categorie Informatie -->
                                    <div class="flex items-start space-x-3">
                                        <!-- Categorie Icoon -->
                                        <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg flex-shrink-0">
                                            <FolderIcon class="w-5 h-5 text-blue-600" />
                                        </div>
                                        <!-- Categorie Details -->
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-gray-900 text-lg truncate">{{ category.name }}</h3>
                                            <p class="text-sm text-gray-500 mt-1">
                                                {{ category.subcategories_count }} 
                                                {{ category.subcategories_count === 1 ? 'subcategory' : 'subcategories' }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Actie Knoppen -->
                                    <div class="flex space-x-2 pt-2 border-t border-gray-200">
                                        <!-- Edit Knop -->
                                        <Link
                                            :href="route('admin.categories.edit', category.id)"
                                            class="flex-1 flex items-center justify-center px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium"
                                        >
                                            <PencilIcon class="h-4 w-4 mr-1" />
                                            Edit
                                        </Link>
                                        <!-- Delete Knop -->
                                        <button
                                            @click="openDeleteModal(category)"
                                            class="flex items-center justify-center px-3 py-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors"
                                        >
                                            <TrashIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Lege Staat voor Mobiel -->
                            <div v-if="filteredCategories.length === 0" class="text-center py-8">
                                <FolderIcon class="mx-auto h-12 w-12 text-gray-400 mb-4" />
                                <p class="text-gray-500 text-lg mb-2">
                                    {{ searchQuery ? 'No categories found' : 'No categories yet' }}
                                </p>
                                <p v-if="!searchQuery" class="text-gray-400 text-sm mb-4">
                                    Create your first category to get started
                                </p>
                                <!-- Eerste Categorie Aanmaken Knop -->
                                <Link
                                    v-if="!searchQuery"
                                    :href="route('admin.categories.create')"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium"
                                >
                                    <PlusIcon class="h-4 w-4 mr-2" />
                                    Add First Category
                                </Link>
                            </div>
                        </div>

                        <!-- Desktop Tabel Weergave (verborgen op kleine schermen) -->
                        <div class="hidden lg:block">
                            <div class="overflow-x-auto">
                                <!-- Categorieën Tabel -->
                                <table class="min-w-full divide-y divide-gray-200">
                                    <!-- Tabel Header -->
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Category
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Subcategories
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <!-- Tabel Body -->
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <!-- Categorie Rij -->
                                        <tr v-for="category in filteredCategories" :key="category.id" class="hover:bg-gray-50">
                                            <!-- Categorie Naam Kolom -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center space-x-3">
                                                    <!-- Categorie Icoon -->
                                                    <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-lg">
                                                        <FolderIcon class="w-4 h-4 text-blue-600" />
                                                    </div>
                                                    <!-- Categorie Naam -->
                                                    <div class="text-sm font-medium text-gray-900">{{ category.name }}</div>
                                                </div>
                                            </td>
                                            <!-- Subcategorieën Kolom -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-600">
                                                    {{ category.subcategories_count }} 
                                                    {{ category.subcategories_count === 1 ? 'subcategory' : 'subcategories' }}
                                                </div>
                                            </td>
                                            <!-- Acties Kolom -->
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-3">
                                                    <!-- Edit Link -->
                                                    <Link 
                                                        :href="route('admin.categories.edit', category.id)"
                                                        class="text-blue-600 hover:text-blue-900 transition-colors"
                                                        title="Edit category"
                                                    >
                                                        <PencilIcon class="h-5 w-5" />
                                                    </Link>
                                                    <!-- Delete Knop -->
                                                    <button 
                                                        @click="openDeleteModal(category)"
                                                        class="text-red-600 hover:text-red-900 transition-colors"
                                                        title="Delete category"
                                                    >
                                                        <TrashIcon class="h-5 w-5" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Lege Staat voor Desktop -->
                            <div v-if="filteredCategories.length === 0" class="text-center py-12">
                                <FolderIcon class="mx-auto h-16 w-16 text-gray-400 mb-6" />
                                <h3 class="text-lg font-medium text-gray-900 mb-2">
                                    {{ searchQuery ? 'No categories found' : 'No categories yet' }}
                                </h3>
                                <p class="text-gray-500 mb-6">
                                    {{ searchQuery ? 'Try adjusting your search terms.' : 'Create your first category to organize your content.' }}
                                </p>
                                <!-- Eerste Categorie Aanmaken Knop -->
                                <Link
                                    v-if="!searchQuery"
                                    :href="route('admin.categories.create')"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium"
                                >
                                    <PlusIcon class="h-4 w-4 mr-2" />
                                    Create First Category
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Uitgebreide Delete Bevestiging Modal -->
        <Modal :show="showDeleteModal" @close="closeDeleteModal" max-width="sm">
            <div class="p-4 sm:p-6">
                <!-- Waarschuwings Icoon -->
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
                    <ExclamationTriangleIcon class="w-6 h-6 text-red-600" />
                </div>
                
                <!-- Modal Titel -->
                <h2 class="text-lg font-medium text-gray-900 text-center mb-2">Delete Category</h2>
                
                <!-- Hoofdconfirmatie Bericht -->
                <p class="text-sm text-gray-600 text-center mb-2">
                    Are you sure you want to delete <strong>{{ selectedCategory?.name }}</strong>?
                </p>
                
                <!-- Waarschuwing voor Subcategorieën -->
                <p v-if="selectedCategory?.subcategories_count > 0" class="text-sm text-red-600 text-center mb-6">
                    This category contains {{ selectedCategory.subcategories_count }} 
                    {{ selectedCategory.subcategories_count === 1 ? 'subcategory' : 'subcategories' }} 
                    that will also be deleted.
                </p>
                
                <!-- Standaard Waarschuwing -->
                <p v-else class="text-sm text-gray-500 text-center mb-6">
                    This action cannot be undone.
                </p>
                
                <!-- Modal Actie Knoppen -->
                <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                    <!-- Cancel Knop -->
                    <button
                        type="button"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                        @click="closeDeleteModal"
                    >
                        Cancel
                    </button>
                    <!-- Delete Bevestiging Knop -->
                    <button 
                        type="button"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        @click="confirmDelete"
                    >
                        Delete Category
                    </button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>