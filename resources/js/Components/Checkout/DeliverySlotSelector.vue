<!-- resources/js/Components/Checkout/DeliverySlotSelector.vue -->
<template>
    <div class="bg-white border rounded-lg shadow-sm">
        <div class="p-6">
            <h3 class="text-lg font-medium mb-6">Kies uw bezorgmoment</h3>
            
            <!-- Loading state -->
            <div v-if="isLoading" class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-2 text-sm text-gray-600">Bezorgmomenten laden...</p>
            </div>

            <!-- Date Selection -->
            <div v-else-if="deliverySlots.length > 0">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Selecteer een dag</h4>
                <div class="grid grid-cols-7 gap-0 border rounded overflow-hidden mb-6">
                    <button 
                        v-for="day in deliverySlots" 
                        :key="day.date"
                        @click="selectDay(day.date)"
                        :disabled="!hasAvailableSlots(day)"
                        :class="[
                            'px-3 py-3 text-sm border-r last:border-r-0 transition-colors relative',
                            selectedDay === day.date ? 'bg-blue-50 text-blue-600 font-medium' : 'bg-white text-gray-700',
                            hasAvailableSlots(day) ? 'hover:bg-gray-50 cursor-pointer' : 'opacity-50 cursor-not-allowed bg-gray-100'
                        ]"
                    >
                        <div class="font-medium">{{ day.day_name }}</div>
                        <div class="text-xs text-gray-500">{{ day.formatted_date }}</div>
                        
                        <!-- Availability indicator -->
                        <div v-if="hasAvailableSlots(day)" class="absolute top-1 right-1">
                            <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                        </div>
                        <div v-else class="absolute top-1 right-1">
                            <div class="w-2 h-2 bg-red-400 rounded-full"></div>
                        </div>
                    </button>
                </div>

                <!-- Time Slots for Selected Day -->
                <div v-if="selectedDay" class="space-y-3">
                    <h4 class="text-sm font-medium text-gray-700">
                        Beschikbare tijden voor {{ getSelectedDayName() }}
                    </h4>
                    
                    <div class="space-y-2">
                        <div 
                            v-for="slot in getSlotsForDay(selectedDay)" 
                            :key="slot.id"
                            :class="[
                                'flex items-center justify-between p-4 border rounded-md transition-all duration-200',
                                selectedSlotId === slot.id 
                                    ? 'border-green-300 bg-green-50' 
                                    : slot.current_available === 0
                                        ? 'border-gray-200 bg-gray-50'
                                        : 'border-gray-200 hover:border-blue-300 hover:bg-blue-50'
                            ]"
                        >
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm font-medium">{{ slot.time_display }}</span>
                                    
                                    <!-- Availability status -->
                                    <span :class="[
                                        'text-xs px-2 py-1 rounded-full',
                                        slot.current_available === 0 
                                            ? 'bg-red-100 text-red-800'
                                            : slot.current_available <= 2
                                                ? 'bg-orange-100 text-orange-800'
                                                : 'bg-green-100 text-green-800'
                                    ]">
                                        <span v-if="slot.current_available === 0">Vol</span>
                                        <span v-else-if="slot.current_available <= 2">
                                            {{ slot.current_available }} {{ slot.current_available === 1 ? 'plek' : 'plekken' }}
                                        </span>
                                        <span v-else>Beschikbaar</span>
                                    </span>
                                </div>

                                <!-- Show last updated time for real-time data -->
                                <div v-if="slot.current_available !== slot.available_slots" class="mt-1">
                                    <span class="text-xs text-gray-500">
                                        Bijgewerkt {{ getTimeAgo(slot.last_updated) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <span class="text-sm font-medium text-gray-900">
                                    € {{ slot.price.toFixed(2) }}
                                </span>
                                
                                <button 
                                    @click="selectSlot(slot)"
                                    :disabled="isSelectingSlot || slot.current_available === 0"
                                    :class="[
                                        'px-4 py-2 text-sm border rounded-md font-medium transition-all duration-200',
                                        selectedSlotId === slot.id 
                                            ? 'bg-green-100 text-green-700 border-green-300 shadow-sm' 
                                            : slot.current_available === 0
                                                ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed'
                                                : 'bg-white text-gray-700 border-gray-300 hover:bg-blue-50 hover:border-blue-400',
                                        isSelectingSlot && selectedSlotId === slot.id ? 'opacity-50 cursor-not-allowed' : ''
                                    ]"
                                >
                                    <span v-if="isSelectingSlot && selectedSlotId === slot.id">
                                        <svg class="animate-spin h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Selecteren...
                                    </span>
                                    <span v-else-if="selectedSlotId === slot.id">
                                        <svg class="h-4 w-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        Geselecteerd
                                    </span>
                                    <span v-else-if="slot.current_available === 0">
                                        Vol
                                    </span>
                                    <span v-else>
                                        Selecteren
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Refresh slots button -->
                    <div class="flex justify-center mt-4">
                        <button 
                            @click="refreshSlots"
                            :disabled="isRefreshing"
                            class="text-sm text-blue-600 hover:text-blue-500 disabled:opacity-50 flex items-center space-x-1"
                        >
                            <svg :class="[
                                'h-4 w-4',
                                isRefreshing ? 'animate-spin' : ''
                            ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>{{ isRefreshing ? 'Vernieuwen...' : 'Vernieuw beschikbaarheid' }}</span>
                        </button>
                    </div>
                </div>

                <div v-else class="text-center text-gray-500 py-8">
                    <p>Selecteer een dag om beschikbare tijden te bekijken</p>
                </div>
            </div>

            <!-- No slots available -->
            <div v-else class="text-center py-8">
                <div class="text-gray-400 mb-4">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-gray-600 mb-4">Er zijn momenteel geen bezorgmomenten beschikbaar.</p>
                <button 
                    @click="refreshSlots"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                >
                    Opnieuw laden
                </button>
            </div>

            <!-- Selected Slot Confirmation -->
            <div v-if="selectedSlotDetails" class="mt-6 p-4 bg-green-50 border border-green-200 rounded-md">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-400 mr-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-green-800">Bezorgmoment geselecteerd</h4>
                        <p class="text-sm text-green-700">
                            {{ selectedSlotDetails.day_name }} {{ selectedSlotDetails.formatted_date }} 
                            om {{ selectedSlotDetails.time_display }}
                            - Bezorgkosten: € {{ selectedSlotDetails.price.toFixed(2) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Error message -->
            <div v-if="errorMessage" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-800">{{ errorMessage }}</p>
                        <button 
                            v-if="showRetryButton"
                            @click="retryLastAction"
                            class="mt-2 text-sm text-red-600 hover:text-red-500 underline"
                        >
                            Opnieuw proberen
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

// Props
const props = defineProps({
    deliverySlots: {
        type: Array,
        default: () => []
    },
    selectedSlotId: {
        type: Number,
        default: null
    }
});

// Emits
const emit = defineEmits(['slot-selected', 'delivery-fee-updated', 'refresh-slots']);

// Reactive state
const selectedDay = ref(null);
const isSelectingSlot = ref(false);
const isLoading = ref(false);
const isRefreshing = ref(false);
const errorMessage = ref(null);
const showRetryButton = ref(false);
const lastAction = ref(null);

// Methods - Define these first before using them
const hasAvailableSlots = (day) => {
    return day.slots && day.slots.some(slot => slot.current_available > 0);
};

const getSlotsForDay = (date) => {
    const day = props.deliverySlots.find(d => d.date === date);
    return day ? day.slots : [];
};

const getSelectedDayName = () => {
    if (!selectedDay.value) return '';
    const day = props.deliverySlots.find(d => d.date === selectedDay.value);
    return day ? `${day.day_name} ${day.formatted_date}` : '';
};

// Set first available day as default - do this after methods are defined
onMounted(() => {
    if (props.deliverySlots.length > 0) {
        const firstAvailableDay = props.deliverySlots.find(day => hasAvailableSlots(day));
        selectedDay.value = firstAvailableDay ? firstAvailableDay.date : props.deliverySlots[0].date;
    }
});

const selectDay = (date) => {
    selectedDay.value = date;
    errorMessage.value = null;
};

const getSelectedSlotDetails = () => {
    if (!props.selectedSlotId) return null;
    
    for (const day of props.deliverySlots) {
        const slot = day.slots ? day.slots.find(s => s.id === props.selectedSlotId) : null;
        if (slot) {
            return {
                ...slot,
                day_name: day.day_name,
                formatted_date: day.formatted_date
            };
        }
    }
    return null;
};

// Computed properties
const selectedSlotDetails = computed(() => getSelectedSlotDetails());

// Select delivery slot with enhanced error handling
const selectSlot = async (slot) => {
    if (isSelectingSlot.value || slot.current_available === 0) return;
    
    isSelectingSlot.value = true;
    errorMessage.value = null;
    showRetryButton.value = false;
    lastAction.value = { type: 'selectSlot', payload: slot };
    
    try {
        const response = await axios.post('/checkout/select-slot', {
            delivery_slot_id: slot.id
        });
        
        if (response.data.success) {
            // Emit events to parent component
            emit('slot-selected', {
                slotId: slot.id,
                deliveryFee: slot.price,
                slotDetails: {
                    ...slot,
                    day_name: props.deliverySlots.find(d => 
                        d.slots && d.slots.some(s => s.id === slot.id)
                    )?.day_name,
                    formatted_date: props.deliverySlots.find(d => 
                        d.slots && d.slots.some(s => s.id === slot.id)
                    )?.formatted_date
                }
            });
            
            emit('delivery-fee-updated', slot.price);
            
            // Show success message briefly
            const tempMessage = errorMessage.value;
            errorMessage.value = null;
            setTimeout(() => {
                if (!errorMessage.value) {
                    // Success feedback could go here
                }
            }, 100);
        } else {
            throw new Error(response.data.message || 'Failed to select slot');
        }
        
    } catch (error) {
        console.error('Error selecting slot:', error);
        
        if (error.response?.status === 422) {
            errorMessage.value = error.response.data.message || 'Dit bezorgmoment is niet meer beschikbaar.';
        } else if (error.response?.status === 500) {
            errorMessage.value = 'Er is een serverfout opgetreden. Probeer het later opnieuw.';
            showRetryButton.value = true;
        } else {
            errorMessage.value = error.message || 'Er is een fout opgetreden bij het selecteren van het bezorgmoment.';
            showRetryButton.value = true;
        }
    } finally {
        isSelectingSlot.value = false;
    }
};

// Refresh delivery slots
const refreshSlots = async () => {
    if (isRefreshing.value) return;
    
    isRefreshing.value = true;
    errorMessage.value = null;
    
    try {
        // Emit refresh event to parent to reload delivery slots
        emit('refresh-slots');
        
        // You could also make a direct API call here if needed
        // const response = await axios.get('/api/delivery-slots');
        // Handle response...
        
    } catch (error) {
        console.error('Error refreshing slots:', error);
        errorMessage.value = 'Kon beschikbaarheid niet vernieuwen. Probeer het opnieuw.';
        showRetryButton.value = true;
    } finally {
        isRefreshing.value = false;
    }
};

// Retry last failed action
const retryLastAction = () => {
    if (!lastAction.value) return;
    
    switch (lastAction.value.type) {
        case 'selectSlot':
            selectSlot(lastAction.value.payload);
            break;
        case 'refreshSlots':
            refreshSlots();
            break;
    }
};

// Get time ago helper
const getTimeAgo = (timestamp) => {
    if (!timestamp) return '';
    
    const now = new Date();
    const time = new Date(timestamp);
    const diffInMinutes = Math.floor((now - time) / (1000 * 60));
    
    if (diffInMinutes < 1) return 'zojuist';
    if (diffInMinutes < 60) return `${diffInMinutes} min geleden`;
    if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)} uur geleden`;
    return time.toLocaleDateString();
};

// Watch for changes in selected slot and emit delivery fee updates
watch(() => selectedSlotDetails.value, (newSlot) => {
    if (newSlot) {
        emit('delivery-fee-updated', newSlot.price);
    }
}, { immediate: true });

// Auto-refresh slot availability every 2 minutes
let refreshInterval;
onMounted(() => {
    refreshInterval = setInterval(() => {
        if (!isRefreshing.value && !isSelectingSlot.value) {
            refreshSlots();
        }
    }, 120000); // 2 minutes
});

onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<style scoped>
/* Custom styles for better mobile responsiveness */
@media (max-width: 640px) {
    .grid-cols-7 {
        grid-template-columns: repeat(3, 1fr);
        gap: 0.25rem;
    }
    
    .grid-cols-7 button {
        padding: 0.75rem 0.5rem;
    }
}

@media (max-width: 480px) {
    .grid-cols-7 {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Enhanced button states */
.transition-all {
    transition: all 0.2s ease-in-out;
}

/* Loading animation for buttons */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Custom focus styles */
button:focus {
    outline: 2px solid #3B82F6;
    outline-offset: 2px;
}

/* Availability indicator animations */
.bg-green-400, .bg-red-400 {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.8); }
    to { opacity: 1; transform: scale(1); }
}
</style>