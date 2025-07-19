/**
 * Bestandsnaam: OrderSuccess.vue (Pages/Checkout)
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-07-01
 * Tijd: 01:25:04
 * Doel: Dit component toont de succespagina na een voltooide bestelling. Bevat order details,
 *       bezorgingsinformatie, betaalstatus, en acties zoals volgen, printen en delen van de
 *       bestelling. Inclusief responsive design en print styling.
 */

<script setup>
// Vue compositie API imports
import { ref, computed, onMounted } from 'vue'

// Inertia.js import voor navigatie
import { router } from '@inertiajs/vue3'

// Component imports
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import NavBar from '@/Components/NavBar.vue'
import Footer from '@/Components/Footer.vue'

// Heroicons imports voor UI iconen
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

// ========== PROPS DEFINITIE ==========

// Props van Laravel server met order data
const props = defineProps({
    order: {                                   // Hoofdorder object met status, totaal, etc.
        type: Object,
        required: true
    },
    orderItems: {                             // Array van bestelde producten
        type: Array,
        default: () => []
    },
    deliveryAddress: {                        // Bezorgadres informatie
        type: Object,
        required: true
    },
    deliverySlot: {                          // Geselecteerde bezorgtijd slot
        type: Object,
        required: true
    },
    user: {                                  // Gebruiker informatie (optioneel)
        type: Object,
        default: null
    }
})

// ========== REACTIVE STATE ==========

// UI state management
const showOrderDetails = ref(false)          // Of order items details zichtbaar zijn
const emailSent = ref(false)                 // Of bevestigingsmail verzonden is

// ========== COMPUTED PROPERTIES ==========

/**
 * Berekent het totale order bedrag
 * @returns {number} Totaal bedrag van de order
 */
const orderTotal = computed(() => {
    return parseFloat(props.order.total_amount) || 0
})

/**
 * Berekent het subtotaal (zonder bezorgkosten)
 * @returns {number} Subtotaal bedrag
 */
const subtotal = computed(() => {
    return parseFloat(props.order.subtotal) || 0
})

/**
 * Berekent de bezorgkosten
 * @returns {number} Bezorgkosten bedrag
 */
const deliveryFee = computed(() => {
    return parseFloat(props.order.delivery_fee) || 0
})

/**
 * Formatteert de geschatte bezorgtijd naar leesbare string
 * @returns {string} Geformatteerde bezorgtijd
 */
const estimatedDeliveryTime = computed(() => {
    if (!props.deliverySlot) return 'Onbekend'
    
    // Format delivery slot time naar Nederlandse datum
    const date = new Date(props.deliverySlot.delivery_date)
    const dayName = date.toLocaleDateString('nl-NL', { weekday: 'long' })
    const formattedDate = date.toLocaleDateString('nl-NL', { 
        day: 'numeric', 
        month: 'long' 
    })
    
    return `${dayName} ${formattedDate} tussen ${props.deliverySlot.time_start} - ${props.deliverySlot.time_end}`
})

/**
 * Vertaalt betaalmethode naar Nederlandse weergave
 * @returns {string} Nederlandse betaalmethode naam
 */
const paymentMethodDisplay = computed(() => {
    const methods = {
        'ideal': 'iDEAL',
        'card': 'Creditcard',
        'cash': 'Contant bij levering'
    }
    return methods[props.order.payment_method] || props.order.payment_method
})

/**
 * Bepaalt CSS classes voor order status badge
 * @returns {string} CSS classes voor status styling
 */
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

/**
 * Vertaalt order status naar Nederlandse tekst
 * @returns {string} Nederlandse status beschrijving
 */
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

// ========== UTILITY METHODS ==========

/**
 * Formatteert bezorgadres naar array van lijnen voor weergave
 * @returns {Array} Array van adres lijnen
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

// ========== NAVIGATIE METHODS ==========

/**
 * Navigeer naar categorieën pagina om verder te winkelen
 */
const continueShopping = () => {
    router.get('/categories')
}

/**
 * Navigeer naar order geschiedenis pagina
 */
const viewOrderHistory = () => {
    router.get('/orders')
}

/**
 * Navigeer naar order tracking pagina
 */
const trackOrder = () => {
    router.get(`/orders/${props.order.id}/track`)
}

// ========== ACTIE METHODS ==========

/**
 * Print de huidige order pagina
 */
const printOrder = () => {
    window.print()
}

/**
 * Deel order via native sharing API of kopieer naar klembord
 */
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
        // Fallback: kopieer link naar klembord
        navigator.clipboard.writeText(window.location.href)
        alert('Link gekopieerd naar klembord!')
    }
}

/**
 * Verstuur bevestigingsmail opnieuw naar gebruiker
 */
const sendConfirmationEmail = async () => {
    try {
        const response = await axios.post(`/orders/${props.order.id}/send-confirmation`)
        if (response.data.success) {
            emailSent.value = true
            // Reset status na 3 seconden
            setTimeout(() => {
                emailSent.value = false
            }, 3000)
        }
    } catch (error) {
        console.error('Error sending email:', error)
        alert('Kon bevestigingsmail niet versturen')
    }
}

// ========== LIFECYCLE HOOKS ==========

/**
 * Component mounted - cleanup checkout data
 */
onMounted(() => {
    // Verwijder checkout gerelateerde data uit localStorage
    if (typeof window !== 'undefined') {
        localStorage.removeItem('checkout_data')
        localStorage.removeItem('selected_delivery_slot')
    }
})
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigatie Balk -->
        <NavBar />
        
        <!-- Top margin voor vaste navbar -->
        <div class="pt-16">
            
            <!-- Success Header Sectie -->
            <div class="bg-green-600">
                <div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <!-- Success Icoon -->
                        <div class="mx-auto w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-lg mb-6">
                            <CheckCircleIcon class="w-12 h-12 text-green-500" />
                        </div>
                        <!-- Success Bericht -->
                        <h1 class="text-3xl sm:text-4xl font-bold text-white mb-4">
                            Je bestelling is binnen! 🎉
                        </h1>
                        <p class="text-lg sm:text-xl text-green-100 mb-2">
                            Bedankt voor je bestelling
                        </p>
                        <!-- Order Nummer -->
                        <p class="text-white text-base sm:text-lg">
                            Bestelnummer: <span class="font-bold underline">#{{ order.order_number }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="max-w-4xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
                
                <!-- Order Status & Quick Info Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6">
                    
                    <!-- Order Status Card -->
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

                    <!-- Delivery Time Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2">Bezorging</h3>
                                <p class="text-xs sm:text-sm text-gray-600 break-words">{{ estimatedDeliveryTime }}</p>
                            </div>
                            <ClockIcon class="w-6 h-6 sm:w-8 sm:h-8 text-gray-400 flex-shrink-0 ml-2" />
                        </div>
                    </div>

                    <!-- Order Total Card -->
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

                <!-- Hoofdinhoud Grid Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Linker Kolom: Order Details -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Bezorginformatie Sectie -->
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
                                    <!-- Bezorgadres -->
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2">Bezorgadres</h4>
                                        <p class="text-gray-700 break-words">
                                            <span v-for="line in formatAddress()" :key="line" class="block">{{ line }}</span>    
                                        </p>
                                    </div>
                                    <!-- Bezorgtijd -->
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2">Bezorgtijd</h4>
                                        <p class="text-gray-700 break-words">{{ estimatedDeliveryTime }}</p>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Bezorgkosten: €{{ deliveryFee.toFixed(2) }}
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Order Opmerkingen -->
                                <div v-if="order.order_notes" class="mt-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                    <h4 class="font-semibold text-yellow-900 mb-2">Opmerkingen</h4>
                                    <p class="text-yellow-800 text-sm">{{ order.order_notes }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items Sectie -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-green-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900 flex items-center">
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                            <ShoppingCartIcon class="w-4 h-4 text-green-600" />
                                        </div>
                                        Bestelde artikelen ({{ orderItems.length }})
                                    </h3>
                                    <!-- Toggle Details Knop -->
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
                            
                            <!-- Uitklapbare Order Items Details -->
                            <div v-if="showOrderDetails" class="p-4 sm:p-6">
                                <div class="space-y-4">
                                    <!-- Product Item Loop -->
                                    <div 
                                        v-for="item in orderItems" 
                                        :key="item.id"
                                        class="flex items-center p-4 bg-gray-50 rounded-lg"
                                    >
                                        <!-- Product Afbeelding -->
                                        <img 
                                            :src="item.product.image_url || '/images/placeholder-product.jpg'" 
                                            :alt="item.product.name"
                                            class="w-12 h-12 sm:w-16 sm:h-16 object-cover rounded-lg mr-4 flex-shrink-0"
                                        />
                                        <!-- Product Info -->
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-medium text-gray-900 truncate">{{ item.product.name }}</h4>
                                            <p class="text-sm text-gray-600">{{ item.quantity }}x €{{ item.unit_price.toFixed(2) }}</p>
                                        </div>
                                        <!-- Item Totaal -->
                                        <div class="text-right flex-shrink-0">
                                            <p class="font-semibold text-gray-900">€{{ (item.quantity * item.unit_price).toFixed(2) }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Order Samenvatting -->
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

                        <!-- Betaalinformatie Sectie -->
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
                                    <!-- Betaalmethode -->
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2">Betaalmethode</h4>
                                        <p class="text-gray-700">{{ paymentMethodDisplay }}</p>
                                    </div>
                                    <!-- Betaalstatus -->
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

                    <!-- Rechter Kolom: Acties -->
                    <div class="lg:col-span-1 space-y-6">
                        
                        <!-- Snelle Acties Sectie -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Acties</h3>
                            </div>
                            
                            <div class="p-4 sm:p-6 space-y-4">
                                <!-- Track Order Knop -->
                                <PrimaryButton @click="trackOrder" class="w-full justify-center">
                                    <TruckIcon class="w-4 h-4 mr-2" />
                                    Bestelling volgen
                                </PrimaryButton>
                                
                                <!-- Order History Knop -->
                                <SecondaryButton @click="viewOrderHistory" class="w-full justify-center">
                                    Bestelhistorie bekijken
                                </SecondaryButton>
                                
                                <!-- Continue Shopping Knop -->
                                <SecondaryButton @click="continueShopping" class="w-full justify-center">
                                    <ShoppingCartIcon class="w-4 h-4 mr-2" />
                                    Verder winkelen
                                </SecondaryButton>
                            </div>
                        </div>

                        <!-- Email Bevestiging Sectie -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-blue-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Bevestigingsmail</h3>
                            </div>
                            
                            <div class="p-4 sm:p-6">
                                <p class="text-sm text-gray-600 mb-4">
                                    Geen bevestigingsmail ontvangen? Verstuur deze opnieuw.
                                </p>
                                
                                <!-- Resend Email Knop -->
                                <SecondaryButton
                                    @click="sendConfirmationEmail"
                                    :disabled="emailSent"
                                    class="w-full justify-center text-sm disabled:opacity-50"
                                >
                                    {{ emailSent ? '✓ Bevestigingsmail verzonden!' : '📧 Bevestigingsmail opnieuw versturen' }}
                                </SecondaryButton>
                            </div>
                        </div>

                        <!-- Delen & Printen Sectie -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-yellow-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Delen & Printen</h3>
                            </div>
                            
                            <div class="p-4 sm:p-6 space-y-3">
                                <!-- Print Knop -->
                                <SecondaryButton
                                    @click="printOrder"
                                    class="w-full justify-center"
                                >
                                    <PrinterIcon class="w-4 h-4 mr-2" />
                                    Bestelling printen
                                </SecondaryButton>
                                
                                <!-- Share Knop -->
                                <SecondaryButton
                                    @click="shareOrder"
                                    class="w-full justify-center"
                                >
                                    <ShareIcon class="w-4 h-4 mr-2" />
                                    Bestelling delen
                                </SecondaryButton>
                            </div>
                        </div>

                        <!-- Order Status Info -->
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

        <!-- Footer Component -->
        <Footer />
    </div>
</template>

<style scoped>
/* Print Styling */
@media print {
    .no-print {
        display: none;
    }
    
    /* Verberg navbar en footer bij printen */
    nav, footer {
        display: none !important;
    }
    
    body {
        background: white !important;
    }
}

/* Focus ring styling */
button:focus-visible {
    outline: none;
}
</style>