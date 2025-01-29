<script setup>
import { onMounted, onBeforeUnmount } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import 'tinymce/tinymce';
import 'tinymce/themes/silver';
import 'tinymce/icons/default';
import 'tinymce/models/dom';

// Initialize TinyMCE
const initEditor = () => {
    tinymce.init({
        selector: '#editor',
        skin_url: '/tinymce-skins/ui/oxide',
        content_css: 'tinymce-skins/content/default/content.css',
        height: 500,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen', 'insertdatetime', 'media', 'table', 'preview', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | ' +
            'bold static forecolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | help',
        setup: function(editor) {
            editor.on('change', function(e) {
                form.content = editor.getContent();
            });
        }
    });
};

onMounted(() => {
    initEditor();
});

onBeforeUnmount(() => {
    // Remove TinyMCE editor when the component is unmounted
    tinymce.remove('#editor');

    // hasSavedChanges checks if the form data has been modified since the page was loaded
    window.addEventListener('beforeunload', function(e) {
        if (hasSavedChanges()) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});

const form = useForm({
    title: '',
    content: '',
    image: null,
    is_published: false,
    published_at: null,
});

const hasSavedChanges = () => {
    return form.title || form.content || form.image || form.published_at;
};

const handleBack = () => {
    if (hasSavedChanges()) {
        if (!confirm('Je hebt niet-opgeslagen wijzigingen. Weet je zeker dat je deze pagina wilt verlaten?')) {
            window.location = route('editor.news.index');
        }
    } else {
        window.location = route('editor.news.index');
    }
};

const submit = () => {
    form.post(route('editor.news.store'));
};
</script>

<template>
    <Head title="Nieuw Artikel" />
    <EditorLayout>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900">
                    Nieuw Artikel
                </h2>
            </div>
            <form @submit.prevent="submit" class="space-y-6 bg-white p-6 rounded-lg shadow">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Titel
                    </label>
                    <input 
                        v-model="form.title"
                        type="text"
                        class="mt-2 focus:ring-orange-500 focus:border-orange-500 block w-full border-gray-300 rounded-md shadow-sm"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Inhoud
                    </label>
                    <textarea
                        id="editor"
                        v-model="form.content"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 resize-none"                
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Afbeelding
                    </label>
                    <input
                        type="file"
                        @input="form.image = $event.target.files[0]"
                        class="mt-1 block w-full"
                        accept="image/*"
                    />
                </div>

                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <input
                            type="checkbox"
                            v-model="form.is_published"
                            class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                        />
                        <label class="ml-2 block text-sm text-gray-900">
                            Direct publiceren
                        </label>
                    </div>

                    <div v-if="!form.is_published">
                        <label class="block text-sm font-medium text-gray-700">
                            Publiceren op
                        </label>
                        <input
                            v-model="form.published_at"
                            type="datetime-local"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                        />
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <SecondaryButton
                        type="button"
                        @click="handleBack"
                    >
                        Annuleren
                    </SecondaryButton>
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