/**
 * Bestandsnaam: Track.vue (Pages/Orders)
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-07-02
 * Tijd: 00:15:49
 * Doel: Order tracking pagina voor klanten met real-time bezorgstatus monitoring. Bevat visuele progress
 *       tracking met animated progress bar, step-by-step bezorgproces weergave, delivery slot informatie,
 *       en interactieve refresh functionaliteit. Inclusief responsive design, status visualisatie met
 *       icons, bezorgtips, en complete navigation flow voor optimale customer experience tijdens het
 *       bezorgproces van e-commerce bestellingen.
 */

<script setup>
// Vue compositie API imports voor reactive state management
import { ref } from 'vue'

// Inertia.js imports voor navigatie en routing
import { Head, router } from '@inertiajs/vue3'

// Layout en component imports
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

// Heroicons imports voor UI iconen en status indicators
import {
    ArrowLeftIcon,
    CheckCircleIcon,
    XMarkIcon,
    TruckIcon,
    CalendarDaysIcon,
    ClockIcon,
    LightBulbIcon,
    ArrowPathIcon,
    DocumentTextIcon,
    ListBulletIcon,
    // Status specific icons voor tracking steps
    ShoppingCartIcon,
    ClipboardDocumentCheckIcon,
    CogIcon,
    HomeIcon
} from '@heroicons/vue/24/outline'

// ========== PROPS DEFINITIE ==========

// Props van server - tracking data en delivery informatie
const props = defineProps({
    order: Object,                            // Complete order object met tracking status
    deliverySlot: Object,                     // Geplande bezorgtijd en datum informatie
    trackingSteps: Array,                     // Array van tracking steps met status en timestamps
    currentStep: Number                       // Index van huidige actieve tracking step
})

// ========== REACTIVE STATE MANAGEMENT ==========

// Loading state voor refresh functionaliteit
const refreshing = ref(false);               // Loading indicator tijdens tracking data refresh

// ========== UTILITY FUNCTIES ==========

/**
 * Formatteert datum/tijd naar Nederlandse locale weergave
 * Gebruikt voor tracking timestamps en delivery slot weergave
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

// ========== STATUS STYLING FUNCTIES ==========

/**
 * Bepaalt CSS classes voor status banner styling
 * Gebruikt voor grote status indicator met icon en beschrijving
 * @param {string} status - Order status identifier
 * @returns {string} CSS classes voor status banner background en text kleur
 */
const getStatusBannerClasses = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-600',           // In afwachting - geel
        'confirmed': 'bg-blue-100 text-blue-600',             // Bevestigd - blauw
        'processing': 'bg-purple-100 text-purple-600',        // In verwerking - paars
        'out_for_delivery': 'bg-indigo-100 text-indigo-600',  // Onderweg - indigo
        'delivered': 'bg-green-100 text-green-600',           // Bezorgd - groen
        'cancelled': 'bg-red-100 text-red-600'                // Geannuleerd - rood
    }
    return classes[status] || 'bg-gray-100 text-gray-600'     // Default fallback
}

/**
 * Bepaalt welk icon gebruikt wordt voor specifieke order status
 * Gebruikt voor visuele status representatie in banner en steps
 * @param {string} status - Order status identifier
 * @returns {Component} Vue icon component voor status
 */
const getStatusIcon = (status) => {
    const icons = {
        'pending': ShoppingCartIcon,                          // Winkelwagen voor pending
        'confirmed': ClipboardDocumentCheckIcon,              // Checklist voor confirmed
        'processing': CogIcon,                                // Tandwiel voor processing
        'out_for_delivery': TruckIcon,                        // Vrachtwagen voor onderweg
        'delivered': HomeIcon,                                // Huis voor bezorgd
        'cancelled': XMarkIcon                                // X voor geannuleerd
    }
    return icons[status] || ShoppingCartIcon                 // Default fallback
}

/**
 * Genereert gebruiksvriendelijke status beschrijving
 * Gebruikt voor informatieve tekst onder status titel
 * @param {string} status - Order status identifier
 * @returns {string} Nederlandse beschrijving van status
 */
const getStatusDescription = (status) => {
    const descriptions = {
        'pending': 'Je bestelling wordt nog verwerkt',
        'confirmed': 'Je bestelling is bevestigd en wordt voorbereid',
        'processing': 'Je bestelling wordt ingepakt',
        'out_for_delivery': 'Je bestelling is onderweg naar je adres',
        'delivered': 'Je bestelling is succesvol bezorgd',
        'cancelled': 'Je bestelling is geannuleerd'
    }
    return descriptions[status] || 'Status onbekend'          // Default fallback
}

// ========== PROGRESS BAR FUNCTIES ==========

/**
 * Berekent progress percentage gebaseerd op order status
 * Gebruikt voor animated progress bar weergave
 * @param {string} status - Order status identifier
 * @returns {number} Percentage tussen 0-100 voor progress bar
 */
const getProgressPercentage = (status) => {
    const percentages = {
        'pending': 10,                                        // 10% - net begonnen
        'confirmed': 25,                                      // 25% - bevestigd
        'processing': 50,                                     // 50% - halverwege
        'out_for_delivery': 75,                               // 75% - bijna klaar
        'delivered': 100,                                     // 100% - voltooid
        'cancelled': 0                                        // 0% - geannuleerd
    }
    return percentages[status] || 0
}

/**
 * Bepaalt progress bar kleur gebaseerd op order status
 * Gebruikt voor visuele feedback van progress state
 * @param {string} status - Order status identifier
 * @returns {string} CSS class voor progress bar kleur
 */
const getProgressBarClass = (status) => {
    if (status === 'cancelled') return 'bg-red-400'          // Rood voor geannuleerd
    if (status === 'delivered') return 'bg-green-400'        // Groen voor voltooid
    return 'bg-blue-400'                                     // Blauw voor actieve progress
}

// ========== TRACKING STEP FUNCTIES ==========

/**
 * Bepaalt CSS classes voor tracking step circles
 * Gebruikt voor visuele weergave van step status (completed, current, pending)
 * @param {string} status - Step status (completed, current, pending, cancelled)
 * @returns {string} CSS classes voor step circle styling
 */
const getStepClasses = (status) => {
    const classes = {
        'completed': 'border-green-400 bg-green-400 text-white',    // Voltooid - groen
        'current': 'border-blue-400 bg-blue-400 text-white',       // Huidige - blauw
        'cancelled': 'border-red-400 bg-red-400 text-white',       // Geannuleerd - rood
        'pending': 'border-gray-300 bg-white text-gray-300'        // Wachtend - grijs
    }
    return classes[status] || 'border-gray-300 bg-white text-gray-300'
}

/**
 * Mapped icon namen naar Vue icon componenten voor tracking steps
 * Gebruikt voor flexibele icon weergave in tracking steps
 * @param {string} iconName - String identifier voor icon
 * @returns {Component} Vue icon component
 */
const getStepIcon = (iconName) => {
    const icons = {
        'check-circle': CheckCircleIcon,
        'clipboard-check': ClipboardDocumentCheckIcon,
        'cog': CogIcon,
        'truck': TruckIcon,
        'home': HomeIcon
    }
    return icons[iconName] || CheckCircleIcon                 // Default fallback
}

// ========== TRACKING REFRESH FUNCTIONALITEIT ==========

/**
 * Refresht tracking data door nieuwe request naar server
 * Async functie met loading state voor real-time status updates
 * Gebruikt Inertia's reload functie voor partial updates
 */
const refreshTracking = async () => {
    refreshing.value = true
    
    try {
        // Alleen order en trackingSteps data refreshen voor efficiency
        await router.reload({ only: ['order', 'trackingSteps'] })
    } catch (error) {
        console.error('Error refreshing tracking:', error)
    } finally {
        refreshing.value = false
    }
}
</script>

<template>
    <!-- Authenticated Layout Wrapper -->
    <AuthenticatedLayout>
        <!-- Dynamic Page Title met Order Nummer -->
        <Head :title="`Volgen: #${order.order_number}`" />

        <!-- Page Header Template Slot -->
        <template #header>
            <div class="flex flex-col space-y-2 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between">
                <!-- Header Titel en Order Info -->
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg sm:text-xl font-semibold leading-tight text-gray-800">
                        Bestelling volgen
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-600 mt-1 truncate">
                        #{{ order.order_number }} • {{ formatDateTime(order.created_at) }}
                    </p>
                </div>
                <!-- Terug naar Details Knop -->
                <SecondaryButton 
                    @click="$inertia.visit(`/orders/${order.id}`)"
                    class="text-sm sm:text-base"
                >
                    <ArrowLeftIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-2" />
                    Terug naar details
                </SecondaryButton>
            </div>
        </template>

        <!-- Main Content Container -->
        <div class="py-4 sm:py-12">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                
                <!-- Huidige Status Banner -->
                <div class="mb-6 sm:mb-8 bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-4 sm:p-6">
                        <!-- Status Info Header -->
                        <div class="flex flex-col space-y-3 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between mb-4">
                            <!-- Status Icon en Beschrijving -->
                            <div class="flex items-center">
                                <!-- Status Icon Circle -->
                                <div :class="[
                                    'w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center mr-3 sm:mr-4',
                                    getStatusBannerClasses(order.status)
                                ]">
                                    <component :is="getStatusIcon(order.status)" class="w-5 h-5 sm:w-6 sm:h-6" />
                                </div>
                                <!-- Status Text -->
                                <div>
                                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900">
                                        {{ order.status_display }}
                                    </h3>
                                    <p class="text-sm sm:text-base text-gray-600">
                                        {{ getStatusDescription(order.status) }}
                                    </p>
                                </div>
                            </div>
                            <!-- Geschatte Bezorgdatum (indien beschikbaar) -->
                            <div v-if="deliverySlot" class="text-left sm:text-right">
                                <p class="text-xs sm:text-sm text-gray-600">Geschatte bezorging</p>
                                <p class="font-semibold text-sm sm:text-base text-gray-900">{{ order.estimated_delivery }}</p>
                            </div>
                        </div>

                        <!-- Animated Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div 
                                :class="[
                                    'h-2 rounded-full transition-all duration-500 ease-in-out',
                                    getProgressBarClass(order.status)
                                ]"
                                :style="{ width: getProgressPercentage(order.status) + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Tracking Steps Timeline -->
                <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Steps Header -->
                    <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                            Bezorgstatus
                        </h3>
                    </div>
                    
                    <!-- Steps Timeline -->
                    <div class="p-4 sm:p-6">
                        <div class="space-y-6 sm:space-y-8">
                            <!-- Tracking Step (herhaalt voor elke step) -->
                            <div
                                v-for="(step, index) in trackingSteps"
                                :key="index"
                                class="relative flex items-start"
                            >
                                <!-- Connecting Line tussen Steps -->
                                <div
                                    v-if="index < trackingSteps.length - 1"
                                    :class="[
                                        'absolute left-3 sm:left-4 top-6 sm:top-8 w-0.5 h-12 sm:h-16',
                                        step.status === 'completed' ? 'bg-green-400' : 'bg-gray-200'
                                    ]"
                                ></div>

                                <!-- Step Icon Circle -->
                                <div class="relative">
                                    <div :class="[
                                        'w-6 h-6 sm:w-8 sm:h-8 rounded-full flex items-center justify-center border-2',
                                        getStepClasses(step.status)
                                    ]">
                                        <!-- Completed Step Icon -->
                                        <component
                                            v-if="step.status === 'completed'"
                                            :is="CheckCircleIcon"
                                            class="w-3 h-3 sm:w-5 sm:h-5"
                                        />
                                        <!-- Current Step Icon -->
                                        <component
                                            v-else-if="step.status === 'current'"
                                            :is="getStepIcon(step.icon)"
                                            class="w-3 h-3 sm:w-4 sm:h-4"
                                        />
                                        <!-- Cancelled Step Icon -->
                                        <component
                                            v-else-if="step.status === 'cancelled'"
                                            :is="XMarkIcon"
                                            class="w-3 h-3 sm:w-4 sm:h-4"
                                        />
                                        <!-- Pending Step Dot -->
                                        <div
                                            v-else
                                            class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-gray-300"
                                        ></div>
                                    </div>
                                </div>

                                <!-- Step Content -->
                                <div class="ml-3 sm:ml-4 flex-1">
                                    <!-- Step Titel en Timestamp -->
                                    <div class="flex flex-col space-y-1 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between">
                                        <!-- Step Titel met Conditionale Styling -->
                                        <h4 :class="[
                                            'text-sm font-medium',
                                            step.status === 'completed' || step.status === 'current'
                                                ? 'text-gray-900'
                                                : step.status === 'cancelled'
                                                    ? 'text-red-600'
                                                    : 'text-gray-500'
                                        ]">
                                            {{ step.title }}
                                        </h4>
                                        <!-- Step Timestamp (indien beschikbaar) -->
                                        <span
                                            v-if="step.date"
                                            class="text-xs text-gray-500"
                                        >
                                            {{ formatDateTime(step.date) }}
                                        </span>
                                    </div>
                                    <!-- Step Beschrijving -->
                                    <p :class="[
                                        'text-xs sm:text-sm mt-1',
                                        step.status === 'completed' || step.status === 'current'
                                            ? 'text-gray-600'
                                            : step.status === 'cancelled'
                                                ? 'text-red-500'
                                                : 'text-gray-400'
                                    ]">
                                        {{ step.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bezorgmoment Informatie -->
                <div v-if="deliverySlot" class="mt-6 sm:mt-8 bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Delivery Header -->
                    <div class="bg-blue-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                            <TruckIcon class="w-4 h-4 sm:w-5 sm:h-5 mr-2 text-blue-600" />
                            Bezorgmoment
                        </h3>
                    </div>
                    
                    <div class="p-4 sm:p-6">
                        <!-- Delivery Slot Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                            <!-- Bezorgdatum Card -->
                            <div class="flex items-center p-3 sm:p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <CalendarDaysIcon class="w-6 h-6 sm:w-8 sm:h-8 text-blue-600 mr-3 sm:mr-4" />
                                <div>
                                    <p class="font-semibold text-sm sm:text-base text-blue-900">{{ deliverySlot.formatted_date }}</p>
                                    <p class="text-xs sm:text-sm text-blue-700">Bezorgdatum</p>
                                </div>
                            </div>
                            
                            <!-- Tijdslot Card -->
                            <div class="flex items-center p-3 sm:p-4 bg-green-50 rounded-lg border border-green-200">
                                <ClockIcon class="w-6 h-6 sm:w-8 sm:h-8 text-green-600 mr-3 sm:mr-4" />
                                <div>
                                    <p class="font-semibold text-sm sm:text-base text-green-900">{{ deliverySlot.formatted_time }}</p>
                                    <p class="text-xs sm:text-sm text-green-700">Tijdslot</p>
                                </div>
                            </div>
                        </div>

                        <!-- Bezorgtips Sectie -->
                        <div class="mt-4 sm:mt-6 p-3 sm:p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <h4 class="font-semibold text-sm sm:text-base text-yellow-900 mb-2 flex items-center">
                                <LightBulbIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-2" />
                                Bezorgtips
                            </h4>
                            <!-- Tips Lijst -->
                            <ul class="text-xs sm:text-sm text-yellow-800 space-y-1">
                                <li>• Zorg dat je thuis bent tijdens het bezorgtijdslot</li>
                                <li>• Houd je telefoon bij de hand voor contact van de bezorger</li>
                                <li>• Controleer je bestelling bij ontvangst</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Tracking Acties -->
                <div class="mt-6 sm:mt-8 flex flex-col space-y-3 sm:space-y-0 sm:flex-row sm:gap-4 sm:justify-center">
                    <!-- Refresh Tracking Knop -->
                    <SecondaryButton 
                        @click="refreshTracking" 
                        :disabled="refreshing"
                        class="w-full sm:w-auto justify-center text-sm sm:text-base"
                    >
                        <ArrowPathIcon :class="['w-3 h-3 sm:w-4 sm:h-4 mr-2', refreshing && 'animate-spin']" />
                        {{ refreshing ? 'Vernieuwen...' : 'Status vernieuwen' }}
                    </SecondaryButton>
                    
                    <!-- Besteldetails Knop -->
                    <SecondaryButton 
                        @click="$inertia.visit(`/orders/${order.id}`)"
                        class="w-full sm:w-auto justify-center text-sm sm:text-base"
                    >
                        <DocumentTextIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-2" />
                        Besteldetails bekijken
                    </SecondaryButton>
                    
                    <!-- Alle Bestellingen Knop -->
                    <SecondaryButton 
                        @click="$inertia.visit('/orders')"
                        class="w-full sm:w-auto justify-center text-sm sm:text-base"
                    >
                        <ListBulletIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-2" />
                        Alle bestellingen
                    </SecondaryButton>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Custom Spinner Animation voor Refresh Button */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>