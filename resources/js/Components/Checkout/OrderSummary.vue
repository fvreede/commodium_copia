<!-- resources/js/Components/Checkout/OrderSummary.vue -->
<template>
    <div class="bg-white border rounded-lg shadow-sm">
        <div class="p-6">
            <h3 class="text-lg font-medium mb-6">Bestelling overzicht</h3>
            
            <!-- Loading state -->
            <div v-if="cartStore.isLoading" class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-2 text-sm text-gray-600">Bestelling laden...</p>
            </div>
            
            <!-- Empty cart state -->
            <div v-else-if="!cartStore.hasItems" class="text-center py-8">
                <div class="text-gray-400 mb-4">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 1.5M7 13l1.5 1.5M17 21a2 2 0 100-4 2 2 0 000 4zM9 21a2 2 0 100-4 2 2 0 000 4z"/>
                    </svg>
                </div>
                <p class="text-gray-600">Je winkelwagen is leeg</p>
                <a 
                    href="/categories" 
                    class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                >
                    Verder winkelen
                </a>
            </div>

            <!-- Cart items -->
            <div v-else>
                <div class="space-y-4 mb-6">
                    <div 
                        v-for="item in cartStore.sortedItems" 
                        :key="item.id || item.product_id"
                        class="flex flex-col sm:flex-row sm:items-start sm:space-x-4 py-4 border-b last:border-b-0 space-y-3 sm:space-y-0"
                    >
                        <!-- Product image -->
                        <div class="flex-shrink-0">
                            <img 
                                :src="getImageUrl(item.image_path)" 
                                :alt="item.name"
                                class="w-12 h-12 sm:w-16 sm:h-16 object-cover rounded-md border"
                                @error="handleImageError"
                            >
                        </div>

                        <!-- Product details -->
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 mb-1">
                                {{ item.name }}
                            </h4>
                            <p v-if="item.short_description" class="text-xs text-gray-500 mb-2">
                                {{ item.short_description }}
                            </p>
                            
                            <!-- Price and quantity info -->
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-600">
                                    {{ item.quantity }}x à € {{ formatPrice(item.price) }}
                                </div>
                                <div class="text-sm font-medium text-gray-900">
                                    € {{ formatPrice(item.price * item.quantity) }}
                                </div>
                            </div>

                            <!-- Stock warning -->
                            <div v-if="item.stock_quantity && item.quantity > item.stock_quantity" 
                                 class="mt-2 text-xs text-red-600 bg-red-50 px-2 py-1 rounded">
                                ⚠️ Slechts {{ item.stock_quantity }} op voorraad
                            </div>
                            
                            <!-- Low stock warning -->
                            <div v-else-if="item.stock_quantity && item.stock_quantity <= 5" 
                                 class="mt-2 text-xs text-orange-600 bg-orange-50 px-2 py-1 rounded">
                                Nog {{ item.stock_quantity }} op voorraad
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Price breakdown -->
                <div class="space-y-3 pt-4 border-t">
                    <!-- Subtotal -->
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">
                            Subtotaal ({{ cartStore.totalItems }} {{ cartStore.totalItems === 1 ? 'artikel' : 'artikelen' }}):
                        </span>
                        <span class="font-medium">€ {{ formatPrice(cartStore.subtotal) }}</span>
                    </div>

                    <!-- Delivery fee -->
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Bezorgkosten:</span>
                        <span class="font-medium">
                            {{ deliveryFee > 0 ? `€ ${formatPrice(deliveryFee)}` : 'Nog te bepalen' }}
                        </span>
                    </div>

                    <!-- Discount (if applicable) -->
                    <div v-if="discount && discount > 0" class="flex justify-between text-sm text-green-600">
                        <span>Korting:</span>
                        <span>-€ {{ formatPrice(discount) }}</span>
                    </div>

                    <!-- Total -->
                    <div class="flex justify-between text-lg font-semibold pt-3 border-t">
                        <span>Totaal:</span>
                        <span>€ {{ formatPrice(orderTotal) }}</span>
                    </div>

                    <!-- Savings info -->
                    <div v-if="discount && discount > 0" class="text-center">
                        <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                            Je bespaart € {{ formatPrice(discount) }}!
                        </span>
                    </div>
                </div>

                <!-- Delivery slot info -->
                <div v-if="selectedSlotDetails" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-blue-400 mr-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-blue-800">Bezorgmoment</h4>
                            <p class="text-sm text-blue-700">
                                {{ selectedSlotDetails.day_name }} {{ selectedSlotDetails.formatted_date }} 
                                om {{ selectedSlotDetails.time_display }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Delivery address info -->
                <div v-if="deliveryAddress" class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-md">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-gray-400 mr-3 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-gray-700">Bezorgadres</h4>
                            <p class="text-sm text-gray-600">
                                {{ formatAddress() }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Error state for stock issues -->
                <div v-if="hasStockIssues" class="mt-6 p-4 bg-red-50 border border-red-200 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Voorraad probleem</h3>
                            <p class="mt-1 text-sm text-red-700">
                                Sommige producten in je winkelwagen zijn niet meer voldoende op voorraad. 
                                Pas de hoeveelheden aan voordat je verder gaat.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action buttons for checkout page -->
                <div v-if="showActions" class="mt-6 space-y-3">
                    <!-- Continue to next step button -->
                    <button 
                        v-if="canProceed"
                        @click="$emit('proceed')"
                        :disabled="isProcessing"
                        :class="[
                            'w-full px-6 py-3 font-medium rounded-md transition-colors',
                            isProcessing
                                ? 'bg-gray-400 text-gray-700 cursor-not-allowed'
                                : 'bg-blue-600 text-white hover:bg-blue-700'
                        ]"
                    >
                        {{ isProcessing ? 'Bezig...' : 'Doorgaan naar bevestiging' }}
                    </button>
                    
                    <!-- Warning when can't proceed -->
                    <div v-else class="text-center">
                        <p class="text-sm text-gray-600 mb-3">
                            {{ getCannotProceedReason() }}
                        </p>
                    </div>

                    <!-- Back to cart button -->
                    <button 
                        @click="$emit('back-to-cart')"
                        class="w-full px-6 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-50 transition-colors"
                    >
                        Terug naar winkelwagen
                    </button>
                </div>

                <!-- Order notes input (for confirmation page) -->
                <div v-if="showOrderNotes" class="mt-6">
                    <label for="order-notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Opmerkingen voor uw bestelling (optioneel)
                    </label>
                    <textarea
                        id="order-notes"
                        :value="orderNotes"
                        @input="$emit('update:order-notes', $event.target.value)"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Bijv. bel aan bij de achterdeur, laat pakket bij de buren, etc."
                        maxlength="500"
                    ></textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ orderNotes?.length || 0 }}/500 karakters
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, watch, onUnmounted } from 'vue';
import { useCartStore } from '@/Stores/cart';

// Props
const props = defineProps({
    deliveryFee: {
        type: Number,
        default: 0
    },
    discount: {
        type: Number,
        default: 0
    },
    selectedSlotDetails: {
        type: Object,
        default: null
    },
    deliveryAddress: {
        type: Object,
        default: null
    },
    showActions: {
        type: Boolean,
        default: true
    },
    showOrderNotes: {
        type: Boolean,
        default: false
    },
    orderNotes: {
        type: String,
        default: ''
    },
    isProcessing: {
        type: Boolean,
        default: false
    }
});

// Emits
const emit = defineEmits([
    'proceed', 
    'back-to-cart', 
    'update:order-notes'
]);

// Initialize cart store
const cartStore = useCartStore();

// Load cart on mount
onMounted(async () => {
    if (!cartStore.hasItems && !cartStore.isLoading) {
        await cartStore.loadCart();
    }
});

// Computed properties
const orderTotal = computed(() => {
    return cartStore.subtotal + props.deliveryFee - (props.discount || 0);
});

const canProceed = computed(() => {
    return cartStore.hasItems && 
           props.selectedSlotDetails && 
           !hasStockIssues.value &&
           !props.isProcessing;
});

const hasStockIssues = computed(() => {
    return cartStore.sortedItems.some(item => 
        item.stock_quantity !== undefined && 
        item.quantity > item.stock_quantity
    );
});

// Methods
const formatPrice = (price) => {
    return Number(price || 0).toLocaleString('nl-NL', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const getImageUrl = (imagePath) => {
    if (!imagePath) {
        return '/images/placeholder.jpg';
    }

    // If it's already a full URL, return as is
    if (imagePath.startsWith('http') || imagePath.startsWith('/storage/') || imagePath.startsWith('/images/')) {
        return imagePath;
    }

    // Assume it's a storage path
    return `/storage/${imagePath}`;
};

const handleImageError = (event) => {
    event.target.src = '/images/placeholder.jpg';
};

const formatAddress = () => {
    if (!props.deliveryAddress) return 'Geen adres ingesteld';
    
    const addr = props.deliveryAddress;
    let formatted = addr.street;
    
    if (addr.house_number) {
        formatted += ` ${addr.house_number}`;
    }
    
    formatted += `, ${addr.postal_code} ${addr.city}`;
    
    return formatted;
};

const getCannotProceedReason = () => {
    if (!cartStore.hasItems) return 'Je winkelwagen is leeg';
    if (!props.selectedSlotDetails) return 'Selecteer eerst een bezorgmoment';
    if (hasStockIssues.value) return 'Controleer de voorraad van je producten';
    return 'Controleer je bestelling';
};

// Watch for cart changes and emit updates
watch(() => cartStore.subtotal, (newSubtotal) => {
    // You can emit subtotal changes if needed
}, { immediate: true });

// Auto-refresh cart data every 30 seconds to catch stock changes
let refreshInterval;
onMounted(() => {
    refreshInterval = setInterval(async () => {
        if (!cartStore.isLoading) {
            await cartStore.loadCart();
        }
    }, 30000);
});

onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<style scoped>
/* Custom focus styles for better accessibility */
button:focus,
textarea:focus {
    outline: 2px solid #3B82F6;
    outline-offset: 2px;
}

/* Smooth transitions for interactive elements */
button,
.transition-colors {
    transition: all 0.2s ease-in-out;
}
</style>