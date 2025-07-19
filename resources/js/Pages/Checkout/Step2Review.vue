<!--Pages/Checkout/Step2Review.vue-->
/**
 * Bestandsnaam: Step2Review.vue (Pages/Checkout)
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-07-01
 * Tijd: 01:25:04
 * Doel: Dit component toont de tweede stap van het checkout proces waar gebruikers hun complete
 *       bestelling kunnen controleren voordat ze naar de bevestiging gaan. Bevat delivery informatie,
 *       order summary, validatie checks en navigatie naar de volgende stap.
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
    PencilIcon, 
    MapPinIcon, 
    ClockIcon, 
    ShoppingCartIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    ArrowLeftIcon,
    ArrowRightIcon
} from '@heroicons/vue/24/outline'

// Store imports
import { useCartStore } from '@/Stores/cart'

// ========== PROPS DEFINITIE ==========

// Props van Laravel server met checkout review data
const props = defineProps({
    deliverySlots: {                           // Array van beschikbare bezorgtijdsloten
        type: Array,
        default: () => []
    },
    deliveryAddress: {                         // Geselecteerd bezorgadres object
        type: Object,
        default: null
    },
    selectedSlotId: {                         // ID van geselecteerd tijdslot
        type: Number,
        default: null
    },
    deliveryFee: {                            // Bezorgkosten voor geselecteerd slot
        type: Number,
        default: 0
    },
    user: {                                   // Gebruiker informatie
        type: Object,
        default: null
    }
})

// ========== STORE INITIALISATIE ==========

// Cart store voor winkelwagen functionaliteiten
const cartStore = useCartStore()

// ========== REACTIVE STATE ==========

// Navigation loading state
const isNavigating = ref(false)               // Loading state voor navigatie naar bevestiging

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
 * Bepaalt of gebruiker door kan naar bevestiging stap
 * @returns {boolean} True als alle vereisten vervuld zijn
 */
const canProceedToConfirm = computed(() => {
    return cartStore.hasItems && 
           hasValidAddress.value && 
           selectedSlotDetails.value &&
           !hasStockIssues.value
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
 * Formatteert geselecteerd bezorgmoment naar leesbare string
 * @returns {string} Geformatteerd bezorgmoment of fallback tekst
 */
const formatDeliverySlot = () => {
    if (!selectedSlotDetails.value) return 'Geen bezorgmoment geselecteerd'
    
    return `${selectedSlotDetails.value.day_name} ${selectedSlotDetails.value.formatted_date}`
}

// ========== NAVIGATIE METHODS ==========

/**
 * Ga terug naar delivery stap voor wijzigingen
 */
const goBackToDelivery = () => {
    router.get('/checkout/delivery')
}

/**
 * Ga terug naar product catalogus voor verder winkelen
 */
const goBackToProducts = () => {
    router.get('/categories')
}

/**
 * Ga door naar bevestiging stap van checkout
 */
const proceedToConfirm = () => {
    if (!canProceedToConfirm.value || isNavigating.value) return
    
    isNavigating.value = true
    
    router.get('/checkout/confirm', {}, {
        onFinish: () => {
            isNavigating.value = false
        }
    })
}

// ========== LIFECYCLE HOOKS ==========

/**
 * Component mounted - laad cart en valideer checkout state
 */
onMounted(async () => {
    await cartStore.loadCart()
    
    // Redirect naar cart als geen items
    if (!cartStore.hasItems) {
        router.get('/cart')
        return
    }
    
    // Redirect naar delivery stap als essentiële data ontbreekt
    if (!hasValidAddress.value || !selectedSlotDetails.value) {
        router.get('/checkout/delivery')
        return
    }
})
</script>

<template>
    <CheckoutLayout :current-step="2" title="Bestelling controleren">
        <div class="max-w-4xl mx-auto space-y-6">
            
            <!-- Progress Bar Sectie -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex items-center justify-between overflow-x-auto">
                    <div class="flex items-center space-x-3 sm:space-x-4 min-w-max">
                        <!-- Bezorgmoment Stap (Voltooid) -->
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <CheckCircleIcon class="w-5 h-5 text-white" />
                            </div>
                            <span class="text-sm font-medium text-gray-900 hidden sm:inline">Bezorgmoment</span>
                        </div>
                        <!-- Progress Lijn -->
                        <div class="w-8 sm:w-12 h-px bg-green-300"></div>
                        <!-- Controleren Stap (Actief) -->
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-sm font-bold text-white">3</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 hidden sm:inline">Controleren</span>
                        </div>
                        <!-- Progress Lijn -->
                        <div class="w-8 sm:w-12 h-px bg-gray-200"></div>
                        <!-- Bevestigen Stap (Toekomstig) -->
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                <span class="text-sm font-bold text-gray-600">4</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 hidden sm:inline">Bevestigen</span>
                        </div>
                    </div>
                </div>
            </div>

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
                        <PrimaryButton @click="goBackToDelivery" class="w-full sm:w-auto">
                            Terug naar vorige stap
                        </PrimaryButton>
                    </div>
                </div>
            </div>

            <!-- Hoofdinhoud Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                
                <!-- Linker Kolom: Order Details -->
                <div class="lg:col-span-3 space-y-6">
                    
                    <!-- Bezorginformatie Samenvatting -->
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
                            
                            <!-- Bezorgadres Review -->
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
                                <!-- Wijzig Adres Knop -->
                                <SecondaryButton @click="goBackToDelivery" class="w-full sm:w-auto sm:ml-4">
                                    <PencilIcon class="w-4 h-4 mr-1.5" />
                                    Wijzigen
                                </SecondaryButton>
                            </div>

                            <!-- Bezorgmoment Review -->
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
                                        <!-- Tijdslot Details -->
                                        <p v-if="selectedSlotDetails" class="text-sm font-medium text-gray-900">
                                            {{ selectedSlotDetails.time_display }}
                                        </p>
                                        <!-- Bezorgkosten Info -->
                                        <p v-if="selectedSlotDetails" class="text-xs text-gray-500 mt-1">
                                            💰 Bezorgkosten: €{{ props.deliveryFee.toFixed(2) }}
                                        </p>
                                    </div>
                                </div>
                                <!-- Wijzig Tijdslot Knop -->
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

                <!-- Rechter Kolom: Acties en Samenvatting -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Order Totaal Card -->
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
                            <!-- Prijs Breakdown -->
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

                            <!-- Bezorgtijd Recap -->
                            <div v-if="selectedSlotDetails" class="mt-6 pt-4 border-t border-gray-200">
                                <div class="text-center p-3 bg-blue-50 rounded-lg">
                                    <div class="text-lg font-bold text-blue-600">{{ selectedSlotDetails.time_display }}</div>
                                    <div class="text-blue-700 text-xs">Bezorgtijd</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acties Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-gray-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Acties</h3>
                        </div>
                        
                        <div class="p-4 sm:p-6 space-y-4">
                            
                            <!-- Verder naar Bevestiging Knop -->
                            <PrimaryButton 
                                @click="proceedToConfirm"
                                :disabled="!canProceedToConfirm || isNavigating"
                                class="w-full justify-center text-base font-medium py-3 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <!-- Loading State -->
                                <span v-if="isNavigating" class="inline-flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Laden...
                                </span>
                                <!-- Normale State -->
                                <span v-else class="flex items-center justify-center">
                                    Naar bevestiging
                                    <ArrowRightIcon class="ml-2 h-4 w-4" />
                                </span>
                            </PrimaryButton>

                            <!-- Navigatie Knoppen Sectie -->
                            <div class="space-y-4 pt-4 border-t border-gray-200">
                                <!-- Terug naar Bezorgmoment -->
                                <SecondaryButton 
                                    @click="goBackToDelivery"
                                    class="w-full justify-center"
                                >
                                    <ArrowLeftIcon class="mr-2 h-4 w-4" />
                                    Terug naar bezorgmoment
                                </SecondaryButton>
                                
                                <!-- Verder Winkelen -->
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

                    <!-- Hulp Card -->
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
                        <!-- Contact Knop -->
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
button:focus-visible {
    outline: none;
}
</style>