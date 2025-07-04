<!--resources/js/Pages/Editor/Promotions/Index.vue-->
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
        <div class="p-4 sm:p-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 space-y-4 sm:space-y-0">
                <h1 class="text-xl sm:text-2xl font-semibold">Aanbiedingen</h1>
                <Link 
                    :href="route('editor.promotions.create')" 
                    :class="[
                        'w-full sm:w-auto',
                        promotions.length === 0 ? 'hidden sm:block' : 'block'
                    ]"
                >
                    <PrimaryButton class="w-full sm:w-auto justify-center">
                        <PlusIcon class="h-5 w-5 mr-2" />
                        Nieuwe Aanbieding
                    </PrimaryButton>
                </Link>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
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
                                    {{ promotion.is_active ? 'Actief' : 'Inactief' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <Link 
                                        :href="route('editor.promotions.edit', promotion.id)"
                                        class="inline-flex text-orange-600 hover:text-orange-900"
                                    >
                                        <PencilIcon class="w-5 h-5" />
                                    </Link>
                                    <button 
                                        @click="$inertia.delete(route('editor.promotions.destroy', promotion.id))"
                                        class="inline-flex text-red-600 hover:text-red-900"
                                    >
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden space-y-4">
                <div v-for="promotion in promotions" :key="promotion.id" 
                     class="bg-white rounded-lg shadow p-4 border border-gray-200">
                    <!-- Title and Status Row -->
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="text-lg font-medium text-gray-900 flex-1 mr-3">
                            {{ promotion.title }}
                        </h3>
                        <span
                            :class="[
                                'px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap',
                                promotion.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                            ]"
                        >
                            {{ promotion.is_active ? 'Actief' : 'Inactief' }}
                        </span>
                    </div>

                    <!-- Valid Until -->
                    <div class="mb-4">
                        <span class="text-sm text-gray-500">Geldig tot: </span>
                        <span class="text-sm font-medium text-gray-900">
                            {{ new Date(promotion.valid_until).toLocaleDateString() }}
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <Link 
                            :href="route('editor.promotions.edit', promotion.id)"
                            class="p-2 text-orange-600 hover:text-orange-900 hover:bg-orange-50 rounded-md transition-colors duration-200"
                        >
                            <PencilIcon class="w-5 h-5" />
                        </Link>
                        <button 
                            @click="$inertia.delete(route('editor.promotions.destroy', promotion.id))"
                            class="p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-md transition-colors duration-200"
                        >
                            <TrashIcon class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <!-- Empty State for Mobile -->
                <div v-if="promotions.length === 0" class="text-center py-12">
                    <div class="text-gray-400 mb-4">
                        <PlusIcon class="w-12 h-12 mx-auto" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Geen aanbiedingen</h3>
                    <p class="text-gray-500 mb-6">Begin met het maken van je eerste aanbieding.</p>
                    <div class="flex justify-center">
                        <Link :href="route('editor.promotions.create')">
                            <PrimaryButton class="w-full sm:w-auto">
                                <PlusIcon class="h-5 w-5 mr-2" />
                                Eerste Aanbieding Maken
                            </PrimaryButton>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </EditorLayout>
</template>