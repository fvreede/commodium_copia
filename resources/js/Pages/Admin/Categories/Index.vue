<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { PencilIcon, TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';

const props = defineProps({
    categories: Array
});

const deleteCategory = (id) => {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(route('admin.categories.destroy', id));
    }
};
</script>

<template>
    <Head title="Categories" />

    <AdminLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header with Add Button -->
                <div class="mb-6 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-900">Categories</h2>
                    <Link
                        :href="route('admin.categories.create')"
                        class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 flex items-center gap-2"
                    >
                        <PlusIcon class="h-5 w-5" />
                        Add Category
                    </Link>
                </div>

                <!-- Categories Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subcategories</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="category in categories" :key="category.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ category.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ category.subcategories_count }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex gap-3">
                                                <Link 
                                                    :href="route('admin.categories.edit', category.id)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    <PencilIcon class="h-5 w-5" />
                                                </Link>
                                                <button 
                                                    @click="deleteCategory(category.id)"
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    <TrashIcon class="h-5 w-5" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>