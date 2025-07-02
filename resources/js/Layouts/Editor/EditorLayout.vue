<!-- resources/js/Layouts/Editor/EditorLayout.vue -->
<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Navbar from '@/Components/Editor/Layout/Navbar.vue';
import Sidebar from '@/Components/Editor/Layout/Sidebar.vue';

// Sidebar state management
const isSidebarOpen = ref(false);
const isMobile = ref(false);

// Check if we're on mobile
const checkMobile = () => {
  isMobile.value = window.innerWidth < 768; // md breakpoint
  
  // Auto-close sidebar on mobile when resizing to desktop
  if (!isMobile.value) {
    isSidebarOpen.value = false;
  }
};

// Toggle sidebar
const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
};

// Close sidebar
const closeSidebar = () => {
  isSidebarOpen.value = false;
};

onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile);
});
</script>

<template>
  <!-- Page Wrapper -->
  <div class="flex h-screen overflow-hidden bg-slate-100">
    <!-- Desktop Sidebar - Always visible on desktop -->
    <div class="hidden md:flex">
      <Sidebar :isOpen="true" :isMobile="false" />
    </div>

    <!-- Mobile Sidebar Overlay - Only on mobile -->
    <div class="md:hidden">
      <Sidebar 
        :isOpen="isSidebarOpen" 
        :isMobile="true"
        @close="closeSidebar" 
      />
    </div>

    <!-- Content Area -->
    <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
      <!-- Header -->
      <Navbar @toggle-sidebar="toggleSidebar" :is-mobile="isMobile" />

      <!-- Main Content -->
      <main>
        <div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
          <slot></slot>
        </div>
      </main>
    </div>
  </div>
</template>