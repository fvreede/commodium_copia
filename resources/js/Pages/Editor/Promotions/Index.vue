<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { TrashIcon, PencilIcon, PlusIcon } from '@heroicons/vue/24/outline';
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    promotions: Array
});
</script>

<template>
    <Head title="Aanbiedingsacties" />
    <EditorLayout>
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold">Aanbiedingen</h1>
                <Link :href="route('editor.promotions.create')">
                    <PrimaryButton>
                        <PlusIcon class="h-5 w-5 mr-2" />
                        Nieuwe Aanbieding
                    </PrimaryButton>
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Title
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Geldig tot
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acties
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="promotion in promotions" :key="promotion.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ promotion.title }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ new Date(promotion.valid_until).toLocaleDateString() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="[
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        promotion.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    ]"
                                >
                                    {{ promotion.is_active? 'Actief' : 'Inactief' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <Link 
                                    :href="route('editor.promotions.edit', promotion.id)"
                                    class="text-orange-600 hover:text-orange-900"
                                >
                                    <PencilIcon class="w-5 h-5" />
                                </Link>
                                <button 
                                    @click="$inertia.delete(route('editor.promotions.destroy', promotion.id))"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </EditorLayout>
</template>