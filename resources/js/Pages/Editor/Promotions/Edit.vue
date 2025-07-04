<!--resources/js/Pages/Editor/Promotions/Edit.vue-->
<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { onMounted, ref, computed } from 'vue';
 
const props = defineProps({
    promotion: {
        type: Object,
        required: true
    },
    products: {
        type: Array,
        required: true
    }
});

const form = useForm({
    title: props.promotion?.title || '',
    description: props.promotion?.description || '',
    cta_text: props.promotion?.cta_text || '',
    image: null,
    valid_until: props.promotion?.valid_until || '',
    products: props.promotion?.products?.map(p => ({
        id: p.id,
        discount_price: p.pivot?.discount_price || '',
    })) || []
});

// Add warning when navigating away with unsaved changes
const hasSavedChanges = () => {
    return form.isDirty;
};

onMounted(() => {
    window.addEventListener('beforeunload', (e) => {
        if (hasSavedChanges()) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});

const handleBack = () => {
    if (hasSavedChanges()) {
        if (confirm('Je hebt niet-opgeslagen wijzigingen. Weet je zeker dat je deze pagina wilt verlaten?')) {
            window.location = route('editor.promotions.index');
        }
    } else {
        window.location = route('editor.promotions.index');
    }
};

const submit = () => {
    form.put(route('editor.promotions.update', props.promotion.id));
};

// Search and filter functionality
const searchQuery = ref('');

// Filter products based on search
const filteredProducts = computed(() => {
    if (!searchQuery.value || !props.products) return props.products || [];
    
    return props.products.filter(product =>
        product.name && product.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

// Get selected products for display
const selectedProducts = computed(() => {
    if (!props.products) return [];
    
    return props.products.filter(product => 
        form.products.some(selected => selected.id === product.id)
    );
});

const toggleProduct = (product) => {
    const existingIndex = form.products.findIndex(p => p.id === product.id);
    
    if (existingIndex > -1) {
        // Remove product
        form.products.splice(existingIndex, 1);
    } else {
        // Add product
        form.products.push({
            id: product.id,
            discount_price: ''
        });
    }
};

const removeProduct = (productId) => {
    const index = form.products.findIndex(p => p.id === productId);
    if (index > -1) {
        form.products.splice(index, 1);
    }
};

const isProductSelected = (productId) => {
    return form.products.some(p => p.id === productId);
};

const getProductDiscountPrice = (productId) => {
    const product = form.products.find(p => p.id === productId);
    return product ? product.discount_price : '';
};

const setProductDiscountPrice = (productId, price) => {
    const product = form.products.find(p => p.id === productId);
    if (product) {
        product.discount_price = price;
    }
};
</script>

<template>
    <Head title="Aanbieding Bewerken" />
    <EditorLayout>
        <div class="min-h-screen bg-gray-50">
            <!-- Mobile Header -->
            <div class="bg-white border-b border-gray-200 px-4 py-3 sm:hidden">
                <div class="flex items-center">
                    <button @click="handleBack" class="mr-3 p-2 -ml-2 rounded-md text-gray-400 hover:text-gray-600">
                        <ArrowLeftIcon class="h-6 w-6" />
                    </button>
                    <h1 class="text-lg font-semibold text-gray-900">Aanbieding bewerken</h1>
                </div>
            </div>

            <div class="max-w-7xl mx-auto py-4 sm:py-6 px-4 sm:px-6 lg:px-8">
                <!-- Desktop Header -->
                <div class="hidden sm:flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <SecondaryButton @click="handleBack" class="mr-4">
                            <ArrowLeftIcon class="h-5 w-5 mr-2" />
                            Terug
                        </SecondaryButton>
                        <h2 class="text-xl font-semibold text-gray-900">
                            Aanbieding bewerken
                        </h2>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information Card -->
                    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Basis informatie</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Titel
                                </label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-base"
                                    placeholder="Voer een titel in"
                                />
                            </div>

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
                            </div>

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
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Geldig tot
                                </label>
                                <input
                                    v-model="form.valid_until"
                                    type="date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-base"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Image Card -->
                    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Afbeelding</h3>
                        
                        <!-- Current Image Preview -->
                        <div v-if="promotion.image_path" class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Huidige afbeelding
                            </label>
                            <img 
                                :src="`/storage/${promotion.image_path}`" 
                                alt="Current promotion image"
                                class="h-32 w-auto object-cover rounded-md border border-gray-200"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nieuwe afbeelding uploaden
                            </label>
                            <input
                                type="file"
                                @input="form.image = $event.target.files[0]"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-base"
                                accept="image/*"
                            />
                            <p class="text-xs text-gray-500 mt-1">
                                Laat leeg om de huidige afbeelding te behouden
                            </p>
                        </div>
                    </div>

                    <!-- Products Card -->
                    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Producten</h3>
                        
                        <!-- Search/Filter -->
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

                        <!-- Selected Products Summary -->
                        <div v-if="selectedProducts.length > 0" class="mb-4 p-3 bg-orange-50 border border-orange-200 rounded-md">
                            <p class="text-sm font-medium text-orange-800 mb-2">
                                {{ selectedProducts.length }} product{{ selectedProducts.length !== 1 ? 'en' : '' }} geselecteerd
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <span 
                                    v-for="product in selectedProducts" 
                                    :key="product.id"
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800"
                                >
                                    {{ product.name }}
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

                        <!-- Products List -->
                        <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-md mb-4">
                            <div v-if="!filteredProducts || filteredProducts.length === 0" class="p-4 text-center text-gray-500">
                                <p v-if="searchQuery">Geen producten gevonden voor "{{ searchQuery }}"</p>
                                <p v-else>Geen producten beschikbaar</p>
                            </div>
                            
                            <div v-else class="divide-y divide-gray-200">
                                <div 
                                    v-for="product in filteredProducts" 
                                    :key="product.id"
                                    class="p-3 hover:bg-gray-50 cursor-pointer transition-colors duration-200"
                                    @click="toggleProduct(product)"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <input
                                                type="checkbox"
                                                :checked="isProductSelected(product.id)"
                                                class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                                                @click.stop
                                                @change="toggleProduct(product)"
                                            />
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ product.name }}</p>
                                                <p v-if="product.price" class="text-xs text-gray-500">€{{ product.price }}</p>
                                            </div>
                                        </div>
                                        <div v-if="isProductSelected(product.id)" class="text-xs text-orange-600 font-medium">
                                            Geselecteerd
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Discount Prices for Selected Products -->
                        <div v-if="selectedProducts.length > 0">
                            <h4 class="text-md font-medium text-gray-900 mb-3">Aanbiedingsprijzen</h4>
                            <div class="space-y-3">
                                <div 
                                    v-for="product in selectedProducts" 
                                    :key="product.id"
                                    class="border border-gray-200 rounded-lg p-3"
                                >
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ product.name }}</p>
                                            <p v-if="product.price" class="text-xs text-gray-500">Normale prijs: €{{ product.price }}</p>
                                        </div>
                                        <div class="w-full sm:w-32">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                Aanbiedingsprijs <span class="text-red-500">*</span>
                                            </label>
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
                        </div>

                        <!-- Help Text -->
                        <div v-if="selectedProducts.length === 0" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                            <p class="text-sm text-blue-800">
                                💡 Selecteer producten door ze aan te vinken. Je kunt meerdere producten selecteren voor deze aanbieding.
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-4">
                            <SecondaryButton 
                                type="button" 
                                @click="handleBack"
                                class="w-full sm:w-auto justify-center order-2 sm:order-1"
                            >
                                Annuleren
                            </SecondaryButton>
                            <PrimaryButton
                                type="submit"
                                :disabled="form.processing"
                                class="w-full sm:w-auto justify-center order-1 sm:order-2"
                            >
                                <span v-if="form.processing">Opslaan...</span>
                                <span v-else>Opslaan</span>
                            </PrimaryButton>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </EditorLayout>
</template>