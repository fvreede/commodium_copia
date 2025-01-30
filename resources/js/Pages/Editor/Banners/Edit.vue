<script setup>
import { onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    category: Object
});

const form = useForm({
    banner: null,
    _method: 'PUT',
});

const hasSavedChanges = () => {
    return form.banner !== null;
};

onMounted(() => {
    window.addEventListener('beforeunload', (event) => {
        if (hasSavedChanges()) {
            event.preventDefault();
            event.returnValue = '';
        }
    });
});

const handleBack = () => {
    if (hasSavedChanges()) {
        if (!confirm('Je hebt niet-opgeslagen wijzigingen. Weet je zeker dat je deze pagina wilt verlaten?')) {
            return;
        }
    }
    window.location = route('editor.banners.index');
};

const submit = () => {
    form.post(route('editor.banners.update', props.category.id));
};
</script>

<template>
    <Head title="Banner Bewerken" />
    <EditorLayout>
        <div class="max-w-7xl mx-auto py-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900">
                    Banner voor {{ category.name }}
                </h2>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <!-- Current Banner Preview -->
                <div class="aspect-w-16 aspect-h-5 bg-gray-100">
                    <img 
                        v-if="category.banner_path"
                        :src="`/storage/${category.banner_path}`" 
                        :alt="`${category.name} banner`"
                        class="w-full h-full object-cover"
                    />
                    <div v-else class="flex items-center justify-center h-full text-gray-400">
                        Geen banner
                    </div>
                </div>

                <!-- Edit Form -->
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Nieuwe Banner
                        </label>
                        <p class="mt-1 text-sm text-gray-500">
                            Upload een nieuwe banner afbeelding. Aanbevolen formaat: 1920x400 pixels.
                        </p>
                        <input
                            type="file"
                            @input="form.banner = $event.target.files[0]"
                            class="mt-2 block w-full"
                            accept="image/*"
                        />
                        <p class="mt-2 text-sm text-gray-500">
                            Ondersteunde formaten: JPG, PNG, GIF (max. 2MB)
                        </p>
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
        </div>
    </EditorLayout>
</template>