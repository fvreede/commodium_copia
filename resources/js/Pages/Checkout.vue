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
    <NavBar />
    
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Header -->
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Bezorgmoment kiezen</h1>
            
            <!-- Progress Steps -->
            <div class="flex items-center justify-center mb-8 bg-white p-4 rounded-lg shadow">
                <div class="flex items-center space-x-8">
                    <div class="flex items-center">
                        <div class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-medium">1</div>
                        <span class="ml-2 text-sm font-medium text-blue-600">Kies uw bezorgmoment</span>
                    </div>
                    <div class="w-16 h-0.5 bg-gray-300"></div>
                    <div class="flex items-center">
                        <div class="bg-gray-300 text-gray-600 rounded-full w-8 h-8 flex items-center justify-center text-sm font-medium">2</div>
                        <span class="ml-2 text-sm font-medium text-gray-500">Doe uw boodschappen</span>
                    </div>
                    <div class="w-16 h-0.5 bg-gray-300"></div>
                    <div class="flex items-center">
                        <div class="bg-gray-300 text-gray-600 rounded-full w-8 h-8 flex items-center justify-center text-sm font-medium">3</div>
                        <span class="ml-2 text-sm font-medium text-gray-500">Bevestig uw bestelling</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Day Selection -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow p-6">
                        <!-- Day Tabs -->
                        <div class="grid grid-cols-7 gap-2 mb-6">
                            <div 
                                v-for="day in deliverySlots" 
                                :key="day.date"
                                @click="selectedDay = day.date"
                                :class="[
                                    'text-center p-3 rounded-lg cursor-pointer transition-colors',
                                    selectedDay === day.date 
                                        ? 'bg-blue-600 text-white' 
                                        : 'bg-gray-100 hover:bg-gray-200 text-gray-700'
                                ]"
                            >
                                <div class="text-sm font-medium">{{ day.day_name }}</div>
                                <div class="text-xs">{{ day.formatted_date }}</div>
                            </div>
                        </div>

                        <!-- Time Slots for Selected Day -->
                        <div v-if="selectedDay">
                            <h3 class="text-lg font-semibold mb-4">
                                Bezorgmomenten voor {{ deliverySlots.find(d => d.date === selectedDay)?.day_name }} {{ deliverySlots.find(d => d.date === selectedDay)?.formatted_date }}
                            </h3>
                            
                            <div class="space-y-3">
                                <div 
                                    v-for="slot in getSlotsForDay(selectedDay)" 
                                    :key="slot.id"
                                    class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50"
                                >
                                    <div class="flex-1">
                                        <div class="font-medium">{{ slot.time_display }}</div>
                                        <div class="text-green-600 font-semibold">€ {{ slot.price.toFixed(2) }}</div>
                                    </div>
                                    <button 
                                        @click="selectDeliverySlot(slot.id)"
                                        :class="[
                                            'px-6 py-2 rounded-lg font-medium transition-colors',
                                            selectedSlot === slot.id 
                                                ? 'bg-green-600 text-white' 
                                                : 'bg-blue-600 text-white hover:bg-blue-700'
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

                <!-- Delivery Address -->
                <div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold mb-4">Bezorgadres</h3>
                        
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <p class="text-sm text-gray-600 mb-2">Uw boodschappen worden bezorgd op dit adres:</p>
                            <p class="font-medium">{{ formatAddress() }}</p>
                        </div>
                        
                        <button class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            Bezorgadres wijzigen
                        </button>
                    </div>

                    <!-- Cart Summary (Empty for now) -->
                    <div class="bg-white rounded-lg shadow p-6 mt-6">
                        <h3 class="text-lg font-semibold mb-4">Uw winkelwagen</h3>
                        
                        <div v-if="cartItems.length === 0" class="text-center text-gray-500 py-6">
                            <p>Uw winkelwagen is leeg</p>
                            <p class="text-sm mt-2">Voeg producten toe om door te gaan</p>
                        </div>
                        
                        <div v-else>
                            <!-- Cart items will be displayed here in assignment 4 -->
                            <div class="border-t pt-4 mt-4">
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
    
    <Footer />
</template>