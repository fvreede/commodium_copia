<!-- Enhanced Product Page with Modern UX -->
<template>
    <NavBar />
    
    <!-- Toast Notification -->
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
                        <p class="mt-1 text-sm text-gray-500">{{ quantity }} {{ quantity === 1 ? 'product' : 'producten' }} toegevoegd</p>
                    </div>
                </div>
            </div>
        </div>
    </Transition>

    <div v-if="props.product" class="bg-gradient-to-br from-gray-50 to-white min-h-screen">
        <!-- Enhanced Responsive Banner -->
        <div v-if="bannerSrc" class="relative category-banner overflow-hidden pt-16">
            <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent z-10"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent z-10"></div>
            <img :src="resolveImagePath(bannerSrc)" 
                 :alt="subcategoryName + ' banner'" 
                 class="w-full h-48 sm:h-72 md:h-80 object-cover object-center transform scale-105 transition-transform duration-700 hover:scale-110" />
            <div class="absolute inset-0 top-16 sm:top-16 z-20 flex flex-col items-start justify-center p-6 sm:p-8">
                <div class="max-w-7xl w-full mx-auto flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="mb-6 md:mb-0">
                        <h2 class="text-2xl font-bold tracking-tight h_text sm:text-3xl md:text-5xl lg:text-6xl mb-2">
                            {{ subcategoryName }}
                        </h2>
                        <p class="text-white/90 text-sm sm:text-base max-w-md">
                            Ontdek onze premium selectie van {{ categoryName.toLowerCase() }}
                        </p>
                    </div>
                    <Link :href="route('subcategories.show', { categoryId: categoryId })" 
                          class="group flex items-center px-6 py-3 text-sm font-medium text-white bg-white/10 backdrop-blur-sm hover:bg-white/20 rounded-full border border-white/20 transition-all duration-300 hover:scale-105 min-h-[48px]">
                        <ArrowLeftIcon class="h-5 w-5 mr-2 transition-transform group-hover:-translate-x-1" />
                        Terug naar {{ categoryName }}
                    </Link>
                </div>
            </div>
        </div>

        <!-- Product Details -->
        <div class="mx-auto max-w-7xl px-4 py-8 sm:py-12 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:items-start lg:gap-x-12">
                <!-- Product Image -->
                <div class="relative group">
                    <div class="aspect-w-1 aspect-h-1 w-full max-w-lg mx-auto overflow-hidden rounded-2xl bg-white">
                        <img :src="resolveImagePath(props.product.imageSrc || props.product.image_path)" 
                             :alt="props.product.name" 
                             class="w-full h-auto object-cover object-center" />
                    </div>
                    <!-- Badge -->
                    <div class="absolute top-4 left-4 space-y-2">
                        <span v-if="props.product.stock_quantity > 0 && props.product.stock_quantity <= 5" 
                              class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 shadow-sm">
                            Laatste {{ props.product.stock_quantity }}!
                        </span>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="mt-8 px-4 sm:mt-12 sm:px-0 lg:mt-0">
                    <nav class="text-sm mb-4">
                        <ol class="flex items-center space-x-2 text-gray-500">
                            <li>{{ categoryName }}</li>
                            <ChevronRightIcon class="w-4 h-4 text-gray-400" />
                            <li class="text-gray-900 font-medium">{{ props.product.name }}</li>
                        </ol>
                    </nav>

                    <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                        {{ props.product.name }}
                    </h1>
                    
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

                    <div class="mt-4">
                        <div v-if="props.product.stock_quantity > 10" class="flex items-center">
                            <div class="w-3 h-3 bg-green-400 rounded-full mr-3 animate-pulse"></div>
                            <span class="text-sm font-medium text-green-700">Ruim op voorraad</span>
                        </div>
                        <div v-else-if="props.product.stock_quantity > 0" class="flex items-center">
                            <div class="w-3 h-3 bg-yellow-400 rounded-full mr-3 animate-pulse"></div>
                            <span class="text-sm font-medium text-yellow-700">
                                Beperkte voorraad - nog {{ props.product.stock_quantity }} beschikbaar
                            </span>
                        </div>
                        <div v-else class="flex items-center">
                            <div class="w-3 h-3 bg-red-400 rounded-full mr-3"></div>
                            <span class="text-sm font-medium text-red-700">Tijdelijk niet op voorraad</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <p class="text-lg text-gray-700 leading-relaxed">
                            {{ props.product.description || props.product.short_description }}
                        </p>
                    </div>
                    
                    <div class="mt-10 space-y-6">
                        <div class="flex items-center space-x-4">
                            <label for="quantity" class="text-base font-medium text-gray-900">Aantal:</label>
                            <div class="flex items-center border-2 border-gray-200 rounded-xl overflow-hidden focus-within:border-orange-500 transition-colors">
                                <button @click="decreaseQuantity" 
                                        :disabled="quantity <= 1"
                                        class="p-3 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors min-w-[48px] min-h-[48px] flex items-center justify-center group">
                                    <MinusIcon class="w-5 h-5 transition-transform group-hover:scale-110" />
                                </button>
                                <input v-model.number="quantity" 
                                       type="number" 
                                       min="1" 
                                       :max="props.product.stock_quantity"
                                       class="w-20 text-center border-0 focus:ring-0 py-3 text-lg font-semibold no-spinner bg-transparent">
                                <button @click="increaseQuantity" 
                                        :disabled="quantity >= props.product.stock_quantity"
                                        class="p-3 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors min-w-[48px] min-h-[48px] flex items-center justify-center group">
                                    <PlusIcon class="w-5 h-5 transition-transform group-hover:scale-110" />
                                </button>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <button 
                                @click="addToCart" 
                                :disabled="isAddingToCart || props.product.stock_quantity === 0" 
                                class="group relative w-full flex items-center justify-center rounded-2xl border-0 bg-gradient-to-r from-orange-500 to-orange-600 py-4 px-8 text-lg font-semibold text-white hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-4 focus:ring-orange-200 disabled:opacity-50 disabled:cursor-not-allowed min-h-[56px] shadow-lg hover:shadow-xl transition-all duration-200"
                            >

                            <div class="flex items-center justify-center min-w-0 w-full">
                                <template v-if="isAddingToCart">
                                    <!-- Loading Spinner -->
                                    <svg class="animate-spin mr-3 h-5 w-5 text-white flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Toevoegen...</span>
                                </template>
                                
                                <template v-else-if="props.product.stock_quantity === 0">
                                    <ExclamationTriangleIcon class="w-5 h-5 mr-3 flex-shrink-0" />
                                    <span>Niet op voorraad</span>
                                </template>
                                
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

        <!-- Full Description -->
         <div class="mt-12 lg:mt-16">
            <div class="bg-white rounded-2xl shadow-lg p-8 max-w-6xl mx-auto">
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 mb-6">
                    Productomschrijving
                </h2>
                <div class="prose prose-lg text-gray-700">
                    <p class="text-lg leading-relaxed">
                        {{ props.product.fullDescription || props.product.full_description || props.product.description }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div v-else class="text-center py-20">
        <div class="max-w-md mx-auto">
            <InformationCircleIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">Product niet gevonden</h3>
            <p class="text-gray-500">Het gevraagde product kon niet worden geladen.</p>
        </div>
    </div>

    <Footer />
</template>


<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import NavBar from '@/Components/NavBar.vue';
import Footer from '@/Components/Footer.vue';
import { useCartStore } from '@/Stores/cart';
import { XMarkIcon, CheckCircleIcon, ArrowLeftIcon, ChevronRightIcon, MinusIcon, PlusIcon, ExclamationTriangleIcon, ShoppingCartIcon, InformationCircleIcon, } from '@heroicons/vue/24/outline';

const cartStore = useCartStore();
const isAddingToCart = ref(false);
const quantity = ref(1);
const showToast = ref(false);

const props = defineProps({
    id: { type: String, required: true },
    product: { type: Object, required: true },
    bannerSrc: { type: String, required: true },
    categoryName: { type: String, required: true },
    subcategoryName: { type: String, required: true },
    categoryId: { type: String, required: true }
});

const increaseQuantity = () => {
    if (quantity.value < props.product.stock_quantity) {
        quantity.value++;
    }
};

const decreaseQuantity = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

const addToCart = async () => {
    if (isAddingToCart.value) return;
    
    isAddingToCart.value = true;
    
    try {
        const productId = props.product.id || props.id;
        const result = await cartStore.addToCart({ 
            id: productId, 
            quantity: quantity.value 
        });
        
        if (result.success) {
            showToast.value = true;
            // Auto-hide toast after 4 seconds
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

const formatPrice = (price) => {
    if (typeof price !== 'number' || isNaN(price)) {
        price = 0;
    }
    return new Intl.NumberFormat('nl-NL', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    }).format(price);
};

const resolveImagePath = (path) => {
    if (!path) return '';
    const cleanPath = path.replace(/^assets\//, '');
    return `/storage/${cleanPath}`;
};

// Auto-scroll to product info on mobile after banner
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
.h_text {
    font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.8);
    color: #F6EBD8;
}

.category-banner {
    margin-bottom: 0;
}

/* Hide number input spinners in all browsers */
.no-spinner::-webkit-inner-spin-button,
.no-spinner::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.no-spinner {
    appearance: textfield;
    -moz-appearance: textfield; /* Firefox */
}

/* Custom focus styles */
.focus-within\:border-orange-500:focus-within {
    border-color: #f97316;
}

/* Smooth transitions */
* {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Enhanced hover effects */
.group:hover .group-hover\:scale-110 {
    transform: scale(1.1);
}

.group:hover .group-hover\:-translate-x-1 {
    transform: translateX(-0.25rem);
}

/* Prose styling for description */
.prose {
    max-width: none;
}

.prose p {
    margin-bottom: 1em;
}

/* Animation for quantity buttons */
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