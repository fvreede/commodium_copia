/**
 * Bestandsnaam: ProductPage.vue
 * Auteur: Fabio Vreede
 * Versie: v1.0.5
 * Datum: 2025-06-26
 * Tijd: 00:55:35
 * Doel: Geavanceerde product detail pagina component voor e-commerce met complete shopping functionaliteit. Bevat responsive hero banner, product afbeelding gallery, real-time voorraad status, quantity selector, shopping cart integratie, toast notifications, Nederlandse prijs formatting en mobile-geoptimaliseerde UX voor optimale product conversie en gebruikerservaring.
 */

<!-- Enhanced Product Page with Modern UX -->
<template>
    <!-- Hoofdnavigatie -->
    <NavBar />
    
    <!-- Toast Notification -->
    <!-- Elegant feedback systeem voor cart acties met smooth animaties -->
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <!-- Success Toast Container -->
        <!-- Modern toast design met ring shadow en responsive positioning -->
        <div v-if="showToast" class="fixed top-20 right-4 z-50 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
            <div class="p-4">
                <div class="flex items-start">
                    <!-- Success Icon -->
                    <div class="flex-shrink-0">
                        <CheckCircleIcon class="h-6 w-6 text-green-400" />
                    </div>
                    
                    <!-- Toast Content -->
                    <!-- Contextuele feedback met product quantity informatie -->
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900">Toegevoegd aan winkelwagen!</p>
                        <p class="mt-1 text-sm text-gray-500">{{ quantity }} {{ quantity === 1 ? 'product' : 'producten' }} toegevoegd</p>
                    </div>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Product Content Container -->
    <!-- Gradient achtergrond voor premium product presentation -->
    <div v-if="props.product" class="bg-gradient-to-br from-gray-50 to-white min-h-screen">
        
        <!-- Enhanced Responsive Banner -->
        <!-- Hero sectie met subcategory branding en contextuele navigatie -->
        <div v-if="bannerSrc" class="relative category-banner overflow-hidden pt-16">
            
            <!-- Multi-layer Gradient Overlays -->
            <!-- Optimale tekst leesbaarheid over banner afbeelding -->
            <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent z-10"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent z-10"></div>
            
            <!-- Banner Afbeelding -->
            <!-- Responsive hero met zoom hover effect -->
            <img 
                :src="resolveImagePath(bannerSrc)" 
                :alt="subcategoryName + ' banner'" 
                class="w-full h-48 sm:h-72 md:h-80 object-cover object-center transform scale-105 transition-transform duration-700 hover:scale-110" 
            />
            
            <!-- Banner Content Overlay -->
            <!-- Subcategory titel en terug navigatie -->
            <div class="absolute inset-0 top-16 sm:top-16 z-20 flex flex-col items-start justify-center p-6 sm:p-8">
                <div class="max-w-7xl w-full mx-auto flex flex-col md:flex-row md:items-center md:justify-between">
                    
                    <!-- Subcategory Branding -->
                    <!-- Titel en beschrijving voor product context -->
                    <div class="mb-6 md:mb-0">
                        <h2 class="text-2xl font-bold tracking-tight h_text sm:text-3xl md:text-5xl lg:text-6xl mb-2">
                            {{ subcategoryName }}
                        </h2>
                        <p class="text-white/90 text-sm sm:text-base max-w-md">
                            Ontdek onze premium selectie van {{ categoryName.toLowerCase() }}
                        </p>
                    </div>
                    
                    <!-- Terug Navigatie -->
                    <!-- Glassmorphism styled navigatie naar category -->
                    <Link 
                        :href="route('subcategories.show', { categoryId: categoryId })" 
                        class="group flex items-center px-6 py-3 text-sm font-medium text-white bg-white/10 backdrop-blur-sm hover:bg-white/20 rounded-full border border-white/20 transition-all duration-300 hover:scale-105 min-h-[48px]"
                    >
                        <ArrowLeftIcon class="h-5 w-5 mr-2 transition-transform group-hover:-translate-x-1" />
                        Terug naar {{ categoryName }}
                    </Link>
                </div>
            </div>
        </div>

        <!-- Product Details Container -->
        <!-- Responsive grid layout voor product afbeelding en informatie -->
        <div class="mx-auto max-w-7xl px-4 py-8 sm:py-12 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:items-start lg:gap-x-12">
                
                <!-- Product Afbeelding Sectie -->
                <!-- High-quality product afbeelding met stock badges -->
                <div class="relative group">
                    <!-- Afbeelding Container -->
                    <!-- Aspect ratio container voor consistente product weergave -->
                    <div class="aspect-w-1 aspect-h-1 w-full max-w-lg mx-auto overflow-hidden rounded-2xl bg-white">
                        <img 
                            :src="resolveImagePath(props.product.imageSrc || props.product.image_path)" 
                            :alt="props.product.name" 
                            class="w-full h-auto object-cover object-center" 
                        />
                    </div>
                    
                    <!-- Stock Status Badge -->
                    <!-- Urgency indicator voor lage voorraad -->
                    <div class="absolute top-4 left-4 space-y-2">
                        <span 
                            v-if="props.product.stock_quantity > 0 && props.product.stock_quantity <= 5" 
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 shadow-sm"
                        >
                            Laatste {{ props.product.stock_quantity }}!
                        </span>
                    </div>
                </div>

                <!-- Product Informatie Sectie -->
                <!-- Uitgebreide product details en cart functionaliteit -->
                <div class="mt-8 px-4 sm:mt-12 sm:px-0 lg:mt-0">
                    
                    <!-- Breadcrumb Navigatie -->
                    <!-- Context navigatie voor product hiërarchie -->
                    <nav class="text-sm mb-4">
                        <ol class="flex items-center space-x-2 text-gray-500">
                            <li>{{ categoryName }}</li>
                            <ChevronRightIcon class="w-4 h-4 text-gray-400" />
                            <li class="text-gray-900 font-medium">{{ props.product.name }}</li>
                        </ol>
                    </nav>

                    <!-- Product Titel -->
                    <!-- Prominente product naam met responsive typography -->
                    <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                        {{ props.product.name }}
                    </h1>
                    
                    <!-- Prijs Sectie -->
                    <!-- Nederlandse prijs formatting met optionele eenheid -->
                    <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:gap-6">
                        <div class="flex items-baseline">
                            <p class="text-3xl sm:text-4xl tracking-tight text-gray-900 font-bold">
                                €{{ formatPrice(props.product.price) }}
                            </p>
                            <span v-if="props.product.size" class="ml-3 text-lg text-gray-600">
                                per {{ props.product.size }}
                            </span>
                        </div>
                    </div>

                    <!-- Real-time Stock Status -->
                    <!-- Dynamische voorraad indicator met kleurcoding -->
                    <div class="mt-4">
                        <!-- Ruime Voorraad -->
                        <div v-if="props.product.stock_quantity > 10" class="flex items-center">
                            <div class="w-3 h-3 bg-green-400 rounded-full mr-3 animate-pulse"></div>
                            <span class="text-sm font-medium text-green-700">Ruim op voorraad</span>
                        </div>
                        
                        <!-- Beperkte Voorraad -->
                        <div v-else-if="props.product.stock_quantity > 0" class="flex items-center">
                            <div class="w-3 h-3 bg-yellow-400 rounded-full mr-3 animate-pulse"></div>
                            <span class="text-sm font-medium text-yellow-700">
                                Beperkte voorraad - nog {{ props.product.stock_quantity }} beschikbaar
                            </span>
                        </div>
                        
                        <!-- Uitverkocht -->
                        <div v-else class="flex items-center">
                            <div class="w-3 h-3 bg-red-400 rounded-full mr-3"></div>
                            <span class="text-sm font-medium text-red-700">Tijdelijk niet op voorraad</span>
                        </div>
                    </div>

                    <!-- Product Beschrijving -->
                    <!-- Korte product omschrijving voor quick overview -->
                    <div class="mt-6">
                        <p class="text-lg text-gray-700 leading-relaxed">
                            {{ props.product.description || props.product.short_description }}
                        </p>
                    </div>
                    
                    <!-- Cart Functionaliteit Sectie -->
                    <!-- Quantity selector en add to cart interface -->
                    <div class="mt-10 space-y-6">
                        
                        <!-- Quantity Selector -->
                        <!-- Touch-vriendelijke quantity controls met validatie -->
                        <div class="flex items-center space-x-4">
                            <label for="quantity" class="text-base font-medium text-gray-900">Aantal:</label>
                            
                            <!-- Quantity Input Container -->
                            <!-- Bordered container met focus states -->
                            <div class="flex items-center border-2 border-gray-200 rounded-xl overflow-hidden focus-within:border-orange-500 transition-colors">
                                
                                <!-- Decrease Button -->
                                <!-- Touch-optimized decrease knop met disabled state -->
                                <button 
                                    @click="decreaseQuantity" 
                                    :disabled="quantity <= 1"
                                    class="p-3 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors min-w-[48px] min-h-[48px] flex items-center justify-center group"
                                >
                                    <MinusIcon class="w-5 h-5 transition-transform group-hover:scale-110" />
                                </button>
                                
                                <!-- Quantity Input -->
                                <!-- Number input met stock validation -->
                                <input 
                                    v-model.number="quantity" 
                                    type="number" 
                                    min="1" 
                                    :max="props.product.stock_quantity"
                                    class="w-20 text-center border-0 focus:ring-0 py-3 text-lg font-semibold no-spinner bg-transparent"
                                />
                                
                                <!-- Increase Button -->
                                <!-- Touch-optimized increase knop met stock limiting -->
                                <button 
                                    @click="increaseQuantity" 
                                    :disabled="quantity >= props.product.stock_quantity"
                                    class="p-3 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors min-w-[48px] min-h-[48px] flex items-center justify-center group"
                                >
                                    <PlusIcon class="w-5 h-5 transition-transform group-hover:scale-110" />
                                </button>
                            </div>
                        </div>

                        <!-- Add to Cart Sectie -->
                        <!-- Primaire cart actie met loading en error states -->
                        <div class="space-y-4">
                            
                            <!-- Main Add to Cart Button -->
                            <!-- Gradient styled button met multi-state content -->
                            <button 
                                @click="addToCart" 
                                :disabled="isAddingToCart || props.product.stock_quantity === 0" 
                                class="group relative w-full flex items-center justify-center rounded-2xl border-0 bg-gradient-to-r from-orange-500 to-orange-600 py-4 px-8 text-lg font-semibold text-white hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-4 focus:ring-orange-200 disabled:opacity-50 disabled:cursor-not-allowed min-h-[56px] shadow-lg hover:shadow-xl transition-all duration-200"
                            >
                                <!-- Button Content Container -->
                                <!-- Flexible content voor verschillende button states -->
                                <div class="flex items-center justify-center min-w-0 w-full">
                                    
                                    <!-- Loading State -->
                                    <!-- Spinner en loading tekst tijdens cart actie -->
                                    <template v-if="isAddingToCart">
                                        <svg class="animate-spin mr-3 h-5 w-5 text-white flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Toevoegen...</span>
                                    </template>
                                    
                                    <!-- Out of Stock State -->
                                    <!-- Disabled state voor uitverkochte producten -->
                                    <template v-else-if="props.product.stock_quantity === 0">
                                        <ExclamationTriangleIcon class="w-5 h-5 mr-3 flex-shrink-0" />
                                        <span>Niet op voorraad</span>
                                    </template>
                                    
                                    <!-- Normal State -->
                                    <!-- Standaard cart button met quantity en totaal prijs -->
                                    <template v-else>
                                        <ShoppingCartIcon class="w-5 h-5 mr-3 flex-shrink-0" />
                                        <span>Voeg {{ quantity }} {{ quantity === 1 ? 'product' : 'producten' }} toe • €{{ formatPrice(props.product.price * quantity) }}</span>
                                    </template>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Uitgebreide Product Beschrijving -->
        <!-- Dedicated sectie voor volledige product informatie -->
        <div class="mt-12 lg:mt-16">
            <div class="bg-white rounded-2xl shadow-lg p-8 max-w-6xl mx-auto">
                <!-- Beschrijving Header -->
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 mb-6">
                    Productomschrijving
                </h2>
                
                <!-- Beschrijving Content -->
                <!-- Prose styling voor rijke tekst formatting -->
                <div class="prose prose-lg text-gray-700">
                    <p class="text-lg leading-relaxed">
                        {{ props.product.fullDescription || props.product.full_description || props.product.description }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Niet Gevonden State -->
    <!-- Error state voor ongeldige product URLs -->
    <div v-else class="text-center py-20">
        <div class="max-w-md mx-auto">
            <InformationCircleIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">Product niet gevonden</h3>
            <p class="text-gray-500">Het gevraagde product kon niet worden geladen.</p>
        </div>
    </div>

    <!-- Footer -->
    <Footer />
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import NavBar from '@/Components/NavBar.vue';
import Footer from '@/Components/Footer.vue';
import { useCartStore } from '@/Stores/cart';
import { 
    XMarkIcon, 
    CheckCircleIcon, 
    ArrowLeftIcon, 
    ChevronRightIcon, 
    MinusIcon, 
    PlusIcon, 
    ExclamationTriangleIcon, 
    ShoppingCartIcon, 
    InformationCircleIcon 
} from '@heroicons/vue/24/outline';

/**
 * CART STORE EN REACTIVE STATE
 * Pinia store integratie en component state management
 */
const cartStore = useCartStore();             // Pinia store voor cart management
const isAddingToCart = ref(false);           // Loading state voor cart operaties
const quantity = ref(1);                     // Geselecteerde product quantity
const showToast = ref(false);                // Toast notification zichtbaarheid

/**
 * COMPONENT EIGENSCHAPPEN
 * Props configuratie voor product data en navigatie context
 */
const props = defineProps({
    id: { type: String, required: true },              // Product ID voor cart operations
    product: { type: Object, required: true },          // Complete product object met details
    bannerSrc: { type: String, required: true },        // Banner afbeelding voor subcategory hero
    categoryName: { type: String, required: true },     // Category naam voor breadcrumb en navigatie
    subcategoryName: { type: String, required: true },  // Subcategory naam voor banner titel
    categoryId: { type: String, required: true }        // Category ID voor terug navigatie
});

/**
 * QUANTITY MANAGEMENT FUNCTIES
 * Product quantity controls met stock validatie
 */

// Verhoogt quantity met stock limiting
const increaseQuantity = () => {
    if (quantity.value < props.product.stock_quantity) {
        quantity.value++;
    }
};

// Verlaagt quantity met minimum 1 limiting
const decreaseQuantity = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

/**
 * SHOPPING CART INTEGRATIE
 * Add to cart functionaliteit met error handling en feedback
 */
const addToCart = async () => {
    // Prevent duplicate cart operations
    if (isAddingToCart.value) return;
    
    isAddingToCart.value = true;
    
    try {
        // Flexible product ID handling voor verschillende data structures
        const productId = props.product.id || props.id;
        const result = await cartStore.addToCart({ 
            id: productId, 
            quantity: quantity.value 
        });
        
        // Success feedback met toast notification
        if (result.success) {
            showToast.value = true;
            // Auto-hide toast na 4 seconden voor clean UX
            setTimeout(() => {
                showToast.value = false;
            }, 4000);
        } else {
            console.error('Failed to add product to cart:', result.message);
        }
    } catch (error) {
        console.error('Error adding product to cart:', error);
    } finally {
        isAddingToCart.value = false;
    }
};

/**
 * UTILITY FUNCTIES
 * Helper functies voor data formatting en path resolution
 */

// Nederlandse prijs formatting met locale specifics
const formatPrice = (price) => {
    if (typeof price !== 'number' || isNaN(price)) {
        price = 0;
    }
    return new Intl.NumberFormat('nl-NL', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    }).format(price);
};

// Intelligente afbeelding path resolver voor Laravel storage
const resolveImagePath = (path) => {
    if (!path) return '';
    const cleanPath = path.replace(/^assets\//, '');
    return `/storage/${cleanPath}`;
};

/**
 * MOBILE UX OPTIMALISATIE
 * Auto-scroll naar product info na banner op mobile devices
 */
onMounted(() => {
    if (window.innerWidth < 768) {
        setTimeout(() => {
            const productInfo = document.querySelector('.lg\\:grid');
            if (productInfo) {
                productInfo.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 100);
    }
});
</script>

<style scoped>
/**
 * COMPONENT STYLING
 * Custom CSS voor typography, animations en responsive behavior
 */

/* Custom banner typography */
.h_text {
    font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.8);
    color: #F6EBD8;
}

/* Banner spacing override */
.category-banner {
    margin-bottom: 0;
}

/* Number input spinner hiding voor alle browsers */
.no-spinner::-webkit-inner-spin-button,
.no-spinner::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.no-spinner {
    appearance: textfield;
    -moz-appearance: textfield; /* Firefox compatibility */
}

/* Custom focus styling voor quantity input */
.focus-within\:border-orange-500:focus-within {
    border-color: #f97316;
}

/* Global smooth transitions */
* {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Enhanced hover effecten */
.group:hover .group-hover\:scale-110 {
    transform: scale(1.1);
}

.group:hover .group-hover\:-translate-x-1 {
    transform: translateX(-0.25rem);
}

/* Prose styling voor product beschrijving */
.prose {
    max-width: none;
}

.prose p {
    margin-bottom: 1em;
}

/* Subtle bounce animatie voor quantity buttons */
@keyframes bounce-subtle {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

.quantity-btn:active {
    animation: bounce-subtle 0.2s ease-in-out;
}
</style>