/**
 * Bestandsnaam: Create.vue (Pages/Editor/Promotions)
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 16:58:38
 * Tijd: 2025-07-04
 * Doel: Create pagina voor nieuwe promoties/aanbiedingen in de editor interface. Bevat volledige promotie
 *       formulier met basis informatie, afbeelding upload met preview, geavanceerde product selectie met
 *       zoekfunctionaliteit, individuele aanbiedingsprijzen per product, responsive mobile/desktop design,
 *       en complete validatie. Volledig promotie management systeem voor marketing campagnes met multi-product
 *       ondersteuning en dynamische prijs configuratie.
 */

<script setup>
// Inertia.js imports voor navigatie en forms
import { Head, useForm } from '@inertiajs/vue3';

// Heroicons imports voor UI iconen
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

// Layout en component imports
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

// Vue compositie API imports voor advanced functionality
import { onMounted, ref, computed } from 'vue';
 
// ========== PROPS DEFINITIE ==========

// Props van server - alleen beschikbare producten voor selectie (geen bestaande promotie)
const props = defineProps({
    products: {                               // Array van alle beschikbare producten voor selectie
        type: Array,
        required: true
    }
});

// ========== FORM STATE MANAGEMENT ==========

// Inertia form setup voor nieuwe promotie - alle velden beginnen leeg
const form = useForm({
    title: '',                                // Lege promotie titel
    description: '',                          // Lege promotie beschrijving
    cta_text: '',                            // Lege call-to-action tekst
    image: null,                             // Geen afbeelding geselecteerd
    valid_until: '',                         // Geen vervaldatum ingesteld
    products: []                             // Geen producten geselecteerd
});

// ========== VALIDATIE EN WAARSCHUWINGEN ==========

/**
 * Controleert of er niet-opgeslagen wijzigingen zijn in het promotie formulier
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
        if (confirm('Je hebt niet-opgeslagen wijzigingen. Weet je zeker dat je deze pagina wilt verlaten?')) {
            window.location = route('editor.promotions.index');
        }
    } else {
        window.location = route('editor.promotions.index');
    }
};

// ========== FORM SUBMIT HANDLERS ==========

/**
 * Formulier verzenden voor nieuwe promotie aanmaken
 * Stuurt POST request naar server met nieuwe promotie data
 */
const submit = () => {
    form.post(route('editor.promotions.store'));
};

// ========== PRODUCT ZOEK EN FILTER FUNCTIONALITEIT ==========

// Reactive search state voor product filtering
const searchQuery = ref('');                  // Huidige zoekterm van gebruiker

/**
 * Gefilterde producten gebaseerd op zoekterm
 * Case-insensitive filtering op product naam voor gebruiksvriendelijke zoekervaring
 * @returns {Array} Gefilterde array van producten die voldoen aan zoekterm
 */
const filteredProducts = computed(() => {
    if (!searchQuery.value || !props.products) return props.products || [];
    
    return props.products.filter(product =>
        product.name && product.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

/**
 * Geselecteerde producten voor weergave in summary sectie
 * Kruist geselecteerde product IDs met volledige product data voor display
 * @returns {Array} Array van volledige product objecten die geselecteerd zijn
 */
const selectedProducts = computed(() => {
    if (!props.products) return [];
    
    return props.products.filter(product => 
        form.products.some(selected => selected.id === product.id)
    );
});

// ========== PRODUCT SELECTIE MANAGEMENT ==========

/**
 * Toggle product selectie - voegt toe of verwijdert product uit selectie
 * Beheert zowel selectie als initialisatie van aanbiedingsprijs velden
 * @param {Object} product - Product object om te selecteren/deselecteren
 */
const toggleProduct = (product) => {
    const existingIndex = form.products.findIndex(p => p.id === product.id);
    
    if (existingIndex > -1) {
        // Verwijder product uit selectie
        form.products.splice(existingIndex, 1);
    } else {
        // Voeg product toe aan selectie met lege aanbiedingsprijs
        form.products.push({
            id: product.id,
            discount_price: ''
        });
    }
};

/**
 * Verwijdert specifiek product uit selectie via summary sectie
 * Alternative removal method voor UX vanuit selected products overview
 * @param {number} productId - ID van product om te verwijderen
 */
const removeProduct = (productId) => {
    const index = form.products.findIndex(p => p.id === productId);
    if (index > -1) {
        form.products.splice(index, 1);
    }
};

/**
 * Controleert of een product momenteel geselecteerd is
 * Utility functie voor conditionale UI rendering (checkboxes, badges)
 * @param {number} productId - ID van product om te controleren
 * @returns {boolean} True als product geselecteerd is
 */
const isProductSelected = (productId) => {
    return form.products.some(p => p.id === productId);
};

// ========== AANBIEDINGSPRIJS MANAGEMENT ==========

/**
 * Haalt huidige aanbiedingsprijs op voor specifiek product
 * Gebruikt voor het tonen van bestaande waarden in input velden
 * @param {number} productId - ID van product
 * @returns {string} Huidige aanbiedingsprijs of lege string
 */
const getProductDiscountPrice = (productId) => {
    const product = form.products.find(p => p.id === productId);
    return product ? product.discount_price : '';
};

/**
 * Stelt aanbiedingsprijs in voor specifiek product
 * Gebruikt door input event handlers voor real-time prijs updates
 * @param {number} productId - ID van product
 * @param {string} price - Nieuwe aanbiedingsprijs waarde
 */
const setProductDiscountPrice = (productId, price) => {
    const product = form.products.find(p => p.id === productId);
    if (product) {
        product.discount_price = price;
    }
};

// ========== IMAGE PREVIEW FUNCTIONALITY ==========

// Reactive state voor afbeelding preview
const imagePreview = ref(null);

/**
 * Handelt afbeelding selectie af en genereert preview
 * @param {Event} event - File input change event
 */
const handleImageSelect = (event) => {
    const file = event.target.files[0];
    form.image = file;
    
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        imagePreview.value = null;
    }
};
</script>

<template>
    <!-- Editor Layout Wrapper -->
    <EditorLayout>
        <!-- Page Title voor Browser Tab -->
        <Head title="Nieuwe Aanbieding" />

        <!-- Full Height Container met Background -->
        <div class="min-h-screen bg-gray-50">
            
            <!-- Mobile Header (alleen zichtbaar op kleine schermen) -->
            <div class="bg-white border-b border-gray-200 px-4 py-3 sm:hidden">
                <!-- Mobile Navigation en Titel -->
                <div class="flex items-center">
                    <!-- Terug Knop voor Mobile -->
                    <button @click="handleBack" class="mr-3 p-2 -ml-2 rounded-md text-gray-400 hover:text-gray-600">
                        <ArrowLeftIcon class="h-6 w-6" />
                    </button>
                    <!-- Mobile Pagina Titel -->
                    <h1 class="text-lg font-semibold text-gray-900">Nieuwe aanbieding</h1>
                </div>
            </div>

            <!-- Main Content Container met Responsive Padding -->
            <div class="max-w-7xl mx-auto py-4 sm:py-6 px-4 sm:px-6 lg:px-8">
                
                <!-- Desktop Header (verborgen op mobile) -->
                <div class="hidden sm:flex items-center justify-between mb-6">
                    <!-- Desktop Navigation en Titel -->
                    <div class="flex items-center">
                        <!-- Terug Knop voor Desktop -->
                        <SecondaryButton @click="handleBack" class="mr-4">
                            <ArrowLeftIcon class="h-5 w-5 mr-2" />
                            Terug
                        </SecondaryButton>
                        <!-- Desktop Pagina Titel -->
                        <h2 class="text-xl font-semibold text-gray-900">
                            Nieuwe aanbieding maken
                        </h2>
                    </div>
                </div>

                <!-- Nieuwe Promotie Formulier -->
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <!-- Basis Informatie Card -->
                    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                        <!-- Card Titel -->
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Basis informatie</h3>
                        
                        <!-- Basis Informatie Velden -->
                        <div class="space-y-4">
                            <!-- Promotie Titel Input -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Titel <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-base"
                                    placeholder="Voer een titel in"
                                    required
                                />
                                <div v-if="form.errors.title" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.title }}
                                </div>
                            </div>

                            <!-- Promotie Beschrijving Textarea -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Beschrijving
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 resize-none text-base"
                                    placeholder="Beschrijf de aanbieding"
                                ></textarea>
                                <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <!-- Call-to-Action Tekst Input -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    CTA Tekst
                                </label>
                                <input
                                    v-model="form.cta_text"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-base"
                                    placeholder="Bijv. 'Bekijk aanbieding'"
                                />
                                <div v-if="form.errors.cta_text" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.cta_text }}
                                </div>
                            </div>

                            <!-- Vervaldatum Input -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Geldig tot <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.valid_until"
                                    type="date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-base"
                                    required
                                />
                                <div v-if="form.errors.valid_until" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.valid_until }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Afbeelding Upload Card -->
                    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                        <!-- Card Titel -->
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Afbeelding</h3>
                        
                        <!-- Afbeelding Upload Input -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Afbeelding uploaden
                            </label>
                            <!-- File Input voor Afbeelding Upload -->
                            <input
                                type="file"
                                @change="handleImageSelect"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-base"
                                accept="image/*"
                            />
                            <div v-if="form.errors.image" class="text-red-600 text-sm mt-1">
                                {{ form.errors.image }}
                            </div>
                        </div>

                        <!-- Image Preview -->
                        <div v-if="imagePreview" class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Voorbeeld
                            </label>
                            <img 
                                :src="imagePreview" 
                                alt="Image preview"
                                class="h-32 w-auto object-cover rounded-md border border-gray-200"
                            />
                        </div>
                    </div>

                    <!-- Product Selectie Card -->
                    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                        <!-- Card Titel -->
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Producten</h3>
                        
                        <!-- Product Zoek Input -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Zoek producten
                            </label>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Type om te zoeken..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-base"
                            />
                        </div>

                        <!-- Geselecteerde Producten Summary -->
                        <div v-if="selectedProducts.length > 0" class="mb-4 p-3 bg-orange-50 border border-orange-200 rounded-md">
                            <!-- Summary Header -->
                            <p class="text-sm font-medium text-orange-800 mb-2">
                                {{ selectedProducts.length }} product{{ selectedProducts.length !== 1 ? 'en' : '' }} geselecteerd
                            </p>
                            <!-- Selected Products Tags -->
                            <div class="flex flex-wrap gap-2">
                                <span 
                                    v-for="product in selectedProducts" 
                                    :key="product.id"
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800"
                                >
                                    {{ product.name }}
                                    <!-- Remove Product Button -->
                                    <button 
                                        @click="removeProduct(product.id)"
                                        class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full text-orange-400 hover:bg-orange-200 hover:text-orange-600"
                                        type="button"
                                    >
                                        ×
                                    </button>
                                </span>
                            </div>
                        </div>

                        <!-- Product Selectie Lijst -->
                        <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-md mb-4">
                            <!-- Lege Staat voor Product Lijst -->
                            <div v-if="!filteredProducts || filteredProducts.length === 0" class="p-4 text-center text-gray-500">
                                <p v-if="searchQuery">Geen producten gevonden voor "{{ searchQuery }}"</p>
                                <p v-else>Geen producten beschikbaar</p>
                            </div>
                            
                            <!-- Product Items -->
                            <div v-else class="divide-y divide-gray-200">
                                <div 
                                    v-for="product in filteredProducts" 
                                    :key="product.id"
                                    class="p-3 hover:bg-gray-50 cursor-pointer transition-colors duration-200"
                                    @click="toggleProduct(product)"
                                >
                                    <!-- Product Item Layout -->
                                    <div class="flex items-center justify-between">
                                        <!-- Product Info met Checkbox -->
                                        <div class="flex items-center space-x-3">
                                            <!-- Product Selectie Checkbox -->
                                            <input
                                                type="checkbox"
                                                :checked="isProductSelected(product.id)"
                                                class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                                                @click.stop
                                                @change="toggleProduct(product)"
                                            />
                                            <!-- Product Details -->
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ product.name }}</p>
                                                <p v-if="product.price" class="text-xs text-gray-500">€{{ product.price }}</p>
                                            </div>
                                        </div>
                                        <!-- Selectie Status Indicator -->
                                        <div v-if="isProductSelected(product.id)" class="text-xs text-orange-600 font-medium">
                                            Geselecteerd
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Aanbiedingsprijzen Configuratie voor Geselecteerde Producten -->
                        <div v-if="selectedProducts.length > 0">
                            <!-- Aanbiedingsprijzen Sectie Titel -->
                            <h4 class="text-md font-medium text-gray-900 mb-3">Aanbiedingsprijzen</h4>
                            <!-- Aanbiedingsprijzen Lijst -->
                            <div class="space-y-3">
                                <div 
                                    v-for="product in selectedProducts" 
                                    :key="product.id"
                                    class="border border-gray-200 rounded-lg p-3"
                                >
                                    <!-- Product Prijs Configuratie Layout -->
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                                        <!-- Product Informatie -->
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ product.name }}</p>
                                            <p v-if="product.price" class="text-xs text-gray-500">Normale prijs: €{{ product.price }}</p>
                                        </div>
                                        <!-- Aanbiedingsprijs Input -->
                                        <div class="w-full sm:w-32">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                Aanbiedingsprijs <span class="text-red-500">*</span>
                                            </label>
                                            <!-- Prijs Input met Validatie -->
                                            <input
                                                type="number"
                                                :value="getProductDiscountPrice(product.id)"
                                                @input="setProductDiscountPrice(product.id, $event.target.value)"
                                                placeholder="0.00"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm"
                                                step="0.01"
                                                min="0"
                                                required
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Validation Error voor Products -->
                            <div v-if="form.errors.products" class="text-red-600 text-sm mt-2">
                                {{ form.errors.products }}
                            </div>
                        </div>

                        <!-- Help Text voor Lege Product Selectie -->
                        <div v-if="selectedProducts.length === 0" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                            <p class="text-sm text-blue-800">
                                💡 Selecteer producten door ze aan te vinken. Je kunt meerdere producten selecteren voor deze aanbieding.
                            </p>
                        </div>
                    </div>

                    <!-- Formulier Actie Knoppen Card -->
                    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                        <!-- Responsive Button Layout -->
                        <div class="flex flex-col sm:flex-row sm:justify-end space-y-4 sm:space-y-0 sm:space-x-4">
                            <!-- Annuleer Knop -->
                            <SecondaryButton 
                                type="button" 
                                @click="handleBack"
                                class="w-full sm:w-auto justify-center order-2 sm:order-1"
                            >
                                Annuleren
                            </SecondaryButton>
                            <!-- Opslaan Knop met Loading State -->
                            <PrimaryButton
                                type="submit"
                                :disabled="form.processing"
                                class="w-full sm:w-auto justify-center order-1 sm:order-2"
                            >
                                <span v-if="form.processing">Aanmaken...</span>
                                <span v-else>Aanbieding Aanmaken</span>
                            </PrimaryButton>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </EditorLayout>
</template>