<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const form = useForm({
    name: '',
});

const hasSavedChanges = () => {
    return form.name !== '';
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
    window.location = route('admin.categories.index');
};

const submit = () => {
    form.post(route('admin.categories.store'));
};
</script>

<template>
    <Head title="Create Category" />

    <AdminLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <section class="max-w-xl">
                            <header>
                                <h2 class="text-lg font-medium text-gray-900">Create Category</h2>
                                <p class="mt-1 text-sm text-gray-600">
                                    Add a new category to organize products.
                                </p>
                            </header>

                            <form @submit.prevent="submit" class="mt-6 space-y-6">
                                <div>
                                    <InputLabel for="name" value="Category Name" />
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

                                <div class="flex items-center gap-4">
                                    <PrimaryButton :disabled="form.processing">
                                        Create Category
                                    </PrimaryButton>
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