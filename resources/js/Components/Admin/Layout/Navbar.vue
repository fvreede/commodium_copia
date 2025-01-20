<script setup>
import { ref, watch } from 'vue';
import * as vueuse from '@vueuse/core';
import { Disclosure } from '@headlessui/vue';
import { BellIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';
import { useUserStore } from '@/Stores/useUserStore';

const userStore = useUserStore();
const searchWrapper = ref(null);
const showResults = ref(false);

watch(() => userStore.searchQuery, vueuse.useDebounceFn(async () => {
        if (userStore.searchQuery) {
            await userStore.performSearch();
            showResults.value = true;
        } else {
            userStore.clearSearch();
            showResults.value = false;
        }
    }, 300)
);

// Close dropdown when clicking outside
vueuse.onClickOutside(searchWrapper, () => {
    showResults.value = false;
});

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
                    <span class="text-2xl font-bold text-gray-800">Admin Dashboard</span>
                    <!-- Search bar -->
                    <div class="ml-8 relative" ref="searchWrapper">
                        <input
                            v-model="userStore.searchQuery"
                            type="text"
                            placeholder="Search users..."
                            class="w-80 pr-10 pl-4 py-2 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:border-transparent"
                        />
                        <MagnifyingGlassIcon class="absolute right-2 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 hover:text-gray-500 transform ease-in-out duration-150" />

                        <!-- Results dropdown -->
                        <div v-if="showResults && userStore.hasSearchResults" class="absolute mt-1 w-full bg-white rounded-md shadow-lg border border-gray-200 max-h-96 overflow-y-auto z-50">
                            <div v-for="user in userStore.filteredUsers"
                                :key="user.id"
                                class="p-3 hover:bg-gray-100 cursor-pointer border-b last:border-b-0">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ user.name }}</span>
                                    <span class="text-sm text-gray-500">{{ user.email }}</span>
                                    <span class="text-xs text-gray-500 capitalize">Role: {{ user.role }}</span>
                                </div>
                            </div>    
                        </div>

                        <!-- Loading state -->
                        <div v-if="userStore.isLoading" class="absolute mt-1 w-full bg-white rounded-md shadow-lg border border-gray-200 p-4 text-center">
                            Loading...
                        </div>

                         <!-- No results message -->
                         <div v-if="showResults && userStore.searchQuery && !userStore.hasSearchResults && !userStore.isLoading" class="absolute mt-1 w-full bg-white rounded-md shadow-lg border border-gray-200 p-4 text-center">
                            No results found.
                        </div>
                    </div>
                </div>

                <!-- Right: Icons & Profile -->
                <div class="flex items-center space-x-6">
                    <!-- Notification Icon -->
                    <button class="p-2 rounded-full text-zinc-700 hover:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-400 transform ease-in-out duration-150">
                        <BellIcon class="h-6 w-6" aria-hidden="true" />
                    </button>

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