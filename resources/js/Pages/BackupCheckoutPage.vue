<!-- Pages/CheckoutPage -->

<script setup>
import NavBar from '@/Components/NavBar.vue';
import Footer from '@/Components/Footer.vue';
import DeliverySlotSelector from '@/Components/Checkout/DeliverySlotSelector.vue';
import OrderSummary from '@/Components/Checkout/OrderSummary.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import { useCartStore } from '@/Stores/cart';

// Props passed from Laravel - only essential data, not cart items
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
    user: {
        type: Object,
        default: null
    }
});

// Initialize cart store
const cartStore = useCartStore();

// Reactive state
const selectedSlotId = ref(props.selectedSlotId || null);
const selectedSlotDetails = ref(null);
const deliveryFee = ref(0);
const isProcessingOrder = ref(false);
const showAddressModal = ref(false);

// Check if user has a valid delivery address
const hasValidAddress = computed(() => {
    return props.deliveryAddress && 
           props.deliveryAddress.street && 
           props.deliveryAddress.postal_code && 
           props.deliveryAddress.city;
});

// Calculate total order amount using cart store
const orderTotal = computed(() => {
    const cartTotal = parseFloat(cartStore.subtotal) || 0;
    const delFee = parseFloat(deliveryFee.value) || 0;
    return cartTotal + delFee;
});

// Check if user can proceed to next step
const canProceed = computed(() => {
    return cartStore.hasItems && selectedSlotId.value && hasValidAddress.value;
});

// Load cart data on mount and watch for changes
onMounted(async () => {
    await cartStore.loadCart();
    
    // If cart is empty, redirect to cart page
    if (!cartStore.hasItems) {
        router.get('/cart');
        return;
    }
    
    // Set initial slot details if slot is pre-selected
    if (selectedSlotId.value) {
        updateSelectedSlotDetails();
    }
});

// Watch for cart changes and redirect if empty
watch(() => cartStore.hasItems, (hasItems) => {
    if (!hasItems && !cartStore.isLoading) {
        router.get('/cart');
    }
});

// Event handlers for components
const handleSlotSelected = (eventData) => {
    selectedSlotId.value = eventData.slotId;
    selectedSlotDetails.value = eventData.slotDetails;
    deliveryFee.value = eventData.deliveryFee;
    
    console.log('Slot selected:', eventData);
};

const handleDeliveryFeeUpdated = (fee) => {
    deliveryFee.value = fee;
};

const handleRefreshSlots = async () => {
    // Reload the page to get fresh delivery slot data
    // You could also make a specific API call here
    router.reload({
        only: ['deliverySlots'],
        preserveState: true
    });
};

const updateSelectedSlotDetails = () => {
    if (!selectedSlotId.value) {
        selectedSlotDetails.value = null;
        deliveryFee.value = 0;
        return;
    }
    
    for (const day of props.deliverySlots) {
        const slot = day.slots ? day.slots.find(s => s.id === selectedSlotId.value) : null;
        if (slot) {
            selectedSlotDetails.value = {
                ...slot,
                day_name: day.day_name,
                formatted_date: day.formatted_date
            };
            deliveryFee.value = parseFloat(slot.price) || 0;
            break;
        }
    }
};

// Proceed to confirmation
const proceedToConfirmation = () => {
    if (!canProceed.value) return;
    
    router.get('/checkout/confirm');
};

// Process order directly (skip confirmation step)
const processOrderDirectly = async () => {
    if (!canProceed.value || isProcessingOrder.value) return;
    
    isProcessingOrder.value = true;
    
    try {
        const response = await axios.post('/checkout', {
            delivery_slot_id: selectedSlotId.value
        });
        
        if (response.data.success) {
            // Clear cart store since order was successful
            await cartStore.loadCart(); // This will show empty cart
            
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
        isProcessingOrder.value = false;
    }
};

// Address management
const openAddressModal = () => {
    showAddressModal.value = true;
};

const formatAddress = () => {
    if (!hasValidAddress.value) {
        return 'Geen adres ingesteld';
    }
    
    const addr = props.deliveryAddress;
    let formatted = addr.street;
    
    if (addr.house_number) {
        formatted += ` ${addr.house_number}`;
    }
    
    formatted += `, ${addr.postal_code} ${addr.city}`;
    
    return formatted;
};

// Session management
const showSessionModal = ref(false);
const sessionWarningShown = ref(false);
let sessionCheckInterval = null;

const checkSession = async () => {
    try {
        const response = await axios.get('/api/session-check');
        const { authenticated, time_remaining } = response.data;

        if (!authenticated) {
            showSessionExpiredModal();
            return;
        }

        // Show warning at 5 minutes remaining
        if (time_remaining <= 300 && !sessionWarningShown.value) {
            showSessionWarning();
        }
    } catch (error) {
        console.error('Error checking session:', error);
    }
};

const showSessionExpiredModal = () => {
    showSessionModal.value = true;
    if (sessionCheckInterval) {
        clearInterval(sessionCheckInterval);
    }
};

const showSessionWarning = () => {
    sessionWarningShown.value = true;
    console.log('Session expires in 5 minutes');
    // You can implement a toast notification here
};

const handleSessionExpiredAction = (action) => {
    if (action === 'login') {
        window.location.href = '/login?return_to=checkout';
    } else if (action === 'continue') {
        showSessionModal.value = false;
        router.get('/categories');
    }
};

// Lifecycle hooks
onMounted(() => {
    sessionCheckInterval = setInterval(checkSession, 120000);
    checkSession();
});

onUnmounted(() => {
    if (sessionCheckInterval) {
        clearInterval(sessionCheckInterval);
    }
});
</script>

<template>
    <!-- Navigatiebalk -->
    <NavBar />

    <!-- Hoofdcontainer voor checkout sectie -->
    <div class="bg-gray-100 min-h-screen">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-6xl py-16 sm:py-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-8">Bezorgmoment kiezen</h2>

                <!-- Breadcrumb Steps -->
                <div class="mb-8">
                    <div class="bg-white border rounded-lg shadow-sm">
                        <div class="px-4 py-2 text-sm font-medium bg-gray-50 rounded-t-lg">Bestelling plaatsen</div>
                        <div class="border-t">
                            <div class="grid grid-cols-3">
                                <div class="px-4 py-3 border-r bg-blue-50">
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium text-blue-600">Stap 1: Kies uw bezorgmoment</span>
                                        <svg class="w-4 h-4 ml-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="px-4 py-3 border-r">
                                    <div class="flex items-center">
                                        <span class="text-sm text-gray-600">Stap 2: Controleer uw bestelling</span>
                                        <svg class="w-4 h-4 ml-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="px-4 py-3">
                                    <span class="text-sm text-gray-600">Stap 3: Bevestig uw bestelling</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading state -->
                <div v-if="cartStore.isLoading" class="text-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
                    <p class="mt-4 text-gray-600">Bestelling laden...</p>
                </div>

                <!-- Main content -->
                <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left column: Delivery slot selection + Address -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Alert if no delivery address -->
                        <div v-if="!hasValidAddress" class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Geen bezorgadres ingesteld</h3>
                                    <p class="mt-1 text-sm text-red-700">Stel eerst een bezorgadres in om verder te kunnen gaan met bestellen.</p>
                                    <div class="mt-3">
                                        <button 
                                            @click="openAddressModal"
                                            class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 transition-colors"
                                        >
                                            Adres instellen
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Slot Selector Component -->
                        <DeliverySlotSelector
                            :delivery-slots="deliverySlots"
                            :selected-slot-id="selectedSlotId"
                            @slot-selected="handleSlotSelected"
                            @delivery-fee-updated="handleDeliveryFeeUpdated"
                            @refresh-slots="handleRefreshSlots"
                        />

                        <!-- Address Section -->
                        <div class="bg-white border rounded-lg shadow-sm">
                            <div class="p-6">
                                <h3 class="text-lg font-medium mb-4">Bezorgadres</h3>
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div :class="[
                                            'p-4 border rounded-md',
                                            hasValidAddress ? 'bg-gray-50 border-gray-200' : 'bg-red-50 border-red-200'
                                        ]">
                                            <div :class="[
                                                'text-sm',
                                                hasValidAddress ? 'text-gray-700' : 'text-red-700'
                                            ]">
                                                {{ formatAddress() }}
                                            </div>
                                        </div>
                                    </div>
                                    <button 
                                        @click="openAddressModal"
                                        class="ml-4 px-6 py-2 bg-white border border-gray-300 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors"
                                    >
                                        {{ hasValidAddress ? 'Wijzigen' : 'Instellen' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right column: Order Summary -->
                    <div class="lg:col-span-1">
                        <OrderSummary
                            :delivery-fee="deliveryFee"
                            :selected-slot-details="selectedSlotDetails"
                            :delivery-address="deliveryAddress"
                            :is-processing="isProcessingOrder"
                            :show-actions="true"
                            @proceed="proceedToConfirmation"
                            @back-to-cart="router.get('/cart')"
                        />

                        <!-- Quick action buttons -->
                        <div v-if="canProceed" class="mt-6 space-y-3">
                            <button 
                                @click="processOrderDirectly"
                                :disabled="isProcessingOrder"
                                :class="[
                                    'w-full px-6 py-3 rounded-md font-medium transition-colors',
                                    isProcessingOrder
                                        ? 'bg-gray-400 text-gray-700 cursor-not-allowed'
                                        : 'bg-green-600 text-white hover:bg-green-700'
                                ]"
                            >
                                {{ isProcessingOrder ? 'Bestelling plaatsen...' : '🚀 Direct bestellen' }}
                            </button>
                            
                            <p class="text-xs text-center text-gray-500">
                                Of ga verder naar de bevestigingspagina voor meer opties
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Help Text -->
                <div v-if="!cartStore.isLoading && cartStore.hasItems && !canProceed" class="mt-8 text-center">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-800">
                            <span v-if="!hasValidAddress">📍 Stel eerst een bezorgadres in</span>
                            <span v-else-if="!selectedSlotId">⏰ Selecteer een bezorgmoment</span>
                            <span v-else>✅ Controleer je bestelling</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <Footer />

    <!-- Session Expired Modal -->
    <div v-if="showSessionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        
        <div class="bg-white rounded-lg p-6 text-left overflow-hidden shadow-xl transform transition-all max-w-lg w-full mx-4">
            <div class="sm:flex sm:items-start">
                <div class="flex justify-center mb-6">
                    <div class="rounded-full bg-amber-100 p-3">
                        <ExclamationTriangleIcon class="h-8 w-8 text-amber-600" />
                    </div>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Je sessie is verlopen
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Je bent automatisch uitgelogd voor je veiligheid. Je winkelwagen is bewaard. Wil je opnieuw inloggen of verdergaan als gast?
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse space-y-2 sm:space-y-0 sm:space-x-reverse sm:space-x-3">
                <button 
                    @click="handleSessionExpiredAction('login')"
                    type="button" 
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
                >
                    Opnieuw inloggen
                </button>
                <button 
                    @click="handleSessionExpiredAction('continue')"
                    type="button" 
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:w-auto sm:text-sm"
                >
                    Verder als gast
                </button>
            </div>
        </div>
    </div>

    <!-- Address Modal (placeholder - you'd implement the actual address form) -->
    <div v-if="showAddressModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-medium mb-4">Bezorgadres instellen</h3>
            <p class="text-sm text-gray-600 mb-4">
                Deze functionaliteit wordt binnenkort toegevoegd. Voor nu kunt u contact opnemen met de klantenservice.
            </p>
            <button 
                @click="showAddressModal = false"
                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
            >
                Sluiten
            </button>
        </div>
    </div>
</template>

<style scoped>
/* Responsive grid adjustments */
@media (max-width: 1024px) {
    .grid.grid-cols-1.lg\\:grid-cols-3 {
        grid-template-columns: 1fr;
    }
    
    .lg\\:col-span-2,
    .lg\\:col-span-1 {
        grid-column: span 1;
    }
}

/* Custom focus styles for better accessibility */
button:focus {
    outline: 2px solid #3B82F6;
    outline-offset: 2px;
}

/* Smooth transitions */
.transition-colors {
    transition: all 0.2s ease-in-out;
}

/* Loading animation */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Enhanced card shadows on hover */
.bg-white.border.rounded-lg.shadow-sm:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: box-shadow 0.2s ease-in-out;
}

/* Status indicator animations */
@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}

.bg-blue-50, .bg-red-50, .bg-green-50 {
    animation: fadeIn 0.3s ease-in-out;
}
</style>