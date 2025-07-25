<!--Components/AddressFormModal.vue-->

/**
 * Bestandsnaam: AddressFormModal.vue (Components)
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-07-25
 * Tijd: 20:34
 * Doel: Herbruikbare modal component voor het toevoegen en bewerken van gebruiker adressen. Bevat volledig
 *       adres formulier met validatie, error handling, en dynamische mode switching (create/edit). Inclusief
 *       session stability handling voor nieuwe gebruikers, CSRF beveiliging, Nederlandse postcode validatie,
 *       en seamless integration met checkout flow. Ondersteunt responsive design, accessibility features,
 *       en optimistische UI updates voor enhanced user experience in address management.
 */

<script setup>
// Vue compositie API imports voor reactive state en watchers
import { ref, computed, watch, toRefs } from 'vue';

// Inertia.js router voor page refreshes en state management
import { router } from '@inertiajs/vue3';

// Heroicons imports voor UI iconen
import { XMarkIcon } from '@heroicons/vue/24/outline';

// ========== PROPS DEFINITIE ==========

// Props van parent component voor modal state en address data
const props = defineProps({
    show: {                                   // Modal visibility boolean
        type: Boolean,
        default: false
    },
    address: {                                // Bestaand address object voor edit mode (null voor create mode)
        type: Object,
        default: null
    }
});

// ========== EMITS DEFINITIE ==========

// Events die naar parent component worden gestuurd
const emit = defineEmits(['close', 'saved']);

// ========== REACTIVE STATE MANAGEMENT ==========

// Form data object met address velden en default waarden
const form = ref({
    street: props.address?.street || '',                    // Straatnaam
    house_number: props.address?.house_number || '',        // Huisnummer inclusief toevoeging
    postal_code: props.address?.postal_code || '',          // Nederlandse postcode (1234 AB format)
    city: props.address?.city || '',                        // Plaatsnaam
    country: props.address?.country || 'Nederland'          // Land (default Nederland)
});

// Loading en error states
const isProcessing = ref(false);             // Loading state tijdens form submission
const errors = ref({});                     // Validation errors object

// ========== COMPUTED PROPERTIES ==========

/**
 * Bepaalt of modal in edit mode is (bestaand address) of create mode (nieuw address)
 * @returns {boolean} True als bestaand address wordt bewerkt
 */
const isEditing = computed(() => !!props.address);

// ========== FORM SUBMISSION ==========

/**
 * Voert address save operatie uit met complete error handling en session management
 * Ondersteunt zowel create als update operaties met appropriate HTTP methods
 * Includeert special handling voor nieuwe gebruikers (session stability)
 */
const saveAddress = async () => {
    if (isProcessing.value) return;           // Prevent double submission
    
    // ========== NIEUWE GEBRUIKER SESSION HANDLING ==========
    
    // Check voor nieuwe gebruiker flag en voeg delay toe voor session stability
    const userRegisteredRecently = sessionStorage.getItem('user_registered_recently');
    if (userRegisteredRecently) {
        console.log('New user detected, adding half-second delay for session stability...');
        await new Promise(resolve => setTimeout(resolve, 500));
        sessionStorage.removeItem('user_registered_recently'); // Clear flag na gebruik
    }

    // ========== FORM PROCESSING SETUP ==========
    
    isProcessing.value = true;
    errors.value = {};                        // Reset previous errors
    
    try {
        // ========== API REQUEST ==========
        
        // Fetch API call met appropriate HTTP method voor create/edit
        const response = await fetch('/api/user/address', {
            method: isEditing.value ? 'PUT' : 'POST',           // RESTful method gebaseerd op mode
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(form.value)
        });
        
        const data = await response.json();
        
        // ========== SUCCESS HANDLING ==========
        
        if (response.ok) {
            // Emit events naar parent component
            emit('saved', data.address);      // Pass saved address data
            emit('close');                    // Close modal
            
            // Refresh page data om updated address info te krijgen
            router.reload({
                only: ['deliveryAddress'],     // Alleen deliveryAddress data refreshen
                preserveState: true           // Behoud andere page state
            });
        } else {
            // ========== ERROR HANDLING ==========
            
            if (data.errors) {
                // Laravel validation errors
                errors.value = data.errors;
            } else {
                // General API errors
                throw new Error(data.message || 'Er is een fout opgetreden');
            }
        }
    } catch (error) {
        console.error('Error saving address:', error);
        errors.value = { general: 'Er is een fout opgetreden bij het opslaan van het adres.' };
    } finally {
        isProcessing.value = false;
    }
};

// ========== MODAL MANAGEMENT ==========

/**
 * Sluit modal als niet aan het processing
 * Voorkomt sluiten tijdens form submission
 */
const closeModal = () => {
    if (!isProcessing.value) {
        emit('close');
    }
};

/**
 * Reset form naar initial state
 * Wordt gebruikt bij modal open en voor cleanup
 */
const resetForm = () => {
    form.value = {
        street: props.address?.street || '',
        house_number: props.address?.house_number || '',
        postal_code: props.address?.postal_code || '',
        city: props.address?.city || '',
        country: props.address?.country || 'Nederland'
    };
    errors.value = {};
};

// ========== WATCHERS ==========

// Watch voor show prop changes om form te resetten bij modal open
const { show } = toRefs(props);
watch(show, (newShow) => {
    if (newShow) {
        resetForm();                          // Reset form data bij elke modal open
    }
});
</script>

<template>
    <!-- Modal Overlay (alleen zichtbaar als show prop true is) -->
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75">
        <!-- Modal Container met Responsive Sizing -->
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 max-h-screen overflow-y-auto">
            
            <!-- Modal Header met Titel en Close Button -->
            <div class="flex items-center justify-between p-6 border-b">
                <!-- Dynamic Modal Titel gebaseerd op edit/create mode -->
                <h3 class="text-lg font-medium text-gray-900">
                    {{ isEditing ? 'Adres wijzigen' : 'Nieuw adres toevoegen' }}
                </h3>
                <!-- Close Button met Processing State -->
                <button 
                    @click="closeModal"
                    :disabled="isProcessing"
                    class="text-gray-400 hover:text-gray-600 disabled:opacity-50"
                >
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>

            <!-- Address Form -->
            <form @submit.prevent="saveAddress" class="p-6 space-y-4">
                
                <!-- General Error Message Display -->
                <div v-if="errors.general" class="p-3 bg-red-50 border border-red-200 rounded-md">
                    <p class="text-sm text-red-700">{{ errors.general }}</p>
                </div>

                <!-- Straatnaam Input Field -->
                <div>
                    <input
                        id="street"
                        v-model="form.street"
                        type="text"
                        required
                        :class="[
                            'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                            errors.street ? 'border-red-300' : 'border-gray-300'
                        ]"
                        placeholder="Straatnaam*"
                    >
                    <!-- Field-specific Error Message -->
                    <p v-if="errors.street" class="mt-1 text-xs text-red-600">{{ errors.street[0] }}</p>
                </div>

                <!-- Huisnummer Input Field -->
                <div>
                    <input
                        id="house_number"
                        v-model="form.house_number"
                        type="text"
                        required
                        :class="[
                            'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                            errors.house_number ? 'border-red-300' : 'border-gray-300'
                        ]"
                        placeholder="Huisnummer (bijv. 12A)*"
                    >
                    <!-- Field-specific Error Message -->
                    <p v-if="errors.house_number" class="mt-1 text-xs text-red-600">{{ errors.house_number[0] }}</p>
                </div>

                <!-- Postcode Input Field met Nederlandse Validatie Pattern -->
                <div>
                    <input
                        id="postal_code"
                        v-model="form.postal_code"
                        type="text"
                        required
                        pattern="[0-9]{4}\s?[A-Za-z]{2}"
                        :class="[
                            'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                            errors.postal_code ? 'border-red-300' : 'border-gray-300'
                        ]"
                        placeholder="Postcode (bijv. 1234 AB)*"
                    >
                    <!-- Field-specific Error Message -->
                    <p v-if="errors.postal_code" class="mt-1 text-xs text-red-600">{{ errors.postal_code[0] }}</p>
                </div>

                <!-- Plaats Input Field -->
                <div>
                    <input
                        id="city"
                        v-model="form.city"
                        type="text"
                        required
                        :class="[
                            'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                            errors.city ? 'border-red-300' : 'border-gray-300'
                        ]"
                        placeholder="Plaats*"
                    >
                    <!-- Field-specific Error Message -->
                    <p v-if="errors.city" class="mt-1 text-xs text-red-600">{{ errors.city[0] }}</p>
                </div>

                <!-- Land Selectie Dropdown -->
                <div>
                    <select
                        id="country"
                        v-model="form.country"
                        required
                        :class="[
                            'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                            errors.country ? 'border-red-300' : 'border-gray-300'
                        ]"
                    >
                        <!-- Placeholder Option -->
                        <option value="" disabled>Land selecteren*</option>
                        <!-- Beschikbare Landen Opties -->
                        <option value="Nederland">Nederland</option>
                        <option value="België">België</option>
                        <option value="Duitsland">Duitsland</option>
                    </select>
                    <!-- Field-specific Error Message -->
                    <p v-if="errors.country" class="mt-1 text-xs text-red-600">{{ errors.country[0] }}</p>
                </div>

                <!-- Form Action Buttons -->
                <div class="flex justify-end space-x-3 pt-4">
                    <!-- Cancel Button -->
                    <button 
                        type="button"
                        @click="closeModal"
                        :disabled="isProcessing"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 disabled:opacity-50 transition-colors"
                    >
                        Annuleren
                    </button>
                    <!-- Submit Button met Dynamic Text en Loading State -->
                    <button 
                        type="submit"
                        :disabled="isProcessing"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 transition-colors"
                    >
                        {{ isProcessing ? 'Opslaan...' : (isEditing ? 'Wijzigen' : 'Opslaan') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
/* ========== CUSTOM FOCUS STYLES ========== */

/* Enhanced focus styling voor accessibility */
input:focus, select:focus {
    outline: 2px solid #3B82F6;              /* Blue outline voor focus visibility */
    outline-offset: 2px;                     /* Spacing tussen element en outline */
}

/* ========== SMOOTH TRANSITIONS ========== */

/* Smooth color transitions voor hover en disabled states */
.transition-colors {
    transition: all 0.2s ease-in-out;        /* Smooth transition voor alle property changes */
}
</style>