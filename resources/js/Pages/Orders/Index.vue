<!-- Pages/Orders/Index.vue -->
<template>
    <AuthenticatedLayout>
        <Head title="Mijn Bestellingen" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Mijn Bestellingen
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Filters -->
                        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <!-- Status Filter -->
                            <div>
                                <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-2">
                                    Filter op status
                                </label>
                                <select
                                    id="status-filter"
                                    v-model="selectedStatus"
                                    @change="applyFilters"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option
                                        v-for="option in statusOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Search -->
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
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>

                            <!-- Clear Filters -->
                            <div class="flex items-end">
                                <SecondaryButton
                                    @click="clearFilters"
                                    class="w-full sm:w-auto"
                                >
                                    <XMarkIcon class="w-4 h-4 mr-2" />
                                    Filters wissen
                                </SecondaryButton>
                            </div>
                        </div>

                        <!-- Orders List -->
                        <div v-if="orders.data.length === 0" class="text-center py-12">
                            <ShoppingBagIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Geen bestellingen gevonden</h3>
                            <p class="text-gray-600 mb-6">
                                {{ hasFilters ? 'Probeer andere filters' : 'Je hebt nog geen bestellingen geplaatst' }}
                            </p>
                            <PrimaryButton @click="$inertia.visit('/categories')">
                                Verder winkelen
                            </PrimaryButton>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="order in orders.data"
                                :key="order.id"
                                class="border border-gray-200 rounded-xl p-4 sm:p-6 hover:border-gray-300 transition-colors"
                            >
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900 mr-3">
                                                #{{ order.order_number }}
                                            </h3>
                                            <span :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                getStatusClasses(order.status)
                                            ]">
                                                {{ order.status_display }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600 space-y-1">
                                            <p>{{ formatDateTime(order.created_at) }}</p>
                                            <p v-if="order.delivery_slot">
                                                Bezorging: {{ order.delivery_slot.formatted_date }} om {{ order.delivery_slot.formatted_time }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 sm:mt-0 sm:ml-4 flex flex-col items-start sm:items-end">
                                        <div class="text-lg font-bold text-gray-900 mb-2">
                                            €{{ order.total.toFixed(2) }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ order.total_items }} {{ order.total_items === 1 ? 'item' : 'items' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Items Preview -->
                                <div class="mb-4">
                                    <p class="text-sm text-gray-600 mb-2">
                                        {{ order.first_item_name }}
                                        <span v-if="order.item_count > 1">
                                            en {{ order.item_count - 1 }} andere {{ order.item_count - 1 === 1 ? 'item' : 'items' }}
                                        </span>
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div class="flex flex-wrap gap-2 pt-4 border-t border-gray-100">
                                    <SecondaryButton
                                        @click="$inertia.visit(`/orders/${order.id}`)"
                                        class="text-sm"
                                    >
                                        <EyeIcon class="w-4 h-4 mr-1.5" />
                                        Details bekijken
                                    </SecondaryButton>
                                    
                                    <SecondaryButton
                                        v-if="order.can_track"
                                        @click="$inertia.visit(`/orders/${order.id}/track`)"
                                        class="text-sm"
                                    >
                                        <TruckIcon class="w-4 h-4 mr-1.5" />
                                        Volgen
                                    </SecondaryButton>
                                    
                                    <button
                                        v-if="order.can_cancel"
                                        @click="showCancelModal(order)"
                                        class="inline-flex items-center px-3 py-1.5 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                                    >
                                        <XMarkIcon class="w-4 h-4 mr-1.5" />
                                        Annuleren
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="orders.links && orders.links.length > 3" class="mt-8">
                            <nav class="flex justify-center">
                                <div class="flex space-x-1">
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

        <!-- Cancel Order Modal -->
        <Modal :show="showingCancelModal" @close="showingCancelModal = false">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <ExclamationTriangleIcon class="h-8 w-8 text-red-500" />
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            Bestelling annuleren
                        </h3>
                        <p class="mt-2 text-sm text-gray-600">
                            Weet je zeker dat je bestelling #{{ orderToCancel?.order_number }} wilt annuleren?
                            Deze actie kan niet ongedaan worden gemaakt.
                        </p>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <SecondaryButton @click="showingCancelModal = false">
                        Sluiten
                    </SecondaryButton>
                    <DangerButton
                        @click="cancelOrder"
                        :disabled="isProcessing"
                    >
                        {{ isProcessing ? 'Annuleren...' : 'Ja, annuleer bestelling' }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import Modal from '@/Components/Modal.vue'
import {
    ShoppingBagIcon,
    EyeIcon,
    TruckIcon,
    XMarkIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
    orders: Object,
    filters: Object,
    statusOptions: Array
})

// Reactive state
const selectedStatus = ref(props.filters.status || 'all')
const searchQuery = ref(props.filters.search || '')
const showingCancelModal = ref(false)
const orderToCancel = ref(null)
const isProcessing = ref(false)

// Computed
const hasFilters = computed(() => {
    return selectedStatus.value !== 'all' || searchQuery.value.length > 0
})

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

const applyFilters = () => {
    router.get('/orders', {
        status: selectedStatus.value,
        search: searchQuery.value
    }, {
        preserveState: true,
        replace: true
    })
}

const clearFilters = () => {
    selectedStatus.value = 'all'
    searchQuery.value = ''
    router.get('/orders', {}, {
        preserveState: true,
        replace: true
    })
}

// Debounced search
let searchTimeout
const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        applyFilters()
    }, 500)
}

const showCancelModal = (order) => {
    orderToCancel.value = order
    showingCancelModal.value = true
}

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