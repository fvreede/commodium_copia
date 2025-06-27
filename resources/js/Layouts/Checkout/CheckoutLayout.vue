<!-- Layouts/CheckoutLayout.vue -->
<script setup>
import NavBar from '@/Components/NavBar.vue';
import Footer from '@/Components/Footer.vue';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import { useCartStore } from '@/Stores/cart';
import axios from 'axios';

// Props
const props = defineProps({
    user: {
        type: Object,
        default: null
    },
    currentStep: {
        type: Number,
        required: true,
        validator: (value) => [1, 2, 3].includes(value)
    },
    title: {
        type: String,
        default: 'Bestelling plaatsen'
    }
});

// Initialize cart store
const cartStore = useCartStore();

// Session management
const showSessionModal = ref(false);
const sessionWarningShown = ref(false);
let sessionCheckInterval = null;

// Steps configuration
const steps = computed(() => [
    {
        number: 1,
        title: 'Bezorgmoment',
        description: 'Kies uw bezorgmoment',
        route: '/checkout/delivery',
        completed: props.currentStep > 1,
        current: props.currentStep === 1
    },
    {
        number: 2,
        title: 'Controleren',
        description: 'Controleer uw bestelling',
        route: '/checkout/review',
        completed: props.currentStep > 2,
        current: props.currentStep === 2
    },
    {
        number: 3,
        title: 'Bevestigen',
        description: 'Bevestig uw bestelling',
        route: '/checkout/confirm',
        completed: false,
        current: props.currentStep === 3
    }
]);

// Load cart on mount
onMounted(async () => {
    await cartStore.loadCart();
    
    // If cart is empty, redirect to cart page
    if (!cartStore.hasItems) {
        router.get('/cart');
        return;
    }
    
    // Start session monitoring
    sessionCheckInterval = setInterval(checkSession, 120000); // Check every 2 minutes
    checkSession();
});

// Watch for cart changes and redirect if empty
watch(() => cartStore.hasItems, (hasItems) => {
    if (!hasItems && !cartStore.isLoading) {
        router.get('/cart');
    }
});

// Session management functions
const checkSession = async () => {
    try {
        const response = await axios.get('/api/session-check');
        const { authenticated, time_remaining } = response.data;

        if (!authenticated) {
            showSessionExpiredModal();
            return;
        }

        // Show warning at 5 minutes remaining
        if (time_remaining <= 300 && !sessionWarningShown.value) {
            showSessionWarning();
        }
    } catch (error) {
        console.error('Error checking session:', error);
    }
};

const showSessionExpiredModal = () => {
    showSessionModal.value = true;
    if (sessionCheckInterval) {
        clearInterval(sessionCheckInterval);
    }
};

const showSessionWarning = () => {
    sessionWarningShown.value = true;
    // You can implement a toast notification here
    console.log('Session expires in 5 minutes');
};

const handleSessionExpiredAction = (action) => {
    if (action === 'login') {
        window.location.href = `/login?return_to=checkout/step${props.currentStep}`;
    } else if (action === 'continue') {
        showSessionModal.value = false;
        router.get('/categories');
    }
};

// Cleanup
onUnmounted(() => {
    if (sessionCheckInterval) {
        clearInterval(sessionCheckInterval);
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <NavBar />

        <!-- Main Content -->
        <div class="bg-gray-100 min-h-screen">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-6xl py-16 sm:py-24">
                    <!-- Page Title -->
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-900">{{ title }}</h1>
                        <p class="mt-2 text-sm text-gray-600">
                            Voltooi uw bestelling in {{ steps.length }} eenvoudige stappen
                        </p>
                    </div>

                    <!-- Progress Steps -->
                    <div class="mb-8">
                        <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
                            <div class="px-4 py-3 bg-gray-50 border-b">
                                <h2 class="text-sm font-medium text-gray-900">Voortgang</h2>
                            </div>
                            
                            <!-- Desktop Steps -->
                            <div class="hidden sm:block">
                                <div class="grid grid-cols-3">
                                    <div 
                                        v-for="(step, index) in steps" 
                                        :key="step.number"
                                        :class="[
                                            'relative px-6 py-4',
                                            index < steps.length - 1 ? 'border-r border-gray-200' : '',
                                            step.current ? 'bg-blue-50' : step.completed ? 'bg-green-50' : 'bg-white'
                                        ]"
                                    >
                                        <div class="flex items-center">
                                            <!-- Step Icon -->
                                            <div :class="[
                                                'flex items-center justify-center w-8 h-8 rounded-full border-2 mr-3',
                                                step.completed 
                                                    ? 'bg-green-500 border-green-500 text-white' 
                                                    : step.current 
                                                        ? 'bg-blue-500 border-blue-500 text-white'
                                                        : 'bg-white border-gray-300 text-gray-500'
                                            ]">
                                                <svg v-if="step.completed" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                <span v-else class="text-sm font-medium">{{ step.number }}</span>
                                            </div>
                                            
                                            <!-- Step Content -->
                                            <div class="flex-1">
                                                <p :class="[
                                                    'text-sm font-medium',
                                                    step.current ? 'text-blue-900' : step.completed ? 'text-green-900' : 'text-gray-500'
                                                ]">
                                                    {{ step.title }}
                                                </p>
                                                <p :class="[
                                                    'text-xs',
                                                    step.current ? 'text-blue-700' : step.completed ? 'text-green-700' : 'text-gray-400'
                                                ]">
                                                    {{ step.description }}
                                                </p>
                                            </div>

                                            <!-- Arrow -->
                                            <div v-if="index < steps.length - 1" class="absolute right-0 top-1/2 transform -translate-y-1/2 translate-x-1/2">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mobile Steps -->
                            <div class="sm:hidden">
                                <div class="px-4 py-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-900">
                                            Stap {{ currentStep }} van {{ steps.length }}
                                        </span>
                                        <span class="text-sm text-gray-600">
                                            {{ steps.find(s => s.current)?.title }}
                                        </span>
                                    </div>
                                    <div class="mt-2">
                                        <div class="bg-gray-200 rounded-full h-2">
                                            <div 
                                                class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                                                :style="{ width: `${(currentStep / steps.length) * 100}%` }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div v-if="cartStore.isLoading" class="text-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                        <p class="mt-4 text-gray-600">Bestelling laden...</p>
                    </div>

                    <!-- Main Content Slot -->
                    <div v-else>
                        <slot />
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <Footer />

        <!-- Session Expired Modal -->
        <div v-if="showSessionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            
            <div class="bg-white rounded-lg p-6 text-left overflow-hidden shadow-xl transform transition-all max-w-lg w-full mx-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="rounded-full bg-amber-100 p-3">
                            <ExclamationTriangleIcon class="h-8 w-8 text-amber-600" />
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Je sessie is verlopen
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Je bent automatisch uitgelogd voor je veiligheid. Je winkelwagen is bewaard. 
                                Wil je opnieuw inloggen of verdergaan als gast?
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                    <button 
                        @click="handleSessionExpiredAction('continue')"
                        type="button" 
                        class="w-full sm:w-auto inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Verder als gast
                    </button>
                    <button 
                        @click="handleSessionExpiredAction('login')"
                        type="button" 
                        class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Opnieuw inloggen
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.transition-all {
    transition: all 0.2s ease-in-out;
}

button:focus {
    outline: 2px solid #3B82F6;
    outline-offset: 2px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>