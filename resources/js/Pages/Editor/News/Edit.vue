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

const props = defineProps({
    article: {
        type: Object,
        required: true
    }
});

// Initialize TinyMCE
const initEditor = () => {
    tinymce.init({
        selector: '#editor',
        skin_url: '/tinymce-skins/ui/oxide',
        content_css: 'tinymce-skins/content/default/content.css',
        height: 500,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'anchor', 'searchreplace', 
            'visualblocks', 'code', 'fullscreen', 'insertdatetime', 'media', 'table', 'preview', 
            'help', 'wordcount'
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
    tinymce.remove('#editor');

    window.addEventListener('beforeunload', function(e) {
        if (hasSavedChanges()) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});

const form = useForm({
    title: props.article.title,
    content: props.article.content,
    image: null,
    is_published: props.article.is_published,
    published_at: props.article.published_at,
    _method: 'PUT'
});

const hasSavedChanges = () => {
    return form.isDirty;
};

const handleBack = () => {
    if (hasSavedChanges()) {
        if (confirm('Je hebt niet-opgeslagen wijzigingen. Weet je zeker dat je deze pagina wilt verlaten?')) {
            window.location = route('editor.news.index');
        }
    } else {
        window.location = route('editor.news.index');
    }
};

const submit = () => {
    form.post(route('editor.news.update', props.article.id));
};
</script>

<template>
    <Head title="Artikel Bewerken" />
    <EditorLayout>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900">
                    Artikel Bewerken
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
                    <!-- Show current image if exists -->
                    <img 
                        v-if="article.image_path"
                        :src="`/storage/${article.image_path}`"
                        class="mt-2 h-32 w-auto object-cover rounded-md"
                        alt="Current article image"
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