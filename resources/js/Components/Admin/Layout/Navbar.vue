<!-- resources/js/Components/Admin/Layout/Navbar.vue -->
<script setup>
import { Disclosure } from '@headlessui/vue';
import { router } from '@inertiajs/vue3';
import { 
  ArrowTopRightOnSquareIcon, 
  Bars3Icon
} from '@heroicons/vue/24/outline';

// Props
const props = defineProps({
  isMobile: {
    type: Boolean,
    default: false
  }
});

// Emits
const emit = defineEmits(['toggle-sidebar']);

const logout = () => {
  router.post(route('logout'));
};

const toggleSidebar = () => {
  emit('toggle-sidebar');
};
</script>

<template>
  <Disclosure as="nav" class="bg-white shadow-lg w-full sticky top-0 z-40">
    <div class="max-w-full mx-auto px-4 sm:px-6">
      <div class="flex justify-between items-center h-16">
        <!-- Left side: Hamburger (mobile) + Logo -->
        <div class="flex items-center">
          <!-- Mobile hamburger menu -->
          <button 
            v-if="isMobile"
            @click="toggleSidebar"
            class="mr-3 p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-gray-500 transition-colors"
            aria-label="Open sidebar"
          >
            <Bars3Icon class="h-6 w-6" />
          </button>

          <!-- Logo/Title -->
          <span class="text-xl sm:text-2xl font-bold text-gray-800">
            <span class="hidden sm:inline">Admin Dashboard</span>
            <span class="sm:hidden">Admin</span>
          </span>
        </div>

        <!-- Right side: Actions -->
        <div class="flex items-center space-x-3 sm:space-x-6">
          <!-- View Site Link -->
          <a
            href="/"
            target="_blank"
            rel="noopener noreferrer"
            class="flex items-center space-x-1 sm:space-x-2 px-2 sm:px-3 py-2 text-xs sm:text-sm font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 hover:text-gray-900 transition-colors duration-150 uppercase"
            title="View Public Site"
          >
            <ArrowTopRightOnSquareIcon class="h-4 w-4 sm:h-5 sm:w-5" />
            <span class="hidden sm:inline">Bekijk site</span>
            <span class="sm:hidden">Site</span>
          </a>

          <!-- Logout Button -->
          <button 
            @click="logout"
            class="px-3 sm:px-4 py-2 border border-transparent text-xs sm:text-sm font-medium rounded-md text-white bg-zinc-700 hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 transition-colors duration-150 uppercase"
          >
            <span class="hidden sm:inline">Uitloggen</span>
            <span class="sm:hidden">Uit</span>
          </button>
        </div>
      </div>
    </div>
  </Disclosure>
</template>