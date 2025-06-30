<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import {
    ShoppingCartIcon,
    TruckIcon,
    ClockIcon,
    CheckCircleIcon,
    XMarkIcon,
    EyeIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    activeOrders: Array,
    orderHistory: Object,
});

const tabs = ref([
    { key: 'active', label: 'Actieve Bestellingen', count: props.activeOrders?.length || 0 },
    { key: 'history', label: 'Bestelgeschiedenis', count: props.orderHistory?.total || 0 },
]);

const currentTab = ref('active');

const formatPrice = (price) => {
    return new Intl.NumberFormat('nl-NL', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
};

const formatDateTime = (dateTime) => {
    return new Date(dateTime).toLocaleDateString('nl-NL', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('nl-NL', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

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

const hasActiveOrders = computed(() => props.activeOrders && props.activeOrders.length > 0);
const hasOrderHistory = computed(() => props.orderHistory && props.orderHistory.data && props.orderHistory.data.length > 0);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Mijn Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                <ShoppingCartIcon class="w-6 h-6 text-blue-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ props.activeOrders?.length || 0 }}</p>
                                <p class="text-sm text-gray-600">Actieve bestellingen</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                <CheckCircleIcon class="w-6 h-6 text-green-600" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ props.orderHistory?.total || 0 }}</p>
                                <p class="text-sm text-gray-600">Totaal bestellingen</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-2">Snel bestellen</p>
                                <PrimaryButton @click="router.visit('/categories')" class="text-sm">
                                    Verder winkelen
                                </PrimaryButton>
                            </div>
                            <ShoppingCartIcon class="w-8 h-8 text-gray-400" />
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <!-- Tabs -->
                    <div class="border-b border-gray-200">
                        <nav class="flex space-x-8 px-6">
                            <button
                                v-for="tab in tabs"
                                :key="tab.key"
                                @click="currentTab = tab.key"
                                :class="[
                                    currentTab === tab.key
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                    'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium'
                                ]"
                            >
                                {{ tab.label }}
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

                    <!-- Active Orders -->
                    <div v-if="currentTab === 'active'" class="p-6">
                        <div v-if="!hasActiveOrders" class="text-center py-12">
                            <ShoppingCartIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Geen actieve bestellingen</h3>
                            <p class="text-gray-600 mb-6">Je hebt momenteel geen lopende bestellingen.</p>
                            <PrimaryButton @click="router.visit('/categories')">
                                Nieuwe bestelling plaatsen
                            </PrimaryButton>
                        </div>
                        
                        <div v-else class="space-y-6">
                            <div v-for="order in activeOrders" :key="order.id" 
                                 class="border border-gray-200 rounded-xl p-6 hover:border-gray-300 transition-colors">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900 mr-3">
                                                Bestelling #{{ order.order_number || order.id }}
                                            </h3>
                                            <span :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border',
                                                getStatusClasses(order.status)
                                            ]">
                                                <component :is="getStatusIcon(order.status)" class="w-3 h-3 mr-1" />
                                                {{ getStatusDisplay(order.status) }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600 space-y-1">
                                            <p>Geplaatst op: {{ formatDateTime(order.created_at) }}</p>
                                            <p v-if="order.delivery_slot">
                                                <TruckIcon class="w-4 h-4 inline mr-1" />
                                                Bezorging: {{ formatDate(order.delivery_slot.date) }} tussen {{ order.delivery_slot.start_time }} - {{ order.delivery_slot.end_time }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="text-right">
                                        <div class="text-xl font-bold text-gray-900 mb-1">
                                            {{ formatPrice(order.total) }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ order.items?.length || 0 }} {{ (order.items?.length || 0) === 1 ? 'item' : 'items' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Items Preview -->
                                <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                                    <p class="text-sm text-gray-700 font-medium mb-2">Items in deze bestelling:</p>
                                    <div class="space-y-1">
                                        <div v-for="item in order.items?.slice(0, 3)" :key="item.id" class="text-sm text-gray-600">
                                            {{ item.quantity }}x {{ item.product?.name || 'Product niet gevonden' }}
                                        </div>
                                        <p v-if="order.items && order.items.length > 3" class="text-xs text-gray-500">
                                            +{{ order.items.length - 3 }} meer {{ order.items.length - 3 === 1 ? 'item' : 'items' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex flex-wrap gap-2 pt-4 border-t border-gray-100">
                                    <SecondaryButton
                                        @click="router.visit(`/orders/${order.id}`)"
                                        class="text-sm"
                                    >
                                        <EyeIcon class="w-4 h-4 mr-1.5" />
                                        Details bekijken
                                    </SecondaryButton>
                                    
                                    <SecondaryButton
                                        @click="router.visit(`/orders/${order.id}/track`)"
                                        class="text-sm"
                                    >
                                        <TruckIcon class="w-4 h-4 mr-1.5" />
                                        Bestelling volgen
                                    </SecondaryButton>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order History -->
                    <div v-if="currentTab === 'history'" class="p-6">
                        <div v-if="!hasOrderHistory" class="text-center py-12">
                            <CheckCircleIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Geen bestelgeschiedenis</h3>
                            <p class="text-gray-600 mb-6">Je hebt nog geen bestellingen voltooid.</p>
                            <PrimaryButton @click="router.visit('/categories')">
                                Je eerste bestelling plaatsen
                            </PrimaryButton>
                        </div>
                        
                        <div v-else>
                            <div class="space-y-4 mb-6">
                                <div v-for="order in orderHistory.data" :key="order.id" 
                                     class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-2">
                                                <h3 class="text-base font-semibold text-gray-900 mr-3">
                                                    #{{ order.order_number || order.id }}
                                                </h3>
                                                <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full border border-green-200">
                                                    <CheckCircleIcon class="w-3 h-3 mr-1" />
                                                    Voltooid
                                                </span>
                                            </div>
                                            <div class="text-sm text-gray-600 space-y-1">
                                                <p>Bezorgd op: {{ formatDate(order.delivery_slot?.date) }}</p>
                                                <p>{{ order.items?.length || 0 }} items • {{ formatPrice(order.total) }}</p>
                                            </div>
                                        </div>
                                        
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
                            
                            <!-- Link to full order history -->
                            <div class="text-center">
                                <SecondaryButton @click="router.visit('/orders')" class="text-sm">
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