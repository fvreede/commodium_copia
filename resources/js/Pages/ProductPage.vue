/**
 * Bestandsnaam: ProductPage.vue
 * Auteur: Fabio Vreede
 * Versie: v1.7.1
 * Datum: 2024-10-29
 * Tijd: 13:41:07
 * Doel: Deze view toont de details van een product op een aparte pagina.
 * Inclusief de banner, productinformatie en een knop om het product aan de winkelwagen toe te voegen.
 */

<template>
    <!-- Navigatiebalk -->
    <NavBar />
    <!-- Controleer of het product bestaat -->
    <div v-if="props.product" class="bg-gray-100">
        <!-- Dynamische Banner met subcategorie-informatie -->
        <div v-if="bannerSrc" class="relative category-banner">
            <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-black/20 z-10"></div>
                <img :src="resolveImagePath(bannerSrc)" :alt="subcategoryName + ' banner'" class="w-full h-64 object-cover object-center" />
            <div class="absolute inset-0 z-20 flex flex-col items-center justify-center p-4">
                <h2 class="text-2xl font-bold tracking-tight h_text sm:text-2xl md:text-4xl lg:text-6xl text-center mb-4 md:mb-0 md:absolute md:left-6 md:top-1/2 md:transform md:-translate-y-1/2">{{ subcategoryName }}
                </h2>

                <!-- Link om terug te gaan naar de subcategorie -->
                <Link :href="route('subcategories.show', { categoryId: categoryId })" class="flex items-center px-4 py-2 text-xs font-medium text-white bg-gray-800 hover:bg-gray-900 rounded-full md:absolute md:right-4 md:top-1/2 md:transform md:-translate-y-1/2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                    Terug naar {{ categoryName }}
                </Link>
            </div>
        </div>

        <!-- Product Details -->
        <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 sm:py-24 lg:max-w-7xl lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:items-start lg:gap-x-8">
                <!-- Product Afbeelding -->
                <div class="flex justify-center items-center aspect-w-1 aspect-h-1 w-full max-w-md lg:max-w-xs overflow-hidden rounded-lg lg:ml-40 ml-0">
                    <img :src="resolveImagePath(props.product.imageSrc || props.product.image_path)" :alt="props.product.name" class="w-full h-auto object-cover object-center sm:rounded-lg" />
                </div>

                <!-- Product Info -->
                <div class="mt-10 px-4 sm:mt-16 sm:px-0 lg:mt-0">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ props.product.name }}</h1>
                    <div class="mt-3">
                        <h2 class="sr-only">Product information</h2>
                        <p class="text-3xl tracking-tight text-gray-900">€{{ formatPrice(props.product.price) }}</p>
                    </div>
                    <div class="mt-6">
                        <h3 class="sr-only">Description</h3>
                        <p class="text-base text-gray-700">{{ props.product.description || props.product.short_description }}</p>
                    </div>
                    
                    <!-- Knop om toe te voegen aan winkelwagen -->
                    <div class="mt-10 flex">
                        <button 
                            @click="addToCart" 
                            :disabled="isAddingToCart || props.product.stock_quantity === 0"
                            type="button" 
                            class="flex max-w-xs flex-1 items-center justify-center rounded-md border border-transparent 
                                bg-orange-600 py-3 px-8 text-base font-medium text-white 
                                hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 
                                focus:ring-offset-brown-50 sm:w-full 
                                disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="isAddingToCart">Toevoegen...</span>
                            <span v-else-if="props.product.stock_quantity === 0">Niet op voorraad</span>
                            <span v-else>Voeg toe aan winkelwagen</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Volledige product omschrijving -->
             <div class="mt-4">
                <h2 class="text-2xl font-semibold tracking-tight text-gray-900">Productomschrijving</h2>
                <hr class="my-4 border-gray-500" />
                <div class="mt-4">
                    <p class="text-base text-gray-700">{{ props.product.fullDescription || props.product.full_description }}</p>
                </div>
            </div>
        </div>
    </div>
    <div v-else class="text-center py-10">
        <p>Product not found.</p>
    </div>
    <!-- Footer -->
    <Footer />
</template>
  
<script setup>
// Het importeren van Vue functies en andere componenten
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import NavBar from '@/Components/NavBar.vue';
import Footer from '@/Components/Footer.vue';

// Winkelwagen store importeren
import { useCartStore } from '@/Stores/cart';
const cartStore = useCartStore();

// Reactive state for loading
const isAddingToCart = ref(false);

// Props definiëren
const props = defineProps({
    id: {
        type: String,
        required: true
    },
    product: {
        type: Object,
        required: true
    },
    bannerSrc: {
        type: String,
        required: true
    },
    categoryName: {
        type: String,
        required: true
    },
    subcategoryName: {
        type: String,
        required: true
    },
    categoryId: {
        type: String,
        required: true
    }
});

/**
 * Add product to cart with proper error handling
 */
const addToCart = async () => {
    if (isAddingToCart.value) return;
    
    isAddingToCart.value = true;
    
    try {
        // Use the correct product ID - prefer the product object's ID
        const productId = props.product.id || props.id;
        console.log('Adding product to cart:', { productId, product: props.product });
        
        const result = await cartStore.addToCart({ id: productId });
        
        if (result.success) {
            // Optional: Show success message
            console.log('Product added to cart successfully');
        } else {
            console.error('Failed to add product to cart:', result.message);
            // Optional: Show error message to user
        }
    } catch (error) {
        console.error('Error adding product to cart:', error);
    } finally {
        isAddingToCart.value = false;
    }
};

/**
 * Functie om prijs te formatteren naar Nederlands formaat.
 * @param { number } price - De prijs van het product
 * @returns { string } - De geformatteerde prijs
 */
const formatPrice = (price) => {
    if (typeof price !== 'number' || isNaN(price)) {
        price = 0;
    }
    return new Intl.NumberFormat('nl-NL', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    }).format(price);
}

/**
 * Functie om afbeeldingspad op te lossen voor producten EN banners.
 * @param { string } path - Pad naar de afbeelding
 * @returns { string } - absolute URL van de afbeelding
 */
const resolveImagePath = (path) => {
    if (!path) return '';
    const cleanPath = path.replace(/^assets\//, '');
    return `/storage/${cleanPath}`;
};
</script>

<style scoped>
.h_text {
    font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
    color: #F6EBD8;
}

.category-banner {
    margin-bottom: 2rem;
}
</style>