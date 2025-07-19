/**
 * Bestandsnaam: Step1Delivery.vue (Pages/Checkout)
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-07-01
 * Tijd: 01:25:04
 * Doel: Dit component toont de eerste stap van het checkout proces waar gebruikers hun bezorgadres
 *       instellen en een bezorgmoment kiezen. Bevat validatie, progress tracking, order samenvatting
 *       en integratie met cart store en address modal.
 */

<script setup>
// Layout en component imports
import CheckoutLayout from '@/Layouts/Checkout/CheckoutLayout.vue'
import DeliverySlotSelector from '@/Components/Checkout/DeliverySlotSelector.vue'
import AddressFormModal from '@/Components/AddressFormModal.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

// Vue en Inertia imports
import { router } from '@inertiajs/vue3'
import { ref, computed, onMounted, watch } from 'vue'

// Heroicons imports voor UI iconen
import { 
    ExclamationTriangleIcon, 
    MapPinIcon, 
    PencilIcon, 
    CheckCircleIcon,
    ShoppingCartIcon,
    CalendarDaysIcon,
    CreditCardIcon,
    ArrowRightIcon,
    ArrowLeftIcon,
    PlusIcon
} from '@heroicons/vue/24/outline'

// Store imports
import { useCartStore } from '@/Stores/cart'

// ========== PROPS DEFINITIE ==========

// Props van Laravel server met checkout data
const props = defineProps({
    deliverySlots: {                           // Array van beschikbare bezorgtijdsloten
        type: Array,
        default: () => []
    },
    deliveryAddress: {                         // Huidig bezorgadres object
        type: Object,
        default: null
    },
    selectedSlotId: {                         // Voorgeselecteerd tijdslot ID
        type: Number,
        default: null
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

// Delivery slot state
const selectedSlotId = ref(props.selectedSlotId || null)    // Geselecteerd tijdslot ID
const selectedSlotDetails = ref(null)                      // Details van geselecteerd slot
const deliveryFee = ref(0)                                 // Bezorgkosten voor gekozen slot

// UI state
const showAddressModal = ref(false)                        // Of address modal zichtbaar is
const isNavigating = ref(false)                            // Loading state voor navigatie

// ========== COMPUTED PROPERTIES ==========

/**
 * Controleert of gebruiker een geldig bezorgadres heeft ingesteld
 * @returns {boolean} True als alle vereiste adres velden ingevuld zijn
 */
const hasValidAddress = computed(() => {
    return props.deliveryAddress && 
           props.deliveryAddress.street && 
           props.deliveryAddress.postal_code && 
           props.deliveryAddress.city
})

/**
 * Bepaalt of gebruiker door kan naar volgende checkout stap
 * @returns {boolean} True als alle vereisten vervuld zijn
 */
const canProceedToNextStep = computed(() => {
    return hasValidAddress.value && selectedSlotId.value && cartStore.hasItems
})

/**
 * Genereert validatie bericht voor ontbrekende stappen
 * @returns {string} Foutbericht of lege string
 */
const stepValidationMessage = computed(() => {
    if (!cartStore.hasItems) return 'Je winkelwagen is leeg'
    if (!hasValidAddress.value) return 'Stel eerst een bezorgadres in'
    if (!selectedSlotId.value) return 'Selecteer een bezorgmoment'
    return ''
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

// ========== MODAL EVENT HANDLERS ==========

/**
 * Open address modal voor adres bewerking
 */
const openAddressModal = () => {
    showAddressModal.value = true
}

/**
 * Sluit address modal
 */
const closeAddressModal = () => {
    showAddressModal.value = false
}

/**
 * Behandel succesvol opgeslagen adres
 * @param {Object} address - Nieuw opgeslagen adres object
 */
const handleAddressSaved = (address) => {
    console.log('Address saved:', address)
    closeAddressModal()
}

// ========== DELIVERY SLOT EVENT HANDLERS ==========

/**
 * Behandel selectie van nieuw tijdslot
 * @param {Object} eventData - Event data met slot informatie
 */
const handleSlotSelected = (eventData) => {
    selectedSlotId.value = eventData.slotId
    selectedSlotDetails.value = eventData.slotDetails
    deliveryFee.value = eventData.deliveryFee
    
    // Sla geselecteerd slot op in sessie
    storeSelectedSlot(eventData)
}

/**
 * Behandel update van bezorgkosten
 * @param {number} fee - Nieuwe bezorgkosten
 */
const handleDeliveryFeeUpdated = (fee) => {
    deliveryFee.value = fee
}

/**
 * Behandel refresh van beschikbare tijdsloten
 */
const handleRefreshSlots = async () => {
    router.reload({
        only: ['deliverySlots'],
        preserveState: true
    })
}

/**
 * Sla geselecteerd tijdslot op in server sessie
 * @param {Object} slotData - Slot data om op te slaan
 */
const storeSelectedSlot = async (slotData) => {
    try {
        await axios.post('/checkout/store-selected-slot', {
            delivery_slot_id: slotData.slotId,
            delivery_fee: slotData.deliveryFee
        })
    } catch (error) {
        console.error('Error storing selected slot:', error)
    }
}

// ========== NAVIGATIE METHODS ==========

/**
 * Ga door naar volgende checkout stap (review)
 */
const proceedToNextStep = () => {
    if (!canProceedToNextStep.value || isNavigating.value) return
    
    isNavigating.value = true
    
    router.get('/checkout/review', {}, {
        onFinish: () => {
            isNavigating.value = false
        }
    })
}

/**
 * Ga terug naar product catalogus
 */
const goBackToProducts = () => {
    router.get('/categories')
}

// ========== LIFECYCLE HOOKS ==========

/**
 * Component mounted - initialiseer geselecteerd slot details
 */
onMounted(() => {
    if (selectedSlotId.value) {
        updateSelectedSlotDetails()
    }
})

/**
 * Update details van geselecteerd tijdslot
 * Zoekt door alle beschikbare slots om details te vinden
 */
const updateSelectedSlotDetails = () => {
    if (!selectedSlotId.value) {
        selectedSlotDetails.value = null
        deliveryFee.value = 0
        return
    }
    
    // Zoek door alle dagen en slots om details te vinden
    for (const day of props.deliverySlots) {
        const slot = day.slots ? day.slots.find(s => s.id === selectedSlotId.value) : null
        if (slot) {
            selectedSlotDetails.value = {
                ...slot,
                day_name: day.day_name,
                formatted_date: day.formatted_date
            }
            deliveryFee.value = parseFloat(slot.price) || 0
            break
        }
    }
}
</script>

<template>
    <CheckoutLayout :current-step="1" title="Bezorgmoment kiezen">
        <div class="max-w-4xl mx-auto space-y-8">
            
            <!-- Progress Bar Sectie -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <!-- Bezorgadres Stap -->
                        <div class="flex items-center space-x-2">
                            <div :class="[
                                'w-8 h-8 rounded-full flex items-center justify-center',
                                hasValidAddress ? 'bg-green-500' : 'bg-blue-500'
                            ]">
                                <CheckCircleIcon v-if="hasValidAddress" class="w-5 h-5 text-white" />
                                <span v-else class="text-sm font-bold text-white">2</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">Bezorgadres</span>
                        </div>
                        <!-- Progress Lijn -->
                        <div :class="['w-12 h-px', hasValidAddress ? 'bg-gray-300' : 'bg-gray-200']"></div>
                        <!-- Bezorgmoment Stap -->
                        <div class="flex items-center space-x-2">
                            <div :class="[
                                'w-8 h-8 rounded-full flex items-center justify-center',
                                selectedSlotId ? 'bg-green-500' : hasValidAddress ? 'bg-blue-500' : 'bg-gray-300'
                            ]">
                                <CheckCircleIcon v-if="selectedSlotId" class="w-5 h-5 text-white" />
                                <span v-else-if="hasValidAddress" class="text-sm font-bold text-white">3</span>
                                <span v-else class="text-sm font-bold text-gray-600">3</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">Bezorgmoment</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hoofdinhoud Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Linker Kolom: Checkout Stappen -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Stap 1: Bezorgadres -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6">
                            <!-- Stap Header -->
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center">
                                    <div :class="[
                                        'w-10 h-10 rounded-full flex items-center justify-center mr-4',
                                        hasValidAddress ? 'bg-green-500' : 'bg-blue-500'
                                    ]">
                                        <CheckCircleIcon v-if="hasValidAddress" class="w-6 h-6 text-white" />
                                        <MapPinIcon v-else class="w-6 h-6 text-white" />
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-semibold text-gray-900">Bezorgadres</h2>
                                        <p class="text-sm text-gray-600">Waar moeten we je bestelling bezorgen?</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Adres Weergave Container -->
                            <div :class="[
                                'p-4 rounded-lg border-2 transition-all duration-200',
                                hasValidAddress 
                                    ? 'bg-green-50 border-green-200' 
                                    : 'bg-gray-50 border-gray-200 border-dashed'
                            ]">
                                <!-- Bestaand Adres -->
                                <div v-if="hasValidAddress" class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900 mb-1">Bezorgadres</h3>
                                        <p class="text-gray-700">
                                            <span v-for="line in formatAddress()" :key="line" class="block">{{ line }}</span>    
                                        </p>
                                    </div>
                                    <!-- Wijzig Adres Knop -->
                                    <SecondaryButton @click="openAddressModal" class="ml-4">
                                        <PencilIcon class="w-4 h-4 mr-1.5" />
                                        Wijzigen
                                    </SecondaryButton>
                                </div>
                                <!-- Geen Adres - Call to Action -->
                                <div v-else class="text-center py-8">
                                    <MapPinIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Bezorgadres instellen</h3>
                                    <p class="text-gray-600 mb-6">Voeg een bezorgadres toe om verder te gaan</p>
                                    <!-- Adres Toevoegen Knop -->
                                    <PrimaryButton @click="openAddressModal">
                                        <PlusIcon class="w-5 h-5 mr-2" />
                                        Adres toevoegen
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stap 2: Bezorgmoment -->
                    <div :class="[
                        'bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200',
                        hasValidAddress ? 'opacity-100' : 'opacity-50 pointer-events-none'
                    ]">
                        <div class="p-6">
                            <!-- Stap Header -->
                            <div class="flex items-center mb-6">
                                <div :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center mr-4',
                                    selectedSlotId ? 'bg-green-500' : hasValidAddress ? 'bg-blue-500' : 'bg-gray-400'
                                ]">
                                    <CheckCircleIcon v-if="selectedSlotId" class="w-6 h-6 text-white" />
                                    <CalendarDaysIcon v-else class="w-6 h-6 text-white" />
                                </div>
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900">Bezorgmoment kiezen</h2>
                                    <p class="text-sm text-gray-600">Selecteer het tijdstip dat voor jou uitkomt</p>
                                </div>
                            </div>

                            <!-- Delivery Slot Selector of Disabled State -->
                            <div v-if="hasValidAddress">
                                <DeliverySlotSelector
                                    :delivery-slots="deliverySlots"
                                    :selected-slot-id="selectedSlotId"
                                    @slot-selected="handleSlotSelected"
                                    @delivery-fee-updated="handleDeliveryFeeUpdated"
                                    @refresh-slots="handleRefreshSlots"
                                />
                            </div>
                            <!-- Disabled State - Adres Vereist -->
                            <div v-else class="text-center py-12 border-2 border-gray-200 border-dashed rounded-lg">
                                <CalendarDaysIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                                <p class="text-gray-500">Stel eerst een bezorgadres in om beschikbare tijdsloten te zien</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rechter Kolom: Order Samenvatting -->
                <div class="lg:col-span-1">
                    <div class="sticky top-8 space-y-6">
                        
                        <!-- Order Samenvatting Card -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Bestelling</h3>
                            </div>
                            
                            <div class="p-6">
                                <!-- Prijs Breakdown -->
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600">Artikelen ({{ cartStore.totalItems }})</span>
                                        <span class="font-medium">€{{ cartStore.subtotal.toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600">Bezorgkosten</span>
                                        <span class="font-medium">
                                            {{ deliveryFee > 0 ? `€${deliveryFee.toFixed(2)}` : '€0,00' }}
                                        </span>
                                    </div>
                                    <div class="border-t border-gray-200 pt-3 flex justify-between items-center">
                                        <span class="text-lg font-semibold text-gray-900">Totaal</span>
                                        <span class="text-lg font-semibold text-gray-900">
                                            €{{ (cartStore.subtotal + deliveryFee).toFixed(2) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Geselecteerd Bezorgmoment Details -->
                                <div v-if="selectedSlotDetails" class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                    <h4 class="text-sm font-medium text-blue-900 mb-2">Gekozen bezorgmoment</h4>
                                    <p class="text-sm text-blue-800">
                                        <strong>{{ selectedSlotDetails.day_name }}</strong> {{ selectedSlotDetails.formatted_date }}
                                    </p>
                                    <p class="text-sm text-blue-700">
                                        {{ selectedSlotDetails.time_display }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Navigatie Sectie -->
                        <div class="space-y-6">
                            
                            <!-- Verder Knop -->
                            <PrimaryButton
                                @click="proceedToNextStep"
                                :disabled="!canProceedToNextStep || isNavigating"
                                class="w-full justify-center py-3 text-base font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <!-- Loading State -->
                                <span v-if="isNavigating" class="inline-flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Laden...
                                </span>
                                <!-- Normale State -->
                                <span v-else class="flex items-center">
                                    Verder naar overzicht
                                    <ArrowRightIcon class="ml-2 h-5 w-5" />
                                </span>
                            </PrimaryButton>

                            <!-- Validatie Foutbericht -->
                            <div v-if="!canProceedToNextStep" class="text-center">
                                <div class="inline-flex items-center px-3 py-2 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <ExclamationTriangleIcon class="w-4 h-4 text-yellow-600 mr-2" />
                                    <p class="text-sm text-yellow-800">{{ stepValidationMessage }}</p>
                                </div>
                            </div>

                            <!-- Terug naar Winkelen Knop -->
                            <SecondaryButton
                                @click="goBackToProducts"
                                class="w-full inline-flex items-center justify-center"
                            >
                                <ArrowLeftIcon class="mr-2 h-4 w-4" />
                                Verder winkelen
                            </SecondaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Modal Component -->
        <AddressFormModal
            :show="showAddressModal"
            :address="deliveryAddress"
            @close="closeAddressModal"
            @saved="handleAddressSaved"
        />
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

/* Sticky positioning voor sidebar */
.sticky {
    position: sticky;
}
</style>