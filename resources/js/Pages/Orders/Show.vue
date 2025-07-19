/**
 * Bestandsnaam: Show.vue (Pages/Orders)
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-07-02
 * Tijd: 00:15:49
 * Doel: Gedetailleerde order weergave pagina voor klanten met volledige order informatie, items overzicht,
 *       bezorg- en betaalgegevens, en order management acties. Bevat responsive design voor mobile/desktop,
 *       print functionaliteit, email resend optie, order tracking, en cancel functionaliteit via modal.
 *       Inclusief image fallbacks, address formatting, en uitgebreide status visualisatie voor complete
 *       order experience en customer service ondersteuning.
 */

<script setup>
// Vue compositie API imports voor reactive state management
import { ref } from 'vue'

// Inertia.js imports voor navigatie en routing
import { Head, router } from '@inertiajs/vue3'

// Layout en component imports
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import Modal from '@/Components/Modal.vue'

// Heroicons imports voor UI iconen
import {
    TruckIcon,
    MapPinIcon,
    CalendarDaysIcon,
    ChatBubbleLeftEllipsisIcon,
    CreditCardIcon,
    PrinterIcon,
    EnvelopeIcon,
    XMarkIcon,
    QuestionMarkCircleIcon,
    ArrowLeftIcon,
    ArrowRightIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

// ========== PROPS DEFINITIE ==========

// Props van server - complete order data en gerelateerde informatie
const props = defineProps({
    order: Object,                            // Complete order object met status, totalen, etc.
    orderItems: Array,                        // Array van order items met product informatie
    deliveryAddress: Object,                  // Bezorgadres informatie
    deliverySlot: Object                      // Geplande bezorgtijd informatie
})

// ========== REACTIVE STATE MANAGEMENT ==========

// Modal en loading states voor order acties
const showCancelModal = ref(false);          // Cancel bevestiging modal visibility
const cancelling = ref(false);               // Loading state voor cancel operatie
const emailSending = ref(false);             // Loading state voor email resend

// ========== UTILITY FUNCTIES ==========

/**
 * Formatteert datum/tijd naar Nederlandse locale weergave
 * Gebruikt voor order datum en andere tijdstempel weergave
 * @param {string} dateTime - ISO datum string van server
 * @returns {string} Geformatteerde Nederlandse datum en tijd
 */
const formatDateTime = (dateTime) => {
    return new Date(dateTime).toLocaleDateString('nl-NL', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

/**
 * Bepaalt CSS classes voor order status badges
 * Gebruikt voor consistente kleur coding van verschillende order statussen
 * @param {string} status - Order status identifier
 * @returns {string} CSS classes voor status badge styling
 */
const getStatusClasses = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',           // In afwachting - geel
        'confirmed': 'bg-blue-100 text-blue-800',             // Bevestigd - blauw
        'processing': 'bg-purple-100 text-purple-800',        // In verwerking - paars
        'out_for_delivery': 'bg-indigo-100 text-indigo-800',  // Onderweg - indigo
        'delivered': 'bg-green-100 text-green-800',           // Bezorgd - groen
        'cancelled': 'bg-red-100 text-red-800'                // Geannuleerd - rood
    }
    return classes[status] || 'bg-gray-100 text-gray-800'     // Default fallback - grijs
}

/**
 * Bepaalt CSS classes voor payment status badges
 * Gebruikt voor visuele weergave van betaalstatus
 * @param {string} status - Payment status identifier
 * @returns {string} CSS classes voor payment status badge
 */
const getPaymentStatusClasses = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',           // Pending betaling - geel
        'processing': 'bg-blue-100 text-blue-800',            // Processing - blauw
        'completed': 'bg-green-100 text-green-800',           // Betaald - groen
        'failed': 'bg-red-100 text-red-800',                  // Mislukt - rood
        'cancelled': 'bg-red-100 text-red-800'                // Geannuleerd - rood
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

/**
 * Formatteert payment method codes naar gebruiksvriendelijke labels
 * @param {string} method - Payment method identifier
 * @returns {string} Geformatteerde payment method naam
 */
const formatPaymentMethod = (method) => {
    const methods = {
        'ideal': 'iDEAL',
        'card': 'Creditcard',
        'cash': 'Contant bij levering'
    }
    return methods[method] || method
}

/**
 * Formatteert payment status codes naar Nederlandse labels
 * @param {string} status - Payment status identifier
 * @returns {string} Nederlandse payment status label
 */
const formatPaymentStatus = (status) => {
    const statuses = {
        'pending': 'In behandeling',
        'processing': 'Wordt verwerkt',
        'completed': 'Betaald',
        'failed': 'Mislukt',
        'cancelled': 'Geannuleerd'
    }
    return statuses[status] || status
}

/**
 * Formatteert address object naar leesbare adresregel
 * Combineert straat, huisnummer en toevoeging tot één regel
 * @param {Object} address - Address object met street, house_number, addition
 * @returns {string} Geformatteerde adresregel
 */
const formatAddressLine = (address) => {
    let line = address.street
    if (address.house_number) {
        line += ` ${address.house_number}`
    }
    if (address.addition) {
        line += `, ${address.addition}`
    }
    return line
}

/**
 * Genereert volledige image URL met fallback voor product afbeeldingen
 * Handelt verschillende image path formats en fallbacks af
 * @param {string} imagePath - Relatief of volledig image path
 * @returns {string} Volledige image URL
 */
const getImageUrl = (imagePath) => {
    if (!imagePath) return '/images/placeholder-product.jpg'
    if (imagePath.startsWith('http') || imagePath.startsWith('/')) return imagePath
    return `/storage/${imagePath}`
}

/**
 * Error handler voor product image loading failures
 * Vervangt broken images met placeholder image
 * @param {Event} event - Image error event
 */
const handleImageError = (event) => {
    event.target.src = '/images/placeholder-product.jpg'
}

// ========== ORDER ACTION HANDLERS ==========

/**
 * Triggert browser print functionaliteit voor order details
 * Gebruikt CSS print styles voor geoptimaliseerde print weergave
 */
const printOrder = () => {
    window.print()
}

/**
 * Verstuurt order bevestigingsmail opnieuw naar klant
 * Async functie met loading state en error handling
 */
const sendConfirmationEmail = async () => {
    emailSending.value = true
    try {
        const response = await axios.post(`/orders/${props.order.id}/send-confirmation`)
        if (response.data.success) {
            // Success feedback (kan vervangen worden door toast notification)
            alert('Bevestigingsmail is verzonden!')
        }
    } catch (error) {
        console.error('Error sending confirmation email:', error)
        alert('Er is een fout opgetreden bij het versturen van de bevestigingsmail.')
    } finally {
        emailSending.value = false
    }
}

/**
 * Voert order annulering uit met bevestiging modal workflow
 * Async functie met loading states en proper error handling
 */
const cancelOrder = async () => {
    cancelling.value = true
    try {
        await router.patch(`/orders/${props.order.id}/cancel`, {}, {
            onSuccess: () => {
                showCancelModal.value = false
            },
            onFinish: () => {
                cancelling.value = false
            }
        })
    } catch (error) {
        console.error('Error cancelling order:', error)
        cancelling.value = false
    }
}
</script>

<template>
    <!-- Authenticated Layout Wrapper -->
    <AuthenticatedLayout>
        <!-- Dynamic Page Title met Order Nummer -->
        <Head :title="`Bestelling #${order.order_number}`" />

        <!-- Page Header Template Slot met Order Info -->
        <template #header>
            <div class="flex flex-col space-y-2 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between">
                <!-- Order Titel en Datum -->
                <div class="flex-1 min-w-0">
                    <!-- Order Nummer Titel -->
                    <h2 class="text-lg sm:text-xl font-semibold leading-tight text-gray-800 truncate">
                        Bestelling #{{ order.order_number }}
                    </h2>
                    <!-- Order Datum -->
                    <p class="text-xs sm:text-sm text-gray-600 mt-1">
                        Geplaatst op {{ formatDateTime(order.created_at) }}
                    </p>
                </div>
                <!-- Order Status Badge -->
                <div class="flex items-center">
                    <span :class="[
                        'inline-flex items-center px-2.5 py-1 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-medium',
                        getStatusClasses(order.status)
                    ]">
                        {{ order.status_display }}
                    </span>
                </div>
            </div>
        </template>

        <!-- Main Content Container -->
        <div class="py-4 sm:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Responsive Grid Layout (2/3 + 1/3 kolommen) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-8">
                    
                    <!-- Main Content Sectie (2/3 breedte op desktop) -->
                    <div class="lg:col-span-2 space-y-4 sm:space-y-8">
                        
                        <!-- Order Items Sectie -->
                        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                            <!-- Items Header -->
                            <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                                    Bestelde items ({{ orderItems.length }})
                                </h3>
                            </div>
                            <!-- Items Lijst -->
                            <div class="divide-y divide-gray-200">
                                <!-- Order Item (herhaalt voor elk item) -->
                                <div
                                    v-for="item in orderItems"
                                    :key="item.id"
                                    class="p-4 sm:p-6"
                                >
                                    <!-- Mobile Layout (stack vertical) -->
                                    <div class="flex sm:hidden space-x-3">
                                        <!-- Product Afbeelding -->
                                        <div class="flex-shrink-0">
                                            <img
                                                :src="getImageUrl(item.product?.image_path)"
                                                :alt="item.product_name"
                                                class="w-16 h-16 object-cover rounded-lg border border-gray-200"
                                                @error="handleImageError"
                                            />
                                        </div>
                                        <!-- Product Details -->
                                        <div class="flex-1 min-w-0">
                                            <!-- Product Naam -->
                                            <h4 class="text-sm font-medium text-gray-900 mb-2 line-clamp-2">
                                                {{ item.product_name }}
                                            </h4>
                                            <!-- Pricing Details -->
                                            <div class="text-xs text-gray-600 space-y-1">
                                                <div class="flex justify-between">
                                                    <span>Aantal:</span>
                                                    <span class="font-medium">{{ item.quantity }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Per stuk:</span>
                                                    <span class="font-medium">€{{ Number(item.price).toFixed(2) }}</span>
                                                </div>
                                                <div class="flex justify-between font-semibold text-gray-900 pt-1 border-t border-gray-100">
                                                    <span>Totaal:</span>
                                                    <span>€{{ Number(item.total).toFixed(2) }}</span>
                                                </div>
                                            </div>
                                            <!-- Product Availability Warning -->
                                            <p v-if="!item.product?.is_active" class="text-xs text-red-600 mt-2">
                                                ⚠️ Product niet meer beschikbaar
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Desktop Layout (horizontal) -->
                                    <div class="hidden sm:flex items-start space-x-4">
                                        <!-- Product Afbeelding -->
                                        <div class="flex-shrink-0">
                                            <img
                                                :src="getImageUrl(item.product?.image_path)"
                                                :alt="item.product_name"
                                                class="w-20 h-20 object-cover rounded-lg border border-gray-200"
                                                @error="handleImageError"
                                            />
                                        </div>
                                        <!-- Product Details -->
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-lg font-medium text-gray-900 mb-1">
                                                {{ item.product_name }}
                                            </h4>
                                            <div class="text-sm text-gray-600 space-y-1">
                                                <p>Aantal: {{ item.quantity }}</p>
                                                <p>Prijs per stuk: €{{ Number(item.price).toFixed(2) }}</p>
                                                <p v-if="!item.product?.is_active" class="text-red-600">
                                                    ⚠️ Product niet meer beschikbaar
                                                </p>
                                            </div>
                                        </div>
                                        <!-- Item Totaal -->
                                        <div class="flex-shrink-0 text-right">
                                            <p class="text-lg font-semibold text-gray-900">
                                                €{{ Number(item.total).toFixed(2) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bezorginformatie Sectie -->
                        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                            <!-- Delivery Header -->
                            <div class="bg-blue-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                                    <TruckIcon class="w-4 h-4 sm:w-5 sm:h-5 mr-2 text-blue-600" />
                                    Bezorginformatie
                                </h3>
                            </div>
                            <div class="p-4 sm:p-6">
                                <!-- Delivery Details Grid -->
                                <div class="space-y-4 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-6">
                                    <!-- Bezorgadres -->
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2 flex items-center text-sm sm:text-base">
                                            <MapPinIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-1.5 text-gray-500" />
                                            Bezorgadres
                                        </h4>
                                        <div class="text-sm sm:text-base text-gray-700 space-y-1">
                                            <p>{{ formatAddressLine(deliveryAddress) }}</p>
                                            <p>{{ deliveryAddress.postal_code }} {{ deliveryAddress.city }}</p>
                                            <p v-if="deliveryAddress.country && deliveryAddress.country !== 'Netherlands'">
                                                {{ deliveryAddress.country }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Bezorgmoment -->
                                    <div v-if="deliverySlot">
                                        <h4 class="font-semibold text-gray-900 mb-2 flex items-center text-sm sm:text-base">
                                            <CalendarDaysIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-1.5 text-gray-500" />
                                            Bezorgmoment
                                        </h4>
                                        <div class="text-sm sm:text-base text-gray-700 space-y-1">
                                            <p class="font-medium">{{ deliverySlot.formatted_date }}</p>
                                            <p>{{ deliverySlot.formatted_time }}</p>
                                            <p class="text-xs sm:text-sm text-gray-600">
                                                Geschatte levering: {{ order.estimated_delivery }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Opmerkingen -->
                                <div v-if="order.order_notes" class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-gray-200">
                                    <h4 class="font-semibold text-gray-900 mb-2 flex items-center text-sm sm:text-base">
                                        <ChatBubbleLeftEllipsisIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-1.5 text-gray-500" />
                                        Opmerkingen
                                    </h4>
                                    <p class="text-sm sm:text-base text-gray-700 bg-yellow-50 p-3 rounded-lg border border-yellow-200">
                                        {{ order.order_notes }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Betaalinformatie Sectie -->
                        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                            <!-- Payment Header -->
                            <div class="bg-green-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                                    <CreditCardIcon class="w-4 h-4 sm:w-5 sm:h-5 mr-2 text-green-600" />
                                    Betaalinformatie
                                </h3>
                            </div>
                            <div class="p-4 sm:p-6">
                                <!-- Payment Details Grid -->
                                <div class="space-y-4 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-6">
                                    <!-- Betaalmethode -->
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2 text-sm sm:text-base">Betaalmethode</h4>
                                        <p class="text-sm sm:text-base text-gray-700">{{ formatPaymentMethod(order.payment_method) }}</p>
                                    </div>
                                    <!-- Betaalstatus -->
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2 text-sm sm:text-base">Betaalstatus</h4>
                                        <span :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            getPaymentStatusClasses(order.payment_status)
                                        ]">
                                            {{ formatPaymentStatus(order.payment_status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Sectie (1/3 breedte op desktop) -->
                    <div class="space-y-4 sm:space-y-8">
                        
                        <!-- Order Overzicht Card -->
                        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                            <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900">Bestelling overzicht</h3>
                            </div>
                            <div class="p-4 sm:p-6">
                                <!-- Pricing Breakdown -->
                                <div class="space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Subtotaal:</span>
                                        <span class="font-medium">€{{ Number(order.subtotal).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Bezorgkosten:</span>
                                        <span class="font-medium">€{{ Number(order.delivery_fee).toFixed(2) }}</span>
                                    </div>
                                    <div class="border-t border-gray-200 pt-3 flex justify-between text-base sm:text-lg font-bold">
                                        <span class="text-gray-900">Totaal:</span>
                                        <span class="text-green-600">€{{ Number(order.total).toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Acties Card -->
                        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                            <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900">Acties</h3>
                            </div>
                            <div class="p-4 sm:p-6 space-y-3 sm:space-y-4">
                                <!-- Track Order Knop (conditioneel) -->
                                <PrimaryButton
                                    v-if="order.can_track"
                                    @click="$inertia.visit(`/orders/${order.id}/track`)"
                                    class="w-full justify-center text-sm sm:text-base"
                                >
                                    <TruckIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-2" />
                                    Bestelling volgen
                                </PrimaryButton>
                                <!-- Print Order Knop -->
                                <SecondaryButton
                                    @click="printOrder"
                                    class="w-full justify-center text-sm sm:text-base"
                                >
                                    <PrinterIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-2" />
                                    Bestelling printen
                                </SecondaryButton>
                                <!-- Resend Email Knop -->
                                <SecondaryButton
                                    @click="sendConfirmationEmail"
                                    :disabled="emailSending"
                                    class="w-full justify-center text-sm sm:text-base"
                                >
                                    <EnvelopeIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-2" />
                                    {{ emailSending ? 'Versturen...' : 'Bevestigingsmail opnieuw' }}
                                </SecondaryButton>
                                <!-- Cancel Order Knop (conditioneel) -->
                                <DangerButton
                                    v-if="order.can_cancel"
                                    @click="showCancelModal = true"
                                    class="w-full justify-center text-sm sm:text-base"
                                >
                                    <XMarkIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-2" />
                                    Bestelling annuleren
                                </DangerButton>
                            </div>
                        </div>

                        <!-- Help en Support Card -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 sm:p-6">
                            <h4 class="font-semibold text-blue-900 mb-2 flex items-center text-sm sm:text-base">
                                <QuestionMarkCircleIcon class="w-4 h-4 sm:w-5 sm:h-5 mr-2" />
                                Hulp nodig?
                            </h4>
                            <p class="text-xs sm:text-sm text-blue-700 mb-3 sm:mb-4">
                                Heb je vragen over je bestelling? Neem contact met ons op.
                            </p>
                            <SecondaryButton class="text-xs sm:text-sm w-full justify-center">
                                Contact opnemen
                            </SecondaryButton>
                        </div>
                    </div>
                </div>

                <!-- Navigation Knoppen -->
                <div class="mt-6 sm:mt-8 flex flex-col space-y-3 sm:space-y-0 sm:flex-row sm:justify-between">
                    <!-- Terug naar Orders -->
                    <SecondaryButton
                        @click="$inertia.visit('/orders')"
                        class="w-full sm:w-auto justify-center sm:justify-start text-sm sm:text-base"
                    >
                        <ArrowLeftIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-2" />
                        Terug naar bestellingen
                    </SecondaryButton>
                    <!-- Verder Winkelen -->
                    <SecondaryButton
                        @click="$inertia.visit('/categories')"
                        class="w-full sm:w-auto justify-center sm:justify-start text-sm sm:text-base"
                    >
                        Verder winkelen
                        <ArrowRightIcon class="w-3 h-3 sm:w-4 sm:h-4 ml-2" />
                    </SecondaryButton>
                </div>
            </div>
        </div>

        <!-- Order Annuleren Bevestiging Modal -->
        <Modal :show="showCancelModal" @close="showCancelModal = false">
            <div class="p-4 sm:p-6">
                <!-- Modal Header met Warning -->
                <div class="flex items-start mb-4">
                    <ExclamationTriangleIcon class="h-6 w-6 sm:h-8 sm:w-8 text-red-500 mr-3 sm:mr-4 flex-shrink-0" />
                    <div class="flex-1">
                        <h3 class="text-base sm:text-lg font-medium text-gray-900">
                            Bestelling annuleren
                        </h3>
                        <p class="mt-2 text-sm text-gray-600">
                            Weet je zeker dat je deze bestelling wilt annuleren?
                            Deze actie kan niet ongedaan worden gemaakt.
                        </p>
                    </div>
                </div>
                <!-- Modal Actie Knoppen -->
                <div class="flex flex-col space-y-2 sm:space-y-0 sm:flex-row sm:justify-end sm:space-x-3">
                    <SecondaryButton
                        @click="showCancelModal = false"
                        class="w-full sm:w-auto justify-center order-2 sm:order-1"
                    >
                        Sluiten
                    </SecondaryButton>
                    <DangerButton
                        @click="cancelOrder"
                        :disabled="cancelling"
                        class="w-full sm:w-auto justify-center order-1 sm:order-2"
                    >
                        {{ cancelling ? 'Annuleren...' : 'Ja, annuleer bestelling' }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Print Optimization Styles */
@media print {
    /* Verberg elementen die niet geprint moeten worden */
    .no-print,
    button,
    nav,
    header {
        display: none !important;
    }

    /* Optimalisatie voor print weergave */
    body {
        background: white !important;
    }

    .bg-gray-50,
    .bg-blue-50,
    .bg-green-50 {
        background: white !important;
    }
}

/* Text Truncation Utility */
.line-clamp-2 {
    display: -webkit-box;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>