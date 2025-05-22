<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    activeOrders: Array,
    orderHistory: Object,
});

const tabs = ref([
    { key: 'active', label: 'Active Orders' },
    { key: 'history', label: 'Order History' },
]);

const currentTab = ref('active');

const formatPrice = (price) => {
    return new Intl.NumberFormat('nl-NL', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
};

const formatDateTime = (date, time) => {
    return new Intl.DateTimeFormat('nl-NL', {
        dateStyle: 'long',
        timeStyle: 'short'
    }).format(new Date(`${date}T${time}`));
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                My Orders
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Tabs -->
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex space-x-8">
                                <button
                                    v-for="tab in tabs"
                                    :key="tab.key"
                                    @click="currentTab = tab.key"
                                    :class="[
                                        currentTab === tab.key
                                            ? 'border-blue-500 text-blue-600'
                                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                        'whitespace-nowrap border-b-2 px-1 pb-4 text-sm font-medium'
                                    ]"
                                >
                                    {{ tab.label }}
                                </button>
                            </nav>
                        </div>

                        <!-- Active Orders -->
                        <div v-if="currentTab === 'active'" class="mt-6">
                            <div v-if="activeOrders.length === 0" class="text-gray-500">
                                No active orders
                            </div>
                            <div v-else class="space-y-6">
                                <div v-for="order in activeOrders" :key="order.id" 
                                     class="border rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="text-lg font-medium">
                                                Order #{{ order.id }}
                                            </h3>
                                            <p class="text-sm text-gray-500">
                                                Delivery: {{ formatDateTime(order.delivery_slot.date, order.delivery_slot.start_time) }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                Total: {{ formatPrice(order.total) }}
                                            </p>
                                        </div>
                                        <span class="px-2 py-1 text-sm rounded-full"
                                              :class="{
                                                  'bg-yellow-100 text-yellow-800': order.status === 'pending',
                                                  'bg-blue-100 text-blue-800': order.status === 'processing'
                                              }">
                                            {{ order.status }}
                                        </span>
                                    </div>
                                    <div class="mt-4">
                                        <h4 class="text-sm font-medium">Items:</h4>
                                        <ul class="mt-2 divide-y divide-gray-200">
                                            <li v-for="item in order.items" :key="item.id" 
                                                class="py-2">
                                                <div class="flex justify-between">
                                                    <span>{{ item.product.name }} × {{ item.quantity }}</span>
                                                    <span>{{ formatPrice(item.price) }}</span>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order History -->
                        <div v-if="currentTab === 'history'" class="mt-6">
                            <div v-if="orderHistory.data.length === 0" class="text-gray-500">
                                No order history
                            </div>
                            <div v-else>
                                <div v-for="order in orderHistory.data" :key="order.id" 
                                     class="border rounded-lg p-4 mb-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="text-lg font-medium">
                                                Order #{{ order.id }}
                                            </h3>
                                            <p class="text-sm text-gray-500">
                                                Delivered: {{ formatDateTime(order.delivery_slot.date, order.delivery_slot.end_time) }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                Total: {{ formatPrice(order.total) }}
                                            </p>
                                        </div>
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                                            Completed
                                        </span>
                                    </div>
                                    <div class="mt-4">
                                        <h4 class="text-sm font-medium">Items:</h4>
                                        <ul class="mt-2 divide-y divide-gray-200">
                                            <li v-for="item in order.items" :key="item.id" 
                                                class="py-2">
                                                <div class="flex justify-between">
                                                    <span>{{ item.product.name }} × {{ item.quantity }}</span>
                                                    <span>{{ formatPrice(item.price) }}</span>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <!-- Pagination -->
                                <!-- Add pagination component here if needed -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>