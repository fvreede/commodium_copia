/**
 * Bestandsnaam: Create.vue (Pages/Editor/Products)
 * Auteur: Fabio Vreede
 * Versie: v1.0.5
 * Datum: 2025-06-07
 * Tijd: 00:00:09
 * Doel: Create pagina voor nieuwe producten in de editor interface. Bevat volledige product formulier
 *       met categorisatie, prijzetting, voorraad management, beschrijvingen, en afbeelding upload.
 *       Inclusief dynamische subcategorie filtering, form validatie, en waarschuwingen voor
 *       niet-opgeslagen wijzigingen. Volledige product management voor e-commerce functionaliteit.
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

// Props van server - categorieën en subcategorieën data voor product classificatie
const props = defineProps({
    categories: Array,                         // Array van hoofdcategorieën voor dropdown
    subcategories: Array                      // Array van subcategorieën met category relaties
});

// ========== FORM STATE MANAGEMENT ==========

// Inertia form setup voor product creatie met alle benodigde velden
const form = useForm({
    name: '',                                 // Product naam
    short_description: '',                    // Korte marketing beschrijving
    full_description: '',                     // Uitgebreide product beschrijving
    price: '',                               // Product prijs in euros
    stock_quantity: 0,                       // Beschikbare voorraad aantal
    category_id: '',                         // Geselecteerde hoofdcategorie ID
    subcategory_id: '',                      // Geselecteerde subcategorie ID
    image: null                              // Product afbeelding bestand
});

// ========== VALIDATIE EN WAARSCHUWINGEN ==========

/**
 * Controleert of er niet-opgeslagen wijzigingen zijn in het product formulier
 * Gebruikt voor waarschuwingen bij het verlaten van de pagina
 * @returns {boolean} True als er wijzigingen zijn die niet opgeslagen zijn
 */
const hasSavedChanges = () => {
    return form.name || form.short_description || form.full_description || 
           form.price || form.subcategory_id || form.image;
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
 * Bug fix: Verkeerde logica in originele code - moet return gebruiken
 */
const handleBack = () => {
    if (hasSavedChanges()) {
        if (!confirm('Je hebt niet-opgeslagen wijzigingen. Weet je zeker dat je deze pagina wilt verlaten?')) {
            return; // Stop uitvoering als gebruiker annuleert
        }
    }
    window.location = route('editor.products.index');
};

// ========== FORM SUBMIT HANDLERS ==========

/**
 * Formulier verzenden voor product creatie
 * Gebruikt forceFormData voor bestand upload ondersteuning
 */
const submit = () => {
    form.post(route('editor.products.store'), {
        forceFormData: true                   // Nodig voor afbeelding upload via multipart/form-data
    });
};

// ========== COMPUTED PROPERTIES VOOR CATEGORISATIE ==========

/**
 * Gegroepeerde subcategorieën per hoofdcategorie voor overzichtelijke weergave
 * Gebruikt voor potentiële grouped dropdown implementatie
 * @returns {Array} Array van objecten met categorie naam en bijbehorende subcategorieën
 */
const groupedSubcategories = computed(() => {
    const groups = {};

    // Groepeer subcategorieën per hoofdcategorie
    for (const sub of props.subcategories) {
        const category = sub.category?.name ?? 'Onbekend';
        if (!groups[category]) {
            groups[category] = [];
        }
        groups[category].push(sub);
    }

    // Converteer naar array format voor template gebruik
    return Object.entries(groups).map(([category, subcategories]) => ({
        category,
        subcategories,
    }));
});

/**
 * Gefilterde subcategorieën gebaseerd op geselecteerde hoofdcategorie
 * Zorgt voor dynamische dropdown filtering bij categorie selectie
 * @returns {Array} Gefilterde array van subcategorieën voor geselecteerde categorie
 */
const filteredSubcategories = computed(() =>
    props.subcategories.filter(
        (sub) => sub.category?.id === parseInt(form.category_id)
    )
);
</script>

<template>
    <!-- Editor Layout Wrapper -->
    <EditorLayout>
        <!-- Page Title voor Browser Tab -->
        <Head title="Nieuw Product" />

        <!-- Main Container met Responsive Padding -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            
            <!-- Page Header Sectie -->
            <div class="mb-6">
                <!-- Pagina Titel -->
                <h2 class="text-xl font-semibold text-gray-900">
                    Nieuw Product
                </h2>
            </div>

            <!-- Product Creatie Formulier -->
            <form @submit.prevent="submit" class="space-y-6 bg-white p-6 rounded-lg shadow">
                
                <!-- Product Naam Input Sectie -->
                <div>
                    <!-- Naam Label -->
                    <label class="block text-sm font-medium text-gray-700">
                        Naam
                    </label>
                    <!-- Naam Input Field -->
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
                    <!-- Korte Beschrijving Textarea -->
                    <textarea 
                        v-model="form.short_description"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 resize-none"
                    />
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
                    <!-- Volledige Beschrijving Textarea -->
                    <textarea 
                        v-model="form.full_description"
                        rows="6"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 resize-none"
                    />
                    <!-- Error Message voor Volledige Beschrijving Validatie -->
                    <p v-if="form.errors.full_description" class="text-sm text-red-600 mt-1">
                        {{ form.errors.full_description }}
                    </p>
                </div>

                <!-- Prijs en Voorraad Side-by-Side Layout -->
                <div class="flex space-x-4">
                    <!-- Prijs Input (Helft van Container Breedte) -->
                    <div class="w-1/2">
                        <!-- Prijs Label -->
                        <label class="block text-sm font-medium text-gray-700">
                            Prijs
                        </label>
                        <!-- Prijs Number Input met Decimaal Ondersteuning -->
                        <input 
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                        />
                    </div>

                    <!-- Voorraad Input (Helft van Container Breedte) -->
                    <div class="w-1/2">
                        <!-- Voorraad Label -->
                        <label class="block text-sm font-medium text-gray-700">
                            Voorraad
                        </label>
                        <!-- Voorraad Number Input met Minimum Waarde -->
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

                <!-- Hoofdcategorie Dropdown Sectie -->
                <div>
                    <!-- Categorie Label -->
                    <label>Categorie</label>
                    <!-- Categorie Select Dropdown -->
                    <select v-model="form.category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                        <!-- Placeholder Option -->
                        <option disabled value="">Selecteer een categorie</option>
                        <!-- Categorie Opties Loop -->
                        <option 
                            v-for="cat in categories" 
                            :key="cat.id" 
                            :value="cat.id"
                        >
                            {{ cat.name }}
                        </option>
                    </select>
                    <!-- Error Message voor Categorie Validatie -->
                    <p v-if="form.errors.category_id" class="text-sm text-red-600 mt-1">
                        {{ form.errors.category_id }}
                    </p>
                </div>

                <!-- Subcategorie Dropdown Sectie (Gefilterd op Hoofdcategorie) -->
                <div>
                    <!-- Subcategorie Label -->
                    <label>Subcategorie</label>
                    <!-- Subcategorie Select Dropdown met Filtering -->
                    <select v-model="form.subcategory_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                        <!-- Placeholder Option -->
                        <option disabled value="">Selecteer een subcategorie</option>
                        <!-- Gefilterde Subcategorie Opties Loop -->
                        <option 
                            v-for="sub in filteredSubcategories" 
                            :key="sub.id" 
                            :value="sub.id"
                        >
                            {{ sub.name }}
                        </option>
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
                    <!-- File Input voor Afbeelding Upload -->
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