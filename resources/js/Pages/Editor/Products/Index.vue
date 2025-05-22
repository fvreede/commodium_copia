<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { PencilIcon, TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    products: Array,
    subcategories: Array,
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('nl-NL', {
        style: 'currency',
        currency: 'EUR',
    }).format(price);
};
</script>

<template>
    <Head title="Producten" />
    <EditorLayout>
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold">
                    Producten
                </h1>
                <Link
                    :href="route('editor.products.create')"
                    class="inline-flex items-center"
                >
                    <PrimaryButton>
                        <PlusIcon class="h-5 w-5 mr-2" />
                        Nieuw Product
                    </PrimaryButton>
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Afbeelding
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Naam
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                subcategorie
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Prijs
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acties
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="product in products" :key="product.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <img 
                                    :src="`/storage/images/products/${product.image?.path || product.image || product.name + '.jpg'}`" 
                                    :alt="product.name"
                                    class="h-12 w-12 object-cover rounded-md"
                                    @error="$event.target.src = '/storage/images/products/default.jpg'"
                                />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ product.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ product.subcategory?.name || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ formatPrice(product.price) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <Link
                                    :href="route('editor.products.edit', product.id)"
                                    class="text-orange-600 hover:text-orange-900"
                                >
                                    <PencilIcon class="h-5 w-5" />
                                </Link>
                                <Link
                                    :href="route('editor.products.destroy', product.id)"
                                    method="delete"
                                    as="button"
                                    class="text-red-600 hover:text-red-900"
                                    type="button"
                                    @click="(e) => {
                                        if (!confirm('Weet je zeker dat je dit product wilt verwijderen?')) {
                                            e.preventDefault();
                                        }
                                    }"
                                >
                                    <TrashIcon class="h-5 w-5" />
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="products.length === 0">
                            <td class="px-6 py-4 text-center text-gray-500" colspan="5">
                                Geen producten gevonden.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </EditorLayout>
</template>