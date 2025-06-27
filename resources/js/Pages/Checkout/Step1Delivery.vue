<!-- Pages/Checkout/Step1Delivery.vue -->
<script setup>
import CheckoutLayout from '@/Layouts/Checkout/CheckoutLayout.vue';
import DeliverySlotSelector from '@/Components/Checkout/DeliverySlotSelector.vue';
import AddressFormModal from '@/Components/AddressFormModal.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import { ExclamationTriangleIcon, MapPinIcon, PencilIcon } from '@heroicons/vue/24/outline';
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
const showAddressModal = ref(false);
const isNavigating = ref(false);

// Computed properties
const hasValidAddress = computed(() => {
    return props.deliveryAddress && 
           props.deliveryAddress.street && 
           props.deliveryAddress.postal_code && 
           props.deliveryAddress.city;
});

const canProceedToNextStep = computed(() => {
    return hasValidAddress.value && selectedSlotId.value && cartStore.hasItems;
});

const stepValidationMessage = computed(() => {
    if (!cartStore.hasItems) return 'Je winkelwagen is leeg';
    if (!hasValidAddress.value) return 'Stel eerst een bezorgadres in';
    if (!selectedSlotId.value) return 'Selecteer een bezorgmoment';
    return '';
});

// Methods
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

const openAddressModal = () => {
    showAddressModal.value = true;
};

const closeAddressModal = () => {
    showAddressModal.value = false;
};

const handleAddressSaved = (address) => {
    // Address was saved successfully
    console.log('Address saved:', address);
    closeAddressModal();
};

// Event handlers for DeliverySlotSelector
const handleSlotSelected = (eventData) => {
    selectedSlotId.value = eventData.slotId;
    selectedSlotDetails.value = eventData.slotDetails;
    deliveryFee.value = eventData.deliveryFee;
    
    // Store selected slot in session
    storeSelectedSlot(eventData);
};

const handleDeliveryFeeUpdated = (fee) => {
    deliveryFee.value = fee;
};

const handleRefreshSlots = async () => {
    // Reload the page to get fresh delivery slot data
    router.reload({
        only: ['deliverySlots'],
        preserveState: true
    });
};

const storeSelectedSlot = async (slotData) => {
    try {
        // Store the selected slot in the session/backend
        await axios.post('/checkout/store-selected-slot', {
            delivery_slot_id: slotData.slotId,
            delivery_fee: slotData.deliveryFee
        });
    } catch (error) {
        console.error('Error storing selected slot:', error);
    }
};

// Navigation
const proceedToNextStep = () => {
    if (!canProceedToNextStep.value || isNavigating.value) return;
    
    isNavigating.value = true;
    
    // Navigate to step 2 (review)
    router.get('/checkout/review', {}, {
        onFinish: () => {
            isNavigating.value = false;
        }
    });
};

const goBackToCart = () => {
    router.get('/cart');
};

// Initialize selected slot details on mount
onMounted(() => {
    if (selectedSlotId.value) {
        updateSelectedSlotDetails();
    }
});

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
</script>

<template>
    <CheckoutLayout :current-step="1" title="Bezorgmoment kiezen">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left column: Main content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Address Section -->
                <div class="bg-white border rounded-lg shadow-sm">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium flex items-center">
                                <MapPinIcon class="w-5 h-5 mr-2 text-gray-400" />
                                Bezorgadres
                            </h3>
                            <button 
                                @click="openAddressModal"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                            >
                                <PencilIcon class="w-4 h-4 mr-1" />
                                {{ hasValidAddress ? 'Wijzigen' : 'Instellen' }}
                            </button>
                        </div>

                        <!-- Address Display -->
                        <div :class="[
                            'p-4 border rounded-md transition-colors',
                            hasValidAddress 
                                ? 'bg-green-50 border-green-200' 
                                : 'bg-red-50 border-red-200'
                        ]">
                            <div class="flex items-start">
                                <div :class="[
                                    'flex-shrink-0 w-5 h-5 mt-0.5 mr-3',
                                    hasValidAddress ? 'text-green-400' : 'text-red-400'
                                ]">
                                    <svg v-if="hasValidAddress" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <ExclamationTriangleIcon v-else />
                                </div>
                                <div class="flex-1">
                                    <p :class="[
                                        'text-sm font-medium',
                                        hasValidAddress ? 'text-green-800' : 'text-red-800'
                                    ]">
                                        {{ hasValidAddress ? 'Bezorgadres ingesteld' : 'Geen bezorgadres' }}
                                    </p>
                                    <p :class="[
                                        'text-sm mt-1',
                                        hasValidAddress ? 'text-green-700' : 'text-red-700'
                                    ]">
                                        {{ formatAddress() }}
                                    </p>
                                    <p v-if="!hasValidAddress" class="text-xs text-red-600 mt-2">
                                        Stel een bezorgadres in om verder te kunnen gaan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Slot Selector -->
                <DeliverySlotSelector
                    :delivery-slots="deliverySlots"
                    :selected-slot-id="selectedSlotId"
                    @slot-selected="handleSlotSelected"
                    @delivery-fee-updated="handleDeliveryFeeUpdated"
                    @refresh-slots="handleRefreshSlots"
                />
            </div>

            <!-- Right column: Summary and navigation -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Progress Summary -->
                <div class="bg-white border rounded-lg shadow-sm">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Voortgang</h3>
                        
                        <div class="space-y-3">
                            <!-- Cart Status -->
                            <div class="flex items-center">
                                <div :class="[
                                    'flex-shrink-0 w-5 h-5 mr-3',
                                    cartStore.hasItems ? 'text-green-400' : 'text-gray-400'
                                ]">
                                    <svg fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span :class="[
                                    'text-sm',
                                    cartStore.hasItems ? 'text-gray-900' : 'text-gray-500'
                                ]">
                                    {{ cartStore.totalItems }} {{ cartStore.totalItems === 1 ? 'artikel' : 'artikelen' }} in winkelwagen
                                </span>
                            </div>

                            <!-- Address Status -->
                            <div class="flex items-center">
                                <div :class="[
                                    'flex-shrink-0 w-5 h-5 mr-3',
                                    hasValidAddress ? 'text-green-400' : 'text-gray-400'
                                ]">
                                    <svg fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span :class="[
                                    'text-sm',
                                    hasValidAddress ? 'text-gray-900' : 'text-gray-500'
                                ]">
                                    Bezorgadres {{ hasValidAddress ? 'ingesteld' : 'nog in te stellen' }}
                                </span>
                            </div>

                            <!-- Delivery Slot Status -->
                            <div class="flex items-center">
                                <div :class="[
                                    'flex-shrink-0 w-5 h-5 mr-3',
                                    selectedSlotId ? 'text-green-400' : 'text-gray-400'
                                ]">
                                    <svg fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span :class="[
                                    'text-sm',
                                    selectedSlotId ? 'text-gray-900' : 'text-gray-500'
                                ]">
                                    Bezorgmoment {{ selectedSlotId ? 'gekozen' : 'nog te kiezen' }}
                                </span>
                            </div>
                        </div>

                        <!-- Selected slot details -->
                        <div v-if="selectedSlotDetails" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                            <p class="text-sm font-medium text-blue-900">Gekozen bezorgmoment:</p>
                            <p class="text-sm text-blue-700">
                                {{ selectedSlotDetails.day_name }} {{ selectedSlotDetails.formatted_date }}
                            </p>
                            <p class="text-sm text-blue-700">
                                {{ selectedSlotDetails.time_display }} - € {{ deliveryFee.toFixed(2) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Quick Summary -->
                <div v-if="cartStore.hasItems" class="bg-white border rounded-lg shadow-sm">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Kostenoverzicht</h3>
                        
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotaal:</span>
                                <span class="font-medium">€ {{ cartStore.subtotal.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Bezorgkosten:</span>
                                <span class="font-medium">
                                    {{ deliveryFee > 0 ? `€ ${deliveryFee.toFixed(2)}` : 'Nog te bepalen' }}
                                </span>
                            </div>
                            <div class="border-t pt-2 flex justify-between font-semibold">
                                <span>Totaal:</span>
                                <span>€ {{ (cartStore.subtotal + deliveryFee).toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="space-y-3">
                    <!-- Continue Button -->
                    <button 
                        @click="proceedToNextStep"
                        :disabled="!canProceedToNextStep || isNavigating"
                        :class="[
                            'w-full px-6 py-3 rounded-md font-medium transition-all duration-200',
                            canProceedToNextStep && !isNavigating
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
                            Verder naar overzicht →
                        </span>
                    </button>

                    <!-- Validation Message -->
                    <div v-if="!canProceedToNextStep" class="text-center">
                        <p class="text-sm text-gray-600 px-2 py-1 bg-yellow-50 border border-yellow-200 rounded-md">
                            {{ stepValidationMessage }}
                        </p>
                    </div>

                    <!-- Back to Cart -->
                    <button 
                        @click="goBackToCart"
                        class="w-full px-6 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-50 transition-colors"
                    >
                        ← Terug naar winkelwagen
                    </button>
                </div>
            </div>
        </div>

        <!-- Address Modal -->
        <AddressFormModal
            :show="showAddressModal"
            :address="deliveryAddress"
            @close="closeAddressModal"
            @saved="handleAddressSaved"
        />
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

/* Custom focus styles for accessibility */
.focus\:outline-none:focus {
    outline: 2px solid transparent;
    outline-offset: 2px;
}

.focus\:ring-2:focus {
    --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
    --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
    box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}
</style>