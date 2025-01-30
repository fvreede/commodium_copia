<script setup>
import { onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    product: Object,
    subcategories: Array
});

const form = useForm({
    name: props.product.name,
    short_description: props.product.short_description,
    full_description: props.product.full_description,
    price: props.product.price,
    subcategory_id: props.product.subcategory_id,
    image: null,
    _method: 'PUT'
});

const hasSavedChanges = () => {
    return form.isDirty;
};

onMounted(() => {
    window.addEventListener('beforeunload', (e) => {
        if (hasSavedChanges()) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});

const handleBack = () => {
    if (hasSavedChanges()) {
        if (!confirm('Je hebt niet-opgeslagen wijzigingen. Weet je zeker dat je deze pagina wilt verlaten?')) {
            return;
        }
    }
    window.location = route('editor.products.index');
};

const submit = () => {
    form.post(route('editor.products.update', props.product.id));
};
</script>

<template>
    <Head title="Product Bewerken" />
    <EditorLayout>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900">
                    Product Bewerken
                </h2>
            </div>

            <form @submit.prevent="submit" class="space-y-6 bg-white p-6 rounded-lg shadow">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Naam
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                    />
                </div>

                <!-- Short Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Korte Beschrijving
                    </label>
                    <textarea
                        v-model="form.short_description"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 resize-none"
                    ></textarea>
                </div>

                <!-- Full Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Volledige Beschrijving
                    </label>
                    <textarea
                        v-model="form.full_description"
                        rows="6"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 resize-none"
                    ></textarea>
                </div>

                <!-- Price -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Prijs (€)
                    </label>
                    <input
                        v-model="form.price"
                        type="number"
                        step="0.01"
                        min="0"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                    />
                </div>

                <!-- Subcategory Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Subcategorie
                    </label>
                    <select
                        v-model="form.subcategory_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                    >
                        <option value="">Selecteer een subcategorie</option>
                        <optgroup 
                            v-for="category in subcategories" 
                            :key="category.id"
                            :label="category.category.name"
                        >
                            <option 
                                v-for="subcategory in category.subcategories" 
                                :key="subcategory.id"
                                :value="subcategory.id"
                            >
                                {{ subcategory.name }}
                            </option>
                        </optgroup>
                    </select>
                </div>

                <!-- Image Upload -->
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
                    <!-- Show current image -->
                    <img 
                        v-if="product.image_path"
                        :src="`/storage/${product.image_path}`"
                        class="mt-2 h-32 w-auto object-cover rounded-md"
                        alt="Current product image"
                    />
                </div>

                <!-- Form Buttons -->
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