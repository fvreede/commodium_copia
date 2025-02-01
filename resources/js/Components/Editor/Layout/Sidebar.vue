<script setup>
import { Link } from '@inertiajs/vue3';
import { HomeIcon, NewspaperIcon, TagIcon, PhotoIcon, Square3Stack3DIcon, ShoppingBagIcon, QueueListIcon, Cog6ToothIcon, PencilSquareIcon  } from '@heroicons/vue/24/outline';
</script>

<template>
    <aside class="w-64 bg-white shadow-lg h-screen">
        <nav class="mt-5 px-2">
            <Link 
                :href="route('editor.dashboard')"
                class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-md"
                :class="{ 'bg-gray-200': route().current('editor.dashboard') }"
            >
                <HomeIcon class="w-5 h-5 mr-3" />
                Dashboard
            </Link>

            <!-- Homepage Beheer -->
            <div class="pt-4">
                <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Homepage beheer
                </div>

                <Link
                    :href="route('editor.promotions.index')"
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-md"
                    :class="{ 'bg-gray-200': route().current('editor.promotions.index') }"
                >
                    <TagIcon class="w-4 h-4 mr-2" />
                    <span>Aanbiedingsacties</span>
                </Link>

                <Link
                    :href="route('editor.news.index')"
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-md"
                    :class="{ 'bg-gray-200': route().current('editor.news.index') }"
                >
                    <NewspaperIcon class="w-4 h-4 mr-2" />
                    <span>Nieuwsartikelen</span>
                </Link>
            </div>

            <!-- Product Beheer -->
            <div class="pt-4">
                <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Catalogus beheer
                </div>
                <Link
                    :href="route('editor.products.index')"
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-md"
                    :class="{ 'bg-gray-200': route().current('editor.products.index') }"
                >
                    <ShoppingBagIcon class="w-4 h-4 mr-2" />
                    <span>Producten</span>
                </Link>
                <Link
                    :href="route('editor.banners.index')"
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-md"
                    :class="{ 'bg-gray-200': route().current('editor.banners.index') }"
                >
                    <PhotoIcon class="w-4 h-4 mr-2" />
                    <span>Categorie Banners</span>
                </Link>
            </div>

            <!-- Settings -->
            <div class="pt-4">
                <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Instellingen
                </div>
                <Link 
                    :href="route('editor.settings')"
                    class="flex items-center px-4 py-2 mt-2 text-gray-700 hover:bg-gray-200 rounded-md"
                    :class="{ 'bg-gray-200': route().current('editor.settings') }"
                >
                    <Cog6ToothIcon class="w-5 h-5 mr-3" />
                    <span>Account Instellingen</span>
                </Link>
            </div>
        </nav>
    </aside>
</template>
