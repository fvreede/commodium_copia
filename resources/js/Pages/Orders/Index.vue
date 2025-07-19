/**
 * Bestandsnaam: Index.vue (Pages/Orders)
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-07-02
 * Tijd: 00:15:49
 * Doel: Overzichtspagina voor klant bestellingen met geavanceerde filtering, zoekfunctionaliteit, en order
 *       management. Bevat responsive design voor mobile/desktop, status indicatoren, order tracking opties,
 *       cancel functionaliteit via modal, en geoptimaliseerde paginatie. Volledig order management systeem
 *       voor klanten met real-time filtering, debounced search, en enhanced UX voor e-commerce platform.
 */

<script setup>
// Vue compositie API imports voor reactive state en computed properties
import { ref, computed, watch } from 'vue'

// Inertia.js imports voor navigatie en routing
import { Head, Link, router } from '@inertiajs/vue3'

// Layout en component imports
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import Modal from '@/Components/Modal.vue'

// Heroicons imports voor UI iconen
import {
    ShoppingBagIcon,
    EyeIcon,
    TruckIcon,
    XMarkIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

// ========== PROPS DEFINITIE ==========

// Props van server - orders data, filters en status opties voor order management
const props = defineProps({
    orders: Object,                           // Paginated orders object met data array en links
    filters: Object,                          // Huidige actieve filters (status, search)
    statusOptions: Array                      // Beschikbare order status opties voor dropdown
})

// ========== REACTIVE STATE MANAGEMENT ==========

// Filter en zoek state
const selectedStatus = ref(props.filters.status || 'all');    // Geselecteerde status filter
const searchQuery = ref(props.filters.search || '');         // Huidige zoekterm
const showingCancelModal = ref(false);                       // Cancel modal visibility state
const orderToCancel = ref(null);                            // Order object dat geannuleerd wordt
const isProcessing = ref(false);                            // Loading state voor cancel operatie

// ========== COMPUTED PROPERTIES ==========

/**
 * Controleert of er actieve filters zijn toegepast
 * Gebruikt voor conditionele weergave van lege staat berichten
 * @returns {boolean} True als er filters actief zijn
 */
const hasFilters = computed(() => {
    return selectedStatus.value !== 'all' || searchQuery.value.length > 0
})

/**
 * Geoptimaliseerde paginatie links voor mobile weergave
 * Toont alleen essentiële navigation (prev, current, next) voor mobile UX
 * @returns {Array} Gefilterde array van pagination links voor mobile
 */
const mobilePaginationLinks = computed(() => {
    const links = props.orders.links || []
    if (links.length <= 3) return links
    
    const prevLink = links[0]                                // Previous page link
    const nextLink = links[links.length - 1]                // Next page link
    const currentPageLink = links.find(link => link.active) // Huidige actieve page link
    
    return [prevLink, currentPageLink, nextLink].filter(Boolean)
})

// ========== UTILITY FUNCTIES ==========

/**
 * Formatteert datum/tijd naar Nederlandse locale weergave
 * Gebruikt voor consistente datum weergave in order lijst
 * @param {string} dateTime - ISO datum string van server
 * @returns {string} Geformatteerde datum en tijd string
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

// ========== FILTER EN ZOEK FUNCTIONALITEIT ==========

/**
 * Past filters toe door nieuwe request naar server te sturen
 * Behoudt state en vervangt URL voor betere navigatie ervaring
 */
const applyFilters = () => {
    router.get('/orders', {
        status: selectedStatus.value,
        search: searchQuery.value
    }, {
        preserveState: true,                  // Behoud component state
        replace: true                         // Vervang URL in plaats van nieuwe history entry
    })
}

/**
 * Wist alle actieve filters en reset naar default state
 * Navigeert terug naar ongevilterde order lijst
 */
const clearFilters = () => {
    selectedStatus.value = 'all'
    searchQuery.value = ''
    router.get('/orders', {}, {
        preserveState: true,
        replace: true
    })
}

// ========== DEBOUNCED SEARCH FUNCTIONALITEIT ==========

// Timeout reference voor debounced search
let searchTimeout

/**
 * Debounced search functie om excessive API calls te voorkomen
 * Wacht 500ms na laatste input voordat filters worden toegepast
 */
const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        applyFilters()
    }, 500)                                   // 500ms delay voor debouncing
}

// ========== ORDER CANCEL FUNCTIONALITEIT ==========

/**
 * Opent cancel bevestiging modal voor specifieke order
 * Slaat order referentie op voor gebruik in cancel operatie
 * @param {Object} order - Order object dat geannuleerd wordt
 */
const showCancelModal = (order) => {
    orderToCancel.value = order
    showingCancelModal.value = true
}

/**
 * Voert order annulering uit met loading state en error handling
 * Stuurt PATCH request naar server en sluit modal bij succes
 */
const cancelOrder = async () => {
    if (!orderToCancel.value) return
    
    isProcessing.value = true
    
    try {
        await router.patch(`/orders/${orderToCancel.value.id}/cancel`, {}, {
            onSuccess: () => {
                showingCancelModal.value = false
                orderToCancel.value = null
            },
            onFinish: () => {
                isProcessing.value = false
            }
        })
    } catch (error) {
        console.error('Error cancelling order:', error)
        isProcessing.value = false
    }
}
</script>

<template>
    <!-- Authenticated Layout Wrapper -->
    <AuthenticatedLayout>
        <!-- Page Title voor Browser Tab -->
        <Head title="Mijn Bestellingen" />

        <!-- Page Header Template Slot -->
        <template #header>
            <h2 class="text-lg sm:text-xl font-semibold leading-tight text-gray-800">
                Mijn Bestellingen
            </h2>
        </template>

        <!-- Main Content Container -->
        <div class="py-4 sm:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        
                        <!-- Filter Sectie met Responsive Grid Layout -->
                        <div class="mb-4 sm:mb-6 space-y-3 sm:space-y-0 sm:grid sm:grid-cols-2 lg:grid-cols-3 sm:gap-4">
                            
                            <!-- Status Filter Dropdown -->
                            <div>
                                <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-2">
                                    Filter op status
                                </label>
                                <select
                                    id="status-filter"
                                    v-model="selectedStatus"
                                    @change="applyFilters"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm sm:text-base"
                                >
                                    <!-- Dynamic Status Options Loop -->
                                    <option
                                        v-for="option in statusOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Search Input met Debounced Functionality -->
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                                    Zoeken
                                </label>
                                <input
                                    id="search"
                                    v-model="searchQuery"
                                    @input="debouncedSearch"
                                    type="text"
                                    placeholder="Bestelnummer of product..."
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm sm:text-base"
                                />
                            </div>

                            <!-- Clear Filters Knop -->
                            <div class="sm:flex sm:items-end">
                                <SecondaryButton
                                    @click="clearFilters"
                                    class="w-full sm:w-auto text-sm"
                                >
                                    <XMarkIcon class="w-4 h-4 mr-2" />
                                    Filters wissen
                                </SecondaryButton>
                            </div>
                        </div>

                        <!-- Orders Lijst of Lege Staat -->
                        <!-- Lege Staat Weergave -->
                        <div v-if="orders.data.length === 0" class="text-center py-8 sm:py-12">
                            <!-- Shopping Bag Icon -->
                            <ShoppingBagIcon class="w-12 h-12 sm:w-16 sm:h-16 text-gray-400 mx-auto mb-4" />
                            <!-- Lege Staat Titel -->
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">Geen bestellingen gevonden</h3>
                            <!-- Dynamisch Lege Staat Bericht -->
                            <p class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-6 px-4">
                                {{ hasFilters ? 'Probeer andere filters' : 'Je hebt nog geen bestellingen geplaatst' }}
                            </p>
                            <!-- Call-to-Action Knop -->
                            <PrimaryButton @click="$inertia.visit('/categories')" class="text-sm sm:text-base">
                                Verder winkelen
                            </PrimaryButton>
                        </div>

                        <!-- Orders Lijst Weergave -->
                        <div v-else class="space-y-3 sm:space-y-4">
                            <!-- Order Card (herhaalt voor elke order) -->
                            <div
                                v-for="order in orders.data"
                                :key="order.id"
                                class="border border-gray-200 rounded-lg sm:rounded-xl p-3 sm:p-6 hover:border-gray-300 transition-colors"
                            >
                                <!-- Order Header Sectie -->
                                <div class="mb-3 sm:mb-4">
                                    <!-- Order Nummer, Status en Totaal Layout -->
                                    <div class="flex flex-col space-y-2 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between">
                                        <!-- Order Nummer en Status Badge -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-col space-y-2 sm:space-y-0 sm:flex-row sm:items-center">
                                                <!-- Order Nummer -->
                                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mr-3">
                                                    #{{ order.order_number }}
                                                </h3>
                                                <!-- Dynamische Status Badge -->
                                                <span :class="[
                                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium w-fit',
                                                    getStatusClasses(order.status)
                                                ]">
                                                    {{ order.status_display }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Order Totaal en Items Count -->
                                        <div class="flex items-center justify-between sm:flex-col sm:items-end sm:justify-start">
                                            <!-- Geformatteerd Totaal Bedrag -->
                                            <div class="text-lg sm:text-xl font-bold text-gray-900">
                                                €{{ Number(order.total).toFixed(2) }}
                                            </div>
                                            <!-- Items Count met Pluralization -->
                                            <div class="text-xs sm:text-sm text-gray-600">
                                                {{ order.total_items }} {{ order.total_items === 1 ? 'item' : 'items' }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Order Meta Informatie -->
                                    <div class="mt-2 text-xs sm:text-sm text-gray-600 space-y-1">
                                        <!-- Geformatteerde Order Datum -->
                                        <p>{{ formatDateTime(order.created_at) }}</p>
                                        <!-- Delivery Slot Informatie (indien beschikbaar) -->
                                        <p v-if="order.delivery_slot" class="flex items-center">
                                            <TruckIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-1 text-gray-400" />
                                            Bezorging: {{ order.delivery_slot.formatted_date }} om {{ order.delivery_slot.formatted_time }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Order Items Preview Sectie -->
                                <div class="mb-3 sm:mb-4 p-2 sm:p-3 bg-gray-50 rounded-md">
                                    <!-- First Item met Additional Items Count -->
                                    <p class="text-xs sm:text-sm text-gray-700">
                                        <span class="font-medium">{{ order.first_item_name }}</span>
                                        <span v-if="order.item_count > 1" class="text-gray-600">
                                            en {{ order.item_count - 1 }} andere {{ order.item_count - 1 === 1 ? 'item' : 'items' }}
                                        </span>
                                    </p>
                                </div>

                                <!-- Order Acties Sectie -->
                                <div class="flex flex-col space-y-2 sm:space-y-0 sm:flex-row sm:flex-wrap sm:gap-2 pt-3 sm:pt-4 border-t border-gray-100">
                                    <!-- Details Bekijken Knop -->
                                    <SecondaryButton
                                        @click="$inertia.visit(`/orders/${order.id}`)"
                                        class="text-xs sm:text-sm w-full sm:w-auto justify-center sm:justify-start"
                                    >
                                        <EyeIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-1.5" />
                                        Details bekijken
                                    </SecondaryButton>
                                    
                                    <!-- Order Tracking Knop (conditioneel) -->
                                    <SecondaryButton
                                        v-if="order.can_track"
                                        @click="$inertia.visit(`/orders/${order.id}/track`)"
                                        class="text-xs sm:text-sm w-full sm:w-auto justify-center sm:justify-start"
                                    >
                                        <TruckIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-1.5" />
                                        Volgen
                                    </SecondaryButton>
                                    
                                    <!-- Order Annuleren Knop (conditioneel) -->
                                    <button
                                        v-if="order.can_cancel"
                                        @click="showCancelModal(order)"
                                        class="inline-flex items-center justify-center sm:justify-start px-3 py-1.5 border border-red-300 text-xs sm:text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors w-full sm:w-auto"
                                    >
                                        <XMarkIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-1.5" />
                                        Annuleren
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Responsive Pagination Sectie -->
                        <div v-if="orders.links && orders.links.length > 3" class="mt-6 sm:mt-8">
                            <nav class="flex justify-center">
                                <!-- Mobile Pagination (alleen essentiële links) -->
                                <div class="flex sm:hidden space-x-1">
                                    <Link
                                        v-for="link in mobilePaginationLinks"
                                        :key="link.label"
                                        :href="link.url"
                                        :class="[
                                            'px-2 py-1 text-sm font-medium rounded-md min-w-[40px] text-center',
                                            link.active
                                                ? 'bg-blue-600 text-white'
                                                : link.url
                                                    ? 'text-gray-700 hover:text-gray-500 hover:bg-gray-100'
                                                    : 'text-gray-400 cursor-not-allowed'
                                        ]"
                                        v-html="link.label"
                                    />
                                </div>
                                
                                <!-- Desktop Pagination (alle links) -->
                                <div class="hidden sm:flex space-x-1">
                                    <Link
                                        v-for="link in orders.links"
                                        :key="link.label"
                                        :href="link.url"
                                        :class="[
                                            'px-3 py-2 text-sm font-medium rounded-md',
                                            link.active
                                                ? 'bg-blue-600 text-white'
                                                : link.url
                                                    ? 'text-gray-700 hover:text-gray-500 hover:bg-gray-100'
                                                    : 'text-gray-400 cursor-not-allowed'
                                        ]"
                                        v-html="link.label"
                                    />
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Annuleren Bevestiging Modal -->
        <Modal :show="showingCancelModal" @close="showingCancelModal = false">
            <div class="p-4 sm:p-6">
                <!-- Modal Header met Warning Icon -->
                <div class="flex items-start mb-4">
                    <!-- Warning Icon Container -->
                    <div class="flex-shrink-0">
                        <ExclamationTriangleIcon class="h-6 w-6 sm:h-8 sm:w-8 text-red-500" />
                    </div>
                    <!-- Modal Content -->
                    <div class="ml-3 sm:ml-4 flex-1">
                        <!-- Modal Titel -->
                        <h3 class="text-base sm:text-lg font-medium text-gray-900">
                            Bestelling annuleren
                        </h3>
                        <!-- Bevestiging Bericht -->
                        <p class="mt-2 text-sm text-gray-600">
                            Weet je zeker dat je bestelling #{{ orderToCancel?.order_number }} wilt annuleren?
                            Deze actie kan niet ongedaan worden gemaakt.
                        </p>
                    </div>
                </div>

                <!-- Modal Actie Knoppen -->
                <div class="flex flex-col space-y-2 sm:space-y-0 sm:flex-row sm:justify-end sm:space-x-3">
                    <!-- Sluiten Knop -->
                    <SecondaryButton 
                        @click="showingCancelModal = false"
                        class="w-full sm:w-auto justify-center order-2 sm:order-1"
                    >
                        Sluiten
                    </SecondaryButton>
                    <!-- Bevestig Annuleren Knop met Loading State -->
                    <DangerButton
                        @click="cancelOrder"
                        :disabled="isProcessing"
                        class="w-full sm:w-auto justify-center order-1 sm:order-2"
                    >
                        {{ isProcessing ? 'Annuleren...' : 'Ja, annuleer bestelling' }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>