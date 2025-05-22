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
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Producten
                    </h1>
                    <p class="text-gray-600 mt-1">Beheer al je producten op één plek</p>
                </div>
                <Link
                    :href="route('editor.products.create')"
                    class="inline-flex items-center"
                >
                    <PrimaryButton class="shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                        <PlusIcon class="h-5 w-5 mr-2" />
                        Nieuw Product
                    </PrimaryButton>
                </Link>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Product
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Categorie
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Prijs
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Acties
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <img 
                                                :src="`/storage/${product.image_path}`" 
                                                :alt="product.name"
                                                class="h-16 w-16 object-cover rounded-lg shadow-sm border border-gray-200"
                                            />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">
                                                {{ product.name }}
                                            </p>
                                            <p class="text-sm text-gray-500 truncate max-w-xs">
                                                {{ product.short_description }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ product.subcategory?.name || 'Geen categorie' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-lg font-bold text-gray-900">
                                        {{ formatPrice(product.price) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center space-x-3">
                                        <Link
                                            :href="route('editor.products.edit', product.id)"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 hover:text-orange-700 transition-all duration-200 hover:scale-105"
                                            title="Bewerken"
                                        >
                                            <PencilIcon class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            :href="route('editor.products.destroy', product.id)"
                                            method="delete"
                                            as="button"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 transition-all duration-200 hover:scale-105"
                                            type="button"
                                            title="Verwijderen"
                                            @click="(e) => {
                                                if (!confirm('Weet je zeker dat je dit product wilt verwijderen?')) {
                                                    e.preventDefault();
                                                }
                                            }"
                                        >
                                            <TrashIcon class="h-4 w-4" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty state -->
                <div v-if="products.length === 0" class="text-center py-16">
                    <div class="mx-auto h-24 w-24 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                        <PlusIcon class="h-12 w-12 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Geen producten gevonden</h3>
                    <p class="text-gray-500 mb-6">Voeg je eerste product toe om te beginnen.</p>
                    <Link :href="route('editor.products.create')">
                        <PrimaryButton>
                            <PlusIcon class="h-5 w-5 mr-2" />
                            Nieuw Product
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </div>
    </EditorLayout>
</template>