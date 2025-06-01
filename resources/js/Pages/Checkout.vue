<script setup>
import NavBar from '@/Components/NavBar.vue';
import Footer from '@/Components/Footer.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

// Props passed from Laravel
const props = defineProps({
    deliverySlots: Array,
    deliveryAddress: Object,
    cartItems: Array,
    cartTotal: Number
});

// Reactive state
const selectedDay = ref(null);
const selectedSlot = ref(null);

// Set first day as default if available
if (props.deliverySlots.length > 0) {
    selectedDay.value = props.deliverySlots[0].date;
}

// Get slots for selected day
const getSlotsForDay = (date) => {
    const day = props.deliverySlots.find(d => d.date === date);
    return day ? day.slots : [];
};

// Select delivery slot
const selectDeliverySlot = (slotId) => {
    router.post('/checkout/select-slot', {
        delivery_slot_id: slotId
    }, {
        preserveState: true,
        onSuccess: () => {
            selectedSlot.value = slotId;
        }
    });
};

// Format address for display
const formatAddress = () => {
    const addr = props.deliveryAddress;
    return `${addr.street}, ${addr.postal_code} ${addr.city}`;
};
</script>

<template>
    <!-- Navigatiebalk -->
    <NavBar />

    <!-- Hoofdcontainer voor checkout sectie -->
    <div class="bg-gray-100">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl py-16 sm:py-24 lg:max-w-none lg:py-32">
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
                                        <span class="text-sm text-gray-600">Stap 2: Doe uw boodschappen</span>
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

                <!-- Date Selection -->
                <div class="mt-8">
                    <div class="grid grid-cols-7 gap-0 border rounded overflow-hidden">
                        <button 
                            v-for="day in deliverySlots" 
                            :key="day.date"
                            @click="selectedDay = day.date"
                            :class="[
                                'px-4 py-3 text-sm hover:bg-gray-50 border-r last:border-r-0',
                                selectedDay === day.date ? 'bg-blue-50 text-blue-600' : 'bg-white'
                            ]"
                        >
                            <div class="font-medium">{{ day.day_name }}</div>
                            <div class="text-xs text-gray-600">{{ day.formatted_date }}</div>
                        </button>
                    </div>
                </div>

                <!-- Delivery Slots -->
                <div class="mt-8">
                    <div class="bg-white border rounded p-6">
                        <h2 v-if="selectedDay" class="text-lg font-medium mb-6">
                            Bezorgmomenten voor {{ deliverySlots.find(d => d.date === selectedDay)?.day_name }} {{ deliverySlots.find(d => d.date === selectedDay)?.formatted_date }}
                        </h2>
                        
                        <div v-if="selectedDay" class="space-y-4">
                            <div 
                                v-for="slot in getSlotsForDay(selectedDay)" 
                                :key="slot.id"
                                class="flex items-center justify-between py-3 border-b last:border-b-0"
                            >
                                <span class="text-sm">{{ slot.time_display }}</span>
                                <span class="text-sm">€ {{ slot.price.toFixed(2) }}</span>
                                <button 
                                    @click="selectDeliverySlot(slot.id)"
                                    :class="[
                                        'px-6 py-2 text-sm border hover:bg-gray-50',
                                        selectedSlot === slot.id ? 'bg-green-50 text-green-700 border-green-300' : 'bg-white'
                                    ]"
                                >
                                    {{ selectedSlot === slot.id ? 'Gekozen' : 'Kies' }}
                                </button>
                            </div>
                        </div>

                        <div v-else class="text-center text-gray-500 py-8">
                            Selecteer een dag om bezorgmomenten te bekijken
                        </div>
                    </div>
                </div>

                <!-- Address Section -->
                <div class="mt-8">
                    <div class="bg-white border rounded p-6">
                        <h3 class="text-sm font-medium mb-4">Uw boodschappen worden bezorgd op dit adres:</h3>
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="w-64 h-32 bg-gray-100 border rounded flex items-center justify-center">
                                    <div class="text-center">
                                        <div class="text-sm text-gray-500 mb-2">Adresgegevens</div>
                                        <div class="text-xs text-gray-700">{{ formatAddress() }}</div>
                                    </div>
                                </div>
                            </div>
                            <button class="px-6 py-2 bg-white border text-sm hover:bg-gray-50">
                                Bezorgadres wijzigen
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Cart Summary (Optional - since you have cart in navbar) -->
                <div class="mt-8">
                    <div class="bg-white border rounded p-6">
                        <h3 class="text-lg font-medium mb-4">Bestelling samenvatting</h3>
                        
                        <div v-if="cartItems.length === 0" class="text-center text-gray-500 py-6">
                            <p class="text-sm">Uw winkelwagen is leeg</p>
                            <p class="text-xs mt-2 text-gray-400">Voeg producten toe om door te gaan</p>
                        </div>
                        
                        <div v-else>
                            <!-- Cart items summary -->
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span>Aantal artikelen:</span>
                                    <span>{{ cartItems.length }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Subtotaal:</span>
                                    <span>€ {{ cartTotal.toFixed(2) }}</span>
                                </div>
                            </div>
                            <div class="border-t pt-4">
                                <div class="flex justify-between font-semibold">
                                    <span>Totaal:</span>
                                    <span>€ {{ cartTotal.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <Footer />
</template>