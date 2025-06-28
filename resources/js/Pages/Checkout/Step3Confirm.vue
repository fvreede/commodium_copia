<!-- Pages/Checkout/Step3Confirm.vue -->
<script setup>
import CheckoutLayout from '@/Layouts/Checkout/CheckoutLayout.vue';
import OrderSummary from '@/Components/Checkout/OrderSummary.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { 
    CheckCircleIcon,
    ExclamationTriangleIcon,
    MapPinIcon,
    ClockIcon,
    ChatBubbleLeftEllipsisIcon,
    CreditCardIcon,
    ShieldCheckIcon
} from '@heroicons/vue/24/outline';
import { useCartStore } from '@/Stores/cart';
import axios from 'axios';

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
const orderNotes = ref('');
const isProcessingOrder = ref(false);
const acceptedTerms = ref(false);
const selectedPaymentMethod = ref('ideal');
const showConfirmModal = ref(false);
const orderError = ref('');

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

const canPlaceOrder = computed(() => {
    return cartStore.hasItems && 
           hasValidAddress.value && 
           selectedSlotDetails.value &&
           acceptedTerms.value &&
           selectedPaymentMethod.value &&
           !hasStockIssues.value &&
           !isProcessingOrder.value;
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

const paymentMethods = [
    { id: 'ideal', name: 'iDEAL', icon: '🏦', description: 'Betaal direct via je bank' },
    { id: 'card', name: 'Creditcard', icon: '💳', description: 'Visa, Mastercard, American Express' },
    { id: 'paypal', name: 'PayPal', icon: '🟦', description: 'Betaal met je PayPal account' },
    { id: 'cash', name: 'Contant', icon: '💵', description: 'Betaal bij levering (excl. online korting)' }
];

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

// Order processing
const showConfirmOrderModal = () => {
    if (!canPlaceOrder.value) return;
    showConfirmModal.value = true;
};

const confirmOrder = async () => {
    if (!canPlaceOrder.value) return;
    
    isProcessingOrder.value = true;
    showConfirmModal.value = false;
    orderError.value = '';
    
    try {
        const response = await axios.post('/checkout', {
            delivery_slot_id: props.selectedSlotId,
            order_notes: orderNotes.value,
            payment_method: selectedPaymentMethod.value
        });
        
        if (response.data.success) {
            // Clear cart store since order was successful
            await cartStore.loadCart();
            
            // Redirect to order confirmation or payment
            if (response.data.payment_url) {
                // Redirect to payment processor
                window.location.href = response.data.payment_url;
            } else {
                // Redirect to order confirmation
                router.get(response.data.redirect);
            }
        } else {
            throw new Error(response.data.message || 'Failed to place order');
        }
        
    } catch (error) {
        console.error('Error processing order:', error);
        
        if (error.response?.status === 422) {
            orderError.value = error.response.data.message || 'Controleer je bestelling en probeer opnieuw.';
        } else if (error.response?.status === 500) {
            orderError.value = 'Er is een serverfout opgetreden. Probeer het later opnieuw.';
        } else {
            orderError.value = error.message || 'Er is een fout opgetreden bij het plaatsen van de bestelling.';
        }
    } finally {
        isProcessingOrder.value = false;
    }
};

const cancelConfirmOrder = () => {
    showConfirmModal.value = false;
};

// Navigation
const goBackToReview = () => {
    router.get('/checkout/review');
};

const goBackToCart = () => {
    router.get('/cart');
};

// Load cart and validate on mount
onMounted(async () => {
    await cartStore.loadCart();
    
    // Redirect if no items in cart
    if (!cartStore.hasItems) {
        router.get('/cart');
        return;
    }
    
    // Redirect to previous steps if essential data is missing
    if (!hasValidAddress.value || !selectedSlotDetails.value) {
        router.get('/checkout/delivery');
        return;
    }
});
</script>

<template>
    <CheckoutLayout :current-step="3" title="Bestelling bevestigen">
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
                                @click="goBackToReview"
                                class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 transition-colors"
                            >
                                Terug naar overzicht
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Error Alert -->
        <div v-if="orderError" class="mb-6">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Fout bij bestelling</h3>
                        <p class="mt-1 text-sm text-red-700">{{ orderError }}</p>
                        <div class="mt-3">
                            <button 
                                @click="orderError = ''"
                                class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 transition-colors"
                            >
                                Sluiten
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left column: Final review and options -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Final Order Summary -->
                <div class="bg-white border rounded-lg shadow-sm">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-6">Finale controle</h3>
                        
                        <!-- Key Information Summary -->
                        <div class="space-y-4 mb-6">
                            <!-- Address -->
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <MapPinIcon class="w-5 h-5 text-gray-400 mr-3" />
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Bezorgadres</p>
                                    <p class="text-sm text-gray-600">{{ formatAddress() }}</p>
                                </div>
                                <CheckCircleIcon class="w-5 h-5 text-green-500" />
                            </div>

                            <!-- Delivery Time -->
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <ClockIcon class="w-5 h-5 text-gray-400 mr-3" />
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Bezorgmoment</p>
                                    <p class="text-sm text-gray-600">{{ formatDeliverySlot() }}</p>
                                    <p class="text-xs text-gray-500">Bezorgkosten: € {{ deliveryFee.toFixed(2) }}</p>
                                </div>
                                <CheckCircleIcon class="w-5 h-5 text-green-500" />
                            </div>
                        </div>

                        <!-- Payment Method Selection -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Betaalmethode</h4>
                            <div class="space-y-2">
                                <div 
                                    v-for="method in paymentMethods" 
                                    :key="method.id"
                                    class="relative"
                                >
                                    <label :class="[
                                        'flex items-center p-4 border rounded-lg cursor-pointer transition-colors',
                                        selectedPaymentMethod === method.id
                                            ? 'border-blue-200 bg-blue-50'
                                            : 'border-gray-200 hover:bg-gray-50'
                                    ]">
                                        <input
                                            v-model="selectedPaymentMethod"
                                            :value="method.id"
                                            type="radio"
                                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                                        >
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center">
                                                <span class="text-lg mr-2">{{ method.icon }}</span>
                                                <span class="text-sm font-medium text-gray-900">{{ method.name }}</span>
                                            </div>
                                            <p class="text-xs text-gray-500">{{ method.description }}</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Order Notes -->
                        <div class="mb-6">
                            <label for="order-notes" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                <ChatBubbleLeftEllipsisIcon class="w-4 h-4 mr-2" />
                                Opmerkingen voor uw bestelling (optioneel)
                            </label>
                            <textarea
                                id="order-notes"
                                v-model="orderNotes"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Bijv. bel aan bij de achterdeur, laat pakket bij de buren, etc."
                                maxlength="500"
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ orderNotes?.length || 0 }}/500 karakters
                            </p>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-6">
                            <label class="flex items-start">
                                <input
                                    v-model="acceptedTerms"
                                    type="checkbox"
                                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded mt-0.5"
                                >
                                <div class="ml-3">
                                    <span class="text-sm text-gray-900">
                                        Ik ga akkoord met de 
                                        <a href="/terms" target="_blank" class="text-blue-600 hover:text-blue-500 underline">
                                            algemene voorwaarden
                                        </a>
                                        en het 
                                        <a href="/privacy" target="_blank" class="text-blue-600 hover:text-blue-500 underline">
                                            privacybeleid
                                        </a>
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Je moet akkoord gaan om de bestelling te kunnen plaatsen.
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Order Summary Component -->
                <OrderSummary
                    :delivery-fee="deliveryFee"
                    :selected-slot-details="selectedSlotDetails"
                    :delivery-address="deliveryAddress"
                    :show-actions="false"
                    :show-order-notes="false"
                />
            </div>

            <!-- Right column: Final actions -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Final Total -->
                <div class="bg-white border rounded-lg shadow-sm">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Te betalen</h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotaal:</span>
                                <span class="font-medium">€ {{ cartStore.subtotal.toFixed(2) }}</span>
                            </div>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Bezorgkosten:</span>
                                <span class="font-medium">€ {{ deliveryFee.toFixed(2) }}</span>
                            </div>
                            
                            <div class="border-t pt-3 flex justify-between text-xl font-bold">
                                <span>Totaal:</span>
                                <span class="text-green-600">€ {{ orderTotal.toFixed(2) }}</span>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-md">
                            <div class="flex items-center text-sm text-green-800">
                                <ShieldCheckIcon class="w-4 h-4 mr-2" />
                                Veilig betalen via SSL-versleuteling
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Place Order Button -->
                <div class="space-y-4">
                    <button 
                        @click="showConfirmOrderModal"
                        :disabled="!canPlaceOrder"
                        :class="[
                            'w-full px-6 py-4 rounded-lg font-bold text-lg transition-all duration-200',
                            canPlaceOrder
                                ? 'bg-green-600 text-white hover:bg-green-700 shadow-lg hover:shadow-xl'
                                : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                        ]"
                    >
                        <span v-if="isProcessingOrder" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Bestelling plaatsen...
                        </span>
                        <span v-else class="flex items-center justify-center">
                            <CreditCardIcon class="w-5 h-5 mr-2" />
                            Bestelling plaatsen
                        </span>
                    </button>
                    
                    <!-- Why can't proceed -->
                    <div v-if="!canPlaceOrder && !isProcessingOrder" class="text-center">
                        <p class="text-sm text-gray-600 px-3 py-2 bg-yellow-50 border border-yellow-200 rounded-md">
                            <span v-if="!acceptedTerms">Accepteer de voorwaarden om verder te gaan</span>
                            <span v-else-if="!selectedPaymentMethod">Selecteer een betaalmethode</span>
                            <span v-else-if="validationIssues.length > 0">Controleer je bestelling</span>
                            <span v-else>Controleer alle velden</span>
                        </p>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="space-y-3">
                    <button 
                        @click="goBackToReview"
                        :disabled="isProcessingOrder"
                        class="w-full px-6 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-50 transition-colors disabled:opacity-50"
                    >
                        ← Terug naar overzicht
                    </button>
                    
                    <button 
                        @click="goBackToCart"
                        :disabled="isProcessingOrder"
                        class="w-full px-6 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-50 transition-colors disabled:opacity-50"
                    >
                        Terug naar winkelwagen
                    </button>
                </div>

                <!-- Security Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-900 mb-2">Beveiliging & Privacy</h4>
                    <ul class="text-xs text-blue-700 space-y-1">
                        <li>• SSL-versleutelde betalingen</li>
                        <li>• Je gegevens worden veilig bewaard</li>
                        <li>• 30 dagen retourrecht</li>
                        <li>• Klantenservice beschikbaar</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Confirm Order Modal -->
        <div v-if="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75">
            <div class="bg-white rounded-lg p-6 max-w-lg w-full mx-4 shadow-xl">
                <div class="flex items-center mb-6">
                    <div class="flex-shrink-0">
                        <div class="rounded-full bg-green-100 p-3">
                            <CheckCircleIcon class="w-8 h-8 text-green-600" />
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">Bestelling bevestigen</h3>
                        <p class="text-sm text-gray-600">
                            Je staat op het punt om je bestelling te plaatsen. Controleer nog een keer of alles correct is.
                        </p>
                    </div>
                </div>

                <div class="mb-6 p-4 bg-gray-50 rounded-md">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Totaalbedrag:</span>
                            <span class="font-bold text-lg">€ {{ orderTotal.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Bezorgmoment:</span>
                            <span class="text-gray-900">{{ selectedSlotDetails?.time_display }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Betaalmethode:</span>
                            <span class="text-gray-900">
                                {{ paymentMethods.find(m => m.id === selectedPaymentMethod)?.name }}
                            </span>
                        </div>
                        <div v-if="orderNotes" class="pt-2 border-t">
                            <span class="text-gray-600">Opmerkingen:</span>
                            <p class="text-gray-900 mt-1">{{ orderNotes }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex space-x-3">
                    <button 
                        @click="cancelConfirmOrder"
                        :disabled="isProcessingOrder"
                        class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors disabled:opacity-50"
                    >
                        Annuleren
                    </button>
                    <button 
                        @click="confirmOrder"
                        :disabled="isProcessingOrder"
                        class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors disabled:opacity-50"
                    >
                        {{ isProcessingOrder ? 'Bestelling plaatsen...' : 'Definitief bestellen' }}
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

button:focus, input:focus, textarea:focus {
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

/* Special styling for the main CTA button */
.bg-green-600:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Radio button custom styling */
input[type="radio"]:checked {
    background-color: #2563eb;
    border-color: #2563eb;
}

/* Checkbox custom styling */
input[type="checkbox"]:checked {
    background-color: #2563eb;
    border-color: #2563eb;
}
</style>