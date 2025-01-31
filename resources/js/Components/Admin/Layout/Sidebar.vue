<script setup>
import { Link } from '@inertiajs/vue3';
import { HomeIcon, UsersIcon, Cog6ToothIcon, Square3Stack3DIcon, QueueListIcon  } from '@heroicons/vue/24/outline';
</script>

<template>
    <aside class="w-64 bg-white shadow-lg h-screen">
        <nav class="mt-5 px-2">
            <Link 
                :href="route('admin.dashboard')"
                class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md"
                :class="{ 'bg-gray-100': route().current('admin.dashboard') }"
            >
                <HomeIcon class="w-5 h-5 mr-3" />
                Dashboard
            </Link>

            <div class="pt-4">
                <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    User Management
                </div>
                <Link 
                    :href="route('admin.users.index')"
                    class="flex items-center px-4 py-2 mt-2 text-gray-700 hover:bg-gray-100 rounded-md"
                    :class="{ 'bg-gray-100': route().current('admin.users.*') }"
                >
                    <UsersIcon class="w-5 h-5 mr-3" />
                    Users
                </Link>
            </div>

            <div class="pt-4">
                <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Catalog Structure
                </div>
                <Link
                    :href="route('admin.categories.index')"
                    class="flex items-center px-4 py-2 mt-2 text-gray-700 hover:bg-gray-100 rounded-md"
                    :class="{ 'bg-gray-100': route().current('admin.categories.*') }"
                >
                    <Square3Stack3DIcon class="w-5 h-5 mr-3" />
                    Categories
                </Link>
                <Link 
                    :href="route('admin.subcategories.index')"
                    class="flex items-center px-4 py-2 mt-2 text-gray-700 hover:bg-gray-100 rounded-md"
                    :class="{ 'bg-gray-100': route().current('admin.subcategories.*') }"
                >
                    <QueueListIcon class="w-5 h-5 mr-3" />
                    Subcategories
                </Link>
            </div>

            <div class="pt-4">
                <Link 
                    :href="route('admin.settings')"
                    class="flex items-center px-4 py-2 mt-2 text-gray-700 hover:bg-gray-100 rounded-md"
                    :class="{ 'bg-gray-100': route().current('admin.settings') }"
                >
                    <Cog6ToothIcon class="w-5 h-5 mr-3" />
                    Settings
                </Link>
            </div>
        </nav>
    </aside>
</template>
