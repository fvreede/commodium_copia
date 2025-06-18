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
                        @click="selectedDay = day.date"
                        :class="[
                            'px-3 py-3 text-sm hover:bg-gray-50 border-r last:border-r-0 transition-colors',
                            selectedDay === day.date ? 'bg-blue-50 text-blue-600 font-medium' : 'bg-white text-gray-700'
                        ]"
                    >
                        <div class="font-medium">{{ day.day_name }}</div>
                        <div class="text-xs text-gray-500">{{ day.formatted_date }}</div>
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
                            class="flex items-center justify-between p-4 border rounded-md hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm font-medium">{{ slot.time_display }}</span>
                                    <span class="text-xs text-gray-500">
                                        ({{ slot.available_slots }} {{ slot.available_slots === 1 ? 'plek' : 'plekken' }} beschikbaar)
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <span class="text-sm font-medium text-gray-900">
                                    € {{ slot.price.toFixed(2) }}
                                </span>
                                
                                <button 
                                    @click="selectSlot(slot)"
                                    :disabled="isSelectingSlot || slot.available_slots === 0"
                                    :class="[
                                        'px-4 py-2 text-sm border rounded-md font-medium transition-colors',
                                        selectedSlotId === slot.id 
                                            ? 'bg-green-50 text-green-700 border-green-300' 
                                            : slot.available_slots === 0
                                                ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed'
                                                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                                        isSelectingSlot ? 'opacity-50 cursor-not-allowed' : ''
                                    ]"
                                >
                                    <span v-if="isSelectingSlot && selectedSlotId === slot.id">
                                        Selecteren...
                                    </span>
                                    <span v-else-if="selectedSlotId === slot.id">
                                        ✓ Geselecteerd
                                    </span>
                                    <span v-else-if="slot.available_slots === 0">
                                        Vol
                                    </span>
                                    <span v-else>
                                        Selecteren
                                    </span>
                                </button>
                            </div>
                        </div>
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
                <p class="text-gray-600">Er zijn momenteel geen bezorgmomenten beschikbaar.</p>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

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
const emit = defineEmits(['slot-selected', 'delivery-fee-updated']);

// Reactive state
const selectedDay = ref(null);
const isSelectingSlot = ref(false);
const isLoading = ref(false);
const errorMessage = ref(null);

// Set first available day as default
if (props.deliverySlots.length > 0) {
    selectedDay.value = props.deliverySlots[0].date;
}

// Get slots for selected day
const getSlotsForDay = (date) => {
    const day = props.deliverySlots.find(d => d.date === date);
    return day ? day.slots : [];
};

// Get selected day name
const getSelectedDayName = () => {
    if (!selectedDay.value) return '';
    const day = props.deliverySlots.find(d => d.date === selectedDay.value);
    return day ? `${day.day_name} ${day.formatted_date}` : '';
};

// Get selected slot details
const selectedSlotDetails = computed(() => {
    if (!props.selectedSlotId) return null;
    
    for (const day of props.deliverySlots) {
        const slot = day.slots.find(s => s.id === props.selectedSlotId);
        if (slot) {
            return {
                ...slot,
                day_name: day.day_name,
                formatted_date: day.formatted_date
            };
        }
    }
    return null;
});

// Select delivery slot
const selectSlot = async (slot) => {
    if (isSelectingSlot.value || slot.available_slots === 0) return;
    
    isSelectingSlot.value = true;
    errorMessage.value = null;
    
    try {
        await router.post('/checkout/select-slot', {
            delivery_slot_id: slot.id
        }, {
            preserveState: true,
            onSuccess: (page) => {
                // Emit events to parent component
                emit('slot-selected', {
                    slotId: slot.id,
                    deliveryFee: slot.price,
                    slotDetails: {
                        ...slot,
                        day_name: props.deliverySlots.find(d => 
                            d.slots.some(s => s.id === slot.id)
                        )?.day_name,
                        formatted_date: props.deliverySlots.find(d => 
                            d.slots.some(s => s.id === slot.id)
                        )?.formatted_date
                    }
                });
                
                emit('delivery-fee-updated', slot.price);
            },
            onError: (errors) => {
                console.error('Error selecting slot:', errors);
                if (errors.slot) {
                    errorMessage.value = errors.slot;
                } else {
                    errorMessage.value = 'Er is een fout opgetreden bij het selecteren van het bezorgmoment.';
                }
            }
        });
    } catch (error) {
        console.error('Unexpected error:', error);
        errorMessage.value = 'Er is een onverwachte fout opgetreden. Probeer het opnieuw.';
    } finally {
        isSelectingSlot.value = false;
    }
};

// Watch for changes in selected slot and emit delivery fee updates
watch(() => selectedSlotDetails.value, (newSlot) => {
    if (newSlot) {
        emit('delivery-fee-updated', newSlot.price);
    }
}, { immediate: true });
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
</style>