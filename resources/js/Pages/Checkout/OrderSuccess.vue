<!-- Pages/Checkout/OrderSuccess.vue -->
<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import NavBar from '@/Components/NavBar.vue'
import Footer from '@/Components/Footer.vue'
import { 
    CheckCircleIcon,
    TruckIcon,
    ClockIcon,
    MapPinIcon,
    CreditCardIcon,
    ShoppingCartIcon,
    PrinterIcon,
    ShareIcon,
    ChevronDownIcon,
    ChevronUpIcon
} from '@heroicons/vue/24/outline'

// Props from Laravel
const props = defineProps({
    order: {
        type: Object,
        required: true
    },
    orderItems: {
        type: Array,
        default: () => []
    },
    deliveryAddress: {
        type: Object,
        required: true
    },
    deliverySlot: {
        type: Object,
        required: true
    },
    user: {
        type: Object,
        default: null
    }
})

// Reactive state
const showOrderDetails = ref(false)
const emailSent = ref(false)

// Computed properties
const orderTotal = computed(() => {
    return parseFloat(props.order.total_amount) || 0
})

const subtotal = computed(() => {
    return parseFloat(props.order.subtotal) || 0
})

const deliveryFee = computed(() => {
    return parseFloat(props.order.delivery_fee) || 0
})

const estimatedDeliveryTime = computed(() => {
    if (!props.deliverySlot) return 'Onbekend'
    
    // Format delivery slot time
    const date = new Date(props.deliverySlot.delivery_date)
    const dayName = date.toLocaleDateString('nl-NL', { weekday: 'long' })
    const formattedDate = date.toLocaleDateString('nl-NL', { 
        day: 'numeric', 
        month: 'long' 
    })
    
    return `${dayName} ${formattedDate} tussen ${props.deliverySlot.time_start} - ${props.deliverySlot.time_end}`
})

const paymentMethodDisplay = computed(() => {
    const methods = {
        'ideal': 'iDEAL',
        'card': 'Creditcard',
        'cash': 'Contant bij levering'
    }
    return methods[props.order.payment_method] || props.order.payment_method
})

const orderStatusColor = computed(() => {
    const statusColors = {
        'pending': 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'confirmed': 'bg-green-100 text-green-800 border-green-200',
        'processing': 'bg-blue-100 text-blue-800 border-blue-200',
        'shipped': 'bg-purple-100 text-purple-800 border-purple-200',
        'delivered': 'bg-green-100 text-green-800 border-green-200'
    }
    return statusColors[props.order.status] || 'bg-gray-100 text-gray-800 border-gray-200'
})

const orderStatusText = computed(() => {
    const statusTexts = {
        'pending': 'In behandeling',
        'confirmed': 'Bevestigd',
        'processing': 'Wordt voorbereid',
        'shipped': 'Onderweg',
        'delivered': 'Bezorgd'
    }
    return statusTexts[props.order.status] || 'Onbekend'
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

const continueShopping = () => {
    router.get('/categories')
}

const viewOrderHistory = () => {
    router.get('/orders')
}

const trackOrder = () => {
    router.get(`/orders/${props.order.id}/track`)
}

const printOrder = () => {
    window.print()
}

const shareOrder = async () => {
    if (navigator.share) {
        try {
            await navigator.share({
                title: `Bestelling #${props.order.order_number}`,
                text: `Mijn bestelling bij Commodum Copia is geplaatst!`,
                url: window.location.href
            })
        } catch (error) {
            console.log('Error sharing:', error)
        }
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href)
        alert('Link gekopieerd naar klembord!')
    }
}

const sendConfirmationEmail = async () => {
    try {
        const response = await axios.post(`/orders/${props.order.id}/send-confirmation`)
        if (response.data.success) {
            emailSent.value = true
            setTimeout(() => {
                emailSent.value = false
            }, 3000)
        }
    } catch (error) {
        console.error('Error sending email:', error)
        alert('Kon bevestigingsmail niet versturen')
    }
}

// Lifecycle
onMounted(() => {
    // Clear any checkout-related data from localStorage if used
    if (typeof window !== 'undefined') {
        localStorage.removeItem('checkout_data')
        localStorage.removeItem('selected_delivery_slot')
    }
})
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation Bar -->
        <NavBar />
        
        <!-- Add top margin to account for fixed navbar -->
        <div class="pt-16">
            <!-- Success Header -->
            <div class="bg-green-600">
                <div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <div class="mx-auto w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-lg mb-6">
                            <CheckCircleIcon class="w-12 h-12 text-green-500" />
                        </div>
                        <h1 class="text-3xl sm:text-4xl font-bold text-white mb-4">
                            Je bestelling is binnen! 🎉
                        </h1>
                        <p class="text-lg sm:text-xl text-green-100 mb-2">
                            Bedankt voor je bestelling
                        </p>
                        <p class="text-white text-base sm:text-lg">
                            Bestelnummer: <span class="font-bold underline">#{{ order.order_number }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="max-w-4xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
                <!-- Order Status & Quick Info -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6">
                    <!-- Order Status -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2">Status</h3>
                                <span :class="[
                                    'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border',
                                    orderStatusColor
                                ]">
                                    {{ orderStatusText }}
                                </span>
                            </div>
                            <TruckIcon class="w-6 h-6 sm:w-8 sm:h-8 text-gray-400" />
                        </div>
                    </div>

                    <!-- Delivery Time -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2">Bezorging</h3>
                                <p class="text-xs sm:text-sm text-gray-600 break-words">{{ estimatedDeliveryTime }}</p>
                            </div>
                            <ClockIcon class="w-6 h-6 sm:w-8 sm:h-8 text-gray-400 flex-shrink-0 ml-2" />
                        </div>
                    </div>

                    <!-- Order Total -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2">Totaal</h3>
                                <p class="text-xl sm:text-2xl font-bold text-green-600">€{{ orderTotal.toFixed(2) }}</p>
                            </div>
                            <CreditCardIcon class="w-6 h-6 sm:w-8 sm:h-8 text-gray-400" />
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Order Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Delivery Information -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-blue-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg sm:text-xl font-semibold text-gray-900 flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <MapPinIcon class="w-4 h-4 text-blue-600" />
                                    </div>
                                    Bezorginformatie
                                </h3>
                            </div>
                            
                            <div class="p-4 sm:p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2">Bezorgadres</h4>
                                        <p class="text-gray-700 break-words">
                                            <span v-for="line in formatAddress()" :key="line" class="block">{{ line }}</span>    
                                        </p>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2">Bezorgtijd</h4>
                                        <p class="text-gray-700 break-words">{{ estimatedDeliveryTime }}</p>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Bezorgkosten: €{{ deliveryFee.toFixed(2) }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div v-if="order.order_notes" class="mt-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                    <h4 class="font-semibold text-yellow-900 mb-2">Opmerkingen</h4>
                                    <p class="text-yellow-800 text-sm">{{ order.order_notes }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-green-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900 flex items-center">
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                            <ShoppingCartIcon class="w-4 h-4 text-green-600" />
                                        </div>
                                        Bestelde artikelen ({{ orderItems.length }})
                                    </h3>
                                    <SecondaryButton 
                                        @click="showOrderDetails = !showOrderDetails"
                                        class="text-sm"
                                    >
                                        {{ showOrderDetails ? 'Verbergen' : 'Tonen' }}
                                        <ChevronDownIcon v-if="!showOrderDetails" class="w-4 h-4 ml-1" />
                                        <ChevronUpIcon v-else class="w-4 h-4 ml-1" />
                                    </SecondaryButton>
                                </div>
                            </div>
                            
                            <div v-if="showOrderDetails" class="p-4 sm:p-6">
                                <div class="space-y-4">
                                    <div 
                                        v-for="item in orderItems" 
                                        :key="item.id"
                                        class="flex items-center p-4 bg-gray-50 rounded-lg"
                                    >
                                        <img 
                                            :src="item.product.image_url || '/images/placeholder-product.jpg'" 
                                            :alt="item.product.name"
                                            class="w-12 h-12 sm:w-16 sm:h-16 object-cover rounded-lg mr-4 flex-shrink-0"
                                        />
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-medium text-gray-900 truncate">{{ item.product.name }}</h4>
                                            <p class="text-sm text-gray-600">{{ item.quantity }}x €{{ item.unit_price.toFixed(2) }}</p>
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            <p class="font-semibold text-gray-900">€{{ (item.quantity * item.unit_price).toFixed(2) }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Order Summary -->
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Subtotaal:</span>
                                            <span class="font-medium">€{{ subtotal.toFixed(2) }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Bezorgkosten:</span>
                                            <span class="font-medium">€{{ deliveryFee.toFixed(2) }}</span>
                                        </div>
                                        <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2">
                                            <span>Totaal:</span>
                                            <span class="text-green-600">€{{ orderTotal.toFixed(2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-purple-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg sm:text-xl font-semibold text-gray-900 flex items-center">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                        <CreditCardIcon class="w-4 h-4 text-purple-600" />
                                    </div>
                                    Betaalinformatie
                                </h3>
                            </div>
                            
                            <div class="p-4 sm:p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2">Betaalmethode</h4>
                                        <p class="text-gray-700">{{ paymentMethodDisplay }}</p>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2">Betaalstatus</h4>
                                        <span :class="[
                                            'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                                            order.payment_status === 'paid' ? 'bg-green-100 text-green-800' : 
                                            order.payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                            'bg-red-100 text-red-800'
                                        ]">
                                            {{ order.payment_status === 'paid' ? 'Betaald' : 
                                               order.payment_status === 'pending' ? 'In behandeling' : 
                                               'Niet betaald' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Actions -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Quick Actions -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Acties</h3>
                            </div>
                            
                            <div class="p-4 sm:p-6 space-y-4">
                                <PrimaryButton @click="trackOrder" class="w-full justify-center">
                                    <TruckIcon class="w-4 h-4 mr-2" />
                                    Bestelling volgen
                                </PrimaryButton>
                                
                                <SecondaryButton @click="viewOrderHistory" class="w-full justify-center">
                                    Bestelhistorie bekijken
                                </SecondaryButton>
                                
                                <SecondaryButton @click="continueShopping" class="w-full justify-center">
                                    <ShoppingCartIcon class="w-4 h-4 mr-2" />
                                    Verder winkelen
                                </SecondaryButton>
                            </div>
                        </div>

                        <!-- Email Confirmation -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-blue-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Bevestigingsmail</h3>
                            </div>
                            
                            <div class="p-4 sm:p-6">
                                <p class="text-sm text-gray-600 mb-4">
                                    Geen bevestigingsmail ontvangen? Verstuur deze opnieuw.
                                </p>
                                
                                <SecondaryButton
                                    @click="sendConfirmationEmail"
                                    :disabled="emailSent"
                                    class="w-full justify-center text-sm disabled:opacity-50"
                                >
                                    {{ emailSent ? '✓ Bevestigingsmail verzonden!' : '📧 Bevestigingsmail opnieuw versturen' }}
                                </SecondaryButton>
                            </div>
                        </div>

                        <!-- Share & Print -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-yellow-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Delen & Printen</h3>
                            </div>
                            
                            <div class="p-4 sm:p-6 space-y-3">
                                <SecondaryButton
                                    @click="printOrder"
                                    class="w-full justify-center"
                                >
                                    <PrinterIcon class="w-4 h-4 mr-2" />
                                    Bestelling printen
                                </SecondaryButton>
                                
                                <SecondaryButton
                                    @click="shareOrder"
                                    class="w-full justify-center"
                                >
                                    <ShareIcon class="w-4 h-4 mr-2" />
                                    Bestelling delen
                                </SecondaryButton>
                            </div>
                        </div>

                        <!-- Order Status -->
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4 sm:p-6">
                            <h4 class="text-lg font-semibold text-green-900 mb-3">Wat gebeurt er nu?</h4>
                            <ul class="text-sm text-green-800 space-y-2">
                                <li class="flex items-start">
                                    <span class="w-2 h-2 bg-green-400 rounded-full mr-3 mt-2 flex-shrink-0"></span>
                                    Je ontvangt binnen enkele minuten een bevestigingsmail
                                </li>
                                <li class="flex items-start">
                                    <span class="w-2 h-2 bg-green-400 rounded-full mr-3 mt-2 flex-shrink-0"></span>
                                    We bereiden je bestelling voor
                                </li>
                                <li class="flex items-start">
                                    <span class="w-2 h-2 bg-green-400 rounded-full mr-3 mt-2 flex-shrink-0"></span>
                                    Je bestelling wordt op tijd bezorgd
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <Footer />
    </div>
</template>

<style scoped>
@media print {
    .no-print {
        display: none;
    }
    
    /* Hide navbar and footer when printing */
    nav, footer {
        display: none !important;
    }
    
    body {
        background: white !important;
    }
}

/* Remove focus rings */
button:focus-visible {
    outline: none;
}
</style>