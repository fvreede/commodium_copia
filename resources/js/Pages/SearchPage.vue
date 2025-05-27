<!-- SearchPage.vue -->
<template>
    <NavBar />
    <div class="min-h-screen bg-gray-50 pt-16">
        <!-- Search Header -->
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
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
            </div>
        </div>

        <!-- Results Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- No query state -->
            <div v-if="!query" class="text-center py-12">
                <MagnifyingGlassIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-4 text-lg font-medium text-gray-900">Begin met zoeken</h3>
                <p class="mt-2 text-gray-600">Gebruik de zoekbalk in de navigatiebalk om producten te vinden</p>
            </div>

            <!-- No results state -->
            <div v-else-if="resultsCount === 0" class="text-center py-12">
                <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-400 mb-4" />
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
    <Footer />
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { MagnifyingGlassIcon, DocumentTextIcon } from '@heroicons/vue/24/outline'
import { useCartStore } from '@/Stores/cart'
import NavBar from '@/Components/NavBar.vue'
import Footer from '@/Components/Footer.vue'

// Props from the controller
const props = defineProps({
    query: String,
    products: Array,
    resultsCount: Number
})

const cartStore = useCartStore()

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
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>