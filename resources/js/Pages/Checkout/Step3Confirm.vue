<!-- Pages/Checkout/Step3Confirm.vue -->
<script setup>
import CheckoutLayout from '@/Layouts/Checkout/CheckoutLayout.vue'
import OrderSummary from '@/Components/Checkout/OrderSummary.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import { router } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import { 
    CheckCircleIcon,
    ExclamationTriangleIcon,
    MapPinIcon,
    ClockIcon,
    ChatBubbleLeftEllipsisIcon,
    CreditCardIcon,
    ShieldCheckIcon,
    ArrowLeftIcon,
    LockClosedIcon
} from '@heroicons/vue/24/outline'
import { useCartStore } from '@/Stores/cart'
import axios from 'axios'

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
const orderNotes = ref('')
const isProcessingOrder = ref(false)
const acceptedTerms = ref(false)
const selectedPaymentMethod = ref('ideal')
const showConfirmModal = ref(false)
const orderError = ref('')

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

const canPlaceOrder = computed(() => {
    return cartStore.hasItems && 
           hasValidAddress.value && 
           selectedSlotDetails.value &&
           acceptedTerms.value &&
           selectedPaymentMethod.value &&
           !hasStockIssues.value &&
           !isProcessingOrder.value
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

const paymentMethods = [
    { id: 'ideal', name: 'iDEAL', icon: '🏦', description: 'Betaal direct via je bank' },
    { id: 'card', name: 'Creditcard', icon: '💳', description: 'Visa, Mastercard, American Express' },
    { id: 'cash', name: 'Contant', icon: '💵', description: 'Betaal bij levering' }
]

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
    
    return `${selectedSlotDetails.value.day_name} ${selectedSlotDetails.value.formatted_date} om ${selectedSlotDetails.value.time_display}`
}

// Order processing
const showConfirmOrderModal = () => {
    if (!canPlaceOrder.value) return
    showConfirmModal.value = true
}

const confirmOrder = async () => {
    if (!canPlaceOrder.value) return
    
    isProcessingOrder.value = true
    showConfirmModal.value = false
    orderError.value = ''
    
    try {
        // Mark cart as processing order
        await cartStore.markOrderProcessing()
        
        const response = await axios.post('/checkout', {
            delivery_slot_id: props.selectedSlotId,
            order_notes: orderNotes.value,
            payment_method: selectedPaymentMethod.value
        })
        
        if (response.data.success) {
            console.log('Order placed successfully:', response.data)
            
            // Mark order as successful in cart store
            await cartStore.markOrderSuccess(response.data.order_id)
            
            // Redirect to order success page
            if (response.data.redirect) {
                window.location.href = response.data.redirect
            } else {
                // Fallback redirect
                router.get(`/checkout/success/${response.data.order_id}`)
            }
        } else {
            throw new Error(response.data.message || 'Failed to place order')
        }
        
    } catch (error) {
        console.error('Error processing order:', error)
        
        // Mark order as failed in cart store
        await cartStore.markOrderFailed()
        
        if (error.response?.status === 422) {
            orderError.value = error.response.data.message || 'Controleer je bestelling en probeer opnieuw.'
        } else if (error.response?.status === 500) {
            orderError.value = 'Er is een serverfout opgetreden. Probeer het later opnieuw.'
        } else {
            orderError.value = error.message || 'Er is een fout opgetreden bij het plaatsen van de bestelling.'
        }
    } finally {
        isProcessingOrder.value = false
    }
}

const cancelConfirmOrder = () => {
    showConfirmModal.value = false
}

// Navigation
const goBackToReview = () => {
    router.get('/checkout/review')
}

const goBackToProducts = () => {
    router.get('/categories')
}

// Load cart and validate on mount
onMounted(async () => {
    await cartStore.loadCart()
    
    // Redirect if no items in cart
    if (!cartStore.hasItems) {
        router.get('/cart')
        return
    }
    
    // Redirect to previous steps if essential data is missing
    if (!hasValidAddress.value || !selectedSlotDetails.value) {
        router.get('/checkout/delivery')
        return
    }
})
</script>

<template>
    <CheckoutLayout :current-step="3" title="Bestelling bevestigen">
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
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <CheckCircleIcon class="w-5 h-5 text-white" />
                            </div>
                            <span class="text-sm font-medium text-gray-900 hidden sm:inline">Controleren</span>
                        </div>
                        <div class="w-8 sm:w-12 h-px bg-green-300"></div>
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-sm font-bold text-white">4</span>
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
                        <PrimaryButton @click="goBackToReview" class="w-full sm:w-auto">
                            Terug naar overzicht
                        </PrimaryButton>
                    </div>
                </div>
            </div>

            <!-- Order Error Alert -->
            <div v-if="orderError" class="bg-red-50 border-2 border-red-200 rounded-xl p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-start">
                    <div class="flex-shrink-0 mx-auto sm:mx-0 mb-4 sm:mb-0">
                        <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center">
                            <ExclamationTriangleIcon class="h-5 w-5 text-white" />
                        </div>
                    </div>
                    <div class="sm:ml-4 flex-1 text-center sm:text-left">
                        <h3 class="text-lg font-semibold text-red-900 mb-2">Fout bij bestelling</h3>
                        <p class="text-red-800 mb-4">{{ orderError }}</p>
                        <PrimaryButton @click="orderError = ''" class="w-full sm:w-auto">
                            Sluiten
                        </PrimaryButton>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <!-- Left column: Final review and options -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Final Order Summary -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-green-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 flex items-center">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <CheckCircleIcon class="w-4 h-4 text-green-600" />
                                </div>
                                Controleer je gegevens
                            </h3>
                        </div>
                        
                        <div class="p-4 sm:p-6">
                            <!-- Key Information Summary -->
                            <div class="space-y-4 mb-8">
                                <!-- Address -->
                                <div class="flex items-center p-4 bg-green-50 rounded-xl border border-green-200">
                                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                        <MapPinIcon class="w-4 h-4 text-white" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-green-900">Bezorgadres</p>
                                        <p class="text-sm text-green-800 break-words">
                                            <span v-for="line in formatAddress()" :key="line" class="block">
                                                {{ line }}
                                            </span>    
                                        </p>
                                    </div>
                                    <CheckCircleIcon class="w-5 h-5 text-green-600 flex-shrink-0" />
                                </div>

                                <!-- Delivery Time -->
                                <div class="flex items-center p-4 bg-blue-50 rounded-xl border border-blue-200">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                        <ClockIcon class="w-4 h-4 text-white" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-blue-900">Bezorgmoment</p>
                                        <p class="text-sm text-blue-800">{{ formatDeliverySlot() }}</p>
                                        <p class="text-xs text-blue-700">💰 Bezorgkosten: €{{ deliveryFee.toFixed(2) }}</p>
                                    </div>
                                    <CheckCircleIcon class="w-5 h-5 text-blue-600 flex-shrink-0" />
                                </div>
                            </div>

                            <!-- Payment Method Selection -->
                            <div class="mb-8">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                        <CreditCardIcon class="w-4 h-4 text-purple-600" />
                                    </div>
                                    Betaalmethode
                                </h4>
                                <div class="grid gap-3">
                                    <div 
                                        v-for="method in paymentMethods" 
                                        :key="method.id"
                                        class="relative"
                                    >
                                        <label :class="[
                                            'flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all duration-200',
                                            selectedPaymentMethod === method.id
                                                ? 'border-blue-300 bg-blue-50'
                                                : 'border-gray-200 hover:bg-gray-50 hover:border-gray-300'
                                        ]">
                                            <input
                                                v-model="selectedPaymentMethod"
                                                :value="method.id"
                                                type="radio"
                                                class="h-4 w-4 text-blue-600 border-gray-300"
                                            >
                                            <div class="ml-4 flex-1">
                                                <div class="flex items-center">
                                                    <span class="text-xl mr-3">{{ method.icon }}</span>
                                                    <span class="text-sm font-semibold text-gray-900">{{ method.name }}</span>
                                                </div>
                                                <p class="text-xs text-gray-600 mt-1">{{ method.description }}</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Notes -->
                            <div class="mb-8">
                                <label for="order-notes" class="flex items-center text-lg font-semibold text-gray-900 mb-4">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                                        <ChatBubbleLeftEllipsisIcon class="w-4 h-4 text-yellow-600" />
                                    </div>
                                    Opmerkingen (optioneel)
                                </label>
                                <textarea
                                    id="order-notes"
                                    v-model="orderNotes"
                                    rows="4"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl"
                                    placeholder="Bijv. bel aan bij de achterdeur, laat pakket bij de buren, etc."
                                    maxlength="500"
                                ></textarea>
                                <p class="mt-2 text-xs text-gray-500 text-right">
                                    {{ orderNotes?.length || 0 }}/500 karakters
                                </p>
                            </div>

                            <!-- Terms and Conditions -->
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                                <label class="flex items-start">
                                    <input
                                        v-model="acceptedTerms"
                                        type="checkbox"
                                        class="h-4 w-4 text-blue-600 border-gray-300 rounded mt-0.5"
                                    >
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-gray-900">
                                            Ik ga akkoord met de 
                                            <a href="/terms" target="_blank" class="text-blue-600 hover:text-blue-500 underline font-semibold">
                                                algemene voorwaarden
                                            </a>
                                            en het 
                                            <a href="/privacy" target="_blank" class="text-blue-600 hover:text-blue-500 underline font-semibold">
                                                privacybeleid
                                            </a>
                                        </span>
                                        <p class="text-xs text-gray-600 mt-1">
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
                <div class="lg:col-span-2 space-y-6">
                    <!-- Final Total -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-green-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <CreditCardIcon class="w-4 h-4 text-green-600" />
                                </div>
                                Te betalen
                            </h3>
                        </div>
                        
                        <div class="p-4 sm:p-6">
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotaal:</span>
                                    <span class="font-medium">€{{ cartStore.subtotal.toFixed(2) }}</span>
                                </div>
                                
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Bezorgkosten:</span>
                                    <span class="font-medium">€{{ deliveryFee.toFixed(2) }}</span>
                                </div>
                                
                                <div class="border-t-2 border-gray-200 pt-3 flex justify-between text-xl font-bold">
                                    <span class="text-gray-900">Totaal:</span>
                                    <span class="text-green-600">€{{ orderTotal.toFixed(2) }}</span>
                                </div>
                            </div>

                            <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                                <div class="flex items-center text-sm text-green-800">
                                    <ShieldCheckIcon class="w-5 h-5 mr-2" />
                                    <span class="font-medium">Veilig betalen via SSL-versleuteling</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Place Order Button -->
                    <div class="space-y-4">
                        <PrimaryButton 
                            @click="showConfirmOrderModal"
                            :disabled="!canPlaceOrder"
                            class="w-full justify-center py-4 text-lg font-bold disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="isProcessingOrder" class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Bestelling plaatsen...
                            </span>
                            <span v-else class="flex items-center justify-center">
                                <LockClosedIcon class="w-5 h-5 mr-2" />
                                Bestelling plaatsen
                            </span>
                        </PrimaryButton>
                        
                        <div v-if="!canPlaceOrder && !isProcessingOrder" class="text-center">
                            <div class="inline-flex items-center px-4 py-3 bg-yellow-50 border border-yellow-200 rounded-xl">
                                <ExclamationTriangleIcon class="w-4 h-4 text-yellow-600 mr-2 flex-shrink-0" />
                                <p class="text-sm font-medium text-yellow-800">
                                    <span v-if="!acceptedTerms">Accepteer de voorwaarden om verder te gaan</span>
                                    <span v-else-if="!selectedPaymentMethod">Selecteer een betaalmethode</span>
                                    <span v-else-if="validationIssues.length > 0">Controleer je bestelling</span>
                                    <span v-else>Controleer alle velden</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="space-y-3">
                        <SecondaryButton 
                            @click="goBackToReview"
                            :disabled="isProcessingOrder"
                            class="w-full justify-center"
                        >
                            <ArrowLeftIcon class="mr-2 h-4 w-4" />
                            Terug naar overzicht
                        </SecondaryButton>
                        
                        <SecondaryButton 
                            @click="goBackToProducts"
                            :disabled="isProcessingOrder"
                            class="w-full justify-center"
                        >
                            <ArrowLeftIcon class="mr-2 h-4 w-4" />
                            Verder winkelen
                        </SecondaryButton>
                    </div>

                    <!-- Security Info -->
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 sm:p-6">
                        <h4 class="text-sm font-semibold text-blue-900 mb-3 flex items-center">
                            <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center mr-2">
                                <ShieldCheckIcon class="w-3 h-3 text-white" />
                            </div>
                            Beveiliging & Privacy
                        </h4>
                        <ul class="text-xs text-blue-700 space-y-2">
                            <li class="flex items-center">
                                <span class="w-2 h-2 bg-blue-400 rounded-full mr-2 flex-shrink-0"></span>
                                SSL-versleutelde betalingen
                            </li>
                            <li class="flex items-center">
                                <span class="w-2 h-2 bg-blue-400 rounded-full mr-2 flex-shrink-0"></span>
                                Veilige gegevensopslag
                            </li>
                            <li class="flex items-center">
                                <span class="w-2 h-2 bg-blue-400 rounded-full mr-2 flex-shrink-0"></span>
                                30 dagen retourrecht
                            </li>
                            <li class="flex items-center">
                                <span class="w-2 h-2 bg-blue-400 rounded-full mr-2 flex-shrink-0"></span>
                                Klantenservice beschikbaar
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirm Order Modal -->
        <Teleport to="body">
            <div v-if="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
                <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 transform transition-all duration-300">
                    <div class="p-6">
                        <div class="flex items-center mb-6">
                            <div class="flex-shrink-0">
                                <div class="rounded-full bg-green-100 p-3">
                                    <CheckCircleIcon class="w-8 h-8 text-green-600" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-semibold text-gray-900">Bestelling bevestigen</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Je staat op het punt om je bestelling te plaatsen. Controleer nog een keer of alles correct is.
                                </p>
                            </div>
                        </div>

                        <div class="mb-6 p-4 bg-gray-50 rounded-xl">
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Totaalbedrag:</span>
                                    <span class="text-xl font-bold text-gray-900">€{{ orderTotal.toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Bezorgmoment:</span>
                                    <span class="font-medium text-gray-900">{{ selectedSlotDetails?.time_display }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Betaalmethode:</span>
                                    <span class="font-medium text-gray-900">
                                        {{ paymentMethods.find(m => m.id === selectedPaymentMethod)?.name }}
                                    </span>
                                </div>
                                <div v-if="orderNotes" class="pt-3 border-t border-gray-200">
                                    <span class="text-gray-600 font-medium">Opmerkingen:</span>
                                    <p class="text-gray-900 mt-1 text-sm">{{ orderNotes }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex space-x-3">
                            <SecondaryButton 
                                @click="cancelConfirmOrder"
                                :disabled="isProcessingOrder"
                                class="flex-1 justify-center"
                            >
                                Annuleren
                            </SecondaryButton>
                            <PrimaryButton 
                                @click="confirmOrder"
                                :disabled="isProcessingOrder"
                                class="flex-1 justify-center disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ isProcessingOrder ? 'Bestelling plaatsen...' : 'Definitief bestellen' }}
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
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
button:focus-visible, 
input:focus, 
textarea:focus {
    outline: none;
}

/* Radio button and checkbox styling */
input[type="radio"]:checked,
input[type="checkbox"]:checked {
    background-color: #2563eb;
    border-color: #2563eb;
}
</style>