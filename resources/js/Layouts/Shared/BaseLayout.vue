<script setup>
import { ref } from 'vue';

defineProps({
    // Allow customizing background colors
    bgColor: {
        type: String,
        default: 'bg-gray-100'
    },
    // Control if sidebar should be shown
    hasSidebar: {
        type: Boolean,
        default: true
    }
})

const isMobileMenuOpen = ref(false)
</script>

<template>
  <div :class="['min-h-screen', bgColor]">
    <!-- Header section -->
    <header class="sticky top-0 z-30 w-full bg-white shadow">
      <slot name="header">
        <!-- Default header if none provided -->
        <div class="h-16 px-4">
          <slot name="header-content" />
        </div>
      </slot>
    </header>

    <div class="flex min-h-[calc(100vh-4rem)]">
      <!-- Sidebar -->
      <template v-if="hasSidebar">
        <!-- Mobile sidebar backdrop -->
        <div 
          v-show="isMobileMenuOpen" 
          class="fixed inset-0 z-20 bg-gray-600 bg-opacity-75 transition-opacity lg:hidden"
          @click="isMobileMenuOpen = false"
        />

        <!-- Mobile sidebar -->
        <aside
          :class="[
            'fixed inset-y-0 left-0 z-20 w-64 transform bg-white transition lg:static lg:translate-x-0',
            isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
          ]"
        >
          <slot name="sidebar" />
        </aside>
      </template>

      <!-- Main content -->
      <main :class="[
        'flex-1 px-4 py-8 transition-all duration-300',
        hasSidebar ? 'lg:pl-64' : ''
      ]">
        <div class="mx-auto max-w-7xl">
          <!-- Breadcrumbs slot -->
          <slot name="breadcrumbs" />
          
          <!-- Page header slot -->
          <slot name="page-header" />
          
          <!-- Main content slot -->
          <slot />
        </div>
      </main>
    </div>

    <!-- Footer -->
    <footer class="bg-white shadow">
      <slot name="footer">
        <!-- Default footer if none provided -->
        <div class="mx-auto max-w-7xl px-4 py-6">
          <slot name="footer-content" />
        </div>
      </slot>
    </footer>
  </div>
</template>