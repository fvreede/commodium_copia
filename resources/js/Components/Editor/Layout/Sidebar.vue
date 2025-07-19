/**
 * Bestandsnaam: Sidebar.vue
 * Auteur: Fabio Vreede
 * Versie: v1.0.7
 * Datum: 2025-07-03
 * Tijd: 00:21:59
 * Doel: Content editor dashboard sidebar component met responsive design voor content management systeem. Bevat mobile sliding panel en desktop static sidebar met georganiseerde navigatie voor dashboard, homepage beheer, catalogus beheer en instellingen. Geoptimaliseerd voor content editors en redacteuren met intuïtieve workflow en HeadlessUI transitions.
 */

<!-- resources/js/Components/Editor/Layout/Sidebar.vue -->
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
 NewspaperIcon,
 TagIcon,
 PhotoIcon,
 ShoppingBagIcon,
 Cog6ToothIcon,
 XMarkIcon
} from '@heroicons/vue/24/outline';

/**
 * COMPONENT EIGENSCHAPPEN
 * Configuratie-opties voor responsive editor sidebar gedrag
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
 * Behandelt navigatie met speciaal mobile gedrag voor editor workflow
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
 * Georganiseerd in logische groepen voor content management functionaliteit
 */

// Hoofddashboard navigatie voor editor overzicht
const navigationItems = [
    {
        name: 'Dashboard',
        href: 'editor.dashboard',
        icon: HomeIcon,
        current: 'editor.dashboard'  // Route patroon voor actieve staat detectie
    }
];

// Homepage beheer sectie voor content editors
const homepageManagement = [
    {
        name: 'Aanbiedingsacties',
        href: 'editor.promotions.index',
        icon: TagIcon,
        current: 'editor.promotions.*'  // Wildcard voor alle promotie routes
    },
    {
        name: 'Nieuwsartikelen',
        href: 'editor.news.index',
        icon: NewspaperIcon,
        current: 'editor.news.*'  // Wildcard voor alle nieuws routes
    }
];

// Catalogus beheer sectie voor product en banner management
const catalogManagement = [
    {
        name: 'Producten',
        href: 'editor.products.index',
        icon: ShoppingBagIcon,
        current: 'editor.products.*'  // Wildcard voor alle product routes
    },
    {
        name: 'Categorie Banners',
        href: 'editor.banners.index',
        icon: PhotoIcon,
        current: 'editor.banners.*'  // Wildcard voor alle banner routes
    }
];

// Instellingen sectie voor systeem configuratie
const settingsItems = [
    {
        name: 'Instellingen',
        href: 'editor.settings',
        icon: Cog6ToothIcon,
        current: 'editor.settings'
    }
];
</script>

<template>
    <!-- 
        MOBILE SIDEBAR (Sliding Panel)
        Overlay sidebar die van links inschuift op mobiele apparaten voor editors
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
                                    <!-- Bevat titel en sluitknop voor mobiele editor gebruikerservaring -->
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
                                    <!-- Scrollbare navigatie-inhoud met georganiseerde editor secties -->
                                    <nav class="flex-1 overflow-y-auto px-4 py-4">
                                        
                                        <!-- Dashboard Sectie -->
                                        <!-- Hoofdoverzicht voor editor statistieken en activiteit -->
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

                                        <!-- Homepage Beheer Sectie -->
                                        <!-- Content management voor homepage elementen -->
                                        <div class="mt-8">
                                            <div class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                                                Homepage beheer
                                            </div>
                                            <div class="space-y-1">
                                                <Link
                                                    v-for="item in homepageManagement"
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

                                        <!-- Catalogus Beheer Sectie -->
                                        <!-- Product en banner management voor editors -->
                                        <div class="mt-8">
                                            <div class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                                                Catalogus beheer
                                            </div>
                                            <div class="space-y-1">
                                                <Link
                                                    v-for="item in catalogManagement"
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

                                        <!-- Instellingen Sectie -->
                                        <!-- Systeem configuratie voor editor omgeving -->
                                        <div class="mt-8">
                                            <div class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                                                Instellingen
                                            </div>
                                            <div class="space-y-1">
                                                <Link
                                                    v-for="item in settingsItems"
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
        Permanente sidebar voor desktop editor layout - altijd zichtbaar
    -->
    <aside v-else class="w-64 bg-white shadow-lg h-screen flex-shrink-0">
        <nav class="mt-5 px-2">
            
            <!-- Desktop Dashboard Sectie -->
            <!-- Compacte desktop weergave van hoofddashboard -->
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

            <!-- Desktop Homepage Beheer Sectie -->
            <!-- Content management tools voor homepage elementen -->
            <div class="pt-4">
                <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Homepage beheer
                </div>
                <Link
                    v-for="item in homepageManagement"
                    :key="item.name"
                    :href="route(item.href)"
                    class="flex items-center px-4 py-2 mt-2 text-gray-700 hover:bg-gray-200 rounded-md transition-colors"
                    :class="route().current(item.current) ? 'bg-gray-200' : ''"
                >
                    <component :is="item.icon" class="w-5 h-5 mr-3" />
                    <span>{{ item.name }}</span>
                </Link>
            </div>

            <!-- Desktop Catalogus Beheer Sectie -->
            <!-- Product en banner management voor desktop editor workflow -->
            <div class="pt-4">
                <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Catalogus beheer
                </div>
                <Link
                    v-for="item in catalogManagement"
                    :key="item.name"
                    :href="route(item.href)"
                    class="flex items-center px-4 py-2 mt-2 text-gray-700 hover:bg-gray-200 rounded-md transition-colors"
                    :class="route().current(item.current) ? 'bg-gray-200' : ''"
                >
                    <component :is="item.icon" class="w-5 h-5 mr-3" />
                    <span>{{ item.name }}</span>
                </Link>
            </div>

            <!-- Desktop Instellingen Sectie -->
            <!-- Systeem configuratie toegang voor desktop gebruikers -->
            <div class="pt-4">
                <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Instellingen
                </div>
                <Link
                    v-for="item in settingsItems"
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