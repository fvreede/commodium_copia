<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { onMounted } from 'vue';
 
const props = defineProps({
    promotion: {
        type: Object,
        required: true
    },
    products: {
        type: Array,
        required: true
    }
});

const form = useForm({
    title: props.promotions.title,
    description: props.promotions.description,
    cta_text: props.promotions.cta_text,
    image: null,
    valid_until: props.promotions.valid_until,
    products: props.promotions.products.map(p => ({
        id: p.id,
        discount_price: p.pivot.discount_price,
    }))
});

// Add warning when navigating away with unsaved changes
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
        if (confirm('Je hebt niet-opgeslagen wijzigingen. Weet je zeker dat je deze pagina wilt verlaten?')) {
            window.location = route('editor.promotions.index');
        }
    } else {
        window.location = route('editor.promotions.index');
    }
};

const submit = () => {
    form.post(route('editor.promotions.store', props.promotion.id), {
        _method: 'PUT',
    });
};
</script>

<template>
    <Head title="Nieuwe Aanbieding" />
    <EditorLayout>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Add header with back button -->
            <div class="flex items-center justify-center">
                <div class="flex items-center justify-between mb-6">
                    <SecondaryButton @click="handleBack">
                        Terug
                    </SecondaryButton>
                    <h2 class="ml-3 text-xl font-semibold text-gray-900">
                        Aanbieding bewerken
                    </h2>
                </div>
            </div>
            <form @submit.prevent="submit" class="space-y-6 bg-white p-6 rounded-lg shadow">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Titel
                    </label>
                    <input
                        v-model="form.title"
                        type="text"
                        class="mt-1 focus:ring-orange-500 focus:border-orange-500 block w-full border-gray-300 rounded-md shadow-sm "
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Beschrijving</label>
                    <textarea
                        v-model="form.description"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 resize-none"
                    ></textarea>
                </div>

                <div>
                    <Label class="block text-sm font-medium text-gray-700">
                        CTA Tekst
                    </Label>
                    <input
                        v-model="form.cta_text"
                        type="text"
                        class="mt-1 focus:ring-orange-500 focus:border-orange-500 block w-full border-gray-300 rounded-md shadow-sm"
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

                <!-- Current Image Preview -->
                <div v-if="promotion.image_path" class="mt-2">
                    <img 
                        :src="`/storage/${promotion.image_path}`" 
                        alt="Current promotion image"
                        class="h-32 w-auto object-cover rounded-md"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Geldig tot
                    </label>
                    <input
                        v-model="form.valid_until"
                        type="date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Producten</label>
                    <div class="mt-2 space-y-2">
                        <div v-for="product in products" :key="product.id" class="flex items-center space-x-4">
                            <input
                                type="checkbox"
                                :value="product.id"
                                v-model="form.products"
                                class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                            />
                            <span>{{ product.name }}</span>
                            <input
                                v-if="form.products.includes(product.id)"
                                type="number"
                                v-model="product.discount_price"
                                placeholder="Aanbiedingsprijs"
                                class="rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                                step="0.01"
                            />
                        </div>
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