/**
 * Bestandsnaam: Settings.vue (Pages/Admin/Settings)
 * Auteur: Fabio Vreede
 * Versie: v1.0.5
 * Datum: 2025-05-22
 * Tijd: 13:57:04
 * Doel: Dit component biedt een interface voor admin gebruikers om hun wachtwoord te wijzigen.
 *       Inclusief wachtwoord zichtbaarheid toggles, validatie, succes berichten, en beveiligings-
 *       waarschuwingen specifiek voor admin accounts.
 */

<script setup>
// Vue compositie API imports
import { ref } from 'vue';

// Inertia.js imports voor form handling en page management
import { Head, useForm } from '@inertiajs/vue3';

// Layout en component imports
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FlashModal from '@/Components/FlashModal.vue';

// Heroicons imports voor UI iconen
import { ExclamationTriangleIcon, EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';

// ========== FLASH MODAL STATE MANAGEMENT ==========

// Flash modal voor succes berichten
const showFlashModal = ref(false);              // Of modal zichtbaar is
const flashMessage = ref('');                   // Bericht tekst in modal

// ========== WACHTWOORD FORM MANAGEMENT ==========

// Inertia.js form voor wachtwoord wijziging
const passwordForm = useForm({
    current_password: '',                       // Huidig wachtwoord van gebruiker
    password: '',                              // Nieuw gewenst wachtwoord
    password_confirmation: '',                 // Bevestiging van nieuw wachtwoord
});

/**
 * Verwerk wachtwoord update form submission
 * Stuurt data naar server en toont succes bericht bij voltooiing
 */
const updatePassword = () => {
    passwordForm.post(route('admin.settings.password'), {
        preserveScroll: true,                   // Behoud scroll positie tijdens update
        onSuccess: () => {
            passwordForm.reset();               // Leeg form velden na succes
            flashMessage.value = 'Wachtwoord succesvol bijgewerkt.';
            showFlashModal.value = true;        // Toon succes modal
        },
    });
};

/**
 * Sluit flash modal
 */
const closeFlashModal = () => {
    showFlashModal.value = false;
};

// ========== WACHTWOORD ZICHTBAARHEID TOGGLES ==========

// State voor wachtwoord zichtbaarheid per veld
const currentPasswordVisible = ref(false);     // Huidig wachtwoord tonen/verbergen
const newPasswordVisible = ref(false);         // Nieuw wachtwoord tonen/verbergen
const confirmPasswordVisible = ref(false);     // Bevestig wachtwoord tonen/verbergen

/**
 * Toggle zichtbaarheid van huidig wachtwoord veld
 */
const toggleCurrentPasswordVisibility = () => {
    currentPasswordVisible.value = !currentPasswordVisible.value;
};

/**
 * Toggle zichtbaarheid van nieuw wachtwoord veld
 */
const toggleNewPasswordVisibility = () => {
    newPasswordVisible.value = !newPasswordVisible.value;
};

/**
 * Toggle zichtbaarheid van wachtwoord bevestiging veld
 */
const toggleConfirmPasswordVisibility = () => {
    confirmPasswordVisible.value = !confirmPasswordVisible.value;
};
</script>

<template>
    <AdminLayout>
        <!-- Page Title voor Browser Tab -->
        <Head title="Beheerinstellingen" />
        
        <!-- Hoofdcontainer -->
        <div class="py-12">
            
            <!-- Flash Modal voor Succes Berichten -->
            <FlashModal
                :show="showFlashModal"
                :message="flashMessage"
                :type="'success'"
                :duration="4000"
                @close="closeFlashModal"
            />
            
            <!-- Instellingen Content Container -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Wachtwoord Wijziging Sectie -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <section class="max-w-xl">
                        
                        <!-- Sectie Header -->
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">Wachtwoord bijwerken</h2>
                            <p class="mt-1 text-sm text-gray-600">
                                Zorg ervoor dat je een lang en willekeurig wachtwoord gebruikt om je account veilig te houden.
                            </p>
                        </header>
                        
                        <!-- Wachtwoord Update Formulier -->
                        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
                            
                            <!-- Huidig Wachtwoord Veld -->
                            <div class="relative">
                                <InputLabel for="current_password" value="Huidig wachtwoord" />
                                <TextInput
                                    id="current_password"
                                    v-model="passwordForm.current_password"
                                    :type="currentPasswordVisible ? 'text' : 'password'"
                                    class="mt-1 block w-full"
                                    autocomplete="current-password"
                                />
                                <!-- Zichtbaarheid Toggle Knop -->
                                <button
                                    type="button"
                                    @click="toggleCurrentPasswordVisibility"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 mt-6"
                                >
                                    <EyeIcon v-if="!currentPasswordVisible" class="h-5 w-5"/>
                                    <EyeSlashIcon v-else class="h-5 w-5"/>
                                </button>
                                <InputError :message="passwordForm.errors.current_password" class="mt-2" />
                            </div>
                            
                            <!-- Nieuw Wachtwoord Veld -->
                            <div class="relative">
                                <InputLabel for="password" value="Nieuw wachtwoord" />
                                <TextInput
                                    id="password"
                                    v-model="passwordForm.password"
                                    :type="newPasswordVisible ? 'text' : 'password'"
                                    class="mt-1 block w-full"
                                    autocomplete="new-password"
                                />
                                <!-- Zichtbaarheid Toggle Knop -->
                                <button
                                    type="button"
                                    @click="toggleNewPasswordVisibility"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 mt-6"
                                >
                                    <EyeIcon v-if="!newPasswordVisible" class="h-5 w-5"/>
                                    <EyeSlashIcon v-else class="h-5 w-5"/>
                                </button>
                                <InputError :message="passwordForm.errors.password" class="mt-2" />
                            </div>
                            
                            <!-- Wachtwoord Bevestiging Veld -->
                            <div class="relative">
                                <InputLabel for="password_confirmation" value="Bevestig wachtwoord" />
                                <TextInput
                                    id="password_confirmation"
                                    v-model="passwordForm.password_confirmation"
                                    :type="confirmPasswordVisible ? 'text' : 'password'"
                                    class="mt-1 block w-full"
                                    autocomplete="new-password"
                                />
                                <!-- Zichtbaarheid Toggle Knop -->
                                <button
                                    type="button"
                                    @click="toggleConfirmPasswordVisibility"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 mt-6"
                                >
                                    <EyeIcon v-if="!confirmPasswordVisible" class="h-5 w-5"/>
                                    <EyeSlashIcon v-else class="h-5 w-5"/>
                                </button>
                                <InputError :message="passwordForm.errors.password_confirmation" class="mt-2" />
                            </div>
                            
                            <!-- Form Actie Sectie -->
                            <div class="flex items-center gap-4">
                                <!-- Submit Knop -->
                                <PrimaryButton :disabled="passwordForm.processing">Opslaan</PrimaryButton>
                                
                                <!-- Tijdelijke Succes Bericht -->
                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-if="passwordForm.recentlySuccessful" class="text-sm text-green-600">Opgeslagen.</p>
                                </Transition>
                            </div>
                        </form>
                    </section>
                </div>
                
                <!-- Beveiligings Waarschuwing Sectie -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <section class="space-y-6">
                            
                            <!-- Waarschuwing Header -->
                            <header>
                                <div class="flex items-center space-x-2">
                                    <ExclamationTriangleIcon class="h-5 w-5 text-yellow-500" />
                                    <h2 class="text-lg font-medium text-gray-900">Beveiligingsmelding</h2>
                                </div>
                            </header>
                            
                            <!-- Beveiligings Richtlijnen -->
                            <div class="text-sm text-gray-600">
                                <p>Als beheerder heb je verhoogde bevoegdheden. Zorg ervoor dat je:</p>
                                <ul class="list-disc list-inside mt-2 space-y-1">
                                    <li>Een sterk, uniek wachtwoord gebruikt</li>
                                    <li>Je inloggegevens nooit deelt</li>
                                    <li>Uitlogt wanneer je werkt op gedeelde apparaten</li>
                                </ul>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>