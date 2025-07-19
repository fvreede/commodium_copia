/**
 * Bestandsnaam: DeliverySlotSelector.vue
 * Auteur: Fabio Vreede
 * Versie: v1.0.4
 * Datum: 2025-06-29
 * Tijd: 15:34:53
 * Doel: Geavanceerde bezorgslot selector component voor checkout proces. Biedt responsive dag- en tijdselectie met real-time beschikbaarheid, automatische verversing, foutafhandeling en mobile-first design voor optimale gebruikerservaring tijdens het afrekenen.
 */

<!-- resources/js/Components/Checkout/DeliverySlotSelector.vue -->
<template>
    <div class="space-y-6">
        <!-- Laadstatus -->
        <!-- Toont loading spinner terwijl bezorgslots worden opgehaald -->
        <div v-if="isLoading" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-600 font-medium">Bezorgmomenten laden...</p>
        </div>

        <!-- Datum Selectie Interface -->
        <!-- Horizontaal scrollbare dag selector met beschikbaarheid indicatoren -->
        <div v-else-if="deliverySlots.length > 0">
            <h4 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z" />
                    </svg>
                </div>
                Selecteer een dag
            </h4>
            
            <!-- Dag Selector Grid -->
            <!-- Responsive grid met dag knoppen en beschikbaarheid status -->
            <div class="flex flex-wrap gap-8 justify-center sm:justify-start mb-8">
                <button 
                    v-for="day in deliverySlots" 
                    :key="day.date"
                    @click="selectDay(day.date)"
                    :disabled="!hasAvailableSlots(day)"
                    :class="[
                        'relative inline-flex flex-col items-center justify-center rounded-lg border text-sm font-semibold shadow-sm transition duration-150 ease-in-out focus:outline-none disabled:opacity-25 p-6 min-h-[100px] w-[110px] flex-shrink-0',
                        selectedDay === day.date 
                            ? 'border-green-500 bg-green-500 text-white hover:bg-green-600' 
                            : hasAvailableSlots(day)
                                ? 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
                                : 'border-gray-300 bg-gray-100 text-gray-400 cursor-not-allowed'
                    ]"
                >
                    <div class="font-semibold">{{ day.day_name }}</div>
                    <div :class="[
                        'text-xs mt-1.5',
                        selectedDay === day.date 
                            ? 'text-green-100' 
                            : hasAvailableSlots(day) 
                                ? 'text-gray-500' 
                                : 'text-gray-400'
                    ]">
                        {{ day.formatted_date }}
                    </div>
                    
                    <!-- Beschikbaarheid Indicator -->
                    <!-- Kleurcoded bolletje toont slot beschikbaarheid -->
                    <div class="absolute top-3 right-3">
                        <div :class="[
                            'w-3 h-3 rounded-full',
                            hasAvailableSlots(day) 
                                ? selectedDay === day.date 
                                    ? 'bg-white' 
                                    : 'bg-green-400'
                                : 'bg-red-400'
                        ]"></div>
                    </div>
                </button>
            </div>

            <!-- Tijdslot Selectie voor Gekozen Dag -->
            <!-- Dynamische tijdslot lijst met real-time beschikbaarheid en prijzen -->
            <div v-if="selectedDay" class="space-y-4">
                <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    Beschikbare tijden voor {{ getSelectedDayName() }}
                </h4>
                
                <!-- Tijdslot Grid -->
                <!-- Responsive grid met mobile/desktop verschillende layouts -->
                <div class="grid gap-4">
                    <div 
                        v-for="slot in getSlotsForDay(selectedDay)" 
                        :key="slot.id"
                        :class="[
                            'p-4 sm:p-5 rounded-xl border-2 transition-all duration-300',
                            slot.current_available === 0
                                ? 'bg-gray-50 border-gray-200 opacity-60'
                                : 'bg-white border-gray-200 hover:border-gray-300 hover:shadow-md'
                        ]"
                    >
                        <!-- Mobile Layout (Verticaal Gestapeld) -->
                        <!-- Mobiel-geoptimaliseerde layout met volledige breedte elementen -->
                        <div class="block sm:hidden space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-gray-900">
                                    {{ slot.time_display }}
                                </span>
                                <span class="text-lg font-bold text-gray-900">
                                    €{{ slot.price.toFixed(2) }}
                                </span>
                            </div>
                            
                            <!-- Beschikbaarheid Status Badge -->
                            <!-- Kleurcoded status indicator voor slot beschikbaarheid -->
                            <div class="flex justify-center">
                                <span :class="[
                                    'text-xs px-3 py-1 rounded-full font-medium',
                                    slot.current_available === 0 
                                        ? 'bg-red-500 text-white'
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

                            <!-- Real-time Update Indicator -->
                            <!-- Toont wanneer data voor het laatst is bijgewerkt voor transparantie -->
                            <div v-if="slot.current_available !== slot.available_slots" class="text-center">
                                <span class="text-xs text-gray-500">
                                    ⏱️ Bijgewerkt {{ getTimeAgo(slot.last_updated) }}
                                </span>
                            </div>
                            
                            <!-- Volledige Breedte Selectie Knop (Mobile) -->
                            <!-- Touch-vriendelijke knop met loading en geselecteerde staten -->
                            <button 
                                @click="selectSlot(slot)"
                                :disabled="isSelectingSlot || slot.current_available === 0"
                                :class="[
                                    'w-full inline-flex items-center justify-center rounded-md border text-sm font-semibold uppercase tracking-widest shadow-sm transition duration-150 ease-in-out focus:outline-none disabled:opacity-25 px-4 py-3',
                                    selectedSlotId === slot.id 
                                        ? 'border-green-500 bg-green-500 text-white hover:bg-green-600 shadow-md' 
                                        : slot.current_available === 0
                                            ? 'border-gray-300 bg-gray-100 text-gray-400 cursor-not-allowed'
                                            : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50',
                                    isSelectingSlot && selectedSlotId === slot.id ? 'opacity-25 cursor-not-allowed' : ''
                                ]"
                            >
                                <!-- Loading Status (Selecteren...) -->
                                <span v-if="isSelectingSlot && selectedSlotId === slot.id" class="flex items-center justify-center">
                                    <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Selecteren...
                                </span>
                                <!-- Geselecteerd Status met Checkmark -->
                                <span v-else-if="selectedSlotId === slot.id" class="flex items-center justify-center">
                                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Geselecteerd
                                </span>
                                <!-- Vol Status (Niet Beschikbaar) -->
                                <span v-else-if="slot.current_available === 0">
                                    Vol
                                </span>
                                <!-- Standaard Selectie Status -->
                                <span v-else>
                                    Selecteren
                                </span>
                            </button>
                        </div>

                        <!-- Desktop Layout (Horizontaal Naast Elkaar) -->
                        <!-- Desktop geoptimaliseerde layout met compacte weergave -->
                        <div class="hidden sm:flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-4 mb-2">
                                    <span class="text-lg font-bold text-gray-900">
                                        {{ slot.time_display }}
                                    </span>
                                    
                                    <!-- Beschikbaarheid Status Badge (Desktop) -->
                                    <span :class="[
                                        'text-xs px-3 py-1 rounded-full font-medium',
                                        slot.current_available === 0 
                                            ? 'bg-red-500 text-white'
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

                                <!-- Real-time Update Indicator (Desktop) -->
                                <div v-if="slot.current_available !== slot.available_slots" class="mb-2">
                                    <span class="text-xs text-gray-500">
                                        ⏱️ Bijgewerkt {{ getTimeAgo(slot.last_updated) }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Prijs en Selectie Actie (Desktop) -->
                            <!-- Compacte prijs weergave met selectie knop -->
                            <div class="flex items-center space-x-4">
                                <span class="text-xl font-bold text-gray-900">
                                    €{{ slot.price.toFixed(2) }}
                                </span>
                                
                                <!-- Compacte Selectie Knop (Desktop) -->
                                <button 
                                    @click="selectSlot(slot)"
                                    :disabled="isSelectingSlot || slot.current_available === 0"
                                    :class="[
                                        'inline-flex items-center justify-center rounded-md border text-xs font-semibold uppercase tracking-widest shadow-sm transition duration-150 ease-in-out focus:outline-none disabled:opacity-25 min-w-[140px] px-4 py-2',
                                        selectedSlotId === slot.id 
                                            ? 'border-green-500 bg-green-500 text-white hover:bg-green-600 shadow-md' 
                                            : slot.current_available === 0
                                                ? 'border-gray-300 bg-gray-100 text-gray-400 cursor-not-allowed'
                                                : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50',
                                        isSelectingSlot && selectedSlotId === slot.id ? 'opacity-25 cursor-not-allowed' : ''
                                    ]"
                                >
                                    <!-- Dezelfde loading/geselecteerd/vol/selecteren staten als mobile -->
                                    <span v-if="isSelectingSlot && selectedSlotId === slot.id" class="flex items-center justify-center">
                                        <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Selecteren...
                                    </span>
                                    <span v-else-if="selectedSlotId === slot.id" class="flex items-center justify-center">
                                        <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
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
                </div>

                <!-- Handmatige Ververs Knop -->
                <!-- Geeft gebruikers controle over data verversing naast automatische updates -->
                <div class="flex justify-center mt-6">
                    <button 
                        @click="refreshSlots"
                        :disabled="isRefreshing"
                        class="inline-flex items-center space-x-2 px-3 py-2 text-sm text-gray-600 hover:text-gray-500 disabled:opacity-50 transition-colors rounded-md hover:bg-gray-50"
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

            <!-- Geen Dag Geselecteerd Boodschap -->
            <!-- Begeleidt gebruiker naar het selecteren van een dag -->
            <div v-else class="text-center py-12 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border-2 border-dashed border-gray-300">
                <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z" />
                    </svg>
                </div>
                <p class="text-gray-600 font-medium">Selecteer eerst een dag om beschikbare tijden te bekijken</p>
                <p class="text-gray-500 text-sm mt-2">Kies een datum hierboven om de bezorgmomenten te zien</p>
            </div>
        </div>

        <!-- Geen Slots Beschikbaar -->
        <!-- Foutafhandeling wanneer er geen bezorgslots beschikbaar zijn -->
        <div v-else class="text-center py-12 bg-gradient-to-br from-red-50 to-rose-100 rounded-xl border-2 border-red-200">
            <div class="w-16 h-16 bg-red-200 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-red-800 font-semibold mb-2">Geen bezorgmomenten beschikbaar</p>
            <p class="text-red-600 text-sm mb-6">Er zijn momenteel geen bezorgmomenten beschikbaar. Probeer het later opnieuw.</p>
            <button 
                @click="refreshSlots"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none"
            >
                Opnieuw laden
            </button>
        </div>

        <!-- Geselecteerd Slot Bevestiging -->
        <!-- Duidelijke visuele bevestiging van de gemaakte keuze -->
        <div v-if="selectedSlotDetails" class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl shadow-green-100">
            <div class="flex items-start">
                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                    <svg class="h-6 w-6 text-white" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-lg font-bold text-green-900 mb-2">✅ Bezorgmoment bevestigd!</h4>
                    <div class="space-y-1">
                        <p class="text-green-800 font-medium">
                            📅 {{ selectedSlotDetails.day_name }} {{ selectedSlotDetails.formatted_date }}
                        </p>
                        <p class="text-green-800 font-medium">
                            🕒 {{ selectedSlotDetails.time_display }}
                        </p>
                        <p class="text-green-800 font-bold">
                            💰 Bezorgkosten: €{{ selectedSlotDetails.price.toFixed(2) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Foutmelding Interface -->
        <!-- Robuuste foutafhandeling met retry opties -->
        <div v-if="errorMessage" class="p-4 bg-gradient-to-r from-red-50 to-rose-50 border-2 border-red-200 rounded-xl">
            <div class="flex">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center">
                        <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-red-800 font-medium">{{ errorMessage }}</p>
                    <button 
                        v-if="showRetryButton"
                        @click="retryLastAction"
                        class="mt-2 inline-flex items-center text-sm text-red-600 hover:text-red-500 underline font-medium transition-colors"
                    >
                        🔄 Opnieuw proberen
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

/**
 * COMPONENT EIGENSCHAPPEN
 * Configuratie data van parent component voor slot weergave en selectie
 */
const props = defineProps({
    deliverySlots: {
        type: Array,
        default: () => []
        // Array van bezorgslot dagen met bijbehorende tijdslots en beschikbaarheid
    },
    selectedSlotId: {
        type: Number,
        default: null
        // ID van het momenteel geselecteerde tijdslot (null = geen selectie)
    }
})

/**
 * COMPONENT EVENTS
 * Events die worden uitgezonden naar parent component voor state synchronisatie
 */
const emit = defineEmits(['slot-selected', 'delivery-fee-updated', 'refresh-slots'])
// slot-selected: Wanneer gebruiker een slot selecteert
// delivery-fee-updated: Voor real-time bezorgkosten updates
// refresh-slots: Voor handmatige data verversing

/**
 * REACTIEVE COMPONENT STATE
 * Lokale state management voor gebruikersinteractie en UI feedback
 */
const selectedDay = ref(null) // Geen standaard dag - gebruiker moet kiezen
const isSelectingSlot = ref(false) // Loading state voor slot selectie proces
const isLoading = ref(false) // Loading state voor initiële data ophalen
const isRefreshing = ref(false) // Loading state voor data verversing
const errorMessage = ref(null) // Actuele foutmelding voor gebruiker
const showRetryButton = ref(false) // Of retry knop getoond moet worden
const lastAction = ref(null) // Laatste actie voor retry functionaliteit

/**
 * BESCHIKBAARHEID CONTROLE METHODEN
 * Utility functies voor slot beschikbaarheid validatie
 */
const hasAvailableSlots = (day) => {
    return day.slots && day.slots.some(slot => slot.current_available > 0)
}

const getSlotsForDay = (date) => {
    const day = props.deliverySlots.find(d => d.date === date)
    return day ? day.slots : []
}

const getSelectedDayName = () => {
    if (!selectedDay.value) return ''
    const day = props.deliverySlots.find(d => d.date === selectedDay.value)
    return day ? `${day.day_name} ${day.formatted_date}` : ''
}

/**
 * AUTOMATISCHE DAG SELECTIE
 * Auto-selecteert eerste beschikbare dag voor vlotte winkel ervaring
 */
onMounted(() => {
    if (props.deliverySlots.length > 0) {
        const firstAvailableDay = props.deliverySlots.find(day => hasAvailableSlots(day))
        selectedDay.value = firstAvailableDay ? firstAvailableDay.date : props.deliverySlots[0].date
    }
})

/**
 * DAG SELECTIE HANDLER
 * Behandelt gebruiker dag selectie en reset error state
 */
const selectDay = (date) => {
    selectedDay.value = date
    errorMessage.value = null
}

/**
 * GESELECTEERD SLOT DETAILS OPHALEN
 * Vindt volledige slot informatie inclusief dag data voor bevestiging weergave
 */
const getSelectedSlotDetails = () => {
    if (!props.selectedSlotId) return null
    
    for (const day of props.deliverySlots) {
        const slot = day.slots ? day.slots.find(s => s.id === props.selectedSlotId) : null
        if (slot) {
            return {
                ...slot,
                day_name: day.day_name,
                formatted_date: day.formatted_date
            }
        }
    }
    return null
}

/**
 * COMPUTED EIGENSCHAPPEN
 * Reactive computed properties voor template gebruik
 */
const selectedSlotDetails = computed(() => getSelectedSlotDetails())

/**
 * SLOT SELECTIE HANDLER
 * Hoofdfunctie voor bezorgslot selectie met uitgebreide foutafhandeling
 */
const selectSlot = async (slot) => {
    if (isSelectingSlot.value || slot.current_available === 0) return
    
    isSelectingSlot.value = true
    errorMessage.value = null
    showRetryButton.value = false
    lastAction.value = { type: 'selectSlot', payload: slot }
    
    try {
        const response = await axios.post('/checkout/select-slot', {
            delivery_slot_id: slot.id
        })
        
        if (response.data.success) {
            // Emit events naar parent component voor state updates
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
            })
            
            emit('delivery-fee-updated', slot.price)
        } else {
            throw new Error(response.data.message || 'Failed to select slot')
        }
        
    } catch (error) {
        console.error('Error selecting slot:', error)
        
        // Specifieke foutafhandeling gebaseerd op HTTP status codes
        if (error.response?.status === 422) {
            errorMessage.value = error.response.data.message || 'Dit bezorgmoment is niet meer beschikbaar.'
        } else if (error.response?.status === 500) {
            errorMessage.value = 'Er is een serverfout opgetreden. Probeer het later opnieuw.'
            showRetryButton.value = true
        } else {
            errorMessage.value = error.message || 'Er is een fout opgetreden bij het selecteren van het bezorgmoment.'
            showRetryButton.value = true
        }
    } finally {
        isSelectingSlot.value = false
    }
}

/**
 * SLOT VERVERSING HANDLER
 * Handmatige verversing van beschikbaarheid data
 */
const refreshSlots = async () => {
    if (isRefreshing.value) return
    
    isRefreshing.value = true
    errorMessage.value = null
    
    try {
        emit('refresh-slots')
    } catch (error) {
        console.error('Error refreshing slots:', error)
        errorMessage.value = 'Kon beschikbaarheid niet vernieuwen. Probeer het opnieuw.'
        showRetryButton.value = true
    } finally {
        isRefreshing.value = false
    }
}

/**
 * RETRY FUNCTIONALITEIT
 * Herprobeert laatste gefaalde actie voor betere gebruikerservaring
 */
const retryLastAction = () => {
    if (!lastAction.value) return
    
    switch (lastAction.value.type) {
        case 'selectSlot':
            selectSlot(lastAction.value.payload)
            break
        case 'refreshSlots':
            refreshSlots()
            break
    }
}

/**
 * TIJD GELEDEN HELPER
 * Formatteert timestamps naar gebruiksvriendelijke relatieve tijd
 */
const getTimeAgo = (timestamp) => {
    if (!timestamp) return ''
    
    const now = new Date()
    const time = new Date(timestamp)
    const diffInMinutes = Math.floor((now - time) / (1000 * 60))
    
    if (diffInMinutes < 1) return 'zojuist'
    if (diffInMinutes < 60) return `${diffInMinutes} min geleden`
    if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)} uur geleden`
    return time.toLocaleDateString()
}

/**
 * SLOT DETAILS WATCHER
 * Observeert wijzigingen in geselecteerd slot en emit bezorgkosten updates
 */
watch(() => selectedSlotDetails.value, (newSlot) => {
    if (newSlot) {
        emit('delivery-fee-updated', newSlot.price)
    }
}, { immediate: true })

/**
 * AUTOMATISCHE VERVERSING SYSTEEM
 * Auto-ververst slot beschikbaarheid elke 2 minuten voor real-time data
 */
let refreshInterval
onMounted(() => {
    refreshInterval = setInterval(() => {
        if (!isRefreshing.value && !isSelectingSlot.value) {
            refreshSlots()
        }
    }, 120000) // 2 minuten interval
})

onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval)
    }
})
</script>

<style scoped>
/**
 * AANGEPASTE STYLING EN ANIMATIES
 * Verbeterde visuele effecten en responsive design ondersteuning
 */

/* Verbeterde animaties en overgangen */
.transition-all {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Aangepaste schaduw utilities voor groene en blauwe theming */
.shadow-green-200 {
    box-shadow: 0 10px 15px -3px rgba(34, 197, 94, 0.3), 0 4px 6px -2px rgba(34, 197, 94, 0.2);
}

.shadow-blue-200 {
    box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3), 0 4px 6px -2px rgba(59, 130, 246, 0.2);
}

.shadow-green-100 {
    box-shadow: 0 4px 6px -1px rgba(34, 197, 94, 0.1), 0 2px 4px -1px rgba(34, 197, 94, 0.06);
}

/* Responsive grid voor mobile optimalisatie */
@media (max-width: 640px) {
    .grid-cols-2 {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .grid-cols-2 {
        grid-template-columns: 1fr;
    }
}

/* Verbeterde button focus staten voor toegankelijkheid */
button:focus-visible {
    outline: none;
}

/* Loading animatie definities */
@keyframes spin {
    to { 
        transform: rotate(360deg); 
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Vloeiende schaal overgangen voor hover effecten */
.hover\:scale-105:hover {
    transform: scale(1.05);
}

.scale-\[1\.02\] {
    transform: scale(1.02);
}

/* Gradient border animaties voor premium effecten */
@keyframes gradient-border {
    0%, 100% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
}

/* Beschikbaarheid indicator animaties */
.bg-green-400, .bg-red-400 {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { 
        opacity: 0; 
        transform: scale(0.8); 
    }
    to { 
        opacity: 1; 
        transform: scale(1); 
    }
}
</style>