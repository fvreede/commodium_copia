<!-- SearchPage.vue -->
<template>
    <div class="min-h-screen bg-gray-50 pt-16">
        <!-- Search Header -->
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Zoekresultaten</h1>
                        <p class="mt-1 text-sm text-gray-600" v-if="query">
                            <span v-if="resultsCount > 0">
                                {{ resultsCount }} {{ resultsCount === 1 ? 'resultaat' : 'resultaten' }} voor "{{ query }}"
                            </span>
                            <span v-else>
                                Geen resultaten gevonden voor "{{ query }}"
                            </span>
                        </p>
                        <p class="mt-1 text-sm text-gray-600" v-else>
                            Voer een zoekterm in om producten te vinden
                        </p>
                    </div>
                    
                    <!-- Search input for desktop -->
                    <div class="mt-4 sm:mt-0 sm:ml-6">
                        <div class="relative">
                            <MagnifyingGlassIcon class="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                            <input
                                type="search"
                                placeholder="Zoek een product"
                                v-model="searchInput"
                                @keyup.enter="performSearch"
                                class="pl-10 pr-4 py-2 w-full sm:w-80 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- No query state -->
            <div v-if="!query" class="text-center py-12">
                <MagnifyingGlassIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-4 text-lg font-medium text-gray-900">Begin met zoeken</h3>
                <p class="mt-2 text-gray-600">Gebruik de zoekbalk hierboven om producten te vinden</p>
            </div>

            <!-- No results state -->
            <div v-else-if="resultsCount === 0" class="text-center py-12">
                <div class="mx-auto h-12 w-12 text-gray-400 mb-4">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Geen producten gevonden</h3>
                <p class="mt-2 text-gray-600">Probeer een andere zoekterm of controleer de spelling</p>
            </div>

            <!-- Results Grid -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden"
                >
                    <Link :href="`/product/${product.id}`" class="block">
                        <!-- Product Image -->
                        <div class="aspect-square bg-gray-100 relative overflow-hidden">
                            <img
                                :src="`/storage/${product.image_path}`"
                                :alt="product.name"
                                class="w-full h-full object-cover hover:scale-105 transition-transform duration-200"
                                @error="handleImageError"
                            />
                        </div>
                        
                        <!-- Product Info -->
                        <div class="p-4">
                            <div class="mb-2">
                                <span class="inline-block px-2 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-full">
                                    {{ product.subcategory.category.name }}
                                </span>
                            </div>
                            
                            <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">
                                {{ product.name }}
                            </h3>
                            
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                {{ product.short_description }}
                            </p>
                            
                            <div class="flex items-center justify-between">
                                <div class="text-lg font-bold text-green-600">
                                    €{{ product.price.toFixed(2) }}
                                </div>
                                
                                <button
                                    @click.prevent="addToCart(product)"
                                    class="px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                                >
                                    Toevoegen
                                </button>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import { useCartStore } from '@/Stores/cart'

// Props from the controller
const props = defineProps({
    query: String,
    products: Array,
    resultsCount: Number
})

// Local reactive data
const searchInput = ref(props.query || '')
const cartStore = useCartStore()

// Perform search when user hits enter
const performSearch = () => {
    if (searchInput.value.trim()) {
        router.get('/search', { q: searchInput.value.trim() })
    }
}

// Add product to cart
const addToCart = (product) => {
    cartStore.addItem({
        id: product.id,
        name: product.name,
        price: product.price,
        image: product.image_path,
        quantity: 1
    })
}

// Handle image loading errors
const handleImageError = (event) => {
    event.target.src = '/images/placeholder-product.jpg'
}

// Update search input when props change (for browser back/forward)
onMounted(() => {
    searchInput.value = props.query || ''
})
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>