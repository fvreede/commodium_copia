<!-- resources/js/Components/Admin/Layout/Sidebar.vue -->
<script setup>
import { Link, router } from '@inertiajs/vue3';
import { 
  Dialog, 
  DialogPanel, 
  TransitionChild, 
  TransitionRoot 
} from '@headlessui/vue';
import { 
  HomeIcon, 
  UsersIcon, 
  Cog6ToothIcon, 
  Square3Stack3DIcon, 
  QueueListIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline';

// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  isMobile: {
    type: Boolean,
    default: false
  }
});

// Emits
const emit = defineEmits(['close']);

// Handle navigation - simple approach
const handleNavigation = (routeName) => {
  if (props.isMobile) {
    // Close sidebar and navigate (like shopping cart pattern)
    emit('close');
    router.visit(route(routeName));
  }
};

// Navigation items
const navigationItems = [
  {
    name: 'Dashboard',
    href: 'admin.dashboard',
    icon: HomeIcon,
    current: 'admin.dashboard'
  }
];

const userManagement = [
  {
    name: 'Gebruikers',
    href: 'admin.users.index',
    icon: UsersIcon,
    current: 'admin.users.*'
  }
];

const catalogStructure = [
  {
    name: 'Categorieën',
    href: 'admin.categories.index',
    icon: Square3Stack3DIcon,
    current: 'admin.categories.*'
  },
  {
    name: 'Subcategorieën',
    href: 'admin.subcategories.index',
    icon: QueueListIcon,
    current: 'admin.subcategories.*'
  }
];

const otherItems = [
  {
    name: 'Instellingen',
    href: 'admin.settings',
    icon: Cog6ToothIcon,
    current: 'admin.settings'
  }
];
</script>

<template>
  <!-- Mobile Sidebar (Sliding Panel) -->
  <TransitionRoot v-if="isMobile" as="template" :show="isOpen">
    <Dialog as="div" class="relative z-50" @close="$emit('close')">
      <!-- Backdrop -->
      <TransitionChild 
        as="template" 
        enter="ease-in-out duration-500" 
        enter-from="opacity-0" 
        enter-to="opacity-100" 
        leave="ease-in-out duration-500" 
        leave-from="opacity-100" 
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <!-- Sidebar panel -->
      <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
          <div class="pointer-events-none fixed inset-y-0 left-0 flex max-w-full">
            <TransitionChild 
              as="template" 
              enter="transform transition ease-in-out duration-500" 
              enter-from="-translate-x-full" 
              enter-to="translate-x-0" 
              leave="transform transition ease-in-out duration-500" 
              leave-from="translate-x-0" 
              leave-to="-translate-x-full"
            >
              <DialogPanel class="pointer-events-auto w-screen" data-sidebar>
                <div class="flex h-full flex-col bg-white shadow-xl">
                  <!-- Header -->
                  <div class="flex items-center justify-between px-4 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Menu</h2>
                    <button
                      type="button"
                      class="rounded-md p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition-colors"
                      @click="$emit('close')"
                    >
                      <span class="sr-only">Sluit menu</span>
                      <XMarkIcon class="h-6 w-6" />
                    </button>
                  </div>

                  <!-- Navigation Content -->
                  <nav class="flex-1 overflow-y-auto px-4 py-4">
                    <!-- Dashboard -->
                    <div class="space-y-1">
                      <Link 
                        v-for="item in navigationItems"
                        :key="item.name"
                        :href="route(item.href)"
                        @click="handleNavigation(item.href)"
                        class="flex items-center px-3 py-3 text-base font-medium rounded-md transition-colors cursor-pointer"
                        :class="route().current(item.current) 
                          ? 'bg-gray-200 text-gray-900' 
                          : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'"
                      >
                        <component :is="item.icon" class="w-6 h-6 mr-3 flex-shrink-0" />
                        {{ item.name }}
                      </Link>
                    </div>

                    <!-- User Management -->
                    <div class="mt-8">
                      <div class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                        Gebruikersbeheer
                      </div>
                      <div class="space-y-1">
                        <Link 
                          v-for="item in userManagement"
                          :key="item.name"
                          :href="route(item.href)"
                          @click="handleNavigation(item.href)"
                          class="flex items-center px-3 py-3 text-base font-medium rounded-md transition-colors cursor-pointer"
                          :class="route().current(item.current) 
                            ? 'bg-gray-200 text-gray-900' 
                            : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'"
                        >
                          <component :is="item.icon" class="w-6 h-6 mr-3 flex-shrink-0" />
                          {{ item.name }}
                        </Link>
                      </div>
                    </div>

                    <!-- Catalog Structure -->
                    <div class="mt-8">
                      <div class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                        Catalogusstructuur
                      </div>
                      <div class="space-y-1">
                        <Link 
                          v-for="item in catalogStructure"
                          :key="item.name"
                          :href="route(item.href)"
                          @click="handleNavigation(item.href)"
                          class="flex items-center px-3 py-3 text-base font-medium rounded-md transition-colors cursor-pointer"
                          :class="route().current(item.current) 
                            ? 'bg-gray-200 text-gray-900' 
                            : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'"
                        >
                          <component :is="item.icon" class="w-6 h-6 mr-3 flex-shrink-0" />
                          {{ item.name }}
                        </Link>
                      </div>
                    </div>

                    <!-- Other Items -->
                    <div class="mt-8">
                      <div class="space-y-1">
                        <Link 
                          v-for="item in otherItems"
                          :key="item.name"
                          :href="route(item.href)"
                          @click="handleNavigation(item.href)"
                          class="flex items-center px-3 py-3 text-base font-medium rounded-md transition-colors cursor-pointer"
                          :class="route().current(item.current) 
                            ? 'bg-gray-200 text-gray-900' 
                            : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'"
                        >
                          <component :is="item.icon" class="w-6 h-6 mr-3 flex-shrink-0" />
                          {{ item.name }}
                        </Link>
                      </div>
                    </div>
                  </nav>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>

  <!-- Desktop Sidebar (Static) -->
  <aside v-else class="w-64 bg-white shadow-lg h-screen flex-shrink-0">
    <nav class="mt-5 px-2">
      <!-- Dashboard -->
      <Link 
        v-for="item in navigationItems"
        :key="item.name"
        :href="route(item.href)"
        class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-md transition-colors"
        :class="route().current(item.current) ? 'bg-gray-200' : ''"
      >
        <component :is="item.icon" class="w-5 h-5 mr-3" />
        {{ item.name }}
      </Link>

      <!-- User Management -->
      <div class="pt-4">
        <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
          Gebruikersbeheer
        </div>
        <Link 
          v-for="item in userManagement"
          :key="item.name"
          :href="route(item.href)"
          class="flex items-center px-4 py-2 mt-2 text-gray-700 hover:bg-gray-200 rounded-md transition-colors"
          :class="route().current(item.current) ? 'bg-gray-200' : ''"
        >
          <component :is="item.icon" class="w-5 h-5 mr-3" />
          <span>{{ item.name }}</span>
        </Link>
      </div>

      <!-- Catalog Structure -->
      <div class="pt-4">
        <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
          Catalogusstructuur
        </div>
        <Link
          v-for="item in catalogStructure"
          :key="item.name"
          :href="route(item.href)"
          class="flex items-center px-4 py-2 mt-2 text-gray-700 hover:bg-gray-200 rounded-md transition-colors"
          :class="route().current(item.current) ? 'bg-gray-200' : ''"
        >
          <component :is="item.icon" class="w-5 h-5 mr-3" />
          <span>{{ item.name }}</span>
        </Link>
      </div>

      <!-- Other Items -->
      <div class="pt-4">
        <Link 
          v-for="item in otherItems"
          :key="item.name"
          :href="route(item.href)"
          class="flex items-center px-4 py-2 mt-2 text-gray-700 hover:bg-gray-200 rounded-md transition-colors"
          :class="route().current(item.current) ? 'bg-gray-200' : ''"
        >
          <component :is="item.icon" class="w-5 h-5 mr-3" />
          <span>{{ item.name }}</span>
        </Link>
      </div>
    </nav>
  </aside>
</template>