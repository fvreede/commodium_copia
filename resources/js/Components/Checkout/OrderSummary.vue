<!-- resources/js/Components/Checkout/OrderSummary.vue -->
<template>
    <div class="bg-white border rounded-xl shadow-sm">
        <div class="p-4 sm:p-6">
            <h3 class="text-lg font-medium mb-6">Bestelling overzicht</h3>
            
            <!-- Loading state -->
            <div v-if="cartStore.isLoading" class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-2 text-sm text-gray-600">Bestelling laden...</p>
            </div>
            
            <!-- Empty cart state -->
            <div v-else-if="!cartStore.hasItems" class="text-center py-8">
                <div class="text-gray-400 mb-4">
                    <ShoppingCartIcon class="w-12 h-12 mx-auto" />
                </div>
                <p class="text-gray-600 mb-4">Je winkelwagen is leeg</p>
                <a href="/categories" class="inline-block">
                    <PrimaryButton>
                        Verder winkelen
                    </PrimaryButton>
                </a>
            </div>

            <!-- Cart items -->
            <div v-else>
                <div class="space-y-4 mb-6">
                    <div 
                        v-for="item in cartStore.sortedItems" 
                        :key="item.id || item.product_id"
                        class="flex items-start space-x-3 py-4 border-b last:border-b-0"
                    >
                        <!-- Product image -->
                        <div class="flex-shrink-0">
                            <img 
                                :src="getImageUrl(item.image_path)" 
                                :alt="item.name"
                                class="w-12 h-12 sm:w-16 sm:h-16 object-cover rounded-lg border border-gray-200"
                                @error="handleImageError"
                            >
                        </div>

                        <!-- Product details -->
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 mb-1 line-clamp-2">
                                {{ item.name }}
                            </h4>
                            <p v-if="item.short_description" class="text-xs text-gray-500 mb-2 line-clamp-1">
                                {{ item.short_description }}
                            </p>
                            
                            <!-- Price info -->
                            <div class="space-y-1">
                                <!-- Quantity and unit price -->
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">
                                        {{ item.quantity }}x à €{{ formatPrice(item.price) }}
                                    </span>
                                    <span class="font-medium text-gray-900">
                                        €{{ formatPrice(item.price * item.quantity) }}
                                    </span>
                                </div>

                                <!-- Stock warnings -->
                                <div v-if="item.stock_quantity && item.quantity > item.stock_quantity" 
                                     class="text-xs text-red-600 bg-red-50 px-2 py-1 rounded-md inline-block">
                                    ⚠️ Slechts {{ item.stock_quantity }} op voorraad
                                </div>
                                
                                <div v-else-if="item.stock_quantity && item.stock_quantity <= 5" 
                                     class="text-xs text-orange-600 bg-orange-50 px-2 py-1 rounded-md inline-block">
                                    Nog {{ item.stock_quantity }} op voorraad
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Price breakdown -->
                <div class="space-y-3 pt-4 border-t border-gray-200">
                    <!-- Subtotal -->
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">
                            Artikelen ({{ cartStore.totalItems }}):
                        </span>
                        <span class="font-medium">€{{ formatPrice(cartStore.subtotal) }}</span>
                    </div>

                    <!-- Delivery fee -->
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Bezorgkosten:</span>
                        <span class="font-medium">
                            {{ deliveryFee > 0 ? `€${formatPrice(deliveryFee)}` : 'Nog te bepalen' }}
                        </span>
                    </div>

                    <!-- Discount (if applicable) -->
                    <div v-if="discount && discount > 0" class="flex justify-between text-sm text-green-600">
                        <span>Korting:</span>
                        <span>-€{{ formatPrice(discount) }}</span>
                    </div>

                    <!-- Total -->
                    <div class="flex justify-between text-lg font-semibold pt-3 border-t border-gray-200">
                        <span class="text-gray-900">Totaal:</span>
                        <span class="text-gray-900">€{{ formatPrice(orderTotal) }}</span>
                    </div>

                    <!-- Savings info -->
                    <div v-if="discount && discount > 0" class="text-center pt-2">
                        <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                            Je bespaart €{{ formatPrice(discount) }}!
                        </span>
                    </div>
                </div>

                <!-- Delivery slot info -->
                <div v-if="selectedSlotDetails" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start">
                        <CalendarDaysIcon class="h-5 w-5 text-blue-400 mr-3 flex-shrink-0 mt-0.5" />
                        <div class="min-w-0">
                            <h4 class="text-sm font-medium text-blue-800 mb-1">Bezorgmoment</h4>
                            <p class="text-sm text-blue-700">
                                {{ selectedSlotDetails.day_name }} {{ selectedSlotDetails.formatted_date }}
                            </p>
                            <p class="text-sm text-blue-700 font-medium">
                                {{ selectedSlotDetails.time_display }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Delivery address info -->
                <div v-if="deliveryAddress" class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex items-start">
                        <MapPinIcon class="h-5 w-5 text-gray-400 mr-3 mt-0.5 flex-shrink-0" />
                        <div class="min-w-0">
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Bezorgadres</h4>
                            <p class="text-sm text-gray-600 break-words">
                                <span v-for="line in formatAddress()" :key="line" class="block" >{{ line }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Error state for stock issues -->
                <div v-if="hasStockIssues" class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <XCircleIcon class="h-5 w-5 text-red-400 mt-0.5" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Voorraad probleem</h3>
                            <p class="mt-1 text-sm text-red-700">
                                Sommige producten zijn niet meer voldoende op voorraad. 
                                Pas de hoeveelheden aan voordat je verder gaat.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action buttons for checkout page -->
                <div v-if="showActions" class="mt-6 space-y-3">
                    <!-- Continue to next step button -->
                    <PrimaryButton 
                        v-if="canProceed"
                        @click="$emit('proceed')"
                        :disabled="isProcessing"
                        class="w-full justify-center py-3 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ isProcessing ? 'Bezig...' : 'Doorgaan naar bevestiging' }}
                    </PrimaryButton>
                    
                    <!-- Warning when can't proceed -->
                    <div v-else class="text-center">
                        <div class="inline-flex items-center px-3 py-2 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <ExclamationTriangleIcon class="w-4 h-4 text-yellow-600 mr-2 flex-shrink-0" />
                            <p class="text-sm text-yellow-800">
                                {{ getCannotProceedReason() }}
                            </p>
                        </div>
                    </div>

                    <!-- Back to cart button -->
                    <SecondaryButton 
                        @click="$emit('back-to-cart')"
                        class="w-full justify-center"
                    >
                        Terug naar winkelwagen
                    </SecondaryButton>
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
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg resize-none"
                        placeholder="Bijv. bel aan bij de achterdeur, laat pakket bij de buren, etc."
                        maxlength="500"
                    ></textarea>
                    <p class="mt-1 text-xs text-gray-500 text-right">
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
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import {
    ShoppingCartIcon,
    CalendarDaysIcon,
    MapPinIcon,
    XCircleIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

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
    const addr = props.deliveryAddress
    let lines = []

    let streetLine = addr.street
    if (addr.house_number) streetLine += ` ${addr.house_number}`
    if (addr.addition) streetLine += `, ${addr.addition}`

    lines.push(streetLine)
    lines.push(`${addr.postal_code} ${addr.city}`)

    return lines
}

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
/* Remove focus rings */
button:focus,
textarea:focus {
    outline: none;
}

/* Line clamp utilities for text truncation - modern approach */
.line-clamp-1 {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    line-clamp: 1;
    -webkit-line-clamp: 1;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    overflow: hidden;
}

/* Disable textarea resize */
.resize-none {
    resize: none;
}
</style>