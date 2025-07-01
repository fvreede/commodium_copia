<!-- Pages/Orders/Track.vue -->
<template>
    <AuthenticatedLayout>
        <Head :title="`Volgen: #${order.order_number}`" />

        <template #header>
            <div class="flex flex-col space-y-2 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg sm:text-xl font-semibold leading-tight text-gray-800">
                        Bestelling volgen
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-600 mt-1 truncate">
                        #{{ order.order_number }} • {{ formatDateTime(order.created_at) }}
                    </p>
                </div>
                <SecondaryButton 
                    @click="$inertia.visit(`/orders/${order.id}`)"
                    class="text-sm sm:text-base"
                >
                    <ArrowLeftIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-2" />
                    Terug naar details
                </SecondaryButton>
            </div>
        </template>

        <div class="py-4 sm:py-12">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Current Status Banner -->
                <div class="mb-6 sm:mb-8 bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-col space-y-3 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between mb-4">
                            <div class="flex items-center">
                                <div :class="[
                                    'w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center mr-3 sm:mr-4',
                                    getStatusBannerClasses(order.status)
                                ]">
                                    <component :is="getStatusIcon(order.status)" class="w-5 h-5 sm:w-6 sm:h-6" />
                                </div>
                                <div>
                                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900">
                                        {{ order.status_display }}
                                    </h3>
                                    <p class="text-sm sm:text-base text-gray-600">
                                        {{ getStatusDescription(order.status) }}
                                    </p>
                                </div>
                            </div>
                            <div v-if="deliverySlot" class="text-left sm:text-right">
                                <p class="text-xs sm:text-sm text-gray-600">Geschatte bezorging</p>
                                <p class="font-semibold text-sm sm:text-base text-gray-900">{{ order.estimated_delivery }}</p>
                            </div>
                        </div>

                        <!-- Progress Bar -->
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

                <!-- Tracking Steps -->
                <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                            Bezorgstatus
                        </h3>
                    </div>
                    
                    <div class="p-4 sm:p-6">
                        <div class="space-y-6 sm:space-y-8">
                            <div
                                v-for="(step, index) in trackingSteps"
                                :key="index"
                                class="relative flex items-start"
                            >
                                <!-- Connecting Line -->
                                <div
                                    v-if="index < trackingSteps.length - 1"
                                    :class="[
                                        'absolute left-3 sm:left-4 top-6 sm:top-8 w-0.5 h-12 sm:h-16',
                                        step.status === 'completed' ? 'bg-green-400' : 'bg-gray-200'
                                    ]"
                                ></div>

                                <!-- Step Icon -->
                                <div class="relative">
                                    <div :class="[
                                        'w-6 h-6 sm:w-8 sm:h-8 rounded-full flex items-center justify-center border-2',
                                        getStepClasses(step.status)
                                    ]">
                                        <component
                                            v-if="step.status === 'completed'"
                                            :is="CheckCircleIcon"
                                            class="w-3 h-3 sm:w-5 sm:h-5"
                                        />
                                        <component
                                            v-else-if="step.status === 'current'"
                                            :is="getStepIcon(step.icon)"
                                            class="w-3 h-3 sm:w-4 sm:h-4"
                                        />
                                        <component
                                            v-else-if="step.status === 'cancelled'"
                                            :is="XMarkIcon"
                                            class="w-3 h-3 sm:w-4 sm:h-4"
                                        />
                                        <div
                                            v-else
                                            class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-gray-300"
                                        ></div>
                                    </div>
                                </div>

                                <!-- Step Content -->
                                <div class="ml-3 sm:ml-4 flex-1">
                                    <div class="flex flex-col space-y-1 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between">
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
                                        <span
                                            v-if="step.date"
                                            class="text-xs text-gray-500"
                                        >
                                            {{ formatDateTime(step.date) }}
                                        </span>
                                    </div>
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

                <!-- Delivery Information -->
                <div v-if="deliverySlot" class="mt-6 sm:mt-8 bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-blue-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                            <TruckIcon class="w-4 h-4 sm:w-5 sm:h-5 mr-2 text-blue-600" />
                            Bezorgmoment
                        </h3>
                    </div>
                    
                    <div class="p-4 sm:p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                            <div class="flex items-center p-3 sm:p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <CalendarDaysIcon class="w-6 h-6 sm:w-8 sm:h-8 text-blue-600 mr-3 sm:mr-4" />
                                <div>
                                    <p class="font-semibold text-sm sm:text-base text-blue-900">{{ deliverySlot.formatted_date }}</p>
                                    <p class="text-xs sm:text-sm text-blue-700">Bezorgdatum</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center p-3 sm:p-4 bg-green-50 rounded-lg border border-green-200">
                                <ClockIcon class="w-6 h-6 sm:w-8 sm:h-8 text-green-600 mr-3 sm:mr-4" />
                                <div>
                                    <p class="font-semibold text-sm sm:text-base text-green-900">{{ deliverySlot.formatted_time }}</p>
                                    <p class="text-xs sm:text-sm text-green-700">Tijdslot</p>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Tips -->
                        <div class="mt-4 sm:mt-6 p-3 sm:p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <h4 class="font-semibold text-sm sm:text-base text-yellow-900 mb-2 flex items-center">
                                <LightBulbIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-2" />
                                Bezorgtips
                            </h4>
                            <ul class="text-xs sm:text-sm text-yellow-800 space-y-1">
                                <li>• Zorg dat je thuis bent tijdens het bezorgtijdslot</li>
                                <li>• Houd je telefoon bij de hand voor contact van de bezorger</li>
                                <li>• Controleer je bestelling bij ontvangst</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 sm:mt-8 flex flex-col space-y-3 sm:space-y-0 sm:flex-row sm:gap-4 sm:justify-center">
                    <SecondaryButton 
                        @click="refreshTracking" 
                        :disabled="refreshing"
                        class="w-full sm:w-auto justify-center text-sm sm:text-base"
                    >
                        <ArrowPathIcon :class="['w-3 h-3 sm:w-4 sm:h-4 mr-2', refreshing && 'animate-spin']" />
                        {{ refreshing ? 'Vernieuwen...' : 'Status vernieuwen' }}
                    </SecondaryButton>
                    
                    <SecondaryButton 
                        @click="$inertia.visit(`/orders/${order.id}`)"
                        class="w-full sm:w-auto justify-center text-sm sm:text-base"
                    >
                        <DocumentTextIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-2" />
                        Besteldetails bekijken
                    </SecondaryButton>
                    
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

<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
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
    // Status icons
    ShoppingCartIcon,
    ClipboardDocumentCheckIcon,
    CogIcon,
    HomeIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
    order: Object,
    deliverySlot: Object,
    trackingSteps: Array,
    currentStep: Number
})

// Reactive state
const refreshing = ref(false)

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

const getStatusBannerClasses = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-600',
        'confirmed': 'bg-blue-100 text-blue-600',
        'processing': 'bg-purple-100 text-purple-600',
        'out_for_delivery': 'bg-indigo-100 text-indigo-600',
        'delivered': 'bg-green-100 text-green-600',
        'cancelled': 'bg-red-100 text-red-600'
    }
    return classes[status] || 'bg-gray-100 text-gray-600'
}

const getStatusIcon = (status) => {
    const icons = {
        'pending': ShoppingCartIcon,
        'confirmed': ClipboardDocumentCheckIcon,
        'processing': CogIcon,
        'out_for_delivery': TruckIcon,
        'delivered': HomeIcon,
        'cancelled': XMarkIcon
    }
    return icons[status] || ShoppingCartIcon
}

const getStatusDescription = (status) => {
    const descriptions = {
        'pending': 'Je bestelling wordt nog verwerkt',
        'confirmed': 'Je bestelling is bevestigd en wordt voorbereid',
        'processing': 'Je bestelling wordt ingepakt',
        'out_for_delivery': 'Je bestelling is onderweg naar je adres',
        'delivered': 'Je bestelling is succesvol bezorgd',
        'cancelled': 'Je bestelling is geannuleerd'
    }
    return descriptions[status] || 'Status onbekend'
}

const getProgressPercentage = (status) => {
    const percentages = {
        'pending': 10,
        'confirmed': 25,
        'processing': 50,
        'out_for_delivery': 75,
        'delivered': 100,
        'cancelled': 0
    }
    return percentages[status] || 0
}

const getProgressBarClass = (status) => {
    if (status === 'cancelled') return 'bg-red-400'
    if (status === 'delivered') return 'bg-green-400'
    return 'bg-blue-400'
}

const getStepClasses = (status) => {
    const classes = {
        'completed': 'border-green-400 bg-green-400 text-white',
        'current': 'border-blue-400 bg-blue-400 text-white',
        'cancelled': 'border-red-400 bg-red-400 text-white',
        'pending': 'border-gray-300 bg-white text-gray-300'
    }
    return classes[status] || 'border-gray-300 bg-white text-gray-300'
}

const getStepIcon = (iconName) => {
    const icons = {
        'check-circle': CheckCircleIcon,
        'clipboard-check': ClipboardDocumentCheckIcon,
        'cog': CogIcon,
        'truck': TruckIcon,
        'home': HomeIcon
    }
    return icons[iconName] || CheckCircleIcon
}

const refreshTracking = async () => {
    refreshing.value = true
    
    try {
        await router.reload({ only: ['order', 'trackingSteps'] })
    } catch (error) {
        console.error('Error refreshing tracking:', error)
    } finally {
        refreshing.value = false
    }
}
</script>

<style scoped>
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>