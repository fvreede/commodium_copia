/**
 * Bestandsnaam: Create.vue (Pages/Editor/News)
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-01-29
 * Tijd: 18:18:36
 * Doel: Create pagina voor nieuwe nieuwsartikelen in de editor interface. Bevat TinyMCE rich text editor,
 *       bestand upload functionaliteit, publicatie opties met scheduling, form validatie, en waarschuwingen
 *       voor niet-opgeslagen wijzigingen. Volledige artikel management voor content creators.
 */

<script setup>
// Vue compositie API imports voor lifecycle management
import { onMounted, onBeforeUnmount } from 'vue';

// Inertia.js imports voor navigatie en forms
import { Head, useForm } from '@inertiajs/vue3';

// Layout en component imports
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

// TinyMCE Rich Text Editor imports
import 'tinymce/tinymce';                      // Basis TinyMCE library
import 'tinymce/themes/silver';               // Silver theme voor moderne UI
import 'tinymce/icons/default';               // Standaard icon set
import 'tinymce/models/dom';                  // DOM model voor editor functionaliteit

// ========== TINYMCE EDITOR CONFIGURATIE ==========

/**
 * Initialiseert TinyMCE rich text editor met volledige configuratie
 * Bevat toolbar setup, plugins, styling en event handlers voor content synchronisatie
 */
const initEditor = () => {
    tinymce.init({
        selector: '#editor',                   // Target textarea element voor editor
        skin_url: '/tinymce-skins/ui/oxide',  // UI skin voor consistente styling
        content_css: 'tinymce-skins/content/default/content.css', // Content styling
        height: 500,                          // Editor hoogte in pixels
        menubar: false,                       // Verberg top menubar voor cleaner interface
        
        // Plugin configuratie voor uitgebreide functionaliteit
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'anchor', 
            'searchreplace', 'visualblocks', 'code', 'fullscreen', 'insertdatetime', 
            'media', 'table', 'preview', 'help', 'wordcount'
        ],
        
        // Toolbar layout met formatting en layout tools
        toolbar: 'undo redo | blocks | ' +
            'bold static forecolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | help',
        
        // Event setup voor real-time content synchronisatie
        setup: function(editor) {
            // Synchroniseer editor content met form state bij wijzigingen
            editor.on('change', function(e) {
                form.content = editor.getContent();
            });
        }
    });
};

// ========== FORM STATE MANAGEMENT ==========

// Inertia form setup voor artikel creatie
const form = useForm({
    title: '',                                // Artikel titel
    content: '',                             // Artikel inhoud (HTML van TinyMCE)
    image: null,                             // Featured image bestand
    is_published: false,                     // Direct publicatie flag
    published_at: null,                      // Geplande publicatie datum/tijd
});

// ========== VALIDATIE EN WAARSCHUWINGEN ==========

/**
 * Controleert of er niet-opgeslagen wijzigingen zijn in het formulier
 * Gebruikt voor waarschuwingen bij het verlaten van de pagina
 * @returns {boolean} True als er wijzigingen zijn die niet opgeslagen zijn
 */
const hasSavedChanges = () => {
    return form.title || form.content || form.image || form.published_at;
};

// ========== LIFECYCLE HOOKS ==========

/**
 * Component mounted hook - initialiseert TinyMCE editor
 * Wordt aangeroepen nadat het component in de DOM is gemount
 */
onMounted(() => {
    initEditor();
});

/**
 * Component unmount hook - opruimen van TinyMCE en event listeners
 * Voorkomt memory leaks en editor conflicts bij navigatie
 */
onBeforeUnmount(() => {
    // Verwijder TinyMCE editor instance bij component unmount
    tinymce.remove('#editor');

    // Registreer beforeunload event voor niet-opgeslagen wijzigingen waarschuwing
    window.addEventListener('beforeunload', function(e) {
        if (hasSavedChanges()) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});

// ========== NAVIGATIE HANDLERS ==========

/**
 * Handelt terug navigatie af met bevestiging bij niet-opgeslagen wijzigingen
 * Toont confirmatie dialog als er wijzigingen zijn, anders directe navigatie
 */
const handleBack = () => {
    if (hasSavedChanges()) {
        if (!confirm('Je hebt niet-opgeslagen wijzigingen. Weet je zeker dat je deze pagina wilt verlaten?')) {
            window.location = route('editor.news.index');
        }
    } else {
        window.location = route('editor.news.index');
    }
};

// ========== FORM SUBMIT HANDLERS ==========

/**
 * Formulier verzenden voor artikel creatie
 * Stuurt POST request naar server met alle artikel data inclusief TinyMCE content
 */
const submit = () => {
    form.post(route('editor.news.store'));
};
</script>

<template>
    <!-- Editor Layout Wrapper -->
    <EditorLayout>
        <!-- Page Title voor Browser Tab -->
        <Head title="Nieuw Artikel" />

        <!-- Main Container met Responsive Padding -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            
            <!-- Page Header Sectie -->
            <div class="mb-6">
                <!-- Pagina Titel -->
                <h2 class="text-xl font-semibold text-gray-900">
                    Nieuw Artikel
                </h2>
            </div>

            <!-- Artikel Creatie Formulier -->
            <form @submit.prevent="submit" class="space-y-6 bg-white p-6 rounded-lg shadow">
                
                <!-- Titel Input Sectie -->
                <div>
                    <!-- Titel Label -->
                    <label class="block text-sm font-medium text-gray-700">
                        Titel
                    </label>
                    <!-- Titel Input Field -->
                    <input 
                        v-model="form.title"
                        type="text"
                        class="mt-2 focus:ring-orange-500 focus:border-orange-500 block w-full border-gray-300 rounded-md shadow-sm"
                    />
                </div>

                <!-- Content Editor Sectie -->
                <div>
                    <!-- Content Label -->
                    <label class="block text-sm font-medium text-gray-700">
                        Inhoud
                    </label>
                    <!-- TinyMCE Rich Text Editor Target -->
                    <textarea
                        id="editor"
                        v-model="form.content"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 resize-none"                
                    />
                </div>

                <!-- Featured Image Upload Sectie -->
                <div>
                    <!-- Image Upload Label -->
                    <label class="block text-sm font-medium text-gray-700">
                        Afbeelding
                    </label>
                    <!-- File Input voor Image Upload -->
                    <input
                        type="file"
                        @input="form.image = $event.target.files[0]"
                        class="mt-1 block w-full"
                        accept="image/*"
                    />
                </div>

                <!-- Publicatie Opties Sectie -->
                <div class="flex items-center space-x-4">
                    <!-- Direct Publiceren Checkbox -->
                    <div class="flex items-center">
                        <!-- Publicatie Status Checkbox -->
                        <input
                            type="checkbox"
                            v-model="form.is_published"
                            class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                        />
                        <!-- Checkbox Label -->
                        <label class="ml-2 block text-sm text-gray-900">
                            Direct publiceren
                        </label>
                    </div>

                    <!-- Geplande Publicatie Sectie (alleen zichtbaar als niet direct gepubliceerd) -->
                    <div v-if="!form.is_published">
                        <!-- Datum/Tijd Scheduling Label -->
                        <label class="block text-sm font-medium text-gray-700">
                            Publiceren op
                        </label>
                        <!-- Datum/Tijd Picker voor Scheduling -->
                        <input
                            v-model="form.published_at"
                            type="datetime-local"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                        />
                    </div>
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
    </EditorLayout>
</template>