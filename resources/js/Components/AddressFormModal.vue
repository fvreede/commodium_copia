<!-- Components/AddressFormModal.vue -->
<script setup>
import { ref, computed, watch, toRefs } from 'vue';
import { router } from '@inertiajs/vue3';
import { XMarkIcon } from '@heroicons/vue/24/outline';

// Props
const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    address: {
        type: Object,
        default: null
    }
});

// Emits
const emit = defineEmits(['close', 'saved']);

// Form data
const form = ref({
    street: props.address?.street || '',
    house_number: props.address?.house_number || '',
    postal_code: props.address?.postal_code || '',
    city: props.address?.city || '',
    country: props.address?.country || 'Nederland'
});

const isProcessing = ref(false);
const errors = ref({});

// Computed
const isEditing = computed(() => !!props.address);

// Methods
const saveAddress = async () => {
    if (isProcessing.value) return;
    
    const userRegisteredRecently = sessionStorage.getItem('user_registered_recently');
    if (userRegisteredRecently) {
        console.log('New user detected, adding half-second delay for session stability...');
        await new Promise(resolve => setTimeout(resolve, 500));
        sessionStorage.removeItem('user_registered_recently'); // Clear flag
    }

    isProcessing.value = true;
    errors.value = {};
    
    try {
        const response = await fetch('/api/user/address', {
            method: isEditing.value ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(form.value)
        });
        
        const data = await response.json();
        
        if (response.ok) {
            emit('saved', data.address);
            emit('close');
            
            // Refresh the page to get updated address data
            router.reload({
                only: ['deliveryAddress'],
                preserveState: true
            });
        } else {
            if (data.errors) {
                errors.value = data.errors;
            } else {
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

const closeModal = () => {
    if (!isProcessing.value) {
        emit('close');
    }
};

// Reset form when modal closes
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

// Watch for show prop changes
const { show } = toRefs(props);
watch(show, (newShow) => {
    if (newShow) {
        resetForm();
    }
});
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 max-h-screen overflow-y-auto">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ isEditing ? 'Adres wijzigen' : 'Nieuw adres toevoegen' }}
                </h3>
                <button 
                    @click="closeModal"
                    :disabled="isProcessing"
                    class="text-gray-400 hover:text-gray-600 disabled:opacity-50"
                >
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="saveAddress" class="p-6 space-y-4">
                <!-- General Error -->
                <div v-if="errors.general" class="p-3 bg-red-50 border border-red-200 rounded-md">
                    <p class="text-sm text-red-700">{{ errors.general }}</p>
                </div>

                <!-- Street -->
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
                    <p v-if="errors.street" class="mt-1 text-xs text-red-600">{{ errors.street[0] }}</p>
                </div>

                <!-- House Number -->
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
                    <p v-if="errors.house_number" class="mt-1 text-xs text-red-600">{{ errors.house_number[0] }}</p>
                </div>

                <!-- Postal Code -->
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
                    <p v-if="errors.postal_code" class="mt-1 text-xs text-red-600">{{ errors.postal_code[0] }}</p>
                </div>

                <!-- City -->
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
                    <p v-if="errors.city" class="mt-1 text-xs text-red-600">{{ errors.city[0] }}</p>
                </div>

                <!-- Country -->
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
                        <option value="" disabled>Land selecteren*</option>
                        <option value="Nederland">Nederland</option>
                        <option value="België">België</option>
                        <option value="Duitsland">Duitsland</option>
                    </select>
                    <p v-if="errors.country" class="mt-1 text-xs text-red-600">{{ errors.country[0] }}</p>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-4">
                    <button 
                        type="button"
                        @click="closeModal"
                        :disabled="isProcessing"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 disabled:opacity-50 transition-colors"
                    >
                        Annuleren
                    </button>
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
/* Custom focus styles */
input:focus, select:focus {
    outline: 2px solid #3B82F6;
    outline-offset: 2px;
}

/* Smooth transitions */
.transition-colors {
    transition: all 0.2s ease-in-out;
}
</style>