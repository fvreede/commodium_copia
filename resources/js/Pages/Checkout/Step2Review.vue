<!-- Pages/Checkout/Step2Review.vue -->
<script setup>
import CheckoutLayout from '@/Layouts/Checkout/CheckoutLayout.vue';
import OrderSummary from '@/Components/Checkout/OrderSummary.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { 
    PencilIcon, 
    MapPinIcon, 
    ClockIcon, 
    ShoppingCartIcon,
    ExclamationTriangleIcon 
} from '@heroicons/vue/24/outline';
import { useCartStore } from '@/Stores/cart';

// Props from Laravel
const props = defineProps({
    deliverySlots: {
        type: Array,
        default: () => []
    },
    deliveryAddress: {
        type: Object,
        default: null
    },
    selectedSlotId: {
        type: Number,
        default: null
    },
    deliveryFee: {
        type: Number,
        default: 0
    },
    user: {
        type: Object,
        default: null
    }
});

// Initialize cart store
const cartStore = useCartStore();

// Reactive state
const isNavigating = ref(false);
const showConfirmModal = ref(false);

// Computed properties
const hasValidAddress = computed(() => {
    return props.deliveryAddress && 
           props.deliveryAddress.street && 
           props.deliveryAddress.postal_code && 
           props.deliveryAddress.city;
});

const selectedSlotDetails = computed(() => {
    if (!props.selectedSlotId) return null;
    
    for (const day of props.deliverySlots) {
        const slot = day.slots ? day.slots.find(s => s.id === props.selectedSlotId) : null;
        if (slot) {
            return {
                ...slot,
                day_name: day.day_name,
                formatted_date: day.formatted_date
            };
        }
    }
    return null;
});

const orderTotal = computed(() => {
    return cartStore.subtotal + props.deliveryFee;
});

const canProceedToConfirm = computed(() => {
    return cartStore.hasItems && 
           hasValidAddress.value && 
           selectedSlotDetails.value &&
           !hasStockIssues.value;
});

const hasStockIssues = computed(() => {
    return cartStore.sortedItems.some(item => 
        item.stock_quantity !== undefined && 
        item.quantity > item.stock_quantity
    );
});

const validationIssues = computed(() => {
    const issues = [];
    
    if (!cartStore.hasItems) {
        issues.push('Je winkelwagen is leeg');
    }
    
    if (!hasValidAddress.value) {
        issues.push('Geen geldig bezorgadres ingesteld');
    }
    
    if (!selectedSlotDetails.value) {
        issues.push('Geen bezorgmoment geselecteerd');
    }
    
    if (hasStockIssues.value) {
        issues.push('Voorraadproblemen met sommige producten');
    }
    
    return issues;
});

// Methods
const formatAddress = () => {
    if (!hasValidAddress.value) return 'Geen adres ingesteld';
    
    const addr = props.deliveryAddress;
    let formatted = addr.street;
    
    if (addr.house_number) {
        formatted += ` ${addr.house_number}`;
    }
    
    if (addr.addition) {
        formatted += ` ${addr.addition}`;
    }
    
    formatted += `, ${addr.postal_code} ${addr.city}`;
    
    return formatted;
};

const formatDeliverySlot = () => {
    if (!selectedSlotDetails.value) return 'Geen bezorgmoment geselecteerd';
    
    return `${selectedSlotDetails.value.day_name} ${selectedSlotDetails.value.formatted_date} om ${selectedSlotDetails.value.time_display}`;
};

// Navigation methods
const goBackToDelivery = () => {
    router.get('/checkout/delivery');
};

const goBackToCart = () => {
    router.get('/cart');
};

const proceedToConfirm = () => {
    if (!canProceedToConfirm.value || isNavigating.value) return;
    
    isNavigating.value = true;
    
    router.get('/checkout/confirm', {}, {
        onFinish: () => {
            isNavigating.value = false;
        }
    });
};

const quickConfirmOrder = () => {
    showConfirmModal.value = true;
};

const confirmQuickOrder = async () => {
    if (!canProceedToConfirm.value) return;
    
    isNavigating.value = true;
    showConfirmModal.value = false;
    
    try {
        const response = await axios.post('/checkout', {
            delivery_slot_id: props.selectedSlotId
        });
        
        if (response.data.success) {
            // Clear cart store since order was successful
            await cartStore.loadCart();
            
            // Redirect to order confirmation
            router.get(response.data.redirect);
        } else {
            alert(response.data.message || 'Er is een fout opgetreden bij het plaatsen van de bestelling.');
        }
        
    } catch (error) {
        console.error('Error processing order:', error);
        const message = error.response?.data?.message || 'Er is een fout opgetreden bij het plaatsen van de bestelling.';
        alert(message);
    } finally {
        isNavigating.value = false;
    }
};

const cancelQuickOrder = () => {
    showConfirmModal.value = false;
};

// Load cart on mount
onMounted(async () => {
    await cartStore.loadCart();
    
    // Redirect if no items in cart
    if (!cartStore.hasItems) {
        router.get('/cart');
        return;
    }
    
    // Redirect to delivery step if essential data is missing
    if (!hasValidAddress.value || !selectedSlotDetails.value) {
        router.get('/checkout/delivery');
        return;
    }
});
</script>

<template>
    <CheckoutLayout :current-step="2" title="Bestelling controleren">
        <!-- Validation Issues Alert -->
        <div v-if="validationIssues.length > 0" class="mb-6">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            Controleer je bestelling
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <li v-for="issue in validationIssues" :key="issue">{{ issue }}</li>
                            </ul>
                        </div>
                        <div class="mt-3">
                            <button 
                                @click="goBackToDelivery"
                                class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 transition-colors"
                            >
                                Terug naar vorige stap
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left column: Order details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Delivery Information Summary -->
                <div class="bg-white border rounded-lg shadow-sm">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-6">Bezorginformatie</h3>
                        
                        <div class="space-y-6">
                            <!-- Delivery Address -->
                            <div class="flex items-start justify-between">
                                <div class="flex items-start flex-1">
                                    <MapPinIcon class="w-5 h-5 text-gray-400 mr-3 mt-1" />
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 mb-1">Bezorgadres</h4>
                                        <p :class="[
                                            'text-sm',
                                            hasValidAddress ? 'text-gray-700' : 'text-red-600'
                                        ]">
                                            {{ formatAddress() }}
                                        </p>
                                    </div>
                                </div>
                                <button 
                                    @click="goBackToDelivery"
                                    class="inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    <PencilIcon class="w-3 h-3 mr-1" />
                                    Wijzigen
                                </button>
                            </div>

                            <!-- Delivery Time -->
                            <div class="flex items-start justify-between">
                                <div class="flex items-start flex-1">
                                    <ClockIcon class="w-5 h-5 text-gray-400 mr-3 mt-1" />
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 mb-1">Bezorgmoment</h4>
                                        <p :class="[
                                            'text-sm',
                                            selectedSlotDetails ? 'text-gray-700' : 'text-red-600'
                                        ]">
                                            {{ formatDeliverySlot() }}
                                        </p>
                                        <p v-if="selectedSlotDetails" class="text-xs text-gray-500 mt-1">
                                            Bezorgkosten: € {{ props.deliveryFee.toFixed(2) }}
                                        </p>
                                    </div>
                                </div>
                                <button 
                                    @click="goBackToDelivery"
                                    class="inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    <PencilIcon class="w-3 h-3 mr-1" />
                                    Wijzigen
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary Component -->
                <OrderSummary
                    :delivery-fee="deliveryFee"
                    :selected-slot-details="selectedSlotDetails"
                    :delivery-address="deliveryAddress"
                    :show-actions="false"
                />
            </div>

            <!-- Right column: Actions and summary -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Order Total Card -->
                <div class="bg-white border rounded-lg shadow-sm">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Totaal overzicht</h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">
                                    Subtotaal ({{ cartStore.totalItems }} {{ cartStore.totalItems === 1 ? 'artikel' : 'artikelen' }}):
                                </span>
                                <span class="font-medium">€ {{ cartStore.subtotal.toFixed(2) }}</span>
                            </div>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Bezorgkosten:</span>
                                <span class="font-medium">€ {{ deliveryFee.toFixed(2) }}</span>
                            </div>
                            
                            <div class="border-t pt-3 flex justify-between text-lg font-semibold">
                                <span>Totaal:</span>
                                <span>€ {{ orderTotal.toFixed(2) }}</span>
                            </div>
                        </div>

                        <!-- Quick stats -->
                        <div class="mt-4 pt-4 border-t">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div class="text-center">
                                    <div class="font-medium text-gray-900">{{ cartStore.totalItems }}</div>
                                    <div class="text-gray-500">{{ cartStore.totalItems === 1 ? 'Artikel' : 'Artikelen' }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="font-medium text-gray-900">
                                        {{ selectedSlotDetails ? selectedSlotDetails.time_display : '--:--' }}
                                    </div>
                                    <div class="text-gray-500">Bezorgtijd</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="bg-white border rounded-lg shadow-sm">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Acties</h3>
                        
                        <div class="space-y-3">
                            <!-- Continue to Confirmation -->
                            <button 
                                @click="proceedToConfirm"
                                :disabled="!canProceedToConfirm || isNavigating"
                                :class="[
                                    'w-full px-6 py-3 rounded-md font-medium transition-all duration-200',
                                    canProceedToConfirm && !isNavigating
                                        ? 'bg-blue-600 text-white hover:bg-blue-700 shadow-sm hover:shadow-md'
                                        : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                ]"
                            >
                                <span v-if="isNavigating" class="inline-flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Laden...
                                </span>
                                <span v-else>
                                    Verder naar bevestiging →
                                </span>
                            </button>

                            <!-- Quick Order -->
                            <button 
                                @click="quickConfirmOrder"
                                :disabled="!canProceedToConfirm || isNavigating"
                                :class="[
                                    'w-full px-6 py-3 rounded-md font-medium transition-all duration-200 border',
                                    canProceedToConfirm && !isNavigating
                                        ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100'
                                        : 'bg-gray-50 text-gray-400 border-gray-200 cursor-not-allowed'
                                ]"
                            >
                                🚀 Direct bestellen
                            </button>
                            
                            <p class="text-xs text-center text-gray-500">
                                Direct bestellen slaat de bevestigingsstap over
                            </p>
                        </div>

                        <!-- Navigation buttons -->
                        <div class="mt-6 pt-6 border-t space-y-3">
                            <button 
                                @click="goBackToDelivery"
                                class="w-full px-6 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-50 transition-colors"
                            >
                                ← Terug naar bezorgmoment
                            </button>
                            
                            <button 
                                @click="goBackToCart"
                                class="w-full px-6 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-50 transition-colors flex items-center justify-center"
                            >
                                <ShoppingCartIcon class="w-4 h-4 mr-2" />
                                Terug naar winkelwagen
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-900 mb-2">Hulp nodig?</h4>
                    <p class="text-xs text-blue-700 mb-3">
                        Controleer alle gegevens voordat je verder gaat. Je kunt altijd terug naar de vorige stappen.
                    </p>
                    <button class="text-xs text-blue-600 hover:text-blue-500 underline">
                        Contact opnemen
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Confirm Modal -->
        <div v-if="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="rounded-full bg-green-100 p-3">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">Bestelling bevestigen</h3>
                        <p class="text-sm text-gray-600">Weet je zeker dat je deze bestelling wilt plaatsen?</p>
                    </div>
                </div>

                <div class="mb-6 p-4 bg-gray-50 rounded-md">
                    <div class="text-sm space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Totaal:</span>
                            <span class="font-medium">€ {{ orderTotal.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Bezorgmoment:</span>
                            <span class="text-sm">{{ selectedSlotDetails?.time_display }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex space-x-3">
                    <button 
                        @click="cancelQuickOrder"
                        :disabled="isNavigating"
                        class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors disabled:opacity-50"
                    >
                        Annuleren
                    </button>
                    <button 
                        @click="confirmQuickOrder"
                        :disabled="isNavigating"
                        class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors disabled:opacity-50"
                    >
                        {{ isNavigating ? 'Bestellen...' : 'Bevestigen' }}
                    </button>
                </div>
            </div>
        </div>
    </CheckoutLayout>
</template>

<style scoped>
.transition-all {
    transition: all 0.2s ease-in-out;
}

button:focus {
    outline: 2px solid #3B82F6;
    outline-offset: 2px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Enhanced button hover effects */
button:hover:not(:disabled) {
    transform: translateY(-1px);
}

button:active:not(:disabled) {
    transform: translateY(0);
}

/* Card hover effects */
.bg-white.border.rounded-lg.shadow-sm:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: box-shadow 0.2s ease-in-out;
}
</style>