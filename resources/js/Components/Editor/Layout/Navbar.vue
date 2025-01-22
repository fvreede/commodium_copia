<script setup>
import { Disclosure } from '@headlessui/vue';
import { router } from '@inertiajs/vue3';

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
                    <!-- Logout Button -->
                    <button 
                        @click="logout"
                        class="ml-4 px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-zinc-700 hover:bg-zinc-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-400 transform ease-in-out duration-150 uppercase" >
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </Disclosure>
</template>