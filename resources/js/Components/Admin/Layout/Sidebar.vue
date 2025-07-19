/**
 * Bestandsnaam: Sidebar.vue
 * Auteur: Fabio Vreede
 * Versie: v1.0.9
 * Datum: 2025-07-03
 * Tijd: 00:09:21
 * Doel: Admin dashboard sidebar component met responsive design. Bevat mobile sliding panel en desktop static sidebar met georganiseerde navigatie voor dashboard, gebruikersbeheer, catalogusstructuur en instellingen. Gebruikt HeadlessUI voor smooth transitions.
 */

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

/**
 * COMPONENT EIGENSCHAPPEN
 * Configuratie-opties voor responsive sidebar gedrag
 */
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
    // Bepaalt of de mobile sidebar geopend is (sliding panel staat)
  },
  isMobile: {
    type: Boolean,
    default: false
    // Schakelt tussen mobile sliding panel en desktop statische sidebar
  }
});

/**
 * COMPONENT EVENTS
 * Events die dit component kan uitzenden naar parent components
 */
const emit = defineEmits(['close']);
// close: Wordt uitgezonden om de mobile sidebar te sluiten

/**
 * NAVIGATIE BEHANDELING
 * Behandelt navigatie met speciaal mobile gedrag (sluit sidebar na navigatie)
 */
const handleNavigation = (routeName) => {
  if (props.isMobile) {
    // Sluit sidebar en navigeer (zoals shopping cart patroon)
    emit('close');
    router.visit(route(routeName));
  }
  // Desktop navigatie wordt automatisch behandeld door Inertia Link
};

/**
 * NAVIGATIE ITEMS CONFIGURATIE
 * Georganiseerd in logische groepen voor admin functionaliteit
 */

// Hoofddashboard navigatie
const navigationItems = [
  {
    name: 'Dashboard',
    href: 'admin.dashboard',
    icon: HomeIcon,
    current: 'admin.dashboard'  // Route patroon voor actieve staat detectie
  }
];

// Gebruikersbeheer sectie
const userManagement = [
  {
    name: 'Gebruikers',
    href: 'admin.users.index',
    icon: UsersIcon,
    current: 'admin.users.*'  // Wildcard voor alle gebruikersbeheer routes
  }
];

// Catalogusstructuur sectie (categorieën en subcategorieën)
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

// Overige items (instellingen, configuratie)
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
  <!-- 
    MOBILE SIDEBAR (Sliding Panel)
    Overlay sidebar die van links inschuift op mobiele apparaten
  -->
  <TransitionRoot v-if="isMobile" as="template" :show="isOpen">
    <Dialog as="div" class="relative z-50" @close="$emit('close')">
      
      <!-- Achtergrond/Overlay -->
      <!-- Semi-transparante achtergrond die het sidebar overlay effect creëert -->
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

      <!-- Sidebar Panel Container -->
      <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
          <div class="pointer-events-none fixed inset-y-0 left-0 flex max-w-full">
            
            <!-- Schuivend Panel met Vloeiende Overgangen -->
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
                  
                  <!-- Mobile Sidebar Header -->
                  <!-- Bevat titel en sluitknop voor mobiele gebruikerservaring -->
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

                  <!-- Mobile Navigatie Inhoud -->
                  <!-- Scrollbare navigatie-inhoud met georganiseerde secties -->
                  <nav class="flex-1 overflow-y-auto px-4 py-4">
                    
                    <!-- Dashboard Sectie -->
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

                    <!-- Gebruikersbeheer Sectie -->
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

                    <!-- Catalogusstructuur Sectie -->
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

                    <!-- Overige Items Sectie -->
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

  <!-- 
    DESKTOP SIDEBAR (Statisch)
    Permanente sidebar voor desktop layout - altijd zichtbaar
  -->
  <aside v-else class="w-64 bg-white shadow-lg h-screen flex-shrink-0">
    <nav class="mt-5 px-2">
      
      <!-- Desktop Dashboard Sectie -->
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

      <!-- Desktop Gebruikersbeheer Sectie -->
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

      <!-- Desktop Catalogusstructuur Sectie -->
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

      <!-- Desktop Overige Items Sectie -->
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