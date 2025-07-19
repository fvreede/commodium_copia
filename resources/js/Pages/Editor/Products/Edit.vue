/**
 * Bestandsnaam: Edit.vue (Pages/Editor/Products)
 * Auteur: Fabio Vreede
 * Versie: v1.0.4
 * Datum: 2025-06-07
 * Tijd: 00:00:09
 * Doel: Edit pagina voor bestaande producten in de editor interface. Bevat volledige product formulier
 *       met categorisatie via gegroepeerde subcategorieën, prijzetting, voorraad management, beschrijvingen,
 *       en afbeelding upload met huidige afbeelding preview. Inclusief form validatie, wijzigingsdetectie
 *       via isDirty, en waarschuwingen voor niet-opgeslagen data. Volledig product beheer voor e-commerce.
 */

<script setup>
// Vue compositie API imports voor lifecycle en reactive data
import { onMounted, computed } from 'vue';

// Inertia.js imports voor navigatie en forms
import { Head, useForm } from '@inertiajs/vue3';

// Layout en component imports
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

// ========== PROPS DEFINITIE ==========

// Props van server - bestaand product data en subcategorieën voor editing
const props = defineProps({
    product: Object,                          // Bestaand product object met alle huidige data
    subcategories: Array                      // Array van alle subcategorieën met category relaties
});

// ========== FORM STATE MANAGEMENT ==========

// Inertia form setup voor product update - geïnitialiseerd met bestaande product data
const form = useForm({
    name: props.product.name,                                    // Bestaande product naam
    short_description: props.product.short_description,         // Bestaande korte beschrijving
    full_description: props.product.full_description,           // Bestaande uitgebreide beschrijving
    price: props.product.price,                                 // Bestaande product prijs
    stock_quantity: props.product.stock_quantity,               // Bestaande voorraad aantal
    category_id: props.product.subcategory?.category?.id ?? '', // Afgeleide hoofdcategorie ID
    subcategory_id: props.product.subcategory_id,              // Bestaande subcategorie ID
    image: null,                                                // Nieuwe afbeelding upload (null = geen wijziging)
    _method: 'PUT'                                              // HTTP method voor update request
});

// ========== VALIDATIE EN WAARSCHUWINGEN ==========

/**
 * Controleert of er niet-opgeslagen wijzigingen zijn in het product formulier
 * Gebruikt Inertia's isDirty property voor betrouwbare wijzigingsdetectie
 * @returns {boolean} True als er wijzigingen zijn die niet opgeslagen zijn
 */
const hasSavedChanges = () => {
    return form.isDirty;
};

// ========== LIFECYCLE HOOKS ==========

/**
 * Component mounted hook - registreert beforeunload event listener
 * Voorkomt ongewenst verlaten van pagina bij niet-opgeslagen wijzigingen
 */
onMounted(() => {
    window.addEventListener('beforeunload', (e) => {
        if (hasSavedChanges()) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});

// ========== NAVIGATIE HANDLERS ==========

/**
 * Handelt terug navigatie af met bevestiging bij niet-opgeslagen wijzigingen
 * Toont confirmatie dialog als er wijzigingen zijn, anders directe navigatie
 */
const handleBack = () => {
    if (hasSavedChanges()) {
        if (!confirm('Je hebt niet-opgeslagen wijzigingen. Weet je zeker dat je deze pagina wilt verlaten?')) {
            return;
        }
    }
    window.location = route('editor.products.index');
};

// ========== FORM SUBMIT HANDLERS ==========

/**
 * Formulier verzenden voor product update
 * Gebruikt form.submit met POST method en PUT override voor RESTful update
 */
const submit = () => {
    form.submit('post', route('editor.products.update', props.product.id), {
        forceFormData: true,                  // Nodig voor afbeelding upload via multipart/form-data
    });
};

// ========== COMPUTED PROPERTIES VOOR CATEGORISATIE ==========

/**
 * Gegroepeerde subcategorieën per hoofdcategorie voor optgroup weergave
 * Creëert hiërarchische dropdown met subcategorieën gegroepeerd onder hoofdcategorieën
 * @returns {Array} Array van objecten met categorie naam en bijbehorende subcategorieën
 */
const groupedSubcategories = computed(() => {
    const groups = {};

    // Groepeer alle subcategorieën per hoofdcategorie
    for (const sub of props.subcategories) {
        const category = sub.category?.name ?? 'Onbekend';
        if (!groups[category]) {
            groups[category] = [];
        }
        groups[category].push(sub);
    }

    // Converteer naar array format voor optgroup template gebruik
    return Object.entries(groups).map(([category, subcategories]) => ({
        category,
        subcategories,
    }));
});
</script>

<template>
    <!-- Editor Layout Wrapper -->
    <EditorLayout>
        <!-- Page Title voor Browser Tab -->
        <Head title="Product Bewerken" />

        <!-- Main Container met Responsive Padding -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            
            <!-- Page Header Sectie -->
            <div class="mb-6">
                <!-- Pagina Titel -->
                <h2 class="text-xl font-semibold text-gray-900">
                    Product Bewerken
                </h2>
            </div>

            <!-- Product Update Formulier -->
            <form @submit.prevent="submit" class="space-y-6 bg-white p-6 rounded-lg shadow">
                
                <!-- Product Naam Input Sectie -->
                <div>
                    <!-- Naam Label -->
                    <label class="block text-sm font-medium text-gray-700">
                        Naam
                    </label>
                    <!-- Naam Input Field met Bestaande Waarde -->
                    <input
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                    />
                    <!-- Error Message voor Naam Validatie -->
                    <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- Korte Beschrijving Textarea Sectie -->
                <div>
                    <!-- Korte Beschrijving Label -->
                    <label class="block text-sm font-medium text-gray-700">
                        Korte Beschrijving
                    </label>
                    <!-- Korte Beschrijving Textarea met Bestaande Content -->
                    <textarea
                        v-model="form.short_description"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 resize-none"
                    ></textarea>
                    <!-- Error Message voor Korte Beschrijving Validatie -->
                    <p v-if="form.errors.short_description" class="text-sm text-red-600 mt-1">
                        {{ form.errors.short_description }}
                    </p>
                </div>

                <!-- Volledige Beschrijving Textarea Sectie -->
                <div>
                    <!-- Volledige Beschrijving Label -->
                    <label class="block text-sm font-medium text-gray-700">
                        Volledige Beschrijving
                    </label>
                    <!-- Volledige Beschrijving Textarea met Bestaande Content -->
                    <textarea
                        v-model="form.full_description"
                        rows="6"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 resize-none"
                    ></textarea>
                    <!-- Error Message voor Volledige Beschrijving Validatie -->
                    <p v-if="form.errors.full_description" class="text-sm text-red-600 mt-1">
                        {{ form.errors.full_description }}
                    </p>
                </div>

                <!-- Prijs en Voorraad Side-by-Side Layout -->
                <div class="flex space-x-4">
                    <!-- Prijs Input (Helft van Container Breedte) -->
                    <div class="w-1/2">
                        <!-- Prijs Label met Euro Symbool -->
                        <label class="block text-sm font-medium text-gray-700">
                            Prijs (€)
                        </label>
                        <!-- Prijs Number Input met Bestaande Waarde -->
                        <input
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                        />
                        <!-- Error Message voor Prijs Validatie -->
                        <p v-if="form.errors.price" class="text-sm text-red-600 mt-1">
                            {{ form.errors.price }}
                        </p>
                    </div>

                    <!-- Voorraad Input (Helft van Container Breedte) -->
                    <div class="w-1/2">
                        <!-- Voorraad Label -->
                        <label class="block text-sm font-medium text-gray-700">
                            Voorraad
                        </label>
                        <!-- Voorraad Number Input met Bestaande Waarde -->
                        <input
                            v-model="form.stock_quantity"
                            type="number"
                            min="1"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                        />
                        <!-- Error Message voor Voorraad Validatie -->
                        <p v-if="form.errors.stock_quantity" class="text-sm text-red-600 mt-1">
                            {{ form.errors.stock_quantity }}
                        </p>
                    </div>
                </div>

                <!-- Subcategorie Selectie met Gegroepeerde Opties -->
                <div>
                    <!-- Subcategorie Label -->
                    <label class="block text-sm font-medium text-gray-700">
                        Subcategorie
                    </label>
                    <!-- Subcategorie Select met Optgroups per Hoofdcategorie -->
                    <select
                        v-model="form.subcategory_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                    >
                        <!-- Placeholder Option -->
                        <option disabled value="">Selecteer een subcategorie</option>
                        <!-- Gegroepeerde Subcategorieën per Hoofdcategorie -->
                        <optgroup
                            v-for="(group, index) in groupedSubcategories"
                            :key="index"
                            :label="group.category"
                        >
                            <!-- Subcategorie Opties binnen Optgroup -->
                            <option
                                v-for="subcategory in group.subcategories"
                                :key="subcategory.id"
                                :value="subcategory.id"
                            >
                                {{ subcategory.name }}
                            </option>
                        </optgroup>
                    </select>
                    <!-- Error Message voor Subcategorie Validatie -->
                    <p v-if="form.errors.subcategory_id" class="text-sm text-red-600 mt-1">
                        {{ form.errors.subcategory_id }}
                    </p>
                </div>

                <!-- Product Afbeelding Upload Sectie -->
                <div>
                    <!-- Afbeelding Upload Label -->
                    <label class="block text-sm font-medium text-gray-700">
                        Afbeelding
                    </label>
                    <!-- File Input voor Nieuwe Afbeelding Upload -->
                    <input
                        type="file"
                        @input="form.image = $event.target.files[0]"
                        class="mt-1 block w-full"
                        accept="image/*"
                    />
                    <!-- Error Message voor Afbeelding Validatie -->
                    <p v-if="form.errors.image" class="text-sm text-red-600 mt-1">
                        {{ form.errors.image }}
                    </p>
                    <!-- Huidige Product Afbeelding Preview (indien aanwezig) -->
                    <img
                        v-if="product.image_path"
                        :src="`/storage/${product.image_path}`"
                        class="mt-2 h-32 w-auto object-cover rounded-md"
                        alt="Current product image"
                    />
                </div>

                <!-- Formulier Actie Knoppen -->
                <div class="flex justify-end space-x-4">
                    <!-- Annuleer Knop met Terug Navigatie -->
                    <SecondaryButton
                        type="button"
                        @click="handleBack"
                    >
                        Annuleren
                    </SecondaryButton>
                    <!-- Opslaan Knop met Loading State -->
                    <PrimaryButton
                        type="submit"
                        :disabled="form.processing"
                    >
                        Opslaan
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </EditorLayout>
</template>