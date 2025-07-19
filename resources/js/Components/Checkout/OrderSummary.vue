/**
 * Bestandsnaam: OrderSummary.vue
 * Auteur: Fabio Vreede
 * Versie: v1.0.5
 * Datum: 2025-07-01
 * Tijd: 01:25:04
 * Doel: Uitgebreide bestelling overzicht component voor checkout proces. Toont winkelwagen items, prijs berekeningen, bezorgdetails, voorraad controles en checkout acties. Bevat real-time cart synchronisatie, foutafhandeling voor voorraad problemen en responsive design voor optimale e-commerce ervaring.
 */

<!-- resources/js/Components/Checkout/OrderSummary.vue -->
<template>
    <div class="bg-white border rounded-xl shadow-sm">
        <div class="p-4 sm:p-6">
            <h3 class="text-lg font-medium mb-6">Bestelling overzicht</h3>
            
            <!-- Laadstatus -->
            <!-- Toont loading spinner tijdens het ophalen van cart gegevens -->
            <div v-if="cartStore.isLoading" class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-2 text-sm text-gray-600">Bestelling laden...</p>
            </div>
            
            <!-- Lege Winkelwagen Status -->
            <!-- Begeleidt gebruiker terug naar winkelen wanneer cart leeg is -->
            <div v-else-if="!cartStore.hasItems" class="text-center py-8">
                <div class="text-gray-400 mb-4">
                    <ShoppingCartIcon class="w-12 h-12 mx-auto" />
                </div>
                <p class="text-gray-600 mb-4">Je winkelwagen is leeg</p>
                <a href="/categories" class="inline-block">
                    <PrimaryButton>
                        Verder winkelen
                    </PrimaryButton>
                </a>
            </div>

            <!-- Winkelwagen Items Overzicht -->
            <!-- Hoofdsectie met alle bestelde producten en berekeningen -->
            <div v-else>
                <!-- Product Items Lijst -->
                <!-- Gedetailleerde weergave van elk product met afbeelding, details en prijzen -->
                <div class="space-y-4 mb-6">
                    <div 
                        v-for="item in cartStore.sortedItems" 
                        :key="item.id || item.product_id"
                        class="flex items-start space-x-3 py-4 border-b last:border-b-0"
                    >
                        <!-- Product Afbeelding -->
                        <!-- Responsive product afbeelding met fallback voor ontbrekende plaatjes -->
                        <div class="flex-shrink-0">
                            <img 
                                :src="getImageUrl(item.image_path)" 
                                :alt="item.name"
                                class="w-12 h-12 sm:w-16 sm:h-16 object-cover rounded-lg border border-gray-200"
                                @error="handleImageError"
                            >
                        </div>

                        <!-- Product Details en Prijsinformatie -->
                        <!-- Naam, beschrijving, hoeveelheid, prijs en voorraad waarschuwingen -->
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 mb-1 line-clamp-2">
                                {{ item.name }}
                            </h4>
                            <p v-if="item.short_description" class="text-xs text-gray-500 mb-2 line-clamp-1">
                                {{ item.short_description }}
                            </p>
                            
                            <!-- Prijs Informatie Sectie -->
                            <!-- Hoeveelheid, eenheidsprijs en totaalprijs per product -->
                            <div class="space-y-1">
                                <!-- Hoeveelheid en Eenheidsprijs -->
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">
                                        {{ item.quantity }}x à €{{ formatPrice(item.price) }}
                                    </span>
                                    <span class="font-medium text-gray-900">
                                        €{{ formatPrice(item.price * item.quantity) }}
                                    </span>
                                </div>

                                <!-- Voorraad Waarschuwingen -->
                                <!-- Kleurcoded alerts voor voorraad problemen en lage voorraad -->
                                <div v-if="item.stock_quantity && item.quantity > item.stock_quantity" 
                                     class="text-xs text-red-600 bg-red-50 px-2 py-1 rounded-md inline-block">
                                    ⚠️ Slechts {{ item.stock_quantity }} op voorraad
                                </div>
                                
                                <div v-else-if="item.stock_quantity && item.stock_quantity <= 5" 
                                     class="text-xs text-orange-600 bg-orange-50 px-2 py-1 rounded-md inline-block">
                                    Nog {{ item.stock_quantity }} op voorraad
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prijs Berekening Overzicht -->
                <!-- Gedetailleerde breakdown van subtotaal, bezorgkosten, korting en eindtotaal -->
                <div class="space-y-3 pt-4 border-t border-gray-200">
                    <!-- Subtotaal -->
                    <!-- Totaal van alle product prijzen zonder extra kosten -->
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">
                            Artikelen ({{ cartStore.totalItems }}):
                        </span>
                        <span class="font-medium">€{{ formatPrice(cartStore.subtotal) }}</span>
                    </div>

                    <!-- Bezorgkosten -->
                    <!-- Toont bezorgkosten of placeholder als nog niet bekend -->
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Bezorgkosten:</span>
                        <span class="font-medium">
                            {{ deliveryFee > 0 ? `€${formatPrice(deliveryFee)}` : 'Nog te bepalen' }}
                        </span>
                    </div>

                    <!-- Korting (indien van toepassing) -->
                    <!-- Toont eventuele kortingen in groen voor positieve feedback -->
                    <div v-if="discount && discount > 0" class="flex justify-between text-sm text-green-600">
                        <span>Korting:</span>
                        <span>-€{{ formatPrice(discount) }}</span>
                    </div>

                    <!-- Eindtotaal -->
                    <!-- Prominente weergave van totaal te betalen bedrag -->
                    <div class="flex justify-between text-lg font-semibold pt-3 border-t border-gray-200">
                        <span class="text-gray-900">Totaal:</span>
                        <span class="text-gray-900">€{{ formatPrice(orderTotal) }}</span>
                    </div>

                    <!-- Besparing Indicator -->
                    <!-- Toont gebruiker hoeveel ze besparen door kortingen -->
                    <div v-if="discount && discount > 0" class="text-center pt-2">
                        <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                            Je bespaart €{{ formatPrice(discount) }}!
                        </span>
                    </div>
                </div>

                <!-- Bezorgslot Informatie -->
                <!-- Toont geselecteerde bezorgdatum en tijdslot in duidelijke format -->
                <div v-if="selectedSlotDetails" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start">
                        <CalendarDaysIcon class="h-5 w-5 text-blue-400 mr-3 flex-shrink-0 mt-0.5" />
                        <div class="min-w-0">
                            <h4 class="text-sm font-medium text-blue-800 mb-1">Bezorgmoment</h4>
                            <p class="text-sm text-blue-700">
                                {{ selectedSlotDetails.day_name }} {{ selectedSlotDetails.formatted_date }}
                            </p>
                            <p class="text-sm text-blue-700 font-medium">
                                {{ selectedSlotDetails.time_display }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Bezorgadres Informatie -->
                <!-- Toont geformatteerd bezorgadres met juiste Nederlandse formattering -->
                <div v-if="deliveryAddress" class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex items-start">
                        <MapPinIcon class="h-5 w-5 text-gray-400 mr-3 mt-0.5 flex-shrink-0" />
                        <div class="min-w-0">
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Bezorgadres</h4>
                            <p class="text-sm text-gray-600 break-words">
                                <span v-for="line in formatAddress()" :key="line" class="block" >{{ line }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Voorraad Probleem Melding -->
                <!-- Waarschuwt gebruiker voor voorraad tekorten die checkout blokkeren -->
                <div v-if="hasStockIssues" class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <XCircleIcon class="h-5 w-5 text-red-400 mt-0.5" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Voorraad probleem</h3>
                            <p class="mt-1 text-sm text-red-700">
                                Sommige producten zijn niet meer voldoende op voorraad. 
                                Pas de hoeveelheden aan voordat je verder gaat.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Checkout Actie Knoppen -->
                <!-- Navigatie knoppen voor checkout flow met validatie en feedback -->
                <div v-if="showActions" class="mt-6 space-y-3">
                    <!-- Doorgaan naar Volgende Stap Knop -->
                    <!-- Primaire actie knop met loading state en validatie -->
                    <PrimaryButton 
                        v-if="canProceed"
                        @click="$emit('proceed')"
                        :disabled="isProcessing"
                        class="w-full justify-center py-3 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ isProcessing ? 'Bezig...' : 'Doorgaan naar bevestiging' }}
                    </PrimaryButton>
                    
                    <!-- Waarschuwing Wanneer Niet Door Kan -->
                    <!-- Toont specifieke reden waarom checkout niet kan doorgaan -->
                    <div v-else class="text-center">
                        <div class="inline-flex items-center px-3 py-2 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <ExclamationTriangleIcon class="w-4 h-4 text-yellow-600 mr-2 flex-shrink-0" />
                            <p class="text-sm text-yellow-800">
                                {{ getCannotProceedReason() }}
                            </p>
                        </div>
                    </div>

                    <!-- Terug naar Winkelwagen Knop -->
                    <!-- Secundaire actie voor het bewerken van cart inhoud -->
                    <SecondaryButton 
                        @click="$emit('back-to-cart')"
                        class="w-full justify-center"
                    >
                        Terug naar winkelwagen
                    </SecondaryButton>
                </div>

                <!-- Bestel Notities Invoerveld -->
                <!-- Optionele tekst input voor speciale instructies aan bezorger -->
                <div v-if="showOrderNotes" class="mt-6">
                    <label for="order-notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Opmerkingen voor uw bestelling (optioneel)
                    </label>
                    <textarea
                        id="order-notes"
                        :value="orderNotes"
                        @input="$emit('update:order-notes', $event.target.value)"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg resize-none"
                        placeholder="Bijv. bel aan bij de achterdeur, laat pakket bij de buren, etc."
                        maxlength="500"
                    ></textarea>
                    <p class="mt-1 text-xs text-gray-500 text-right">
                        {{ orderNotes?.length || 0 }}/500 karakters
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, watch, onUnmounted } from 'vue';
import { useCartStore } from '@/Stores/cart';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import {
    ShoppingCartIcon,
    CalendarDaysIcon,
    MapPinIcon,
    XCircleIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

/**
 * COMPONENT EIGENSCHAPPEN
 * Externe data en configuratie van parent componenten
 */
const props = defineProps({
    deliveryFee: {
        type: Number,
        default: 0
        // Bezorgkosten voor totaal berekening
    },
    discount: {
        type: Number,
        default: 0
        // Kortingsbedrag voor totaal berekening
    },
    selectedSlotDetails: {
        type: Object,
        default: null
        // Geselecteerde bezorgslot met datum en tijd informatie
    },
    deliveryAddress: {
        type: Object,
        default: null
        // Bezorgadres object met straat, huisnummer, postcode, stad
    },
    showActions: {
        type: Boolean,
        default: true
        // Of checkout actie knoppen getoond moeten worden
    },
    showOrderNotes: {
        type: Boolean,
        default: false
        // Of bestel notities input getoond moet worden
    },
    orderNotes: {
        type: String,
        default: ''
        // Huidige waarde van bestel notities
    },
    isProcessing: {
        type: Boolean,
        default: false
        // Loading state voor checkout processing
    }
});

/**
 * COMPONENT EVENTS
 * Events die naar parent componenten worden uitgezonden
 */
const emit = defineEmits([
    'proceed',           // Doorgaan naar volgende checkout stap
    'back-to-cart',      // Terug naar winkelwagen bewerking
    'update:order-notes' // Bijwerken van bestel notities
]);

/**
 * CART STORE INITIALISATIE
 * Pinia store voor cart state management
 */
const cartStore = useCartStore();

/**
 * COMPONENT INITIALISATIE
 * Laadt cart data bij component mount als nog niet aanwezig
 */
onMounted(async () => {
    if (!cartStore.hasItems && !cartStore.isLoading) {
        await cartStore.loadCart();
    }
});

/**
 * COMPUTED EIGENSCHAPPEN
 * Reactive berekeningen gebaseerd op props en store state
 */

// Totaal bedrag berekening inclusief alle kosten en kortingen
const orderTotal = computed(() => {
    return cartStore.subtotal + props.deliveryFee - (props.discount || 0);
});

// Validatie of checkout kan doorgaan
const canProceed = computed(() => {
    return cartStore.hasItems && 
           props.selectedSlotDetails && 
           !hasStockIssues.value &&
           !props.isProcessing;
});

// Controle op voorraad problemen die checkout blokkeren
const hasStockIssues = computed(() => {
    return cartStore.sortedItems.some(item => 
        item.stock_quantity !== undefined && 
        item.quantity > item.stock_quantity
    );
});

/**
 * UTILITY METHODEN
 * Helper functies voor data formatting en afhandeling
 */

// Nederlandse prijs formatting met juiste decimalen
const formatPrice = (price) => {
    return Number(price || 0).toLocaleString('nl-NL', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

// Intelligente afbeelding URL generatie met fallbacks
const getImageUrl = (imagePath) => {
    if (!imagePath) {
        return '/images/placeholder.jpg';
    }

    // Als het al een volledige URL is, return as-is
    if (imagePath.startsWith('http') || imagePath.startsWith('/storage/') || imagePath.startsWith('/images/')) {
        return imagePath;
    }

    // Assumeer dat het een storage path is
    return `/storage/${imagePath}`;
};

// Afbeelding error handling voor ontbrekende plaatjes
const handleImageError = (event) => {
    event.target.src = '/images/placeholder.jpg';
};

// Nederlandse adres formatting met juiste regel verdeling
const formatAddress = () => {
    const addr = props.deliveryAddress
    let lines = []

    let streetLine = addr.street
    if (addr.house_number) streetLine += ` ${addr.house_number}`
    if (addr.addition) streetLine += `, ${addr.addition}`

    lines.push(streetLine)
    lines.push(`${addr.postal_code} ${addr.city}`)

    return lines
}

// Specifieke reden waarom checkout niet kan doorgaan
const getCannotProceedReason = () => {
    if (!cartStore.hasItems) return 'Je winkelwagen is leeg';
    if (!props.selectedSlotDetails) return 'Selecteer eerst een bezorgmoment';
    if (hasStockIssues.value) return 'Controleer de voorraad van je producten';
    return 'Controleer je bestelling';
};

/**
 * CART WIJZIGINGEN WATCHER
 * Observeert cart subtotaal voor mogelijke updates naar parent
 */
watch(() => cartStore.subtotal, (newSubtotal) => {
    // Emit subtotaal wijzigingen indien nodig voor parent component
}, { immediate: true });

/**
 * AUTOMATISCHE CART VERVERSING
 * Ververst cart data elke 30 seconden om voorraad wijzigingen te detecteren
 */
let refreshInterval;
onMounted(() => {
    refreshInterval = setInterval(async () => {
        if (!cartStore.isLoading) {
            await cartStore.loadCart();
        }
    }, 30000); // 30 seconden interval
});

onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<style scoped>
/**
 * COMPONENT STYLING
 * Aangepaste CSS voor betere gebruikerservaring en accessibility
 */

/* Verwijder focus rings voor betere visuele consistentie */
button:focus,
textarea:focus {
    outline: none;
}

/* Line clamp utilities voor tekst afkapping - moderne benadering */
.line-clamp-1 {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    line-clamp: 1;
    -webkit-line-clamp: 1;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    overflow: hidden;
}

/* Uitschakelen van textarea resize voor consistente layout */
.resize-none {
    resize: none;
}
</style>