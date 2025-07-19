/**
 * Bestandsnaam: Edit.vue (Pages/Editor/Banners)
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-06-30
 * Tijd: 20:50:14
 * Doel: Editor pagina voor het bewerken van categorie banners. Bevat een upload formulier voor nieuwe banners,
 *       preview van huidige banner, validatie voor niet-opgeslagen wijzigingen, en complete banner management
 *       functionaliteiten voor categorieën.
 */

<script setup>
// Vue compositie API imports
import { onMounted } from 'vue';

// Inertia.js imports voor navigatie en forms
import { Head, useForm } from '@inertiajs/vue3';

// Layout en component imports
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

// ========== PROPS DEFINITIE ==========

// Props van server - categorie data met banner informatie
const props = defineProps({
    category: Object                            // Categorie object met id, name, banner_path en andere eigenschappen
});

// ========== FORM STATE MANAGEMENT ==========

// Inertia form setup voor banner upload en update
const form = useForm({
    banner: null,                              // Nieuwe banner bestand voor upload
    _method: 'PUT',                           // HTTP method voor update request naar server
});

// ========== VALIDATIE EN WAARSCHUWINGEN ==========

/**
 * Controleert of er niet-opgeslagen wijzigingen zijn in het formulier
 * Gebruikt om gebruiker te waarschuwen bij het verlaten van de pagina
 * @returns {boolean} True als er wijzigingen zijn die niet opgeslagen zijn
 */
const hasSavedChanges = () => {
    return form.banner !== null;
};

// ========== LIFECYCLE HOOKS ==========

/**
 * Component mounted hook - registreert event listeners
 * Voegt beforeunload event toe om gebruiker te waarschuwen bij niet-opgeslagen wijzigingen
 */
onMounted(() => {
    window.addEventListener('beforeunload', (event) => {
        if (hasSavedChanges()) {
            event.preventDefault();
            event.returnValue = '';
        }
    });
});

// ========== NAVIGATIE HANDLERS ==========

/**
 * Handelt terug navigatie af met bevestiging bij niet-opgeslagen wijzigingen
 * Toont confirmatie dialog als er wijzigingen zijn
 * Navigeert terug naar banner index pagina bij bevestiging
 */
const handleBack = () => {
    if (hasSavedChanges()) {
        if (!confirm('Je hebt niet-opgeslagen wijzigingen. Weet je zeker dat je deze pagina wilt verlaten?')) {
            return;
        }
    }
    window.location = route('editor.banners.index');
};

// ========== FORM SUBMIT HANDLERS ==========

/**
 * Formulier verzenden voor banner update
 * Stuurt POST request naar server met nieuwe banner data
 * Gebruikt Inertia form helper voor automatische error handling
 */
const submit = () => {
    form.post(route('editor.banners.update', props.category.id));
};
</script>

<template>
    <!-- Editor Layout Wrapper -->
    <EditorLayout>
        <!-- Page Title voor Browser Tab -->
        <Head title="Banner Bewerken" />

        <!-- Main Container met Responsive Padding -->
        <div class="max-w-7xl mx-auto py-4 sm:px-6 lg:px-8">
            
            <!-- Page Header Sectie -->
            <div class="mb-6">
                <!-- Pagina Titel met Categorie Naam -->
                <h2 class="text-xl font-semibold text-gray-900">
                    Banner voor {{ category.name }}
                </h2>
            </div>

            <!-- Main Content Card -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                
                <!-- Banner Preview Sectie -->
                <div class="aspect-w-16 aspect-h-5 bg-gray-100">
                    <!-- Huidige Banner Afbeelding (indien aanwezig) -->
                    <img 
                        v-if="category.banner_path"
                        :src="`/storage/${category.banner_path}`" 
                        :alt="`${category.name} banner`"
                        class="w-full h-full object-cover"
                    />
                    <!-- Placeholder voor Geen Banner -->
                    <div v-else class="flex items-center justify-center h-full text-gray-400">
                        Geen banner
                    </div>
                </div>

                <!-- Banner Upload Formulier -->
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    
                    <!-- File Upload Sectie -->
                    <div>
                        <!-- Upload Label -->
                        <label class="block text-sm font-medium text-gray-700">
                            Nieuwe Banner
                        </label>
                        <!-- Upload Instructies -->
                        <p class="mt-1 text-sm text-gray-500">
                            Upload een nieuwe banner afbeelding. Aanbevolen formaat: 1920x400 pixels.
                        </p>
                        <!-- File Input Field -->
                        <input
                            type="file"
                            @input="form.banner = $event.target.files[0]"
                            class="mt-2 block w-full"
                            accept="image/*"
                        />
                        <!-- Bestandseisen en Restricties -->
                        <p class="mt-2 text-sm text-gray-500">
                            Ondersteunde formaten: JPG, PNG, GIF (max. 2MB)
                        </p>
                    </div>

                    <!-- Formulier Actie Knoppen -->
                    <div class="flex justify-end space-x-4">
                        <!-- Annuleer Knop met Terug Navigatie -->
                        <SecondaryButton
                            type="button"
                            @click="handleBack"
                        >
                            Annuleren
                        </SecondaryButton>
                        <!-- Opslaan Knop met Loading State -->
                        <PrimaryButton
                            type="submit"
                            :disabled="form.processing"
                        >
                            Opslaan
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </EditorLayout>
</template>