<template>
  <NavBar />
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
            <button @click="addToCart(product)" class="w-full px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors duration-150">
              Toevoegen
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
import { Link } from '@inertiajs/vue3';
import { MagnifyingGlassIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';
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

const addToCart = (product) => {
  cartStore.addItem({
    id: product.id,
    name: product.name,
    price: product.price,
    image: product.image_path,
    quantity: 1,
  });
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