<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PencilIcon, TrashIcon, PlusIcon, MagnifyingGlassIcon, FolderIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    categories: Array
});

const searchQuery = ref('');
const showDeleteModal = ref(false);
const selectedCategory = ref(null);

const filteredCategories = computed(() => {
    if (!searchQuery.value) return props.categories;
    
    const query = searchQuery.value.toLowerCase();
    return props.categories.filter(category => 
        category.name.toLowerCase().includes(query)
    );
});

const openDeleteModal = (category) => {
    selectedCategory.value = category;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (selectedCategory.value) {
        router.delete(route('admin.categories.destroy', selectedCategory.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false;
                selectedCategory.value = null;
            }
        });
    }
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    selectedCategory.value = null;
};
</script>

<template>
    <Head title="Categories" />

    <AdminLayout>
        <div class="py-4 sm:py-6 lg:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-4 sm:p-6">
                        <!-- Header with Search and Add Button -->
                        <div class="flex flex-col space-y-4 mb-6">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                                <h2 class="text-xl sm:text-2xl font-semibold text-gray-800">Categories</h2>
                                <Link
                                    :href="route('admin.categories.create')"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-3 sm:py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium"
                                >
                                    <PlusIcon class="h-4 w-4 mr-2" />
                                    Add Category
                                </Link>
                            </div>
                            
                            <!-- Search Bar -->
                            <div class="relative w-full">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search categories..."
                                    class="w-full pr-10 pl-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:border-transparent text-base"
                                />
                                <MagnifyingGlassIcon class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                            </div>
                        </div>

                        <!-- Mobile Card View (visible on small screens) -->
                        <div class="block lg:hidden space-y-4">
                            <div 
                                v-for="category in filteredCategories" 
                                :key="category.id"
                                class="bg-gray-50 rounded-lg p-4 border border-gray-200"
                            >
                                <div class="flex flex-col space-y-3">
                                    <!-- Category Info -->
                                    <div class="flex items-start space-x-3">
                                        <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg flex-shrink-0">
                                            <FolderIcon class="w-5 h-5 text-blue-600" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-gray-900 text-lg truncate">{{ category.name }}</h3>
                                            <p class="text-sm text-gray-500 mt-1">
                                                {{ category.subcategories_count }} 
                                                {{ category.subcategories_count === 1 ? 'subcategory' : 'subcategories' }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="flex space-x-2 pt-2 border-t border-gray-200">
                                        <Link
                                            :href="route('admin.categories.edit', category.id)"
                                            class="flex-1 flex items-center justify-center px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium"
                                        >
                                            <PencilIcon class="h-4 w-4 mr-1" />
                                            Edit
                                        </Link>
                                        <button
                                            @click="openDeleteModal(category)"
                                            class="flex items-center justify-center px-3 py-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors"
                                        >
                                            <TrashIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Empty State for Mobile -->
                            <div v-if="filteredCategories.length === 0" class="text-center py-8">
                                <FolderIcon class="mx-auto h-12 w-12 text-gray-400 mb-4" />
                                <p class="text-gray-500 text-lg mb-2">
                                    {{ searchQuery ? 'No categories found' : 'No categories yet' }}
                                </p>
                                <p v-if="!searchQuery" class="text-gray-400 text-sm mb-4">
                                    Create your first category to get started
                                </p>
                                <Link
                                    v-if="!searchQuery"
                                    :href="route('admin.categories.create')"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium"
                                >
                                    <PlusIcon class="h-4 w-4 mr-2" />
                                    Add First Category
                                </Link>
                            </div>
                        </div>

                        <!-- Desktop Table View (hidden on small screens) -->
                        <div class="hidden lg:block">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Category
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Subcategories
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="category in filteredCategories" :key="category.id" class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center space-x-3">
                                                    <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-lg">
                                                        <FolderIcon class="w-4 h-4 text-blue-600" />
                                                    </div>
                                                    <div class="text-sm font-medium text-gray-900">{{ category.name }}</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-600">
                                                    {{ category.subcategories_count }} 
                                                    {{ category.subcategories_count === 1 ? 'subcategory' : 'subcategories' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-3">
                                                    <Link 
                                                        :href="route('admin.categories.edit', category.id)"
                                                        class="text-blue-600 hover:text-blue-900 transition-colors"
                                                        title="Edit category"
                                                    >
                                                        <PencilIcon class="h-5 w-5" />
                                                    </Link>
                                                    <button 
                                                        @click="openDeleteModal(category)"
                                                        class="text-red-600 hover:text-red-900 transition-colors"
                                                        title="Delete category"
                                                    >
                                                        <TrashIcon class="h-5 w-5" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Empty State for Desktop -->
                            <div v-if="filteredCategories.length === 0" class="text-center py-12">
                                <FolderIcon class="mx-auto h-16 w-16 text-gray-400 mb-6" />
                                <h3 class="text-lg font-medium text-gray-900 mb-2">
                                    {{ searchQuery ? 'No categories found' : 'No categories yet' }}
                                </h3>
                                <p class="text-gray-500 mb-6">
                                    {{ searchQuery ? 'Try adjusting your search terms.' : 'Create your first category to organize your content.' }}
                                </p>
                                <Link
                                    v-if="!searchQuery"
                                    :href="route('admin.categories.create')"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium"
                                >
                                    <PlusIcon class="h-4 w-4 mr-2" />
                                    Create First Category
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="closeDeleteModal" max-width="sm">
            <div class="p-4 sm:p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
                    <ExclamationTriangleIcon class="w-6 h-6 text-red-600" />
                </div>
                <h2 class="text-lg font-medium text-gray-900 text-center mb-2">Delete Category</h2>
                <p class="text-sm text-gray-600 text-center mb-2">
                    Are you sure you want to delete <strong>{{ selectedCategory?.name }}</strong>?
                </p>
                <p v-if="selectedCategory?.subcategories_count > 0" class="text-sm text-red-600 text-center mb-6">
                    This category contains {{ selectedCategory.subcategories_count }} 
                    {{ selectedCategory.subcategories_count === 1 ? 'subcategory' : 'subcategories' }} 
                    that will also be deleted.
                </p>
                <p v-else class="text-sm text-gray-500 text-center mb-6">
                    This action cannot be undone.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                    <button
                        type="button"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                        @click="closeDeleteModal"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        @click="confirmDelete"
                    >
                        Delete Category
                    </button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>