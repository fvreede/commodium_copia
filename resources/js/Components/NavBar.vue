/**
 * Bestandsnaam: NavBar.vue
 * Auteur: Fabio Vreede
 * Versie: v1.0.13
 * Datum: 2025-07-02
 * Tijd: 20:44:39
 * Doel: Hoofdnavigatie component voor e-commerce website met geavanceerde responsive design. Bevat intelligent zoeksysteem met real-time suggesties, shopping cart integratie, gebruikers authenticatie menu's, mobile hamburger navigatie en optimale UX voor zowel desktop als mobile gebruikers. Geïntegreerd met Pinia cart store en Inertia.js routing.
 */

<!-- Updated NavBar.vue with better mobile UX and consistent icons -->
<template>
    <!-- 
        HOOFDNAVIGATIE CONTAINER
        Fixed navbar met HeadlessUI Disclosure voor responsive functionaliteit
    -->
    <Disclosure as="nav" class="bg-slate-100 fixed w-full top-0 z-[1000] shadow-md" v-slot="{ open }">
        <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
            <div class="relative flex h-16 items-center justify-between">
                
                <!-- Linkerzijde: Mobile Search + Logo + Desktop Search + Desktop Navigatie -->
                <!-- Responsive layout die zich aanpast aan schermgrootte -->
                <div class="flex flex-1 items-center justify-center sm:items-stretch sm:justify-start">
                    
                    <!-- Mobile Zoek Knop -->
                    <!-- Alleen zichtbaar op mobile voor compact design -->
                    <div class="absolute inset-y-0 left-0 flex items-center sm:hidden">
                        <button @click="toggleButton('search')" class="relative rounded-full p-2 text-gray-700 hover:bg-slate-200 hover:text-gray-900">
                            <MagnifyingGlassIcon class="h-6 w-6" aria-hidden="true"/>
                        </button>
                    </div>

                    <!-- Logo/Merk Identificatie -->
                    <!-- Centrale branding element met link naar homepage -->
                    <Link href="/">
                        <ApplicationLogo />
                    </Link>

                    <!-- Desktop Zoekbalk -->
                    <!-- Geavanceerde zoekfunctionaliteit met real-time suggesties -->
                    <div class="hidden sm:ml-6 sm:block flex-1 max-w-2xl">
                        <div class="relative max-w-md">
                            <!-- Hoofd Zoek Input -->
                            <input 
                                type="search" 
                                placeholder="Zoek een product"  
                                v-model="searchQuery"
                                @keyup.enter="performSearch"
                                @input="handleSearchInput"
                                @focus="showSuggestions = true"
                                @blur="hideSuggestions"
                                class="w-full max-w-md rounded-md border border-gray-300 p-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 shadow-sm pr-10"
                            />
                            <!-- Zoek Icoon Knop -->
                            <MagnifyingGlassIcon 
                                class="absolute right-3 top-2.5 h-4 w-4 text-gray-400 cursor-pointer" 
                                @click="performSearch"
                            />
                            
                            <!-- Zoek Suggesties Dropdown -->
                            <!-- Real-time product suggesties met afbeeldingen en prijzen -->
                            <div 
                                v-if="showSuggestions && suggestions.length > 0" 
                                class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-lg z-50 max-h-80 overflow-y-auto"
                            >
                                <!-- Individuele Product Suggestie -->
                                <div
                                    v-for="suggestion in suggestions"
                                    :key="suggestion.id"
                                    @mousedown="selectSuggestion(suggestion)"
                                    class="flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                >
                                    <!-- Product Afbeelding -->
                                    <img 
                                        :src="`/storage/${suggestion.image_path}`" 
                                        :alt="suggestion.name"
                                        class="w-10 h-10 object-cover rounded-md mr-3"
                                        @error="handleImageError"
                                    />
                                    <!-- Product Details -->
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ suggestion.name }}</p>
                                        <p class="text-xs text-gray-500">{{ suggestion.category_name }} • {{ suggestion.subcategory_name }}</p>
                                    </div>
                                    <!-- Product Prijs -->
                                    <div class="text-sm font-semibold text-green-600">
                                        €{{ suggestion.price.toFixed(2) }}
                                    </div>
                                </div>
                                
                                <!-- Alle Resultaten Bekijken Optie -->
                                <div 
                                    @mousedown="performSearch"
                                    class="p-3 text-center text-sm text-blue-600 hover:bg-blue-50 cursor-pointer font-medium"
                                >
                                    Alle resultaten bekijken voor "{{ searchQuery }}"
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Hoofdnavigatie Links -->
                    <!-- Primaire navigatie items voor desktop gebruikers -->
                    <div class="hidden sm:ml-6 sm:block">
                        <div class="flex space-x-4">
                            <NavLink v-for="item in navigation" :key="item.name" :href="item.href">{{ item.name }}</NavLink>
                        </div>
                    </div>
                </div>

                <!-- Mobile: Cart + Hamburger Menu -->
                <!-- Compacte mobile interface met essentiële functionaliteiten -->
                <div class="flex items-center space-x-2 sm:hidden">
                    
                    <!-- Mobile Winkelwagen Knop -->
                    <!-- Winkelwagen toegang met item counter badge -->
                    <button @click="toggleCart" type="button" class="relative rounded-full bg-slate-50 p-2 text-gray-700 hover:bg-slate-200 hover:text-gray-900">
                        <ShoppingCartIcon class="h-6 w-6" />
                        <!-- Cart Item Counter Badge -->
                        <span v-if="cartItemCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs">
                            {{ cartItemCount }}
                        </span>
                    </button>
                    
                    <!-- Mobile Hamburger Menu -->
                    <!-- Toggle tussen open/gesloten menu states -->
                    <DisclosureButton @click="toggleButton('menu')" class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-700 hover:bg-slate-200 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-black">
                        <span class="absolute -inset-0.5"/>
                        <span class="sr-only">Open main menu</span>
                        <Bars3Icon v-if="!menuOpen" class="block h-6 w-6" aria-hidden="true"/>
                        <XMarkIcon v-else class="block h-6 w-6" aria-hidden="true"/>
                    </DisclosureButton>
                </div>

                <!-- Desktop: Cart + Profiel Menu -->
                <!-- Desktop specifieke functionaliteiten en gebruikers menu -->
                <div class="hidden sm:flex sm:items-center sm:ml-6 sm:space-x-3">
                    
                    <!-- Desktop Winkelwagen -->
                    <!-- Desktop winkelwagen met hover effecten -->
                    <div class="relative">
                        <button @click="toggleCart" type="button" class="relative rounded-full bg-slate-50 p-1 text-gray-700 hover:bg-slate-200 hover:text-gray-900">
                            <ShoppingCartIcon class="h-6 w-6" />
                            <!-- Cart Badge met Item Count -->
                            <span v-if="cartItemCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs">
                                {{ cartItemCount }}
                            </span>
                        </button>
                    </div> 
                    
                    <!-- Desktop Profiel Menu -->
                    <!-- Uitgebreide gebruikers dropdown met account functies -->
                    <Menu as="div" class="relative">
                        <MenuButton v-slot="{ open }" as="template">
                            <button 
                                :class="[
                                    'relative flex rounded-full p-1 text-gray-700 hover:bg-slate-200 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-200 transition-colors duration-150',
                                    open ? 'bg-slate-200' : 'bg-slate-50'
                                ]"
                            >
                                <span class="absolute -inset-1.5"/>
                                <span class="sr-only">Open user menu</span>
                                <UserIcon class="h-6 w-6" aria-hidden="true"/>
                            </button>
                        </MenuButton>
                        
                        <!-- Dropdown Menu Transitions -->
                        <Transition 
                            enter-active-class="transition ease-out duration-100" 
                            enter-from-class="transform opacity-0 scale-95" 
                            enter-to-class="transform opacity-100 scale-100" 
                            leave-active-class="transition ease-in duration-75" 
                            leave-from-class="transform opacity-100 scale-100" 
                            leave-to-class="transform opacity-0 scale-95"
                        >
                            <MenuItems class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                
                                <!-- Gast Gebruiker Menu Items -->
                                <!-- Login en registratie opties voor niet-ingelogde gebruikers -->
                                <template v-if="!isAuthenticated">
                                    <!-- Inloggen Optie -->
                                    <MenuItem v-slot="{ active }">
                                        <Link 
                                            :href="route('login')" 
                                            :class="[
                                                active ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
                                                'group flex items-center px-4 py-2 text-sm font-medium w-full'
                                            ]"
                                        >
                                            <UserIcon class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" />
                                            Inloggen
                                        </Link>
                                    </MenuItem>
                                    
                                    <!-- Registreren Optie -->
                                    <MenuItem v-slot="{ active }">
                                        <Link 
                                            :href="route('register')" 
                                            :class="[
                                                active ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
                                                'group flex items-center px-4 py-2 text-sm font-medium w-full'
                                            ]"
                                        >
                                            <UserPlusIcon class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" />
                                            Registreren
                                        </Link>
                                    </MenuItem>
                                </template>
                                
                                <!-- Ingelogde Gebruiker Menu Items -->
                                <!-- Uitgebreide account management voor geauthenticeerde gebruikers -->
                                <template v-else>
                                    <!-- Gebruiker Informatie Header -->
                                    <!-- Toont gebruiker details aan bovenkant van menu -->
                                    <div class="px-4 py-3 border-b border-gray-200">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                    <UserIcon class="h-5 w-5 text-gray-500" />
                                                </div>
                                            </div>
                                            <div class="ml-3 min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ user.name }}</p>
                                                <p class="text-xs text-gray-500 truncate">{{ user.email }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Navigatie Menu Items -->
                                    <!-- Account gerelateerde navigatie opties -->
                                    <div class="py-1">
                                        <!-- Dashboard Link -->
                                        <MenuItem v-slot="{ active }">
                                            <Link 
                                                :href="route('dashboard')" 
                                                :class="[
                                                    active ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
                                                    'group flex items-center px-4 py-2 text-sm w-full'
                                                ]"
                                            >
                                                <HomeIcon class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" />
                                                Dashboard
                                            </Link>
                                        </MenuItem>
                                        
                                        <!-- Mijn Bestellingen Link -->
                                        <MenuItem v-slot="{ active }">
                                            <Link 
                                                :href="route('orders.index')" 
                                                :class="[
                                                    active ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
                                                    'group flex items-center px-4 py-2 text-sm w-full'
                                                ]"
                                            >
                                                <ShoppingBagIcon class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" />
                                                Mijn Bestellingen
                                            </Link>
                                        </MenuItem>
                                        
                                        <!-- Profiel Instellingen Link -->
                                        <MenuItem v-slot="{ active }">
                                            <Link 
                                                :href="route('profile.edit')" 
                                                :class="[
                                                    active ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
                                                    'group flex items-center px-4 py-2 text-sm w-full'
                                                ]"
                                            >
                                                <Cog6ToothIcon class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" />
                                                Profiel Instellingen
                                            </Link>
                                        </MenuItem>
                                    </div>
                                    
                                    <!-- Scheidingslijn -->
                                    <div class="border-t border-gray-100"></div>
                                    
                                    <!-- Uitloggen Sectie -->
                                    <!-- Prominente uitlog functionaliteit -->
                                    <div class="py-1">
                                        <MenuItem v-slot="{ active }">
                                            <Link 
                                                :href="route('logout')" 
                                                method="post"
                                                :class="[
                                                    active ? 'bg-red-50 text-red-700' : 'text-red-600',
                                                    'group flex items-center px-4 py-2 text-sm font-medium w-full'
                                                ]"
                                            >
                                                <ArrowRightStartOnRectangleIcon class="mr-3 h-5 w-5" />
                                                Uitloggen
                                            </Link>
                                        </MenuItem>
                                    </div>
                                </template>
                            </MenuItems>
                        </Transition>
                    </Menu>
                </div>
            </div>
        </div>

        <!-- Mobile Zoekfunctionaliteit -->
        <!-- Uitklapbare zoekbalk voor mobile gebruikers met suggesties -->
        <div v-if="showSearch && !menuOpen" class="sm:hidden px-4 pb-3 pt-2">
            <div class="relative">
                <!-- Mobile Zoek Input -->
                <input 
                    type="search" 
                    placeholder="Zoek een product" 
                    v-model="mobileSearchQuery"
                    @keyup.enter="performMobileSearch"
                    @input="handleMobileSearchInput"
                    @focus="showMobileSuggestions = true"
                    @blur="hideMobileSuggestions"
                    class="w-full rounded-md border border-gray-300 p-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 shadow-sm pr-10"
                />
                <!-- Mobile Zoek Icoon -->
                <MagnifyingGlassIcon 
                    class="absolute right-3 top-2.5 h-4 w-4 text-gray-400 cursor-pointer" 
                    @click="performMobileSearch"
                />
                
                <!-- Mobile Zoek Suggesties -->
                <!-- Compacte suggesties voor mobiele interface -->
                <div 
                    v-if="showMobileSuggestions && mobileSuggestions.length > 0" 
                    class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-lg z-50 max-h-60 overflow-y-auto"
                >
                    <!-- Mobile Product Suggestie -->
                    <div
                        v-for="suggestion in mobileSuggestions"
                        :key="suggestion.id"
                        @mousedown="selectMobileSuggestion(suggestion)"
                        class="flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                    >
                        <!-- Compacte Product Afbeelding -->
                        <img 
                            :src="`/storage/${suggestion.image_path}`" 
                            :alt="suggestion.name"
                            class="w-8 h-8 object-cover rounded mr-2"
                            @error="handleImageError"
                        />
                        <!-- Mobile Product Info -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ suggestion.name }}</p>
                            <p class="text-xs text-gray-500">€{{ suggestion.price.toFixed(2) }}</p>
                        </div>
                    </div>
                    
                    <!-- Mobile Alle Resultaten Optie -->
                    <div 
                        @mousedown="performMobileSearch"
                        class="p-3 text-center text-sm text-blue-600 hover:bg-blue-50 cursor-pointer font-medium"
                    >
                        Alle resultaten bekijken
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Hamburger Menu -->
        <!-- Uitklapbaar volledig scherm menu voor mobile navigatie -->
        <DisclosurePanel v-if="menuOpen" class="sm:hidden">
            <div class="space-y-1 px-2 pb-3 pt-2">
                
                <!-- Mobile Navigatie Items -->
                <!-- Primaire navigatie links voor mobile -->
                <DisclosureButton 
                    v-for="item in navigation" 
                    :key="item.name" 
                    as="a" 
                    :href="item.href" 
                    class="text-gray-700 hover:bg-gray-200 hover:text-gray-900 block rounded-md px-3 py-2 text-base font-medium"
                >
                    {{ item.name }}
                </DisclosureButton>
                
                <!-- Scheidingslijn -->
                <hr class="my-2 border-gray-300">
                
                <!-- Mobile Profiel Sectie -->
                <!-- Account management voor mobile gebruikers -->
                <div class="pt-2">
                    <!-- Mobile Gast Menu -->
                    <!-- Login en registratie voor niet-ingelogde mobile gebruikers -->
                    <template v-if="!isAuthenticated">
                        <!-- Mobile Inloggen -->
                        <DisclosureButton 
                            as="a" 
                            :href="route('login')"
                            class="text-gray-700 hover:bg-gray-200 hover:text-gray-900 flex items-center rounded-md px-3 py-2 text-base font-medium"
                        >
                            <UserIcon class="h-5 w-5 mr-3 text-gray-400" />
                            Inloggen
                        </DisclosureButton>
                        
                        <!-- Mobile Registreren -->
                        <DisclosureButton 
                            as="a" 
                            :href="route('register')"
                            class="text-gray-700 hover:bg-gray-200 hover:text-gray-900 flex items-center rounded-md px-3 py-2 text-base font-medium"
                        >
                            <UserPlusIcon class="h-5 w-5 mr-3 text-gray-400" />
                            Registreren
                        </DisclosureButton>
                    </template>
                    
                    <!-- Mobile Ingelogde Gebruiker Menu -->
                    <!-- Volledige account functies voor mobile -->
                    <template v-else>
                        <!-- Mobile Gebruiker Info -->
                        <!-- Gebruiker details header voor mobile menu -->
                        <div class="px-3 py-2 border-b border-gray-200 mb-2">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                        <UserIcon class="h-5 w-5 text-gray-500" />
                                    </div>
                                </div>
                                <div class="ml-3 min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ user.name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ user.email }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mobile Dashboard Link -->
                        <DisclosureButton 
                            as="a" 
                            :href="route('dashboard')"
                            class="text-gray-700 hover:bg-gray-200 hover:text-gray-900 flex items-center rounded-md px-3 py-2 text-base font-medium"
                        >
                            <HomeIcon class="h-5 w-5 mr-3 text-gray-400" />
                            Dashboard
                        </DisclosureButton>
                        
                        <!-- Mobile Mijn Bestellingen Link -->
                        <DisclosureButton 
                            as="a" 
                            :href="route('orders.index')"
                            class="text-gray-700 hover:bg-gray-200 hover:text-gray-900 flex items-center rounded-md px-3 py-2 text-base font-medium"
                        >
                            <ShoppingBagIcon class="h-5 w-5 mr-3 text-gray-400" />
                            Mijn Bestellingen
                        </DisclosureButton>
                        
                        <!-- Mobile Profiel Instellingen Link -->
                        <DisclosureButton 
                            as="a" 
                            :href="route('profile.edit')"
                            class="text-gray-700 hover:bg-gray-200 hover:text-gray-900 flex items-center rounded-md px-3 py-2 text-base font-medium"
                        >
                            <Cog6ToothIcon class="h-5 w-5 mr-3 text-gray-400" />
                            Profiel Instellingen
                        </DisclosureButton>
                        
                        <!-- Mobile Uitloggen -->
                        <!-- Prominente uitlog optie voor mobile -->
                        <DisclosureButton 
                            as="a" 
                            :href="route('logout')" 
                            method="post"
                            class="text-red-600 hover:bg-red-50 hover:text-red-700 flex items-center rounded-md px-3 py-2 text-base font-medium"
                        >
                            <ArrowRightStartOnRectangleIcon class="h-5 w-5 mr-3" />
                            Uitloggen
                        </DisclosureButton>
                    </template>
                </div>
            </div>
        </DisclosurePanel>
    </Disclosure>

    <!-- Winkelwagen Component -->
    <!-- Sliding cart panel met complete shopping cart functionaliteit -->
    <ShoppingCart :isOpen="isCartOpen" @close="closeCart"/>

    <!-- Uitlog Succes Modal -->
    <!-- Feedback modal voor succesvolle uitlog actie -->
    <LogoutSuccessModal 
        :show="showLogoutModal" 
        @close="closeLogoutModal" 
    />
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Disclosure, DisclosureButton, DisclosurePanel, Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { Bars3Icon, XMarkIcon, ShoppingCartIcon, UserIcon, MagnifyingGlassIcon, UserPlusIcon, HomeIcon, ShoppingBagIcon, Cog6ToothIcon, ArrowRightStartOnRectangleIcon } from '@heroicons/vue/24/outline'
import ShoppingCart from '@/Components/ShoppingCart.vue'
import { useCartStore } from '@/Stores/cart'
import NavLink from './NavLink.vue'
import { usePage, Link, router } from '@inertiajs/vue3'
import ApplicationLogo from './ApplicationLogo.vue'
import axios from 'axios'
import LogoutSuccessModal from '@/Components/LogoutSuccessModal.vue'

/**
 * NAVIGATIE CONFIGURATIE
 * Hoofdnavigatie items voor de website
 */
const navigation = [
    { name: 'Producten', href: '/categories' },
    //{ name: 'Bestellen', href: '/checkout' }, // Toekomstige uitbreiding
]

/**
 * RESPONSIVE UI STATE MANAGEMENT
 * Controleert welke mobile elementen actief zijn
 */
const activeButton = ref(null) // Huidige actieve mobile knop (search/menu)
const menuOpen = ref(false)    // Mobile hamburger menu staat

/**
 * ZOEKFUNCTIONALITEIT STATE
 * Separate state voor desktop en mobile zoek ervaringen
 */
const searchQuery = ref('')            // Desktop zoek query
const mobileSearchQuery = ref('')      // Mobile zoek query  
const suggestions = ref([])            // Desktop zoek suggesties
const mobileSuggestions = ref([])      // Mobile zoek suggesties
const showSuggestions = ref(false)     // Desktop suggesties zichtbaarheid
const showMobileSuggestions = ref(false) // Mobile suggesties zichtbaarheid
const searchTimeout = ref(null)       // Debounce timeout voor zoek API calls

/**
 * WINKELWAGEN STATE MANAGEMENT
 * Shopping cart interface en animatie state
 */
const isCartOpen = ref(false)    // Cart panel zichtbaarheid
const isCartUpdated = ref(false) // Animatie trigger voor cart updates

/**
 * CART STORE INTEGRATIE
 * Pinia store connectie voor cart management
 */
const cartStore = useCartStore()
const cartItemCount = computed(() => cartStore.totalItems)

/**
 * CART ANIMATIE WATCHER
 * Triggert pulse animatie wanneer cart items wijzigen
 */
watch(cartItemCount, () => {
    isCartUpdated.value = true
    setTimeout(() => {
        isCartUpdated.value = false
    }, 300) // 300ms animatie duur
})

/**
 * COMPUTED EIGENSCHAPPEN
 * Reactive UI state berekeningen
 */
const showSearch = computed(() => activeButton.value === 'search')

/**
 * DESKTOP ZOEKFUNCTIONALITEIT
 * Hoofdzoek implementatie voor desktop gebruikers
 */

// Voert zoekactie uit en navigeert naar zoekresultaten pagina
const performSearch = () => {
    if (searchQuery.value.trim()) {
        router.get('/search', { q: searchQuery.value.trim() })
        showSuggestions.value = false
    }
}

// Behandelt input changes met debounce voor performance
const handleSearchInput = () => {
    clearTimeout(searchTimeout.value)
    
    if (searchQuery.value.length >= 2) {
        searchTimeout.value = setTimeout(() => {
            fetchSuggestions(searchQuery.value)
        }, 300) // 300ms debounce
    } else {
        suggestions.value = []
        showSuggestions.value = false
    }
}

// Haalt zoek suggesties op van de server
const fetchSuggestions = async (query) => {
    try {
        const response = await axios.get('/search/suggestions', {
            params: { q: query }
        })
        suggestions.value = response.data
        showSuggestions.value = suggestions.value.length > 0
    } catch (error) {
        console.error('Error fetching suggestions:', error)
        suggestions.value = []
        showSuggestions.value = false
    }
}

// Selecteert een suggestie en navigeert naar product pagina
const selectSuggestion = (suggestion) => {
    router.get(`/product/${suggestion.id}`)
    showSuggestions.value = false
    searchQuery.value = ''
}

// Verbergt suggesties met kleine delay voor UX
const hideSuggestions = () => {
    setTimeout(() => {
        showSuggestions.value = false
    }, 200)
}

/**
 * MOBILE ZOEKFUNCTIONALITEIT
 * Geoptimaliseerde zoek implementatie voor mobile interface
 */

// Voert mobile zoekactie uit
const performMobileSearch = () => {
    if (mobileSearchQuery.value.trim()) {
        router.get('/search', { q: mobileSearchQuery.value.trim() })
        showMobileSuggestions.value = false
        activeButton.value = null // Sluit mobile search
    }
}

// Behandelt mobile search input met debounce
const handleMobileSearchInput = () => {
    clearTimeout(searchTimeout.value)
    
    if (mobileSearchQuery.value.length >= 2) {
        searchTimeout.value = setTimeout(() => {
            fetchMobileSuggestions(mobileSearchQuery.value)
        }, 300)
    } else {
        mobileSuggestions.value = []
        showMobileSuggestions.value = false
    }
}

// Haalt mobile suggesties op (compactere data)
const fetchMobileSuggestions = async (query) => {
    try {
        const response = await axios.get('/search/suggestions', {
            params: { q: query }
        })
        mobileSuggestions.value = response.data
        showMobileSuggestions.value = mobileSuggestions.value.length > 0
    } catch (error) {
        console.error('Error fetching mobile suggestions:', error)
        mobileSuggestions.value = []
        showMobileSuggestions.value = false
    }
}

// Selecteert mobile suggestie en sluit interface
const selectMobileSuggestion = (suggestion) => {
    router.get(`/product/${suggestion.id}`)
    showMobileSuggestions.value = false
    mobileSearchQuery.value = ''
    activeButton.value = null
}

// Verbergt mobile suggesties met delay
const hideMobileSuggestions = () => {
    setTimeout(() => {
        showMobileSuggestions.value = false
    }, 200)
}

/**
 * UTILITY FUNCTIES
 * Helper functies voor afbeelding handling en interface management
 */

// Fallback voor ontbrekende product afbeeldingen
const handleImageError = (event) => {
    event.target.src = '/images/placeholder-product.jpg'
}

// Toggle functie voor mobile interface elementen
const toggleButton = (button) => {
    if (activeButton.value === button) {
        activeButton.value = null;
        if (button === 'menu') {
            menuOpen.value = false
        }
    } else {
        activeButton.value = button;
        if (button === 'menu') {
            menuOpen.value = true;
        } else {
            menuOpen.value = false;
        }
    }
};

/**
 * WINKELWAGEN MANAGEMENT
 * Shopping cart interface controle functies
 */

// Toggle cart panel zichtbaarheid
const toggleCart = () => {
    console.log('toggleCart called, current state:', isCartOpen.value)
    isCartOpen.value = !isCartOpen.value
}

// Sluit cart panel
const closeCart = () => {
    console.log('closeCart called')
    isCartOpen.value = false
}

/**
 * GEBRUIKER AUTHENTICATIE
 * User authentication state en logout handling
 */

// Gebruiker informatie van Inertia page props
const user = computed(() => usePage().props.auth.user);
const isAuthenticated = computed(() => !!user.value);

// Logout modal state
const showLogoutModal = ref(false)

/**
 * LOGOUT SUCCESS WATCHER
 * Detecteert succesvolle logout en toont confirmation modal
 */
watch(() => usePage().props.flash, (flash) => {
    if (flash && flash.logout && flash.logout.success) {
        showLogoutModal.value = true
    }
}, { deep: true })

// Sluit logout confirmation modal
const closeLogoutModal = () => {
    showLogoutModal.value = false
}
</script>

<style scoped>  
/**
 * COMPONENT ANIMATIES
 * Custom CSS voor cart update feedback en transitions
 */

/* Cart update pulse animatie */
.scale-110 {
    animation: pulse 0.3s ease-in-out;
}

/* Pulse animatie keyframes voor cart feedback */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}
</style>