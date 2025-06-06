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
    stock_quantity: props.product.stock_quantity,
    category_id: props.product.subcategory?.category?.id ?? '',
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
  form.submit('post', route('editor.products.update', props.product.id), {
    forceFormData: true,
  });
};

import { computed } from 'vue';

const groupedSubcategories = computed(() => {
  const groups = {};

  for (const sub of props.subcategories) {
    const category = sub.category?.name ?? 'Onbekend';
    if (!groups[category]) {
      groups[category] = [];
    }
    groups[category].push(sub);
  }

  return Object.entries(groups).map(([category, subcategories]) => ({
    category,
    subcategories,
  }));
});
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
                    <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">
                        {{ form.errors.name }}
                    </p>
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
                    <p v-if="form.errors.short_description" class="text-sm text-red-600 mt-1">
                        {{ form.errors.short_description }}
                    </p>
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
                    <p v-if="form.errors.full_description" class="text-sm text-red-600 mt-1">
                        {{ form.errors.full_description }}
                    </p>
                </div>

                <!-- Price and Stock side by side -->
                <div class="flex space-x-4">
                    <!-- Price -->
                    <div class="w-1/2">
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
                        <p v-if="form.errors.price" class="text-sm text-red-600 mt-1">
                            {{ form.errors.price }}
                        </p>
                    </div>

                    <!-- Stock Quantity -->
                    <div class="w-1/2">
                        <label class="block text-sm font-medium text-gray-700">
                            Voorraad
                        </label>
                        <input
                            v-model="form.stock_quantity"
                            type="number"
                            min="1"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                        />
                        <p v-if="form.errors.stock_quantity" class="text-sm text-red-600 mt-1">
                            {{ form.errors.stock_quantity }}
                        </p>
                    </div>
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
                        <option disabled value="">Selecteer een subcategorie</option>
                        <optgroup
                            v-for="(group, index) in groupedSubcategories"
                            :key="index"
                            :label="group.category"
                        >
                            <option
                                v-for="subcategory in group.subcategories"
                                :key="subcategory.id"
                                :value="subcategory.id"
                            >
                                {{ subcategory.name }}
                            </option>
                        </optgroup>
                    </select>
                    <p v-if="form.errors.subcategory_id" class="text-sm text-red-600 mt-1">
                        {{ form.errors.subcategory_id }}
                    </p>
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
                    <p v-if="form.errors.image" class="text-sm text-red-600 mt-1">
                        {{ form.errors.image }}
                    </p>
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
