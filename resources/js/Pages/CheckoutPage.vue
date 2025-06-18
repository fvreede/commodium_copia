<!-- resources/js/Pages/CheckoutPage.vue -->
<script setup>
import NavBar from '@/Components/NavBar.vue';
import Footer from '@/Components/Footer.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios'

// Props passed from Laravel
const props = defineProps({
    deliverySlots: Array,
    deliveryAddress: Object, // This can be null/undefined
    cartItems: Array,
    cartTotal: Number,
    selectedSlotId: Number
});

// Reactive state
const selectedDay = ref(null);
const selectedSlot = ref(props.selectedSlotId || null);
const isSelectingSlot = ref(false);

// Set first day as default if available
if (props.deliverySlots.length > 0) {
    selectedDay.value = props.deliverySlots[0].date;
}

// Get slots for selected day
const getSlotsForDay = (date) => {
    const day = props.deliverySlots.find(d => d.date === date);
    return day ? day.slots : [];
};

// Calculate delivery fee based on selected slot
const deliveryFee = computed(() => {
    if (!selectedSlot.value) return 0;
    
    for (const day of props.deliverySlots) {
        const slot = day.slots.find(s => s.id === selectedSlot.value);
        if (slot) return slot.price;
    }
    return 0;
});

// Calculate total order amount
const orderTotal = computed(() => {
    return props.cartTotal + deliveryFee.value;
});

// Check if user can proceed to next step
const canProceed = computed(() => {
    return props.cartItems.length > 0 && selectedSlot.value && hasValidAddress.value;
});

// Check if user has a valid delivery address
const hasValidAddress = computed(() => {
    return props.deliveryAddress && 
           props.deliveryAddress.street && 
           props.deliveryAddress.postal_code && 
           props.deliveryAddress.city;
});

// Select delivery slot
const selectDeliverySlot = async (slotId) => {
    if (isSelectingSlot.value) return;
    
    isSelectingSlot.value = true;
    
    try {
        const response = await axios.post('/checkout/select-slot', {
            delivery_slot_id: slotId
        });
        
        selectedSlot.value = slotId;
        // Optionally show success message
        
    } catch (error) {
        console.error('Error selecting slot:', error);
        // Handle error appropriately
    } finally {
        isSelectingSlot.value = false;
    }
};

// Proceed to confirmation
const proceedToConfirmation = () => {
    if (!canProceed.value) return;
    
    router.get('/checkout/confirm');
};

// Format address for display - SAFE VERSION
const formatAddress = () => {
    if (!hasValidAddress.value) {
        return 'Geen adres ingesteld';
    }
    
    const addr = props.deliveryAddress;
    let formatted = addr.street;
    
    if (addr.house_number) {
        formatted += ` ${addr.house_number}`;
    }
    
    formatted += `, ${addr.postal_code} ${addr.city}`;
    
    return formatted;
};

// Get selected slot details for display
const selectedSlotDetails = computed(() => {
    if (!selectedSlot.value) return null;
    
    for (const day of props.deliverySlots) {
        const slot = day.slots.find(s => s.id === selectedSlot.value);
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
</script>

<template>
    <!-- Navigatiebalk -->
    <NavBar />

    <!-- Hoofdcontainer voor checkout sectie -->
    <div class="bg-gray-100 min-h-screen">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-4xl py-16 sm:py-24">
                <h2 class="text-2xl font-bold text-gray-900">Bezorgmoment kiezen</h2>

                <!-- Breadcrumb Steps -->
                <div class="mt-6">
                    <div class="bg-white border rounded">
                        <div class="px-4 py-2 text-sm font-medium">Kop bestellen</div>
                        <div class="border-t">
                            <div class="grid grid-cols-3">
                                <div class="px-4 py-3 border-r bg-blue-50">
                                    <div class="flex items-center">
                                        <span class="text-sm">Stap 1: Kies uw bezorgmoment</span>
                                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="px-4 py-3 border-r">
                                    <div class="flex items-center">
                                        <span class="text-sm text-gray-600">Stap 2: Controleer uw bestelling</span>
                                        <svg class="w-4 h-4 ml-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="px-4 py-3">
                                    <span class="text-sm text-gray-600">Stap 3: Bevestig uw bestelling</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert if cart is empty -->
                <div v-if="cartItems.length === 0" class="mt-8 bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Winkelwagen is leeg</h3>
                            <p class="mt-1 text-sm text-yellow-700">Voeg eerst producten toe aan uw winkelwagen voordat u kunt bestellen.</p>
                        </div>
                    </div>
                </div>

                <!-- Alert if no delivery address -->
                <div v-if="cartItems.length > 0 && !hasValidAddress" class="mt-8 bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Geen bezorgadres ingesteld</h3>
                            <p class="mt-1 text-sm text-red-700">Stel eerst een bezorgadres in om verder te kunnen gaan met bestellen.</p>
                            <div class="mt-2">
                                <button class="px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition-colors">
                                    Adres instellen
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date Selection -->
                <div v-if="cartItems.length > 0 && hasValidAddress" class="mt-8">
                    <h3 class="text-lg font-medium mb-4">Kies een bezorgdag</h3>
                    <div class="grid grid-cols-7 gap-0 border rounded overflow-hidden">
                        <button 
                            v-for="day in deliverySlots" 
                            :key="day.date"
                            @click="selectedDay = day.date"
                            :class="[
                                'px-4 py-3 text-sm hover:bg-gray-50 border-r last:border-r-0 transition-colors',
                                selectedDay === day.date ? 'bg-blue-50 text-blue-600' : 'bg-white'
                            ]"
                        >
                            <div class="font-medium">{{ day.day_name }}</div>
                            <div class="text-xs text-gray-600">{{ day.formatted_date }}</div>
                        </button>
                    </div>
                </div>

                <!-- Delivery Slots -->
                <div v-if="cartItems.length > 0 && hasValidAddress" class="mt-8">
                    <div class="bg-white border rounded p-6">
                        <h3 v-if="selectedDay" class="text-lg font-medium mb-6">
                            Bezorgmomenten voor {{ deliverySlots.find(d => d.date === selectedDay)?.day_name }} {{ deliverySlots.find(d => d.date === selectedDay)?.formatted_date }}
                        </h3>
                        
                        <div v-if="selectedDay" class="space-y-4">
                            <div 
                                v-for="slot in getSlotsForDay(selectedDay)" 
                                :key="slot.id"
                                class="flex items-center justify-between py-3 border-b last:border-b-0"
                            >
                                <div class="flex-1">
                                    <span class="text-sm font-medium">{{ slot.time_display }}</span>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <span class="text-sm font-medium">€ {{ slot.price.toFixed(2) }}</span>
                                    <button 
                                        @click="selectDeliverySlot(slot.id)"
                                        :disabled="isSelectingSlot"
                                        :class="[
                                            'px-6 py-2 text-sm border hover:bg-gray-50 transition-colors rounded',
                                            selectedSlot === slot.id 
                                                ? 'bg-green-50 text-green-700 border-green-300' 
                                                : 'bg-white border-gray-300',
                                            isSelectingSlot ? 'opacity-50 cursor-not-allowed' : ''
                                        ]"
                                    >
                                        {{ selectedSlot === slot.id ? 'Gekozen' : 'Kies' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center text-gray-500 py-8">
                            Selecteer een dag om bezorgmomenten te bekijken
                        </div>
                    </div>
                </div>

                <!-- Selected Slot Confirmation -->
                <div v-if="selectedSlotDetails" class="mt-8">
                    <div class="bg-green-50 border border-green-200 rounded p-4">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-green-400 mr-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium text-green-800">Bezorgmoment geselecteerd</h4>
                                <p class="text-sm text-green-700">
                                    {{ selectedSlotDetails.day_name }} {{ selectedSlotDetails.formatted_date }} om {{ selectedSlotDetails.time_display }}
                                    (€ {{ selectedSlotDetails.price.toFixed(2) }})
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Section -->
                <div v-if="cartItems.length > 0" class="mt-8">
                    <div class="bg-white border rounded p-6">
                        <h3 class="text-lg font-medium mb-4">Bezorgadres</h3>
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div v-if="hasValidAddress" class="p-4 bg-gray-50 border rounded">
                                    <div class="text-sm text-gray-700">{{ formatAddress() }}</div>
                                </div>
                                <div v-else class="p-4 bg-red-50 border border-red-200 rounded">
                                    <div class="text-sm text-red-700">{{ formatAddress() }}</div>
                                </div>
                            </div>
                            <button class="ml-4 px-6 py-2 bg-white border text-sm hover:bg-gray-50 rounded transition-colors">
                                {{ hasValidAddress ? 'Wijzigen' : 'Instellen' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div v-if="cartItems.length > 0" class="mt-8">
                    <div class="bg-white border rounded p-6">
                        <h3 class="text-lg font-medium mb-4">Bestelling samenvatting</h3>
                        
                        <!-- Cart items -->
                        <div class="space-y-3 mb-6">
                            <div 
                                v-for="item in cartItems" 
                                :key="item.id"
                                class="flex items-center justify-between py-2 border-b"
                            >
                                <div class="flex items-center space-x-3">
                                    <img 
                                        :src="item.image_path" 
                                        :alt="item.name"
                                        class="w-12 h-12 object-cover rounded"
                                        @error="$event.target.src = '/images/placeholder.jpg'"
                                    >
                                    <div>
                                        <p class="text-sm font-medium">{{ item.name }}</p>
                                        <p class="text-xs text-gray-500">{{ item.quantity }}x</p>
                                    </div>
                                </div>
                                <span class="text-sm font-medium">€ {{ item.line_total.toFixed(2) }}</span>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="space-y-2 border-t pt-4">
                            <div class="flex justify-between text-sm">
                                <span>Subtotaal ({{ cartItems.length }} artikel{{ cartItems.length > 1 ? 'en' : '' }}):</span>
                                <span>€ {{ cartTotal.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span>Bezorgkosten:</span>
                                <span>€ {{ deliveryFee.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-semibold border-t pt-2">
                                <span>Totaal:</span>
                                <span>€ {{ orderTotal.toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div v-if="cartItems.length > 0" class="mt-8 flex items-center justify-between">
                    <button 
                        @click="router.get('/cart')"
                        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
                    >
                        ← Terug naar winkelwagen
                    </button>

                    <button 
                        @click="proceedToConfirmation"
                        :disabled="!canProceed"
                        :class="[
                            'px-8 py-3 rounded-md font-medium transition-colors',
                            canProceed 
                                ? 'bg-blue-600 text-white hover:bg-blue-700' 
                                : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                        ]"
                    >
                        Verder naar bevestiging →
                    </button>
                </div>

                <!-- Help Text -->
                <div v-if="cartItems.length > 0 && !canProceed" class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        <span v-if="!hasValidAddress">Stel eerst een bezorgadres in en </span>
                        <span v-if="!selectedSlot">selecteer een bezorgmoment om verder te gaan</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <Footer />
</template>

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