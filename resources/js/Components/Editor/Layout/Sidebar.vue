<script setup>
import { Link } from '@inertiajs/vue3';
import { HomeIcon, NewspaperIcon, TagIcon, PhotoIcon, Square3Stack3DIcon, ShoppingBagIcon, QueueListIcon, Cog6ToothIcon, PencilSquareIcon  } from '@heroicons/vue/24/outline';
</script>

<template>
    <aside class="w-64 bg-white shadow-lg h-screen">
        <nav class="mt-5 px-2">
            <Link 
                :href="route('editor.dashboard')"
                class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md"
                :class="{ 'bg-gray-100': route().current('editor.dashboard') }"
            >
                <HomeIcon class="w-5 h-5 mr-3" />
                Dashboard
            </Link>

            <!-- Content Management -->
            <div class="pt-4">
                <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Content Management
                </div>

                <Link
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md"
                >
                    <TagIcon class="w-4 h-4 mr-2" />
                    Promotions
                </Link>

                <Link
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md"
                >
                    <NewspaperIcon class="w-4 h-4 mr-2" />
                    News Articles
                </Link>

                <Link
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md"
                >
                    <PhotoIcon class="w-4 h-4 mr-2" />
                    Category Banners
                </Link>
            </div>

            <!-- Catalog Management -->
            <div class="pt-4">
                <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Catalog Management
                </div>

                <Link
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md"
                >
                    <Square3Stack3DIcon class="w-4 h-4 mr-2" />
                    Categories
                </Link>

                <Link
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md"
                >
                    <ShoppingBagIcon class="w-4 h-4 mr-2" />
                    Products
                </Link>
            </div>

            <!-- Website Management -->
            <div class="pt-4">
                <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Website Management
                </div>

                <Link
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md"
                >
                    <PencilSquareIcon class="w-4 h-4 mr-2" />
                    Pages
                </Link>

                <Link
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md"
                >
                    <QueueListIcon class="w-4 h-4 mr-2" />
                    Navigation
                </Link>
            </div>

            <!-- Settings -->
            <div class="pt">
                <Link 
                    :href="route('editor.settings')"
                    class="flex items-center px-4 py-2 mt-2 text-gray-700 hover:bg-gray-100 rounded-md"
                    :class="{ 'bg-gray-100': route().current('editor.settings') }"
                >
                    <Cog6ToothIcon class="w-5 h-5 mr-3" />
                    Settings
                </Link>
            </div>
        </nav>
    </aside>
</template>
