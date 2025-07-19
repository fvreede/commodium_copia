/**
 * Bestandsnaam: Index.vue (Pages/Editor/Products)
 * Auteur: Fabio Vreede
 * Versie: v1.0.8
 * Datum: 2025-06-07
 * Tijd: 00:00:09
 * Doel: Overzichtspagina voor producten management in de editor interface. Bevat een geavanceerde tabel
 *       weergave met product afbeeldingen, categorisatie, prijs formatting, voorraad status indicatoren,
 *       en complete CRUD functionaliteiten. Inclusief delete bevestiging modal, lege staat handling,
 *       responsive design, en enhanced UX met hover effecten en transitions voor optimale gebruikerservaring.
 */

<script setup>
// Vue compositie API imports voor reactive state management
import { ref } from 'vue';

// Inertia.js imports voor navigatie en routing
import { Head, Link, router } from '@inertiajs/vue3';

// Heroicons imports voor UI iconen
import { PencilIcon, TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';

// Layout en component imports
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

// ========== PROPS DEFINITIE ==========

// Props van server - producten en subcategorieën data voor overzicht weergave
const props = defineProps({
    products: Array,                          // Array van product objecten met id, name, price, stock_quantity, etc.
    subcategories: Array,                     // Array van subcategorieën voor filtering (toekomstig gebruik)
});

// ========== UTILITY FUNCTIES ==========

/**
 * Formatteert prijs naar Nederlandse valuta weergave
 * Gebruikt Intl.NumberFormat voor consistente lokalisatie en currency formatting
 * @param {number} price - Numerieke prijs waarde
 * @returns {string} Geformatteerde prijs string in Nederlandse Euro format
 */
const formatPrice = (price) => {
    return new Intl.NumberFormat('nl-NL', {
        style: 'currency',                    // Currency formatting style
        currency: 'EUR',                      // Euro valuta
    }).format(price);
};

// ========== MODAL STATE MANAGEMENT ==========

// Modal visibility en product state voor delete bevestiging
const showModal = ref(false);                // Boolean voor modal visibility
const productToDelete = ref(null);           // ID van product dat verwijderd wordt

// ========== DELETE OPERATIE HANDLERS ==========

/**
 * Opent delete bevestiging modal voor specifiek product
 * Slaat product ID op voor gebruik in delete operatie
 * @param {number} productId - ID van product dat verwijderd wordt
 */
const confirmDelete = (productId) => {
    productToDelete.value = productId;
    showModal.value = true;
}

/**
 * Voert product verwijdering uit na modal bevestiging
 * Stuurt DELETE request naar server en reset modal state bij completion
 */
const deleteProduct = () => {
    if (!productToDelete.value) return;
    
    router.delete(route('editor.products.destroy', productToDelete.value), {
        onFinish: () => {
            showModal.value = false;          // Sluit modal na operatie
            productToDelete.value = null;     // Reset geselecteerd product
        }
    });
};
</script>

<template>
    <!-- Editor Layout Wrapper -->
    <EditorLayout>
        <!-- Page Title voor Browser Tab -->
        <Head title="Producten" />

        <!-- Main Container met Padding -->
        <div class="p-6">
            
            <!-- Enhanced Page Header Sectie met Beschrijving -->
            <div class="flex justify-between items-center mb-8">
                <!-- Header Content met Titel en Beschrijving -->
                <div>
                    <!-- Hoofdtitel met Verbeterde Typography -->
                    <h1 class="text-3xl font-bold text-gray-900">
                        Producten
                    </h1>
                    <!-- Ondertitel voor Context -->
                    <p class="text-gray-600 mt-1">Beheer al je producten op één plek</p>
                </div>
                <!-- Navigatie naar Create Pagina met Enhanced Styling -->
                <Link
                    :href="route('editor.products.create')"
                    class="inline-flex items-center"
                >
                    <!-- Nieuw Product Knop met Hover Effecten -->
                    <PrimaryButton class="shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                        <PlusIcon class="h-5 w-5 mr-2" />
                        Nieuw Product
                    </PrimaryButton>
                </Link>
            </div>

            <!-- Enhanced Producten Tabel Container -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Responsive Tabel Wrapper -->
                <div class="overflow-x-auto">
                    <!-- Producten Data Tabel -->
                    <table class="min-w-full">
                        
                        <!-- Enhanced Tabel Header met Gradient -->
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <!-- Product Kolom Header -->
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Product
                                </th>
                                <!-- Categorie Kolom Header -->
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Categorie
                                </th>
                                <!-- Prijs Kolom Header -->
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Prijs
                                </th>
                                <!-- Voorraad Kolom Header -->
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Voorraad
                                </th>
                                <!-- Acties Kolom Header (Gecentreerd) -->
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Acties
                                </th>
                            </tr>
                        </thead>
                        
                        <!-- Tabel Body met Enhanced Styling -->
                        <tbody class="divide-y divide-gray-100">
                            <!-- Product Rij met Hover Effect (herhaalt voor elk product) -->
                            <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50 transition-colors duration-150">
                                
                                <!-- Product Info Kolom met Afbeelding en Details -->
                                <td class="px-6 py-4">
                                    <!-- Flexbox Layout voor Afbeelding en Text -->
                                    <div class="flex items-center space-x-4">
                                        <!-- Product Afbeelding Container -->
                                        <div class="flex-shrink-0">
                                            <!-- Product Thumbnail met Enhanced Styling -->
                                            <img
                                                :src="`/storage/${product.image_path}`"
                                                :alt="product.name"
                                                class="h-16 w-16 object-cover rounded-lg shadow-sm border border-gray-200"
                                            />
                                        </div>
                                        <!-- Product Text Informatie -->
                                        <div class="flex-1 min-w-0">
                                            <!-- Product Naam met Truncation -->
                                            <p class="text-sm font-semibold text-gray-900 truncate">
                                                {{ product.name }}
                                            </p>
                                            <!-- Korte Beschrijving met Truncation -->
                                            <p class="text-sm text-gray-500 truncate max-w-xs">
                                                {{ product.short_description }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Categorie Badge Kolom -->
                                <td class="px-6 py-4">
                                    <!-- Subcategorie Badge met Fallback -->
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ product.subcategory?.name || 'Geen categorie' }}
                                    </span>
                                </td>
                                
                                <!-- Geformatteerde Prijs Kolom -->
                                <td class="px-6 py-4">
                                    <!-- Prijs met Nederlandse Currency Formatting -->
                                    <span class="text-lg font-bold text-gray-900">
                                        {{ formatPrice(product.price) }}
                                    </span>
                                </td>
                                
                                <!-- Voorraad Status Kolom met Color Coding -->
                                <td class="px-6 py-4">
                                    <!-- Voorraad Aantal met Dynamische Kleuren -->
                                    <span
                                        class="font-medium"
                                        :class="{
                                            'text-green-600': product.stock_quantity > 10,      // Hoge voorraad - groen
                                            'text-orange-500': product.stock_quantity > 0 && product.stock_quantity <= 10, // Lage voorraad - oranje
                                            'text-red-600': product.stock_quantity === 0        // Uitverkocht - rood
                                        }"
                                    >
                                        {{ product.stock_quantity }}
                                    </span>
                                </td>
                                
                                <!-- Acties Kolom met Enhanced Button Styling -->
                                <td class="px-6 py-4">
                                    <!-- Gecentreerde Actie Knoppen Container -->
                                    <div class="flex justify-center space-x-3">
                                        <!-- Edit Product Link met Hover Effecten -->
                                        <Link
                                            :href="route('editor.products.edit', product.id)"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 hover:text-orange-700 transition-all duration-200 hover:scale-105"
                                            title="Bewerken"
                                        >
                                            <PencilIcon class="h-4 w-4" />
                                        </Link>
                                        <!-- Delete Product Button met Hover Effecten -->
                                        <button
                                            @click="confirmDelete(product.id)"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 transition-all duration-200 hover:scale-105"
                                            title="Verwijderen"
                                        >
                                            <TrashIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Enhanced Lege Staat Sectie -->
                <div v-if="products.length === 0" class="text-center py-16">
                    <!-- Icon Container met Enhanced Styling -->
                    <div class="mx-auto h-24 w-24 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                        <PlusIcon class="h-12 w-12 text-gray-400" />
                    </div>
                    <!-- Lege Staat Titel -->
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Geen producten gevonden</h3>
                    <!-- Lege Staat Beschrijving -->
                    <p class="text-gray-500 mb-6">Voeg je eerste product toe om te beginnen.</p>
                    <!-- Call-to-Action Knop voor Eerste Product -->
                    <Link :href="route('editor.products.create')">
                        <PrimaryButton>
                            <PlusIcon class="h-5 w-5 mr-2" />
                            Nieuw Product
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </div>
    </EditorLayout>

    <!-- Delete Bevestiging Modal Component -->
    <ConfirmModal
        :visible="showModal"
        :title="'Product Verwijderen'"
        :message="'Weet je zeker dat je dit product wilt verwijderen?'"
        :cancelText="'Annuleren'"
        :confirmText="'Verwijderen'"
        @cancel="showModal = false"
        @confirm="deleteProduct"
    />
</template>