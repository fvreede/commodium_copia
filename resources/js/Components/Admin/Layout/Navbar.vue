<template>
    <disclosure class="bg-white shadow-lg w-full sticky top-0 z-50">
        <div class="max-w-full mx-auto px-6">
            <div class="flex justify-between items-center h-16">
                <!-- Left: Logo & Search Bar -->
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-gray-800">Admin Dashboard</span>
                    <!-- Search bar -->
                    <div class="ml-8 relative">
                        <input
                            type="text"
                            placeholder="Search..."
                            class="w-80 pr-10 pl-4 py-2 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:border-transparent"
                        />
                        <MagnifyingGlassIcon class="absolute right-2 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 hover:text-gray-500 transform ease-in-out duration-150" />
                    </div>
                </div>

                <!-- Right: Icons & Profile -->
                <div class="flex items-center space-x-6">
                    <!-- Notification Icon -->
                    <button class="p-2 rounded-full text-zinc-700 hover:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-400 transform ease-in-out duration-150">
                        <BellIcon class="h-6 w-6" aria-hidden="true" />
                    </button>

                    <!-- Logout Button -->
                    <button class="ml-4 px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-zinc-700 hover:bg-zinc-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-400 transform ease-in-out duration-150 uppercase" >
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </disclosure>
</template>

<script setup>
import { Disclosure } from '@headlessui/vue';
import { BellIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
</script>
