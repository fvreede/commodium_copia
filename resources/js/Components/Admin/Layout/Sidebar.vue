<script setup>
import { Link } from '@inertiajs/vue3';
import { HomeIcon, UsersIcon, Cog6ToothIcon  } from '@heroicons/vue/24/outline';
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

            <Link 
                :href="route('admin.users.index')"
                class="flex items-center px-4 py-2 mt-2 text-gray-700 hover:bg-gray-100 rounded-md"
                :class="{ 'bg-gray-100': route().current('admin.users.*') }"
            >
                <UsersIcon class="w-5 h-5 mr-3" />
                Users
            </Link>

            <Link 
                :href="route('admin.settings')"
                class="flex items-center px-4 py-2 mt-2 text-gray-700 hover:bg-gray-100 rounded-md"
            >
                <Cog6ToothIcon class="w-5 h-5 mr-3" />
                Settings
            </Link>
        </nav>
    </aside>
</template>
