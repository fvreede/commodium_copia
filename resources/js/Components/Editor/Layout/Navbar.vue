<script setup>
import { Disclosure } from '@headlessui/vue';
import { router } from '@inertiajs/vue3';
import { ArrowTopRightOnSquareIcon } from '@heroicons/vue/24/outline';

const logout = () => {
    router.post(route('logout'))
}
</script>

<template>
    <Disclosure as="nav" class="bg-white shadow-lg w-full sticky top-0 z-50">
        <div class="max-w-full mx-auto px-6">
            <div class="flex justify-between items-center h-16">
                <!-- Left: Logo & Search Bar -->
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-gray-800">Editor Dashboard</span>
                </div>

                <!-- Right: Icons & Profile -->
                <div class="flex items-center space-x-6">
                    <!-- View Site Link -->
                    <a
                        href="/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center space-x-2 px-3 py-2 text-sm font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 hover:text-gray-900 transition-colors duration-150 uppercase"
                        title="View Public Site"
                    >
                        <ArrowTopRightOnSquareIcon class="h-4 w-4" />
                        <span>Bekijk site</span>
                    </a>
                    <!-- Logout Button -->
                    <button 
                        @click="logout"
                        class="ml-4 px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-zinc-700 hover:bg-zinc-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-400 transform ease-in-out duration-150 uppercase" >
                        Uitloggen
                    </button>
                </div>
            </div>
        </div>
    </Disclosure>
</template>