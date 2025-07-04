<template>
  <NavBar />
  
  <!-- Toast Notification - Same as product page -->
  <Transition
    enter-active-class="transform ease-out duration-300 transition"
    enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
    leave-active-class="transition ease-in duration-100"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div v-if="showToast" class="fixed top-20 right-4 z-50 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
      <div class="p-4">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <CheckCircleIcon class="h-6 w-6 text-green-400" />
          </div>
          <div class="ml-3 w-0 flex-1 pt-0.5">
            <p class="text-sm font-medium text-gray-900">Toegevoegd aan winkelwagen!</p>
            <p class="mt-1 text-sm text-gray-500">{{ lastAddedProduct }} toegevoegd</p>
          </div>
        </div>
      </div>
    </div>
  </Transition>

  <div class="bg-gray-100 min-h-screen pt-16">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
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

    <!-- Product Content -->
    <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 sm:py-24 lg:max-w-7xl lg:px-8">
      <!-- No query state -->
      <div v-if="!query" class="text-center py-20">
        <MagnifyingGlassIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-4 text-lg font-medium text-gray-900">Begin met zoeken</h3>
        <p class="mt-2 text-gray-600">Gebruik de zoekbalk in de navigatiebalk om producten te vinden</p>
      </div>

      <!-- No results state -->
      <div v-else-if="resultsCount === 0" class="text-center py-20">
        <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-400 mb-4" />
        <h3 class="text-lg font-medium text-gray-900">Geen producten gevonden</h3>
        <p class="mt-2 text-gray-600">Probeer een andere zoekterm of controleer de spelling</p>
      </div>

      <!-- Results Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <div v-for="product in products" :key="product.id" class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
          <Link :href="`/product/${product.id}`" class="block">
            <!-- Image -->
            <div class="aspect-w-1 aspect-h-1 w-full bg-gray-100 overflow-hidden">
              <img :src="`/storage/${product.image_path}`" :alt="product.name" class="w-full h-full object-cover object-center" @error="handleImageError" />
            </div>

            <!-- Info -->
            <div class="p-4">
              <h4 class="text-sm font-medium text-gray-900 line-clamp-2">{{ product.name }}</h4>
              <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ product.short_description }}</p>
              <p class="mt-2 text-lg font-semibold text-green-600">€{{ product.price.toFixed(2) }}</p>
            </div>
          </Link>
          <div class="px-4 pb-4">
            <button 
              @click="addToCart(product)" 
              :disabled="loadingProducts.has(product.id)"
              class="w-full px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
            >
              <template v-if="loadingProducts.has(product.id)">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Toevoegen...
              </template>
              <template v-else>
                Toevoegen
              </template>
            </button>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="isLoading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span class="ml-2 text-gray-600">Zoeken...</span>
      </div>
    </div>
  </div>
  <Footer />
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { MagnifyingGlassIcon, DocumentTextIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';
import { useCartStore } from '@/Stores/cart';
import NavBar from '@/Components/NavBar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
  query: String,
  products: Array,
  resultsCount: Number,
  isLoading: { type: Boolean, default: false },
});

const cartStore = useCartStore();

// Toast notification state
const showToast = ref(false);
const lastAddedProduct = ref('');
const loadingProducts = ref(new Set());

const addToCart = async (product) => {
  try {
    // Add loading state for this specific product
    loadingProducts.value.add(product.id);
    
    const result = await cartStore.addToCart({
      id: product.id,
      quantity: 1
    });
    
    if (result.success) {
      // Show toast notification
      lastAddedProduct.value = product.name;
      showToast.value = true;
      
      // Auto-hide toast after 4 seconds
      setTimeout(() => {
        showToast.value = false;
      }, 4000);
      
      console.log('Product added to cart successfully');
    } else {
      console.error('Failed to add product to cart:', result.message);
    }
  } catch (error) {
    console.error('Error adding product to cart:', error);
  } finally {
    // Remove loading state
    loadingProducts.value.delete(product.id);
  }
};

const handleImageError = (event) => {
  event.target.src = '/images/placeholder-product.jpg';
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  line-clamp: 2;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.group:hover {
  transform: translateY(-2px);
  transition: all 0.2s ease-in-out;
}
</style>