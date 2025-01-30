<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { PencilIcon } from '@heroicons/vue/24/outline';
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';

const props = defineProps({
    categories: Array
});
</script>

<template>
    <Head title="Categorie Banners" />
    <EditorLayout>
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold">
                    Categorie Banners
                </h1>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="category in categories" :key="category.id" class="bg-white rounded-lg shadow overflow-hidden">
                    <!-- Banner Preview -->
                    <div class="aspect-w-16 aspect-h-9 bg-gray-200">
                        <img 
                            v-if="category.banner_path"
                            :src="`/storage/${category.banner_path}`" 
                            :alt="`${category.name} banner`"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="flex items-center justify-center h-full text-gray-400">
                            Geen banner
                        </div>
                    </div>
                
                    <!-- Category Info -->
                    <div class="p-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ category.name }}
                            </h3>
                            <Link
                                :href="route('editor.banners.edit', category.id)"
                                class="p-2 text-orange-600 hover:text-orange-900 rounded-full hover:bg-orange-50"
                            >
                                <PencilIcon class="h-5 w-5" />
                            </Link>
                        </div>

                        <!-- Image Details -->
                        <dl class="mt-2 text-sm text-gray-500">
                            <div class="mt-1">
                                <dt class="inline">Laatst bijgewerkt: </dt>
                                <dd class="inline">{{ category.update_at ? new Date(category.update_at).toLocaleDateString() : 'Nooit' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </EditorLayout>
</template>