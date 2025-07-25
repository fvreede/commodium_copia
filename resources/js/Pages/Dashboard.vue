/**
 * Bestandsnaam: Dashboard.vue
 * Auteur: Fabio Vreede
 * Versie: v1.0.4
 * Datum: 2025-07-02
 * Tijd: 00:15:49
 * Doel: Dit component toont het hoofddashboard van de gebruiker met een overzicht van actieve bestellingen, 
 *       bestelgeschiedenis en snelle statistieken. Gebruikers kunnen hun bestellingen beheren en volgen.
 */

<script setup>
// Vue compositie API imports
import { ref, computed } from 'vue';

// Layout en navigatie imports
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

// Component imports
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

// Heroicons imports voor iconen
import {
    ShoppingCartIcon,
    TruckIcon,
    ClockIcon,
    CheckCircleIcon,
    XMarkIcon,
    EyeIcon
} from '@heroicons/vue/24/outline';

// Props definitie - data van de server
const props = defineProps({
    activeOrders: Array,      // Actieve bestellingen array
    orderHistory: Object,     // Bestelgeschiedenis object met paginatie
});

// Tab configuratie voor navigatie tussen actieve bestellingen en geschiedenis
const tabs = ref([
    { key: 'active', label: 'Actieve Bestellingen', count: props.activeOrders?.length || 0 },
    { key: 'history', label: 'Bestelgeschiedenis', count: props.orderHistory?.total || 0 },
]);

// Huidige actieve tab state
const currentTab = ref('active');

/**
 * Formatteert een prijs naar Nederlandse valuta (EUR)
 * @param {number} price - De prijs om te formatteren
 * @returns {string} Geformatteerde prijs string
 */
const formatPrice = (price) => {
    return new Intl.NumberFormat('nl-NL', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
};

/**
 * Formatteert een datum en tijd naar Nederlandse weergave
 * @param {string} dateTime - ISO datum string
 * @returns {string} Geformatteerde datum en tijd
 */
const formatDateTime = (dateTime) => {
    return new Date(dateTime).toLocaleDateString('nl-NL', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

/**
 * Formatteert alleen de datum naar Nederlandse weergave
 * @param {string} date - ISO datum string
 * @returns {string} Geformatteerde datum
 */
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('nl-NL', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

/**
 * Bepaalt de CSS classes voor bestelling status badges
 * @param {string} status - Status van de bestelling
 * @returns {string} CSS classes voor de status badge
 */
const getStatusClasses = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'confirmed': 'bg-blue-100 text-blue-800 border-blue-200',
        'processing': 'bg-purple-100 text-purple-800 border-purple-200',
        'out_for_delivery': 'bg-indigo-100 text-indigo-800 border-indigo-200',
        'delivered': 'bg-green-100 text-green-800 border-green-200',
        'cancelled': 'bg-red-100 text-red-800 border-red-200'
    };
    return classes[status] || 'bg-gray-100 text-gray-800 border-gray-200';
};

/**
 * Vertaalt status codes naar Nederlandse weergave
 * @param {string} status - Status code van de bestelling
 * @returns {string} Nederlandse status beschrijving
 */
const getStatusDisplay = (status) => {
    const statuses = {
        'pending': 'In behandeling',
        'confirmed': 'Bevestigd',
        'processing': 'Wordt voorbereid',
        'out_for_delivery': 'Onderweg',
        'delivered': 'Bezorgd',
        'cancelled': 'Geannuleerd'
    };
    return statuses[status] || 'Onbekend';
};

/**
 * Bepaalt het juiste icoon voor elke bestelling status
 * @param {string} status - Status van de bestelling
 * @returns {Component} Vue component voor het icoon
 */
const getStatusIcon = (status) => {
    const icons = {
        'pending': ClockIcon,
        'confirmed': CheckCircleIcon,
        'processing': ShoppingCartIcon,
        'out_for_delivery': TruckIcon,
        'delivered': CheckCircleIcon,
        'cancelled': XMarkIcon
    };
    return icons[status] || ClockIcon;
};

// Computed properties voor conditionele weergave
const hasActiveOrders = computed(() => props.activeOrders && props.activeOrders.length > 0);
const hasOrderHistory = computed(() => props.orderHistory && props.orderHistory.data && props.orderHistory.data.length > 0);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <!-- Page Header -->
        <template #header>
            <h2 class="text-lg sm:text-xl font-semibold leading-tight text-gray-800">
                Mijn Overzicht
            </h2>
        </template>

        <div class="py-4 sm:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Snelle Statistieken Sectie -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
                    
                    <!-- Actieve Bestellingen Card -->
                    <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                        <div class="flex items-center">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-3 sm:mr-4">
                                <ShoppingCartIcon class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" />
                            </div>
                            <div>
                                <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ props.activeOrders?.length || 0 }}</p>
                                <p class="text-xs sm:text-sm text-gray-600">Actieve bestellingen</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Totaal Bestellingen Card -->
                    <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                        <div class="flex items-center">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-lg flex items-center justify-center mr-3 sm:mr-4">
                                <CheckCircleIcon class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" />
                            </div>
                            <div>
                                <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ props.orderHistory?.total || 0 }}</p>
                                <p class="text-xs sm:text-sm text-gray-600">Totaal bestellingen</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Snel Bestellen Card -->
                    <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 sm:col-span-2 lg:col-span-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs sm:text-sm text-gray-600 mb-2">Snel bestellen</p>
                                <PrimaryButton @click="router.visit('/categories')" class="text-xs sm:text-sm">
                                    Verder winkelen
                                </PrimaryButton>
                            </div>
                            <ShoppingCartIcon class="w-6 h-6 sm:w-8 sm:h-8 text-gray-400" />
                        </div>
                    </div>
                </div>

                <!-- Hoofdinhoud Container -->
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    
                    <!-- Tab Navigatie -->
                    <div class="border-b border-gray-200">
                        <nav class="flex space-x-4 sm:space-x-8 px-4 sm:px-6 overflow-x-auto">
                            <button
                                v-for="tab in tabs"
                                :key="tab.key"
                                @click="currentTab = tab.key"
                                :class="[
                                    currentTab === tab.key
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                    'whitespace-nowrap border-b-2 px-1 py-3 sm:py-4 text-sm font-medium flex-shrink-0'
                                ]"
                            >
                                <span class="block sm:inline">{{ tab.label }}</span>
                                <!-- Tab Counter Badge -->
                                <span v-if="tab.count > 0" :class="[
                                    'ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs',
                                    currentTab === tab.key
                                        ? 'bg-blue-100 text-blue-600'
                                        : 'bg-gray-100 text-gray-600'
                                ]">
                                    {{ tab.count }}
                                </span>
                            </button>
                        </nav>
                    </div>

                    <!-- Actieve Bestellingen Tab Content -->
                    <div v-if="currentTab === 'active'" class="p-4 sm:p-6">
                        
                        <!-- Lege Staat - Geen Actieve Bestellingen -->
                        <div v-if="!hasActiveOrders" class="text-center py-8 sm:py-12">
                            <ShoppingCartIcon class="w-12 h-12 sm:w-16 sm:h-16 text-gray-400 mx-auto mb-4" />
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">Geen actieve bestellingen</h3>
                            <p class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-6 px-4">Je hebt momenteel geen lopende bestellingen.</p>
                            <PrimaryButton @click="router.visit('/categories')" class="text-sm sm:text-base">
                                Nieuwe bestelling plaatsen
                            </PrimaryButton>
                        </div>
                        
                        <!-- Actieve Bestellingen Lijst -->
                        <div v-else class="space-y-4 sm:space-y-6">
                            <div v-for="order in activeOrders" :key="order.id" 
                                 class="border border-gray-200 rounded-lg sm:rounded-xl p-4 sm:p-6 hover:border-gray-300 transition-colors">
                                
                                <!-- Mobiele Layout -->
                                <div class="block sm:hidden">
                                    <div class="mb-3">
                                        <h3 class="text-base font-semibold text-gray-900 mb-2">
                                            #{{ order.order_number || order.id }}
                                        </h3>
                                        
                                        <!-- Status en Prijs Rij -->
                                        <div class="flex items-center justify-between mb-2">
                                            <!-- Status Badge -->
                                            <span :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border w-fit',
                                                getStatusClasses(order.status)
                                            ]">
                                                <component :is="getStatusIcon(order.status)" class="w-3 h-3 mr-1" />
                                                {{ getStatusDisplay(order.status) }}
                                            </span>
                                            
                                            <!-- Prijs en Items Info -->
                                            <div class="text-right">
                                                <div class="text-lg font-bold text-gray-900">
                                                    {{ formatPrice(order.total) }}
                                                </div>
                                                <div class="text-xs text-gray-600">
                                                    {{ order.items?.length || 0 }} {{ (order.items?.length || 0) === 1 ? 'item' : 'items' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Datum en Bezorging Info -->
                                    <div class="text-xs text-gray-600 space-y-1 mb-3">
                                        <p>{{ formatDateTime(order.created_at) }}</p>
                                        <p v-if="order.delivery_slot" class="flex items-center">
                                            <TruckIcon class="w-3 h-3 mr-1" />
                                            {{ formatDate(order.delivery_slot.date) }} • {{ order.delivery_slot.start_time }}-{{ order.delivery_slot.end_time }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Desktop Layout -->
                                <div class="hidden sm:block">
                                    <div class="flex items-start justify-between mb-4">
                                        <!-- Bestelling Info Links -->
                                        <div class="flex-1">
                                            <div class="flex items-center mb-2">
                                                <h3 class="text-lg font-semibold text-gray-900 mr-3">
                                                    Bestelling #{{ order.order_number || order.id }}
                                                </h3>
                                                <!-- Status Badge -->
                                                <span :class="[
                                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border',
                                                    getStatusClasses(order.status)
                                                ]">
                                                    <component :is="getStatusIcon(order.status)" class="w-3 h-3 mr-1" />
                                                    {{ getStatusDisplay(order.status) }}
                                                </span>
                                            </div>
                                            <!-- Datum en Bezorging Details -->
                                            <div class="text-sm text-gray-600 space-y-1">
                                                <p>Geplaatst op: {{ formatDateTime(order.created_at) }}</p>
                                                <p v-if="order.delivery_slot">
                                                    <TruckIcon class="w-4 h-4 inline mr-1" />
                                                    Bezorging: {{ formatDate(order.delivery_slot.date) }} tussen {{ order.delivery_slot.start_time }} - {{ order.delivery_slot.end_time }}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <!-- Prijs Info Rechts -->
                                        <div class="text-right">
                                            <div class="text-xl font-bold text-gray-900 mb-1">
                                                {{ formatPrice(order.total) }}
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                {{ order.items?.length || 0 }} {{ (order.items?.length || 0) === 1 ? 'item' : 'items' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bestelling Items Voorvertoning -->
                                <div class="mb-3 sm:mb-4 p-3 bg-gray-50 rounded-lg">
                                    <p class="text-xs sm:text-sm text-gray-700 font-medium mb-2">Items in deze bestelling:</p>
                                    <div class="space-y-1">
                                        <!-- Toon eerste 3 items -->
                                        <div v-for="item in order.items?.slice(0, 3)" :key="item.id" class="text-xs sm:text-sm text-gray-600">
                                            {{ item.quantity }}x {{ item.product?.name || 'Product niet gevonden' }}
                                        </div>
                                        <!-- Teller voor extra items -->
                                        <p v-if="order.items && order.items.length > 3" class="text-xs text-gray-500">
                                            +{{ order.items.length - 3 }} meer {{ order.items.length - 3 === 1 ? 'item' : 'items' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Actie Knoppen -->
                                <div class="flex flex-col space-y-2 sm:space-y-0 sm:flex-row sm:flex-wrap sm:gap-2 pt-3 sm:pt-4 border-t border-gray-100">
                                    <!-- Details Bekijken Knop -->
                                    <SecondaryButton
                                        @click="router.visit(`/orders/${order.id}`)"
                                        class="text-xs sm:text-sm w-full sm:w-auto justify-center sm:justify-start"
                                    >
                                        <EyeIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-1.5" />
                                        Details bekijken
                                    </SecondaryButton>
                                    
                                    <!-- Bestelling Volgen Knop -->
                                    <SecondaryButton
                                        @click="router.visit(`/orders/${order.id}/track`)"
                                        class="text-xs sm:text-sm w-full sm:w-auto justify-center sm:justify-start"
                                    >
                                        <TruckIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-1.5" />
                                        Bestelling volgen
                                    </SecondaryButton>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bestelgeschiedenis Tab Content -->
                    <div v-if="currentTab === 'history'" class="p-4 sm:p-6">
                        
                        <!-- Lege Staat - Geen Bestelgeschiedenis -->
                        <div v-if="!hasOrderHistory" class="text-center py-8 sm:py-12">
                            <CheckCircleIcon class="w-12 h-12 sm:w-16 sm:h-16 text-gray-400 mx-auto mb-4" />
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">Geen bestelgeschiedenis</h3>
                            <p class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-6 px-4">Je hebt nog geen bestellingen voltooid.</p>
                            <PrimaryButton @click="router.visit('/categories')" class="text-sm sm:text-base">
                                Je eerste bestelling plaatsen
                            </PrimaryButton>
                        </div>
                        
                        <!-- Bestelgeschiedenis Lijst -->
                        <div v-else>
                            <div class="space-y-3 sm:space-y-4 mb-4 sm:mb-6">
                                <div v-for="order in orderHistory.data" :key="order.id" 
                                     class="border border-gray-200 rounded-lg p-3 sm:p-4 hover:border-gray-300 transition-colors">
                                    
                                    <!-- Mobiele Layout Geschiedenis -->
                                    <div class="flex flex-col space-y-2 sm:hidden">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-sm font-semibold text-gray-900">
                                                #{{ order.order_number || order.id }}
                                            </h3>
                                            <!-- Voltooid Status Badge -->
                                            <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full border border-green-200">
                                                <CheckCircleIcon class="w-3 h-3 mr-1" />
                                                Voltooid
                                            </span>
                                        </div>
                                        <!-- Bestelling Details -->
                                        <div class="text-xs text-gray-600 space-y-1">
                                            <p>Bezorgd: {{ formatDate(order.delivery_slot?.date) }}</p>
                                            <p>{{ order.items?.length || 0 }} items • {{ formatPrice(order.total) }}</p>
                                        </div>
                                        <!-- Bekijk Knop -->
                                        <SecondaryButton
                                            @click="router.visit(`/orders/${order.id}`)"
                                            class="text-xs w-full justify-center mt-2"
                                        >
                                            <EyeIcon class="w-3 h-3 mr-1.5" />
                                            Bekijken
                                        </SecondaryButton>
                                    </div>

                                    <!-- Desktop Layout Geschiedenis -->
                                    <div class="hidden sm:flex sm:items-start sm:justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-2">
                                                <h3 class="text-base font-semibold text-gray-900 mr-3">
                                                    #{{ order.order_number || order.id }}
                                                </h3>
                                                <!-- Voltooid Status Badge -->
                                                <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full border border-green-200">
                                                    <CheckCircleIcon class="w-3 h-3 mr-1" />
                                                    Voltooid
                                                </span>
                                            </div>
                                            <!-- Bestelling Info -->
                                            <div class="text-sm text-gray-600 space-y-1">
                                                <p>Bezorgd op: {{ formatDate(order.delivery_slot?.date) }}</p>
                                                <p>{{ order.items?.length || 0 }} items • {{ formatPrice(order.total) }}</p>
                                            </div>
                                        </div>
                                        
                                        <!-- Bekijk Knop Desktop -->
                                        <SecondaryButton
                                            @click="router.visit(`/orders/${order.id}`)"
                                            class="text-sm"
                                        >
                                            <EyeIcon class="w-4 h-4 mr-1.5" />
                                            Bekijken
                                        </SecondaryButton>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Link naar Volledige Bestelgeschiedenis -->
                            <div class="text-center">
                                <SecondaryButton 
                                    @click="router.visit('/orders')" 
                                    class="text-xs sm:text-sm w-full sm:w-auto"
                                >
                                    Alle bestellingen bekijken
                                </SecondaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>