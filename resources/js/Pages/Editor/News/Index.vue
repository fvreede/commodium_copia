<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { PencilIcon, TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    articles: Array
});
</script>

<template>
    <Head title="Nieuwsartikelen" />
    <EditorLayout>
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold">Nieuwsartikelen</h1>
                <Link
                    :href="route('editor.news.create')"
                    class="inline-flex items-center"
                >
                    <PrimaryButton>
                        <PlusIcon class="h-5 w-5 mr-2" />
                        Nieuw Artikel
                    </PrimaryButton>
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Titel
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Gepubliceerd op
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
                        <tr v-for="article in articles" :key="article.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ article.title }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ article.published_at ? new Date(article.published_at).toLocaleString() : 'Niet gepubliceerd' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="[
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        article.is_published ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                                    ]"
                                >
                                    {{ article.is_published ? 'Gepubliceerd' : 'Concept' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <Link
                                    :href="route('editor.news.edit', article.id)"
                                    class="text-orange-600 hover:text-orange-800"
                                >
                                    <PencilIcon class="h-5 w-5" />
                                </Link>
                                <Link
                                    :href="route('editor.news.destroy', article.id)"
                                    method="delete"
                                    class="text-red-600 hover:text-red-800"
                                >
                                    <TrashIcon class="h-5 w-5" />
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="articles.length === 0">
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500 ">
                                Geen nieuwsartikelen gevonden
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </EditorLayout>
</template>