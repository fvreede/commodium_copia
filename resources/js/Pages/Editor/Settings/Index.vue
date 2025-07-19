/**
 * Bestandsnaam: Settings.vue (Pages/Editor)
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-05-23
 * Tijd: 15:02:52
 * Doel: Settings pagina voor editor gebruikers met wachtwoord management functionaliteit. Bevat veilige
 *       wachtwoord update formulier met toggle visibility, form validatie, success feedback via flash modal,
 *       en security awareness sectie. Inclusief enhanced UX met password reveal/hide functionaliteit,
 *       transition effects, en uitgebreide beveiligingsrichtlijnen voor admin gebruikers.
 */

<script setup>
// Vue compositie API imports voor reactive state management
import { ref } from 'vue';

// Inertia.js imports voor navigatie en forms
import { Head, useForm } from '@inertiajs/vue3';

// Layout en component imports
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FlashModal from '@/Components/FlashModal.vue';

// Heroicons imports voor UI iconen
import { ExclamationTriangleIcon, EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';

// ========== FLASH MESSAGE STATE MANAGEMENT ==========

// Modal state voor success feedback
const showFlashModal = ref(false);           // Boolean voor flash modal visibility
const flashMessage = ref('');               // Success bericht content

// ========== PASSWORD FORM STATE MANAGEMENT ==========

// Inertia form setup voor wachtwoord update functionaliteit
const passwordForm = useForm({
    current_password: '',                     // Huidig wachtwoord voor verificatie
    password: '',                            // Nieuw wachtwoord
    password_confirmation: '',               // Nieuw wachtwoord bevestiging
});

// ========== FORM SUBMIT HANDLERS ==========

/**
 * Voert wachtwoord update uit met volledige validatie en feedback
 * Stuurt POST request naar server, reset form bij succes en toont feedback modal
 */
const updatePassword = () => {
    passwordForm.post(route('editor.settings.password'), {
        preserveScroll: true,                // Behoud scroll positie na form submit
        onSuccess: () => {
            passwordForm.reset();            // Reset alle form velden na success
            flashMessage.value = 'Password updated successfully.';
            showFlashModal.value = true;     // Toon success feedback modal
        },
    });
};

// ========== MODAL HANDLERS ==========

/**
 * Sluit flash message modal
 * Reset modal state na gebruiker interactie of timeout
 */
const closeFlashModal = () => {
    showFlashModal.value = false;
};

// ========== PASSWORD VISIBILITY TOGGLE FUNCTIONALITY ==========

// Reactive state voor password field visibility per input type
const currentPasswordVisible = ref(false);   // Toggle state voor huidig wachtwoord veld
const newPasswordVisible = ref(false);       // Toggle state voor nieuw wachtwoord veld
const confirmPasswordVisible = ref(false);   // Toggle state voor bevestig wachtwoord veld

/**
 * Toggle visibility voor huidig wachtwoord input veld
 * Wisselt tussen password en text input type voor security/usability balance
 */
const toggleCurrentPasswordVisibility = () => {
    currentPasswordVisible.value = !currentPasswordVisible.value;
}

/**
 * Toggle visibility voor nieuw wachtwoord input veld
 * Helpt gebruikers bij het invoeren van complex wachtwoord door tijdelijke visibility
 */
const toggleNewPasswordVisibility = () => {
    newPasswordVisible.value = !newPasswordVisible.value;
}

/**
 * Toggle visibility voor wachtwoord bevestiging input veld
 * Voorkomt typfouten door gebruikers toe te staan verificatie van ingevoerde waarde
 */
const toggleConfirmPasswordVisibility = () => {
    confirmPasswordVisible.value = !confirmPasswordVisible.value;
}
</script>

<template>
    <!-- Editor Layout Wrapper -->
    <EditorLayout>
        <!-- Page Title voor Browser Tab -->
        <Head title="Editor Settings" />

        <!-- Main Container met Padding -->
        <div class="py-12">
            
            <!-- Flash Message Modal voor Success Feedback -->
            <FlashModal
                :show="showFlashModal"
                :message="flashMessage"
                :type="'success'"
                :duration="4000"
                @close="closeFlashModal"
            />

            <!-- Settings Content Container -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Wachtwoord Update Sectie -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <!-- Password Section Container -->
                    <section class="max-w-xl">
                        
                        <!-- Section Header met Titel en Beschrijving -->
                        <header>
                            <!-- Section Titel -->
                            <h2 class="text-lg font-medium text-gray-900">Wachtwoord bijwerken</h2>
                            <!-- Security Guidance Tekst -->
                            <p class="mt-1 text-sm text-gray-600">
                                Zorg ervoor dat je een lang en willekeurig wachtwoord gebruikt om je account veilig te houden.
                            </p>
                        </header>

                        <!-- Password Update Formulier -->
                        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
                            
                            <!-- Huidig Wachtwoord Input met Visibility Toggle -->
                            <div class="relative">
                                <!-- Input Label -->
                                <InputLabel for="current_password" value="Huidig wachtwoord" />
                                <!-- Password Input met Dynamic Type -->
                                <TextInput
                                    id="current_password"
                                    v-model="passwordForm.current_password"
                                    :type="currentPasswordVisible ? 'text' : 'password'"
                                    class="mt-1 block w-full"
                                    autocomplete="current-password"
                                />
                                <!-- Visibility Toggle Button -->
                                <button
                                    type="button"
                                    @click="toggleCurrentPasswordVisibility"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 mt-6"
                                >
                                    <!-- Dynamic Icon Based on Visibility State -->
                                    <EyeIcon v-if="!currentPasswordVisible" class="h-5 w-5"/>
                                    <EyeSlashIcon v-else class="h-5 w-5"/>
                                </button>
                                <!-- Validation Error Message -->
                                <InputError :message="passwordForm.errors.current_password" class="mt-2" />
                            </div>

                            <!-- Nieuw Wachtwoord Input met Visibility Toggle -->
                            <div class="relative">
                                <!-- Input Label -->
                                <InputLabel for="password" value="Nieuw wachtwoord" />
                                <!-- Password Input met Dynamic Type -->
                                <TextInput
                                    id="password"
                                    v-model="passwordForm.password"
                                    :type="newPasswordVisible ? 'text' : 'password'"
                                    class="mt-1 block w-full"
                                    autocomplete="new-password"
                                />
                                <!-- Visibility Toggle Button -->
                                <button
                                    type="button"
                                    @click="toggleNewPasswordVisibility"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 mt-6"
                                >
                                    <!-- Dynamic Icon Based on Visibility State -->
                                    <EyeIcon v-if="!newPasswordVisible" class="h-5 w-5"/>
                                    <EyeSlashIcon v-else class="h-5 w-5"/>
                                </button>
                                <!-- Validation Error Message -->
                                <InputError :message="passwordForm.errors.password" class="mt-2" />
                            </div>

                            <!-- Wachtwoord Bevestiging Input met Visibility Toggle -->
                            <div class="relative">
                                <!-- Input Label -->
                                <InputLabel for="password_confirmation" value="Bevestig wachtwoord" />
                                <!-- Password Input met Dynamic Type -->
                                <TextInput
                                    id="password_confirmation"
                                    v-model="passwordForm.password_confirmation"
                                    :type="confirmPasswordVisible ? 'text' : 'password'"
                                    class="mt-1 block w-full"
                                    autocomplete="new-password"
                                />
                                <!-- Visibility Toggle Button -->
                                <button
                                    type="button"
                                    @click="toggleConfirmPasswordVisibility"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 mt-6"
                                >
                                    <!-- Dynamic Icon Based on Visibility State -->
                                    <EyeIcon v-if="!confirmPasswordVisible" class="h-5 w-5"/>
                                    <EyeSlashIcon v-else class="h-5 w-5"/>
                                </button>
                                <!-- Validation Error Message -->
                                <InputError :message="passwordForm.errors.password_confirmation" class="mt-2" />
                            </div>

                            <!-- Form Submit Sectie met Feedback -->
                            <div class="flex items-center gap-4">
                                <!-- Submit Knop met Loading State -->
                                <PrimaryButton :disabled="passwordForm.processing">Opslaan</PrimaryButton>
                                <!-- Success Feedback met Smooth Transition -->
                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <!-- Recently Successful Indicator -->
                                    <p v-if="passwordForm.recentlySuccessful" class="text-sm text-green-600">Opgeslagen.</p>
                                </Transition>
                            </div>
                        </form>
                    </section>
                </div>

                <!-- Security Notice Sectie -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <!-- Security Notice Container -->
                    <div class="max-w-xl">
                        <!-- Security Information Section -->
                        <section class="space-y-6">
                            
                            <!-- Security Notice Header met Warning Icon -->
                            <header>
                                <!-- Icon en Titel Container -->
                                <div class="flex items-center space-x-2">
                                    <!-- Warning Triangle Icon -->
                                    <ExclamationTriangleIcon class="h-5 w-5 text-yellow-500" />
                                    <!-- Security Notice Titel -->
                                    <h2 class="text-lg font-medium text-gray-900">Beveiligingsmelding</h2>
                                </div>
                            </header>

                            <!-- Security Guidelines Content -->
                            <div class="text-sm text-gray-600">
                                <!-- Security Introduction -->
                                <p>Als beheerder heb je verhoogde bevoegdheden. Zorg ervoor dat je:</p>
                                <!-- Security Best Practices Lijst -->
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
    </EditorLayout>
</template>