<!-- Pages/Orders/Show.vue -->
<template>
    <AuthenticatedLayout>
        <Head :title="`Bestelling #${order.order_number}`" />

        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Bestelling #{{ order.order_number }}
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Geplaatst op {{ formatDateTime(order.created_at) }}
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <span :class="[
                        'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                        getStatusClasses(order.status)
                    ]">
                        {{ order.status_display }}
                    </span>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Order Items -->
                        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Bestelde items ({{ orderItems.length }})
                                </h3>
                            </div>
                            
                            <div class="divide-y divide-gray-200">
                                <div
                                    v-for="item in orderItems"
                                    :key="item.id"
                                    class="p-6 flex items-start space-x-4"
                                >
                                    <!-- Product Image -->
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
                                            <p>Prijs per stuk: €{{ item.price.toFixed(2) }}</p>
                                            <p v-if="!item.product?.is_active" class="text-red-600">
                                                ⚠️ Product niet meer beschikbaar
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Item Total -->
                                    <div class="flex-shrink-0 text-right">
                                        <p class="text-lg font-semibold text-gray-900">
                                            €{{ item.total.toFixed(2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Information -->
                        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                            <div class="bg-blue-50 px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <TruckIcon class="w-5 h-5 mr-2 text-blue-600" />
                                    Bezorginformatie
                                </h3>
                            </div>
                            
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Delivery Address -->
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                                            <MapPinIcon class="w-4 h-4 mr-1.5 text-gray-500" />
                                            Bezorgadres
                                        </h4>
                                        <div class="text-gray-700 space-y-1">
                                            <p>{{ formatAddressLine(deliveryAddress) }}</p>
                                            <p>{{ deliveryAddress.postal_code }} {{ deliveryAddress.city }}</p>
                                            <p v-if="deliveryAddress.country && deliveryAddress.country !== 'Netherlands'">
                                                {{ deliveryAddress.country }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Delivery Slot -->
                                    <div v-if="deliverySlot">
                                        <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                                            <CalendarDaysIcon class="w-4 h-4 mr-1.5 text-gray-500" />
                                            Bezorgmoment
                                        </h4>
                                        <div class="text-gray-700 space-y-1">
                                            <p class="font-medium">{{ deliverySlot.formatted_date }}</p>
                                            <p>{{ deliverySlot.formatted_time }}</p>
                                            <p class="text-sm text-gray-600">
                                                Geschatte levering: {{ order.estimated_delivery }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Notes -->
                                <div v-if="order.order_notes" class="mt-6 pt-6 border-t border-gray-200">
                                    <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                                        <ChatBubbleLeftEllipsisIcon class="w-4 h-4 mr-1.5 text-gray-500" />
                                        Opmerkingen
                                    </h4>
                                    <p class="text-gray-700 bg-yellow-50 p-3 rounded-lg border border-yellow-200">
                                        {{ order.order_notes }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                            <div class="bg-green-50 px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <CreditCardIcon class="w-5 h-5 mr-2 text-green-600" />
                                    Betaalinformatie
                                </h3>
                            </div>
                            
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2">Betaalmethode</h4>
                                        <p class="text-gray-700">{{ formatPaymentMethod(order.payment_method) }}</p>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2">Betaalstatus</h4>
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

                    <!-- Sidebar -->
                    <div class="space-y-8">
                        <!-- Order Summary -->
                        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Bestelling overzicht</h3>
                            </div>
                            
                            <div class="p-6">
                                <div class="space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Subtotaal:</span>
                                        <span class="font-medium">€{{ order.subtotal.toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Bezorgkosten:</span>
                                        <span class="font-medium">€{{ order.delivery_fee.toFixed(2) }}</span>
                                    </div>
                                    <div class="border-t border-gray-200 pt-3 flex justify-between text-lg font-bold">
                                        <span class="text-gray-900">Totaal:</span>
                                        <span class="text-green-600">€{{ order.total.toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Acties</h3>
                            </div>
                            
                            <div class="p-6 space-y-4">
                                <PrimaryButton
                                    v-if="order.can_track"
                                    @click="$inertia.visit(`/orders/${order.id}/track`)"
                                    class="w-full justify-center"
                                >
                                    <TruckIcon class="w-4 h-4 mr-2" />
                                    Bestelling volgen
                                </PrimaryButton>

                                <SecondaryButton
                                    @click="printOrder"
                                    class="w-full justify-center"
                                >
                                    <PrinterIcon class="w-4 h-4 mr-2" />
                                    Bestelling printen
                                </SecondaryButton>

                                <SecondaryButton
                                    @click="sendConfirmationEmail"
                                    :disabled="emailSending"
                                    class="w-full justify-center"
                                >
                                    <EnvelopeIcon class="w-4 h-4 mr-2" />
                                    {{ emailSending ? 'Versturen...' : 'Bevestigingsmail opnieuw' }}
                                </SecondaryButton>

                                <DangerButton
                                    v-if="order.can_cancel"
                                    @click="showCancelModal = true"
                                    class="w-full justify-center"
                                >
                                    <XMarkIcon class="w-4 h-4 mr-2" />
                                    Bestelling annuleren
                                </DangerButton>
                            </div>
                        </div>

                        <!-- Help -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                            <h4 class="font-semibold text-blue-900 mb-2 flex items-center">
                                <QuestionMarkCircleIcon class="w-5 h-5 mr-2" />
                                Hulp nodig?
                            </h4>
                            <p class="text-sm text-blue-700 mb-4">
                                Heb je vragen over je bestelling? Neem contact met ons op.
                            </p>
                            <SecondaryButton class="text-sm w-full justify-center">
                                Contact opnemen
                            </SecondaryButton>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="mt-8 flex justify-between">
                    <SecondaryButton @click="$inertia.visit('/orders')">
                        <ArrowLeftIcon class="w-4 h-4 mr-2" />
                        Terug naar bestellingen
                    </SecondaryButton>
                    
                    <SecondaryButton @click="$inertia.visit('/categories')">
                        Verder winkelen
                        <ArrowRightIcon class="w-4 h-4 ml-2" />
                    </SecondaryButton>
                </div>
            </div>
        </div>

        <!-- Cancel Order Modal -->
        <Modal :show="showCancelModal" @close="showCancelModal = false">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <ExclamationTriangleIcon class="h-8 w-8 text-red-500 mr-4" />
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">
                            Bestelling annuleren
                        </h3>
                        <p class="mt-2 text-sm text-gray-600">
                            Weet je zeker dat je deze bestelling wilt annuleren?
                            Deze actie kan niet ongedaan worden gemaakt.
                        </p>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <SecondaryButton @click="showCancelModal = false">
                        Sluiten
                    </SecondaryButton>
                    <DangerButton
                        @click="cancelOrder"
                        :disabled="cancelling"
                    >
                        {{ cancelling ? 'Annuleren...' : 'Ja, annuleer bestelling' }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import Modal from '@/Components/Modal.vue'
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

// Props
const props = defineProps({
    order: Object,
    orderItems: Array,
    deliveryAddress: Object,
    deliverySlot: Object
})

// Reactive state
const showCancelModal = ref(false)
const cancelling = ref(false)
const emailSending = ref(false)

// Methods
const formatDateTime = (dateTime) => {
    return new Date(dateTime).toLocaleDateString('nl-NL', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getStatusClasses = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'confirmed': 'bg-blue-100 text-blue-800',
        'processing': 'bg-purple-100 text-purple-800',
        'out_for_delivery': 'bg-indigo-100 text-indigo-800',
        'delivered': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusClasses = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'processing': 'bg-blue-100 text-blue-800',
        'completed': 'bg-green-100 text-green-800',
        'failed': 'bg-red-100 text-red-800',
        'cancelled': 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatPaymentMethod = (method) => {
    const methods = {
        'ideal': 'iDEAL',
        'card': 'Creditcard',
        'cash': 'Contant bij levering'
    }
    return methods[method] || method
}

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

const getImageUrl = (imagePath) => {
    if (!imagePath) return '/images/placeholder-product.jpg'
    if (imagePath.startsWith('http') || imagePath.startsWith('/')) return imagePath
    return `/storage/${imagePath}`
}

const handleImageError = (event) => {
    event.target.src = '/images/placeholder-product.jpg'
}

const printOrder = () => {
    window.print()
}

const sendConfirmationEmail = async () => {
    emailSending.value = true
    
    try {
        const response = await axios.post(`/orders/${props.order.id}/send-confirmation`)
        
        if (response.data.success) {
            // Show success message (you could use a toast notification here)
            alert('Bevestigingsmail is verzonden!')
        }
    } catch (error) {
        console.error('Error sending confirmation email:', error)
        alert('Er is een fout opgetreden bij het versturen van de bevestigingsmail.')
    } finally {
        emailSending.value = false
    }
}

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

<style scoped>
@media print {
    /* Hide elements that shouldn't be printed */
    .no-print,
    button,
    nav,
    header {
        display: none !important;
    }
    
    /* Optimize for printing */
    body {
        background: white !important;
    }
    
    .bg-gray-50,
    .bg-blue-50,
    .bg-green-50 {
        background: white !important;
    }
}
</style>