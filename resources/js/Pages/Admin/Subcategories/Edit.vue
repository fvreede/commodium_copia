/**
 * Bestandsnaam: Edit.vue (Pages/Admin/Subcategories)
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-02-02
 * Tijd: 00:33:31
 * Doel: Dit component biedt een interface voor het bewerken van bestaande subcategorieën in de admin.
 *       Gebruikers kunnen de naam wijzigen en parent categorie aanpassen. Inclusief onopgeslagen
 *       wijzigingen detectie en success feedback na updates.
 */

<script setup>
// Inertia.js imports voor form handling en page management
import { Head, useForm } from '@inertiajs/vue3';

// Vue lifecycle hook
import { onMounted } from 'vue';

// Layout en component imports
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

// ========== PROPS DEFINITIE ==========

// Props van server - bestaande subcategorie data en beschikbare categorieën
const props = defineProps({
    subcategory: Object,                        // Bestaande subcategorie object met naam en category_id
    categories: Array,                          // Array van beschikbare categorieën voor dropdown
});

// ========== FORM MANAGEMENT ==========

// Inertia.js form met bestaande subcategorie data als initiële waarden
const form = useForm({
    name: props.subcategory.name,               // Huidige naam van subcategorie
    category_id: props.subcategory.category_id, // Huidige parent categorie ID
});

// ========== ONOPGESLAGEN WIJZIGINGEN DETECTIE ==========

/**
 * Controleert of er onopgeslagen wijzigingen zijn door huidige form waarden
 * te vergelijken met originele subcategorie data
 * @returns {boolean} True als er wijzigingen zijn gemaakt
 */
const hasSavedChanges = () => {
    return form.name !== props.subcategory.name || 
           form.category_id !== props.subcategory.category_id;
};

/**
 * Component mounted - registreer beforeunload event listener
 * Waarschuwt gebruiker bij accidenteel verlaten van pagina met onopgeslagen wijzigingen
 */
onMounted(() => {
    window.addEventListener('beforeunload', (event) => {
        if (hasSavedChanges()) {
            event.preventDefault();
            event.returnValue = '';
        }
    });
});

// ========== EVENT HANDLERS ==========

/**
 * Behandel terug navigatie met onopgeslagen wijzigingen controle
 * Toont bevestiging dialog als er onopgeslagen wijzigingen zijn
 */
const handleBack = () => {
    if (hasSavedChanges()) {
        if (!confirm('Je hebt niet-opgeslagen wijzigingen. Weet je zeker dat je deze pagina wilt verlaten?')) {
            return;
        }
    }
    window.location = route('admin.subcategories.index');
};

/**
 * Verwerk form submission voor subcategorie update
 * Gebruikt PUT method voor update operatie via Inertia.js
 */
const submit = () => {
    form.put(route('admin.subcategories.update', props.subcategory.id));
};
</script>

<template>
    <!-- Page Title voor Browser Tab -->
    <Head title="Edit Subcategory" />

    <AdminLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <section class="max-w-xl">
                            
                            <!-- Sectie Header -->
                            <header>
                                <h2 class="text-lg font-medium text-gray-900">Edit Subcategory</h2>
                                <p class="mt-1 text-sm text-gray-600">
                                    Update subcategory information.
                                </p>
                            </header>

                            <!-- Subcategorie Bewerk Formulier -->
                            <form @submit.prevent="submit" class="mt-6 space-y-6">
                                
                                <!-- Subcategorie Naam Veld -->
                                <div>
                                    <InputLabel for="name" value="Subcategory Name" />
                                    <TextInput
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError :message="form.errors.name" class="mt-2" />
                                </div>

                                <!-- Parent Categorie Selectie -->
                                <div>
                                    <InputLabel for="category_id" value="Category" />
                                    <select
                                        id="category_id"
                                        v-model="form.category_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required
                                    >
                                        <!-- Standaard Optie -->
                                        <option value="">Select a category</option>
                                        <!-- Categorie Opties Loop -->
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.category_id" class="mt-2" />
                                </div>

                                <!-- Form Actie Knoppen en Success Feedback -->
                                <div class="flex items-center gap-4">
                                    <!-- Update Submit Knop -->
                                    <PrimaryButton :disabled="form.processing">
                                        Update Subcategory
                                    </PrimaryButton>
                                    
                                    <!-- Cancel Knop -->
                                    <SecondaryButton
                                        type="button"
                                        @click="handleBack"
                                    >
                                        Cancel
                                    </SecondaryButton>

                                    <!-- Tijdelijke Success Bericht -->
                                    <Transition
                                        enter-active-class="transition ease-in-out"
                                        enter-from-class="opacity-0"
                                        leave-active-class="transition ease-in-out"
                                        leave-to-class="opacity-0"
                                    >
                                        <p v-if="form.recentlySuccessful" class="text-sm text-green-600">Saved.</p>
                                    </Transition>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>