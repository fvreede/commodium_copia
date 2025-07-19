/**
 * Bestandsnaam: Edit.vue (Pages/Admin/Users)
 * Auteur: Fabio Vreede
 * Versie: v1.0.5
 * Datum: 2025-07-04
 * Tijd: 16:58:38
 * Doel: Dit component toont een modal voor het bewerken van bestaande gebruikers in de admin interface.
 *       Bevat speciale bescherming voor admin accounts, reactive form updates, en uitgebreide 
 *       validatie met error display. Ondersteunt rol wijzigingen met beperkingen.
 */

<script setup>
// Inertia.js import voor form handling
import { useForm } from '@inertiajs/vue3';

// Vue reactivity import
import { watch } from 'vue';

// Component imports
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

// Heroicons imports voor UI iconen
import { PencilIcon, XMarkIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

// ========== PROPS DEFINITIE ==========

// Props van parent component
const props = defineProps({
    show: Boolean,                              // Of modal zichtbaar is
    user: {                                    // Gebruiker object om te bewerken
        type: Object,
        default: null
    },
    roles: {                                   // Array van beschikbare rollen
        type: Array,
        required: true
    },
});

// ========== EMIT EVENTS ==========

// Events die naar parent component gestuurd worden
const emit = defineEmits(['close']);

// ========== FORM MANAGEMENT ==========

// Inertia.js form voor gebruiker bewerking met lege initiële waarden
const form = useForm({
    name: '',                                  // Naam van gebruiker
    email: '',                                 // Email adres van gebruiker
    role: '',                                  // Geselecteerde rol van gebruiker
});

// ========== REACTIVE WATCHERS ==========

/**
 * Watch voor wijzigingen in props.user en update form data reactief
 * Sluit modal automatisch als admin gebruiker wordt bewerkt
 */
watch(() => props.user, (newUser) => {
    if (newUser) {
        // Voorkom bewerking van admin gebruikers door modal te sluiten
        if (hasRole(newUser, 'admin')) {
            emit('close');
            return;
        }
        // Vul form met bestaande gebruiker data
        form.name = newUser.name || '';
        form.email = newUser.email || '';
        form.role = newUser.roles && newUser.roles.length > 0 ? newUser.roles[0].name : '';
    } else {
        // Reset form als geen gebruiker geselecteerd
        form.reset();
    }
}, { immediate: true });

// ========== EVENT HANDLERS ==========

/**
 * Verwerk form submission voor gebruiker update
 * Gebruikt PATCH method voor update operatie
 */
const submit = () => {
    if (!props.user) return;
    
    form.patch(route('admin.users.update', props.user.id), {
        preserveScroll: true,                   // Behoud scroll positie tijdens request
        onSuccess: () => {
            form.reset();                       // Reset form na succesvolle update
            emit('close');                      // Sluit modal
        },
    });
};

// ========== UTILITY FUNCTIES ==========

/**
 * Controleert of gebruiker een specifieke rol heeft
 * @param {Object} user - Gebruiker object met roles array
 * @param {string} roleName - Naam van rol om te controleren
 * @returns {boolean} True als gebruiker de rol heeft
 */
const hasRole = (user, roleName) => {
    return user.roles.some(role => role.name === roleName);
};

/**
 * Sluit modal en reset form state
 * Leegt alle velden en errors voordat modal gesloten wordt
 */
const closeModal = () => {
    form.reset();                              // Reset alle form velden
    form.clearErrors();                        // Verwijder alle validatie errors
    emit('close');                             // Emit close event naar parent
};

/**
 * Controleert of huidige gebruiker de beschermde admin account is
 * @returns {boolean} True als dit de hoofdadmin account is
 */
const isAdminUser = () => {
    return props.user?.email === 'admin@cc.nl';
};
</script>

<template>
    <!-- Modal Container -->
    <Modal :show="show" @close="closeModal" max-width="md">
        <div class="relative">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <!-- Header Icoon -->
                    <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg">
                        <PencilIcon class="w-5 h-5 text-blue-600" />
                    </div>
                    <!-- Modal Titel en Gebruiker Info -->
                    <div>
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Edit User</h2>
                        <p v-if="user" class="text-sm text-gray-500 mt-1">{{ user.name }}</p>
                    </div>
                </div>
                <!-- Sluit Knop -->
                <button 
                    @click="closeModal"
                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                >
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>

            <!-- Admin Account Bescherming Waarschuwing -->
            <div v-if="isAdminUser()" class="mx-4 sm:mx-6 mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                <div class="flex items-start space-x-3">
                    <ExclamationTriangleIcon class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" />
                    <div>
                        <p class="text-sm font-medium text-amber-800">Protected Account</p>
                        <p class="text-xs text-amber-700 mt-1">Role cannot be changed for the main admin account.</p>
                    </div>
                </div>
            </div>

            <!-- Gebruiker Bewerk Formulier -->
            <form @submit.prevent="submit" class="p-4 sm:p-6">
                
                <!-- Error Berichten Sectie -->
                <div v-if="Object.keys(form.errors).length > 0" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start space-x-3">
                        <ExclamationTriangleIcon class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" />
                        <div>
                            <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                            <!-- Error Lijst -->
                            <ul class="mt-2 text-sm text-red-700 space-y-1">
                                <li v-for="(error, field) in form.errors" :key="field">
                                    {{ error }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 sm:space-y-5">
                    
                    <!-- Volledige Naam Veld -->
                    <div>
                        <InputLabel for="edit_name" value="Full Name" class="text-sm font-medium text-gray-700" />
                        <TextInput
                            id="edit_name"
                            v-model="form.name"
                            type="text"
                            class="mt-2 block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter full name"
                            required
                            :disabled="!props.user"
                            :class="{ 
                                'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.name,
                                'bg-gray-50': !props.user
                            }"
                        />
                        <!-- Error Bericht -->
                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <!-- Email Adres Veld -->
                    <div>
                        <InputLabel for="edit_email" value="Email Address" class="text-sm font-medium text-gray-700" />
                        <TextInput
                            id="edit_email"
                            v-model="form.email"
                            type="email"
                            class="mt-2 block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter email address"
                            required
                            :disabled="!props.user"
                            :class="{ 
                                'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.email,
                                'bg-gray-50': !props.user
                            }"
                        />
                        <!-- Error Bericht -->
                        <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Gebruikersrol Selectie -->
                    <div>
                        <InputLabel for="edit_role" value="User Role" class="text-sm font-medium text-gray-700" />
                        <select
                            id="edit_role"
                            v-model="form.role"
                            class="mt-2 block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                            required
                            :disabled="!props.user || isAdminUser()"
                            :class="{ 
                                'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.role,
                                'bg-gray-50': !props.user || isAdminUser()
                            }"
                        >
                            <!-- Standaard Optie -->
                            <option value="">Select a role</option>
                            <!-- Rollen Loop -->
                            <option 
                                v-for="role in roles" 
                                :key="role.name" 
                                :value="role.name"
                            >
                                {{ role.name }}
                            </option>
                        </select>
                        <!-- Error Bericht -->
                        <div v-if="form.errors.role" class="mt-1 text-sm text-red-600">
                            {{ form.errors.role }}
                        </div>
                        <!-- Admin Bescherming Bericht -->
                        <p v-if="isAdminUser()" class="mt-1 text-xs text-gray-500">
                            Role is protected for admin accounts
                        </p>
                    </div>
                </div>

                <!-- Form Actie Knoppen -->
                <div class="mt-8 pt-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <!-- Update User Submit Knop -->
                        <PrimaryButton 
                            type="submit"
                            :disabled="form.processing || !props.user"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 text-sm font-medium transition-colors"
                        >
                            <!-- Loading Spinner -->
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <!-- Normaal Icoon -->
                            <PencilIcon v-else class="w-4 h-4 mr-2" />
                            {{ form.processing ? 'Updating...' : 'Update User' }}
                        </PrimaryButton>
                        
                        <!-- Cancel Knop -->
                        <SecondaryButton
                            type="button"
                            @click="closeModal"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                            :disabled="form.processing"
                        >
                            Cancel
                        </SecondaryButton>
                    </div>
                </div>
            </form>
        </div>
    </Modal>
</template>