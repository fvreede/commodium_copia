/**
 * Bestandsnaam: Step3Confirm.vue (Pages/Checkout)
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-07-01
 * Tijd: 01:25:04
 * Doel: Dit component toont de finale stap van het checkout proces waar gebruikers hun bestelling
 *       definitief bevestigen en plaatsen. Bevat payment method selectie, order notes, terms acceptance,
 *       comprehensive validation, order processing met error handling en success redirection.
 */

<script setup>
// Layout en component imports
import CheckoutLayout from '@/Layouts/Checkout/CheckoutLayout.vue'
import OrderSummary from '@/Components/Checkout/OrderSummary.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

// Vue en Inertia imports
import { router } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'

// Heroicons imports voor UI iconen
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

// Store en API imports
import { useCartStore } from '@/Stores/cart'
import axios from 'axios'

// ========== PROPS DEFINITIE ==========

// Props van Laravel server met finale checkout data
const props = defineProps({
    deliverySlots: {                           // Array van beschikbare bezorgtijdsloten
        type: Array,
        default: () => []
    },
    deliveryAddress: {                         // Definitief bezorgadres object
        type: Object,
        default: null
    },
    selectedSlotId: {                         // ID van definitief geselecteerd tijdslot
        type: Number,
        default: null
    },
    deliveryFee: {                            // Finale bezorgkosten
        type: Number,
        default: 0
    },
    user: {                                   // Gebruiker informatie
        type: Object,
        default: null
    }
})

// ========== STORE INITIALISATIE ==========

// Cart store voor winkelwagen functionaliteiten en order processing
const cartStore = useCartStore()

// ========== REACTIVE STATE ==========

// Order form state
const orderNotes = ref('')                    // Optionele opmerkingen van klant
const isProcessingOrder = ref(false)          // Loading state voor order placement
const acceptedTerms = ref(false)              // Terms & conditions acceptatie
const selectedPaymentMethod = ref('ideal')    // Geselecteerde betaalmethode

// UI state
const showConfirmModal = ref(false)           // Finale bevestiging modal
const orderError = ref('')                    // Error bericht voor order placement

// ========== COMPUTED PROPERTIES ==========

/**
 * Controleert of gebruiker een geldig bezorgadres heeft ingesteld
 * @returns {boolean} True als alle vereiste adres velden aanwezig zijn
 */
const hasValidAddress = computed(() => {
    return props.deliveryAddress && 
           props.deliveryAddress.street && 
           props.deliveryAddress.postal_code && 
           props.deliveryAddress.city
})

/**
 * Vindt details van geselecteerd tijdslot door alle beschikbare slots te doorzoeken
 * @returns {Object|null} Slot details object of null als niet gevonden
 */
const selectedSlotDetails = computed(() => {
    if (!props.selectedSlotId) return null
    
    // Zoek door alle dagen en slots om details te vinden
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

/**
 * Berekent totaal order bedrag inclusief bezorgkosten
 * @returns {number} Totaal order bedrag
 */
const orderTotal = computed(() => {
    return cartStore.subtotal + props.deliveryFee
})

/**
 * Bepaalt of gebruiker order kan plaatsen gebaseerd op alle validatie criteria
 * @returns {boolean} True als alle vereisten vervuld zijn
 */
const canPlaceOrder = computed(() => {
    return cartStore.hasItems && 
           hasValidAddress.value && 
           selectedSlotDetails.value &&
           acceptedTerms.value &&
           selectedPaymentMethod.value &&
           !hasStockIssues.value &&
           !isProcessingOrder.value
})

/**
 * Controleert op voorraadproblemen in winkelwagen items
 * @returns {boolean} True als er voorraadproblemen zijn
 */
const hasStockIssues = computed(() => {
    return cartStore.sortedItems.some(item => 
        item.stock_quantity !== undefined && 
        item.quantity > item.stock_quantity
    )
})

/**
 * Genereert lijst van validatie problemen voor bestelling
 * @returns {Array} Array van probleem beschrijvingen
 */
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

// ========== STATIC DATA ==========

// Beschikbare betaalmethoden configuratie
const paymentMethods = [
    { id: 'ideal', name: 'iDEAL', icon: '🏦', description: 'Betaal direct via je bank' },
    { id: 'card', name: 'Creditcard', icon: '💳', description: 'Visa, Mastercard, American Express' },
    { id: 'cash', name: 'Contant', icon: '💵', description: 'Betaal bij levering' }
]

// ========== UTILITY METHODS ==========

/**
 * Formatteert bezorgadres naar array van display lijnen
 * @returns {Array} Array van geformatteerde adres lijnen
 */
const formatAddress = () => {
    const addr = props.deliveryAddress
    let lines = []

    // Combineer straat, huisnummer en toevoeging
    let streetLine = addr.street
    if (addr.house_number) streetLine += ` ${addr.house_number}`
    if (addr.addition) streetLine += `, ${addr.addition}`

    lines.push(streetLine)
    lines.push(`${addr.postal_code} ${addr.city}`)

    return lines
}

/**
 * Formatteert geselecteerd bezorgmoment naar volledig leesbare string
 * @returns {string} Geformatteerd bezorgmoment met dag, datum en tijd
 */
const formatDeliverySlot = () => {
    if (!selectedSlotDetails.value) return 'Geen bezorgmoment geselecteerd'
    
    return `${selectedSlotDetails.value.day_name} ${selectedSlotDetails.value.formatted_date} om ${selectedSlotDetails.value.time_display}`
}

// ========== ORDER PROCESSING METHODS ==========

/**
 * Toon finale bevestiging modal voordat order geplaatst wordt
 */
const showConfirmOrderModal = () => {
    if (!canPlaceOrder.value) return
    showConfirmModal.value = true
}

/**
 * Verwerk finale order placement na bevestiging
 * Handles alle server communicatie en error scenarios
 */
const confirmOrder = async () => {
    if (!canPlaceOrder.value) return
    
    isProcessingOrder.value = true
    showConfirmModal.value = false
    orderError.value = ''
    
    try {
        // Markeer cart als processing in store
        await cartStore.markOrderProcessing()
        
        // Verstuur order naar server
        const response = await axios.post('/checkout', {
            delivery_slot_id: props.selectedSlotId,
            order_notes: orderNotes.value,
            payment_method: selectedPaymentMethod.value
        })
        
        if (response.data.success) {
            console.log('Order placed successfully:', response.data)
            
            // Markeer order als succesvol in cart store
            await cartStore.markOrderSuccess(response.data.order_id)
            
            // Redirect naar success pagina
            if (response.data.redirect) {
                window.location.href = response.data.redirect
            } else {
                // Fallback redirect naar success pagina
                router.get(`/checkout/success/${response.data.order_id}`)
            }
        } else {
            throw new Error(response.data.message || 'Failed to place order')
        }
        
    } catch (error) {
        console.error('Error processing order:', error)
        
        // Markeer order als gefaald in cart store
        await cartStore.markOrderFailed()
        
        // Behandel verschillende error types met specifieke berichten
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

/**
 * Annuleer order bevestiging en sluit modal
 */
const cancelConfirmOrder = () => {
    showConfirmModal.value = false
}

// ========== NAVIGATIE METHODS ==========

/**
 * Ga terug naar review stap voor laatste wijzigingen
 */
const goBackToReview = () => {
    router.get('/checkout/review')
}

/**
 * Ga terug naar product catalogus voor verder winkelen
 */
const goBackToProducts = () => {
    router.get('/categories')
}

// ========== LIFECYCLE HOOKS ==========

/**
 * Component mounted - laad cart en valideer finale checkout state
 */
onMounted(async () => {
    await cartStore.loadCart()
    
    // Redirect naar cart als geen items
    if (!cartStore.hasItems) {
        router.get('/cart')
        return
    }
    
    // Redirect naar eerdere stappen als essentiële data ontbreekt
    if (!hasValidAddress.value || !selectedSlotDetails.value) {
        router.get('/checkout/delivery')
        return
    }
})
</script>

<template>
    <CheckoutLayout :current-step="3" title="Bestelling bevestigen">
        <div class="max-w-4xl mx-auto space-y-6">
        
            <!-- Validatie Problemen Alert -->
            <div v-if="validationIssues.length > 0" class="bg-red-50 border-2 border-red-200 rounded-xl p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-start">
                    <!-- Alert Icoon -->
                    <div class="flex-shrink-0 mx-auto sm:mx-0 mb-4 sm:mb-0">
                        <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center">
                            <ExclamationTriangleIcon class="h-5 w-5 text-white" />
                        </div>
                    </div>
                    <!-- Alert Content -->
                    <div class="sm:ml-4 flex-1 text-center sm:text-left">
                        <h3 class="text-lg font-semibold text-red-900 mb-2">
                            Controleer je bestelling
                        </h3>
                        <!-- Lijst van Problemen -->
                        <div class="text-red-800 mb-4">
                            <ul class="space-y-1">
                                <li v-for="issue in validationIssues" :key="issue" class="text-sm">• {{ issue }}</li>
                            </ul>
                        </div>
                        <!-- Terug Knop -->
                        <PrimaryButton @click="goBackToReview" class="w-full sm:w-auto">
                            Terug naar overzicht
                        </PrimaryButton>
                    </div>
                </div>
            </div>

            <!-- Order Error Alert -->
            <div v-if="orderError" class="bg-red-50 border-2 border-red-200 rounded-xl p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-start">
                    <!-- Error Icoon -->
                    <div class="flex-shrink-0 mx-auto sm:mx-0 mb-4 sm:mb-0">
                        <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center">
                            <ExclamationTriangleIcon class="h-5 w-5 text-white" />
                        </div>
                    </div>
                    <!-- Error Content -->
                    <div class="sm:ml-4 flex-1 text-center sm:text-left">
                        <h3 class="text-lg font-semibold text-red-900 mb-2">Fout bij bestelling</h3>
                        <p class="text-red-800 mb-4">{{ orderError }}</p>
                        <!-- Sluiten Knop -->
                        <PrimaryButton @click="orderError = ''" class="w-full sm:w-auto">
                            Sluiten
                        </PrimaryButton>
                    </div>
                </div>
            </div>

            <!-- Hoofdinhoud Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                
                <!-- Linker Kolom: Finale Review en Opties -->
                <div class="lg:col-span-3 space-y-6">
                    
                    <!-- Finale Order Samenvatting -->
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
                            
                            <!-- Key Informatie Samenvatting -->
                            <div class="space-y-4 mb-8">
                                
                                <!-- Bezorgadres Review -->
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

                                <!-- Bezorgmoment Review -->
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

                            <!-- Betaalmethode Selectie -->
                            <div class="mb-8">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                        <CreditCardIcon class="w-4 h-4 text-purple-600" />
                                    </div>
                                    Betaalmethode
                                </h4>
                                <!-- Payment Method Options -->
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
                                            <!-- Radio Input -->
                                            <input
                                                v-model="selectedPaymentMethod"
                                                :value="method.id"
                                                type="radio"
                                                class="h-4 w-4 text-blue-600 border-gray-300"
                                            >
                                            <!-- Payment Method Info -->
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

                            <!-- Order Opmerkingen -->
                            <div class="mb-8">
                                <label for="order-notes" class="flex items-center text-lg font-semibold text-gray-900 mb-4">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                                        <ChatBubbleLeftEllipsisIcon class="w-4 h-4 text-yellow-600" />
                                    </div>
                                    Opmerkingen (optioneel)
                                </label>
                                <!-- Textarea voor Opmerkingen -->
                                <textarea
                                    id="order-notes"
                                    v-model="orderNotes"
                                    rows="4"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl"
                                    placeholder="Bijv. bel aan bij de achterdeur, laat pakket bij de buren, etc."
                                    maxlength="500"
                                ></textarea>
                                <!-- Character Counter -->
                                <p class="mt-2 text-xs text-gray-500 text-right">
                                    {{ orderNotes?.length || 0 }}/500 karakters
                                </p>
                            </div>

                            <!-- Terms and Conditions Acceptatie -->
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                                <label class="flex items-start">
                                    <!-- Checkbox -->
                                    <input
                                        v-model="acceptedTerms"
                                        type="checkbox"
                                        class="h-4 w-4 text-blue-600 border-gray-300 rounded mt-0.5"
                                    >
                                    <!-- Terms Tekst -->
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

                <!-- Rechter Kolom: Finale Acties -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Finale Totaal Card -->
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
                            <!-- Prijs Breakdown -->
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

                            <!-- Beveiliging Badge -->
                            <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                                <div class="flex items-center text-sm text-green-800">
                                    <ShieldCheckIcon class="w-5 h-5 mr-2" />
                                    <span class="font-medium">Veilig betalen via SSL-versleuteling</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bestelling Plaatsen Sectie -->
                    <div class="space-y-4">
                        <!-- Hoofdknop voor Order Plaatsing -->
                        <PrimaryButton 
                            @click="showConfirmOrderModal"
                            :disabled="!canPlaceOrder"
                            class="w-full justify-center py-4 text-lg font-bold disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <!-- Loading State -->
                            <span v-if="isProcessingOrder" class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Bestelling plaatsen...
                            </span>
                            <!-- Normale State -->
                            <span v-else class="flex items-center justify-center">
                                <LockClosedIcon class="w-5 h-5 mr-2" />
                                Bestelling plaatsen
                            </span>
                        </PrimaryButton>
                        
                        <!-- Validatie Foutberichten -->
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

                    <!-- Navigatie Knoppen -->
                    <div class="space-y-3">
                        <!-- Terug naar Review -->
                        <SecondaryButton 
                            @click="goBackToReview"
                            :disabled="isProcessingOrder"
                            class="w-full justify-center"
                        >
                            <ArrowLeftIcon class="mr-2 h-4 w-4" />
                            Terug naar overzicht
                        </SecondaryButton>
                        
                        <!-- Verder Winkelen -->
                        <SecondaryButton 
                            @click="goBackToProducts"
                            :disabled="isProcessingOrder"
                            class="w-full justify-center"
                        >
                            <ArrowLeftIcon class="mr-2 h-4 w-4" />
                            Verder winkelen
                        </SecondaryButton>
                    </div>

                    <!-- Beveiliging & Privacy Info -->
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 sm:p-6">
                        <h4 class="text-sm font-semibold text-blue-900 mb-3 flex items-center">
                            <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center mr-2">
                                <ShieldCheckIcon class="w-3 h-3 text-white" />
                            </div>
                            Beveiliging & Privacy
                        </h4>
                        <!-- Trust Badges Lijst -->
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

        <!-- Finale Bevestiging Modal -->
        <Teleport to="body">
            <div v-if="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
                <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 transform transition-all duration-300">
                    <div class="p-6">
                        
                        <!-- Modal Header -->
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

                        <!-- Order Samenvatting in Modal -->
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
                                <!-- Order Notes -->
                                <div v-if="orderNotes" class="pt-3 border-t border-gray-200">
                                    <span class="text-gray-600 font-medium">Opmerkingen:</span>
                                    <p class="text-gray-900 mt-1 text-sm">{{ orderNotes }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Actie Knoppen -->
                        <div class="flex space-x-3">
                            <!-- Annuleren Knop -->
                            <SecondaryButton 
                                @click="cancelConfirmOrder"
                                :disabled="isProcessingOrder"
                                class="flex-1 justify-center"
                            >
                                Annuleren
                            </SecondaryButton>
                            <!-- Definitief Bestellen Knop -->
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
/* Loading spinner animatie */
@keyframes spin {
    to { 
        transform: rotate(360deg); 
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Focus ring styling */
button:focus-visible, 
input:focus, 
textarea:focus {
    outline: none;
}

/* Radio button en checkbox styling */
input[type="radio"]:checked,
input[type="checkbox"]:checked {
    background-color: #2563eb;
    border-color: #2563eb;
}
</style>