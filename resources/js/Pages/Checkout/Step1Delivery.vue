<!-- Pages/Checkout/Step1Delivery.vue -->
<script setup>
import CheckoutLayout from '@/Layouts/Checkout/CheckoutLayout.vue'
import DeliverySlotSelector from '@/Components/Checkout/DeliverySlotSelector.vue'
import AddressFormModal from '@/Components/AddressFormModal.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import { router } from '@inertiajs/vue3'
import { ref, computed, onMounted, watch } from 'vue'
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
    user: {
        type: Object,
        default: null
    }
})

// Initialize cart store
const cartStore = useCartStore()

// Reactive state
const selectedSlotId = ref(props.selectedSlotId || null)
const selectedSlotDetails = ref(null)
const deliveryFee = ref(0)
const showAddressModal = ref(false)
const isNavigating = ref(false)

// Computed properties
const hasValidAddress = computed(() => {
    return props.deliveryAddress && 
           props.deliveryAddress.street && 
           props.deliveryAddress.postal_code && 
           props.deliveryAddress.city
})

const canProceedToNextStep = computed(() => {
    return hasValidAddress.value && selectedSlotId.value && cartStore.hasItems
})

const stepValidationMessage = computed(() => {
    if (!cartStore.hasItems) return 'Je winkelwagen is leeg'
    if (!hasValidAddress.value) return 'Stel eerst een bezorgadres in'
    if (!selectedSlotId.value) return 'Selecteer een bezorgmoment'
    return ''
})

// Methods
const formatAddress = () => {
    if (!hasValidAddress.value) {
        return 'Nog geen adres ingesteld'
    }
    
    const addr = props.deliveryAddress
    let formatted = addr.street
    
    if (addr.house_number) {
        formatted += ` ${addr.house_number}`
    }
    
    formatted += `, ${addr.postal_code} ${addr.city}`
    
    return formatted
}

const openAddressModal = () => {
    showAddressModal.value = true
}

const closeAddressModal = () => {
    showAddressModal.value = false
}

const handleAddressSaved = (address) => {
    console.log('Address saved:', address)
    closeAddressModal()
}

// Event handlers for DeliverySlotSelector
const handleSlotSelected = (eventData) => {
    selectedSlotId.value = eventData.slotId
    selectedSlotDetails.value = eventData.slotDetails
    deliveryFee.value = eventData.deliveryFee
    
    storeSelectedSlot(eventData)
}

const handleDeliveryFeeUpdated = (fee) => {
    deliveryFee.value = fee
}

const handleRefreshSlots = async () => {
    router.reload({
        only: ['deliverySlots'],
        preserveState: true
    })
}

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

// Navigation
const proceedToNextStep = () => {
    if (!canProceedToNextStep.value || isNavigating.value) return
    
    isNavigating.value = true
    
    router.get('/checkout/review', {}, {
        onFinish: () => {
            isNavigating.value = false
        }
    })
}

const goBackToProducts = () => {
    router.get('/categories')
}

// Initialize selected slot details on mount
onMounted(() => {
    if (selectedSlotId.value) {
        updateSelectedSlotDetails()
    }
})

const updateSelectedSlotDetails = () => {
    if (!selectedSlotId.value) {
        selectedSlotDetails.value = null
        deliveryFee.value = 0
        return
    }
    
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
            <!-- Progress Bar -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <CheckCircleIcon class="w-5 h-5 text-white" />
                            </div>
                            <span class="text-sm font-medium text-gray-900">Winkelwagen</span>
                        </div>
                        <div class="w-12 h-px bg-gray-300"></div>
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
                        <div :class="['w-12 h-px', hasValidAddress ? 'bg-gray-300' : 'bg-gray-200']"></div>
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

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Steps -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Step 1: Address -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6">
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

                            <!-- Address Display -->
                            <div :class="[
                                'p-4 rounded-lg border-2 transition-all duration-200',
                                hasValidAddress 
                                    ? 'bg-green-50 border-green-200' 
                                    : 'bg-gray-50 border-gray-200 border-dashed'
                            ]">
                                <div v-if="hasValidAddress" class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-medium text-gray-900 mb-1">Bezorgadres</h3>
                                        <p class="text-gray-700">{{ formatAddress() }}</p>
                                    </div>
                                    <SecondaryButton @click="openAddressModal" class="ml-4">
                                        <PencilIcon class="w-4 h-4 mr-1.5" />
                                        Wijzigen
                                    </SecondaryButton>
                                </div>
                                <div v-else class="text-center py-8">
                                    <MapPinIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Bezorgadres instellen</h3>
                                    <p class="text-gray-600 mb-6">Voeg een bezorgadres toe om verder te gaan</p>
                                    <PrimaryButton @click="openAddressModal">
                                        <PlusIcon class="w-5 h-5 mr-2" />
                                        Adres toevoegen
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Delivery Time -->
                    <div :class="[
                        'bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200',
                        hasValidAddress ? 'opacity-100' : 'opacity-50 pointer-events-none'
                    ]">
                        <div class="p-6">
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

                            <div v-if="hasValidAddress">
                                <DeliverySlotSelector
                                    :delivery-slots="deliverySlots"
                                    :selected-slot-id="selectedSlotId"
                                    @slot-selected="handleSlotSelected"
                                    @delivery-fee-updated="handleDeliveryFeeUpdated"
                                    @refresh-slots="handleRefreshSlots"
                                />
                            </div>
                            <div v-else class="text-center py-12 border-2 border-gray-200 border-dashed rounded-lg">
                                <CalendarDaysIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                                <p class="text-gray-500">Stel eerst een bezorgadres in om beschikbare tijdsloten te zien</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Summary -->
                <div class="lg:col-span-1">
                    <div class="sticky top-8 space-y-6">
                        <!-- Order Summary -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Bestelling</h3>
                            </div>
                            
                            <div class="p-6">
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

                                <!-- Selected Delivery Details -->
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

                        <!-- Navigation -->
                        <div class="space-y-6">
                            <!-- Continue Button -->
                            <PrimaryButton
                                @click="proceedToNextStep"
                                :disabled="!canProceedToNextStep || isNavigating"
                                class="w-full justify-center py-3 text-base font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="isNavigating" class="inline-flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Laden...
                                </span>
                                <span v-else class="flex items-center">
                                    Verder naar overzicht
                                    <ArrowRightIcon class="ml-2 h-5 w-5" />
                                </span>
                            </PrimaryButton>

                            <!-- Validation Message -->
                            <div v-if="!canProceedToNextStep" class="text-center">
                                <div class="inline-flex items-center px-3 py-2 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <ExclamationTriangleIcon class="w-4 h-4 text-yellow-600 mr-2" />
                                    <p class="text-sm text-yellow-800">{{ stepValidationMessage }}</p>
                                </div>
                            </div>

                            <!-- Back Button -->
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
@keyframes spin {
    to { 
        transform: rotate(360deg); 
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

.sticky {
    position: sticky;
}
</style>