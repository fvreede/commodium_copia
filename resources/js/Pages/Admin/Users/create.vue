/**
 * Bestandsnaam: Create.vue (Pages/Admin/Users)
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-07-04
 * Tijd: 16:58:38
 * Doel: Dit component toont een modal voor het aanmaken van nieuwe gebruikers in de admin interface.
 *       Bevat formulier velden voor gebruikersinformatie, wachtwoord en rol selectie met volledige 
 *       validatie en responsive design.
 */

<script setup>
// Inertia.js import voor form handling
import { useForm } from '@inertiajs/vue3';

// Component imports
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

// Heroicons imports voor UI iconen
import { UserPlusIcon, XMarkIcon } from '@heroicons/vue/24/outline';

// ========== PROPS DEFINITIE ==========

// Props van parent component
const props = defineProps({
    show: Boolean,                              // Of modal zichtbaar is
    roles: Array                               // Array van beschikbare rollen voor selectie
});

// ========== EMIT EVENTS ==========

// Events die naar parent component gestuurd worden
const emit = defineEmits(['close']);

// ========== FORM MANAGEMENT ==========

// Inertia.js form voor nieuwe gebruiker aanmaak
const form = useForm({
    name: '',                                  // Volledige naam van gebruiker
    email: '',                                 // Email adres van gebruiker
    password: '',                              // Wachtwoord voor nieuwe gebruiker
    password_confirmation: '',                 // Wachtwoord bevestiging
    role: ''                                   // Geselecteerde rol voor gebruiker
});

// ========== EVENT HANDLERS ==========

/**
 * Verwerk form submission voor nieuwe gebruiker
 * Stuurt POST request naar server en sluit modal bij succes
 */
const submit = () => {
    form.post(route('admin.users.store'), {
        preserveScroll: true,                   // Behoud scroll positie tijdens request
        onSuccess: () => {
            form.reset();                       // Leeg alle form velden
            emit('close');                      // Sluit modal
        },
    });
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
                        <UserPlusIcon class="w-5 h-5 text-blue-600" />
                    </div>
                    <!-- Modal Titel -->
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Create New User</h2>
                </div>
                <!-- Sluit Knop -->
                <button 
                    @click="closeModal"
                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                >
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>

            <!-- Gebruiker Aanmaak Formulier -->
            <form @submit.prevent="submit" class="p-4 sm:p-6">
                <div class="space-y-4 sm:space-y-5">
                    
                    <!-- Volledige Naam Veld -->
                    <div>
                        <InputLabel for="name" value="Full Name" class="text-sm font-medium text-gray-700" />
                        <TextInput 
                            id="name" 
                            v-model="form.name" 
                            type="text" 
                            class="mt-2 block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="Enter full name"
                            required 
                            :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.name }"
                        />
                        <!-- Error Bericht -->
                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <!-- Email Adres Veld -->
                    <div>
                        <InputLabel for="email" value="Email Address" class="text-sm font-medium text-gray-700" />
                        <TextInput 
                            id="email" 
                            v-model="form.email" 
                            type="email" 
                            class="mt-2 block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="Enter email address"
                            required 
                            :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.email }"
                        />
                        <!-- Error Bericht -->
                        <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Wachtwoord Veld -->
                    <div>
                        <InputLabel for="password" value="Password" class="text-sm font-medium text-gray-700" />
                        <TextInput 
                            id="password" 
                            v-model="form.password" 
                            type="password" 
                            class="mt-2 block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="Enter password"
                            required 
                            :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.password }"
                        />
                        <!-- Error Bericht -->
                        <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                            {{ form.errors.password }}
                        </div>
                        <!-- Wachtwoord Vereisten -->
                        <p class="mt-1 text-xs text-gray-500">Minimum 8 characters required</p>
                    </div>

                    <!-- Wachtwoord Bevestiging Veld -->
                    <div>
                        <InputLabel for="password_confirmation" value="Confirm Password" class="text-sm font-medium text-gray-700" />
                        <TextInput 
                            id="password_confirmation" 
                            v-model="form.password_confirmation" 
                            type="password" 
                            class="mt-2 block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="Confirm password"
                            required 
                            :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.password_confirmation }"
                        />
                        <!-- Error Bericht -->
                        <div v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">
                            {{ form.errors.password_confirmation }}
                        </div>
                    </div>

                    <!-- Gebruikersrol Selectie -->
                    <div>
                        <InputLabel for="role" value="User Role" class="text-sm font-medium text-gray-700" />
                        <select 
                            id="role" 
                            v-model="form.role" 
                            class="mt-2 block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                            required 
                            :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.role }"
                        >
                            <!-- Standaard Optie -->
                            <option value="">Select a role</option>
                            <!-- Rollen Loop -->
                            <option v-for="role in roles" :key="role.name" :value="role.name">
                                {{ role.name }}
                            </option>
                        </select>
                        <!-- Error Bericht -->
                        <div v-if="form.errors.role" class="mt-1 text-sm text-red-600">
                            {{ form.errors.role }}
                        </div>
                    </div>
                </div>

                <!-- Form Actie Knoppen -->
                <div class="mt-8 pt-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <!-- Create User Submit Knop -->
                        <PrimaryButton 
                            type="submit"
                            :disabled="form.processing"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 text-sm font-medium transition-colors"
                        >
                            <!-- Loading Spinner -->
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <!-- Normaal Icoon -->
                            <UserPlusIcon v-else class="w-4 h-4 mr-2" />
                            {{ form.processing ? 'Creating...' : 'Create User' }}
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