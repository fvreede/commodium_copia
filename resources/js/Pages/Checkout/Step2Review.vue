<!-- Pages/Checkout/Step2Review.vue -->
<script setup>
import CheckoutLayout from '@/Layouts/Checkout/CheckoutLayout.vue'
import OrderSummary from '@/Components/Checkout/OrderSummary.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import { router } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import { 
    PencilIcon, 
    MapPinIcon, 
    ClockIcon, 
    ShoppingCartIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    ArrowLeftIcon,
    ArrowRightIcon
} from '@heroicons/vue/24/outline'
import { useCartStore } from '@/Stores/cart'

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
})

// Initialize cart store
const cartStore = useCartStore()

// Reactive state
const isNavigating = ref(false)

// Computed properties
const hasValidAddress = computed(() => {
    return props.deliveryAddress && 
           props.deliveryAddress.street && 
           props.deliveryAddress.postal_code && 
           props.deliveryAddress.city
})

const selectedSlotDetails = computed(() => {
    if (!props.selectedSlotId) return null
    
    for (const day of props.deliverySlots) {
        const slot = day.slots ? day.slots.find(s => s.id === props.selectedSlotId) : null
        if (slot) {
            return {
                ...slot,
                day_name: day.day_name,
                formatted_date: day.formatted_date
            }
        }
    }
    return null
})

const orderTotal = computed(() => {
    return cartStore.subtotal + props.deliveryFee
})

const canProceedToConfirm = computed(() => {
    return cartStore.hasItems && 
           hasValidAddress.value && 
           selectedSlotDetails.value &&
           !hasStockIssues.value
})

const hasStockIssues = computed(() => {
    return cartStore.sortedItems.some(item => 
        item.stock_quantity !== undefined && 
        item.quantity > item.stock_quantity
    )
})

const validationIssues = computed(() => {
    const issues = []
    
    if (!cartStore.hasItems) {
        issues.push('Je winkelwagen is leeg')
    }
    
    if (!hasValidAddress.value) {
        issues.push('Geen geldig bezorgadres ingesteld')
    }
    
    if (!selectedSlotDetails.value) {
        issues.push('Geen bezorgmoment geselecteerd')
    }
    
    if (hasStockIssues.value) {
        issues.push('Voorraadproblemen met sommige producten')
    }
    
    return issues
})

// Methods
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

const formatDeliverySlot = () => {
    if (!selectedSlotDetails.value) return 'Geen bezorgmoment geselecteerd'
    
    return `${selectedSlotDetails.value.day_name} ${selectedSlotDetails.value.formatted_date}`
}

// Navigation methods
const goBackToDelivery = () => {
    router.get('/checkout/delivery')
}

const goBackToProducts = () => {
    router.get('/categories')
}

const proceedToConfirm = () => {
    if (!canProceedToConfirm.value || isNavigating.value) return
    
    isNavigating.value = true
    
    router.get('/checkout/confirm', {}, {
        onFinish: () => {
            isNavigating.value = false
        }
    })
}

// Load cart on mount
onMounted(async () => {
    await cartStore.loadCart()
    
    // Redirect if no items in cart
    if (!cartStore.hasItems) {
        router.get('/cart')
        return
    }
    
    // Redirect to delivery step if essential data is missing
    if (!hasValidAddress.value || !selectedSlotDetails.value) {
        router.get('/checkout/delivery')
        return
    }
})
</script>

<template>
    <CheckoutLayout :current-step="2" title="Bestelling controleren">
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Progress Bar -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex items-center justify-between overflow-x-auto">
                    <div class="flex items-center space-x-3 sm:space-x-4 min-w-max">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <CheckCircleIcon class="w-5 h-5 text-white" />
                            </div>
                            <span class="text-sm font-medium text-gray-900 hidden sm:inline">Bezorgmoment</span>
                        </div>
                        <div class="w-8 sm:w-12 h-px bg-green-300"></div>
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-sm font-bold text-white">3</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 hidden sm:inline">Controleren</span>
                        </div>
                        <div class="w-8 sm:w-12 h-px bg-gray-200"></div>
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                <span class="text-sm font-bold text-gray-600">4</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 hidden sm:inline">Bevestigen</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Validation Issues Alert -->
            <div v-if="validationIssues.length > 0" class="bg-red-50 border-2 border-red-200 rounded-xl p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-start">
                    <div class="flex-shrink-0 mx-auto sm:mx-0 mb-4 sm:mb-0">
                        <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center">
                            <ExclamationTriangleIcon class="h-5 w-5 text-white" />
                        </div>
                    </div>
                    <div class="sm:ml-4 flex-1 text-center sm:text-left">
                        <h3 class="text-lg font-semibold text-red-900 mb-2">
                            Controleer je bestelling
                        </h3>
                        <div class="text-red-800 mb-4">
                            <ul class="space-y-1">
                                <li v-for="issue in validationIssues" :key="issue" class="text-sm">• {{ issue }}</li>
                            </ul>
                        </div>
                        <PrimaryButton @click="goBackToDelivery" class="w-full sm:w-auto">
                            Terug naar vorige stap
                        </PrimaryButton>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <!-- Left column: Order details -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Delivery Information Summary -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-blue-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 flex items-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <MapPinIcon class="w-4 h-4 text-blue-600" />
                                </div>
                                Bezorginformatie
                            </h3>
                        </div>
                        
                        <div class="p-4 sm:p-6 space-y-4">
                            <!-- Delivery Address -->
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-3 sm:space-y-0">
                                <div class="flex items-start flex-1">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                        <MapPinIcon class="w-4 h-4 text-green-600" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-semibold text-gray-900 mb-1">Bezorgadres</h4>
                                        <p :class="[
                                            'text-sm break-words',
                                            hasValidAddress ? 'text-gray-700' : 'text-red-600'
                                        ]">
                                            <span v-for="line in formatAddress()" :key="line" class="block" >{{ line }}</span>
                                        </p>
                                    </div>
                                </div>
                                <SecondaryButton @click="goBackToDelivery" class="w-full sm:w-auto sm:ml-4">
                                    <PencilIcon class="w-4 h-4 mr-1.5" />
                                    Wijzigen
                                </SecondaryButton>
                            </div>

                            <!-- Delivery Time -->
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-3 sm:space-y-0">
                                <div class="flex items-start flex-1">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                        <ClockIcon class="w-4 h-4 text-blue-600" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-semibold text-gray-900 mb-1">Bezorgmoment</h4>
                                        <p :class="[
                                            'text-sm mb-1',
                                            selectedSlotDetails ? 'text-gray-700' : 'text-red-600'
                                        ]">
                                            {{ formatDeliverySlot() }}
                                        </p>
                                        <p v-if="selectedSlotDetails" class="text-sm font-medium text-gray-900">
                                            {{ selectedSlotDetails.time_display }}
                                        </p>
                                        <p v-if="selectedSlotDetails" class="text-xs text-gray-500 mt-1">
                                            💰 Bezorgkosten: €{{ props.deliveryFee.toFixed(2) }}
                                        </p>
                                    </div>
                                </div>
                                <SecondaryButton @click="goBackToDelivery" class="w-full sm:w-auto sm:ml-4">
                                    <PencilIcon class="w-4 h-4 mr-1.5" />
                                    Wijzigen
                                </SecondaryButton>
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
                <div class="lg:col-span-2 space-y-6">
                    <!-- Order Total Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-green-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <ShoppingCartIcon class="w-4 h-4 text-green-600" />
                                </div>
                                Totaal
                            </h3>
                        </div>
                        
                        <div class="p-4 sm:p-6">
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">
                                        Artikelen ({{ cartStore.totalItems }}):
                                    </span>
                                    <span class="font-medium">€{{ cartStore.subtotal.toFixed(2) }}</span>
                                </div>
                                
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Bezorgkosten:</span>
                                    <span class="font-medium">€{{ deliveryFee.toFixed(2) }}</span>
                                </div>
                                
                                <div class="border-t-2 border-gray-200 pt-3 flex justify-between text-lg sm:text-xl font-bold">
                                    <span class="text-gray-900">Totaal:</span>
                                    <span class="text-green-600">€{{ orderTotal.toFixed(2) }}</span>
                                </div>
                            </div>

                            <!-- Delivery info recap -->
                            <div v-if="selectedSlotDetails" class="mt-6 pt-4 border-t border-gray-200">
                                <div class="text-center p-3 bg-blue-50 rounded-lg">
                                    <div class="text-lg font-bold text-blue-600">{{ selectedSlotDetails.time_display }}</div>
                                    <div class="text-blue-700 text-xs">Bezorgtijd</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-gray-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Acties</h3>
                        </div>
                        
                        <div class="p-4 sm:p-6 space-y-4">
                            <!-- Continue to Confirmation -->
                            <PrimaryButton 
                                @click="proceedToConfirm"
                                :disabled="!canProceedToConfirm || isNavigating"
                                class="w-full justify-center text-base font-medium py-3 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="isNavigating" class="inline-flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Laden...
                                </span>
                                <span v-else class="flex items-center justify-center">
                                    Naar bevestiging
                                    <ArrowRightIcon class="ml-2 h-4 w-4" />
                                </span>
                            </PrimaryButton>

                            <!-- Navigation buttons -->
                            <div class="space-y-4 pt-4 border-t border-gray-200">
                                <SecondaryButton 
                                    @click="goBackToDelivery"
                                    class="w-full justify-center"
                                >
                                    <ArrowLeftIcon class="mr-2 h-4 w-4" />
                                    Terug naar bezorgmoment
                                </SecondaryButton>
                                
                                <SecondaryButton 
                                    @click="goBackToProducts"
                                    class="w-full justify-center"
                                >
                                    <ArrowLeftIcon class="mr-2 h-4 w-4" />
                                    Verder winkelen
                                </SecondaryButton>
                            </div>
                        </div>
                    </div>

                    <!-- Help Card -->
                    <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4 sm:p-6">
                        <h4 class="text-sm font-semibold text-blue-900 mb-2 flex items-center">
                            <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center mr-2">
                                <span class="text-xs text-white font-bold">?</span>
                            </div>
                            Hulp nodig?
                        </h4>
                        <p class="text-sm text-blue-700 mb-3">
                            Controleer alle gegevens voordat je verder gaat.
                        </p>
                        <SecondaryButton class="text-sm">
                            Contact opnemen
                        </SecondaryButton>
                    </div>
                </div>
            </div>
        </div>
    </CheckoutLayout>
</template>

<style scoped>
@keyframes spin {
    to { 
        transform: rotate(360deg); 
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Remove focus rings */
button:focus-visible {
    outline: none;
}
</style>