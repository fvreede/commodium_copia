/**
 * Bestandsnaam: Create.vue (Pages/Admin/Subcategories)
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-02-02
 * Tijd: 00:33:31
 * Doel: Dit component biedt een interface voor het aanmaken van nieuwe subcategorieën in de admin.
 *       Gebruikers kunnen een naam invoeren en een parent categorie selecteren. Inclusief onopgeslagen
 *       wijzigingen detectie en bevestiging bij navigatie.
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

// Props van server - beschikbare categorieën voor selectie
const props = defineProps({
    categories: Array,                          // Array van categorie objecten voor dropdown
});

// ========== FORM MANAGEMENT ==========

// Inertia.js form voor subcategorie aanmaak
const form = useForm({
    name: '',                                   // Naam van de nieuwe subcategorie
    category_id: '',                           // ID van geselecteerde parent categorie
});

// ========== ONOPGESLAGEN WIJZIGINGEN DETECTIE ==========

/**
 * Controleert of er onopgeslagen wijzigingen zijn in het formulier
 * @returns {boolean} True als er data is ingevoerd
 */
const hasSavedChanges = () => {
    return form.name !== '' || form.category_id !== '';
};

/**
 * Component mounted - registreer beforeunload event listener
 * Waarschuwt gebruiker bij accidenteel verlaten van pagina met onopgeslagen data
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
 * Toont bevestiging dialog als er onopgeslagen data is
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
 * Verwerk form submission voor nieuwe subcategorie
 * Stuurt form data naar server via Inertia.js POST request
 */
const submit = () => {
    form.post(route('admin.subcategories.store'));
};
</script>

<template>
    <!-- Page Title voor Browser Tab -->
    <Head title="Create Subcategory" />

    <AdminLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <section class="max-w-xl">
                            
                            <!-- Sectie Header -->
                            <header>
                                <h2 class="text-lg font-medium text-gray-900">Create Subcategory</h2>
                                <p class="mt-1 text-sm text-gray-600">
                                    Add a new subcategory to organize products.
                                </p>
                            </header>

                            <!-- Subcategorie Aanmaak Formulier -->
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
                                        autofocus
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

                                <!-- Form Actie Knoppen -->
                                <div class="flex items-center gap-4">
                                    <!-- Submit Knop -->
                                    <PrimaryButton :disabled="form.processing">
                                        Create Subcategory
                                    </PrimaryButton>
                                    <!-- Cancel Knop -->
                                    <SecondaryButton
                                        type="button"
                                        @click="handleBack"
                                    >
                                        Cancel
                                    </SecondaryButton>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>