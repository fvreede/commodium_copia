<!-- NavBar.vue - Enhanced Version -->
<template>
    <Disclosure as="nav" class="bg-slate-100 fixed w-full top-0 z-[1000] shadow-md" v-slot="{ open }">
        <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
            <div class="relative flex h-16 items-center justify-between">
                <div class="flex flex-1 items-center justify-center sm:items-stretch sm:justify-start">
                    <!-- Mobile search button -->
                    <div class="absolute inset-y-0 left-0 flex items-center sm:hidden">
                        <button @click="toggleButton('search')" class="relative rounded-full p-2 text-gray-700 hover:bg-slate-200 hover:text-gray-900">
                            <MagnifyingGlassIcon class="h-6 w-6" aria-hidden="true"/>
                        </button>
                    </div>

                    <!-- Logo -->                   
                    <Link href="/">
                        <ApplicationLogo />
                    </Link>

                    <!-- Enhanced Desktop Search Bar -->
                    <div class="hidden sm:ml-6 sm:block relative flex-1 max-w-2xl">
                        <div class="relative">
                            <div class="relative">
                                <MagnifyingGlassIcon class="absolute left-3 top-3 h-5 w-5 text-gray-400" />
                                <input 
                                    type="search" 
                                    placeholder="Zoek een product..."  
                                    v-model="search.searchQuery.value"
                                    @keyup.enter="search.performSearch()"
                                    @input="handleSearchInput"
                                    @focus="handleSearchFocus"
                                    @blur="search.hideSuggestions"
                                    @keydown.arrow-down.prevent="navigateSuggestions(1)"
                                    @keydown.arrow-up.prevent="navigateSuggestions(-1)"
                                    @keydown.escape="clearSearch"
                                    class="w-full pl-10 pr-12 py-3 text-sm bg-white rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm transition-all duration-200"
                                />
                                <div class="absolute right-2 top-2 flex items-center space-x-1">
                                    <button 
                                        v-if="search.searchQuery.value"
                                        @click="clearSearch"
                                        class="p-1 rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-600"
                                    >
                                        <XMarkIcon class="h-4 w-4" />
                                    </button>
                                    <button 
                                        @click="search.performSearch()"
                                        class="p-1 rounded-full hover:bg-blue-50 text-blue-500 hover:text-blue-600"
                                    >
                                        <MagnifyingGlassIcon class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Enhanced Search Suggestions Dropdown -->
                            <Transition
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="opacity-0 scale-95"
                                enter-to-class="opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="opacity-100 scale-100"
                                leave-to-class="opacity-0 scale-95"
                            >
                                <div 
                                    v-if="search.showSuggestions.value && (search.suggestions.value.length > 0 || search.isLoading.value)" 
                                    class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-xl z-50 max-h-96 overflow-hidden"
                                >
                                    <!-- Loading state -->
                                    <div v-if="search.isLoading.value" class="p-4 text-center">
                                        <div class="inline-flex items-center">
                                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span class="text-sm text-gray-500">Zoeken...</span>
                                        </div>
                                    </div>

                                    <!-- Suggestions -->
                                    <div v-else class="max-h-80 overflow-y-auto">
                                        <div
                                            v-for="(suggestion, index) in search.suggestions.value"
                                            :key="suggestion.id"
                                            @mousedown="search.selectSuggestion(suggestion)"
                                            @mouseenter="selectedSuggestionIndex = index"
                                            :class="[
                                                'flex items-center p-4 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors duration-150',
                                                selectedSuggestionIndex === index ? 'bg-blue-50' : ''
                                            ]"
                                        >
                                            <div class="relative w-12 h-12 bg-gray-100 rounded-lg overflow-hidden mr-4 flex-shrink-0">
                                                <img 
                                                    :src="`/storage/${suggestion.image_path}`" 
                                                    :alt="suggestion.name"
                                                    class="w-full h-full object-cover"
                                                    @error="handleImageError"
                                                />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ suggestion.name }}</p>
                                                <p class="text-xs text-gray-500 mt-1">{{ suggestion.category_name }} • {{ suggestion.subcategory_name }}</p>
                                            </div>
                                            <div class="text-sm font-semibold text-green-600 ml-4">
                                                €{{ suggestion.price.toFixed(2) }}
                                            </div>
                                        </div>
                                        
                                        <!-- View all results option -->
                                        <div 
                                            @mousedown="search.performSearch()"
                                            @mouseenter="selectedSuggestionIndex = search.suggestions.value.length"
                                            :class="[
                                                'p-4 text-center text-sm font-medium cursor-pointer transition-colors duration-150',
                                                selectedSuggestionIndex === search.suggestions.value.length 
                                                    ? 'bg-blue-600 text-white' 
                                                    : 'text-blue-600 hover:bg-blue-50'
                                            ]"
                                        >
                                            <MagnifyingGlassIcon class="inline-block w-4 h-4 mr-2" />
                                            Alle resultaten bekijken voor "{{ search.searchQuery.value }}"
                                        </div>
                                    </div>

                                    <!-- No results state -->
                                    <div v-if="!search.isLoading.value && search.suggestions.value.length === 0 && search.searchQuery.value.length >= 2" class="p-4 text-center text-gray-500">
                                        <div class="text-sm">Geen producten gevonden voor "{{ search.searchQuery.value }}"</div>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden sm:ml-6 sm:block">
                        <div class="flex space-x-4">
                            <NavLink v-for="item in navigation" :key="item.name" :href="item.href">{{ item.name }}</NavLink>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="absolute inset-y-0 right-0 flex items-center sm:hidden">
                    <DisclosureButton @click="toggleButton('menu')" class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-700 hover:bg-slate-200 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-black">
                        <span class="absolute -inset-0.5"/>
                        <span class="sr-only">Open main menu</span>
                        <Bars3Icon v-if="!menuOpen" class="block h-6 w-6" aria-hidden="true"/>
                        <XMarkIcon v-else class="block h-6 w-6" aria-hidden="true"/>
                    </DisclosureButton>
                </div>

                <!-- Cart and Profile Container -->
                <div class="absolute inset-y-0 right-10 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
                    <div class="relative">
                        <button @click="toggleCart" type="button" class="relative rounded-full bg-slate-50 p-1 text-gray-700 hover:bg-slate-200 hover:text-gray-900">
                            <ShoppingCartIcon class="h-6 w-6" />
                            <span v-if="cartItemCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs">
                                {{ cartItemCount }}
                            </span>
                        </button>
                    </div>
                    
                    <!-- Profile Menu -->
                    <Menu as="div" class="relative ml-3">
                        <div>
                            <MenuButton 
                                @click="toggleButton('profile')" 
                                class="relative flex rounded-full bg-slate-50 p-1 text-gray-700 hover:bg-slate-200 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-200"
                            >
                                <span class="absolute -inset-1.5"/>
                                <span class="sr-only">Open user menu</span>
                                <UserIcon class="h-6 w-6" aria-hidden="true"/>
                            </MenuButton>
                        </div>
                        
                        <Transition 
                            enter-active-class="transition ease-out duration-100" 
                            enter-from-class="transform opacity-0 scale-95" 
                            enter-to-class="transform opacity-100 scale-100" 
                            leave-active-class="transition ease-in duration-75" 
                            leave-from-class="transform opacity-100 scale-100" 
                            leave-to-class="transform opacity-0 scale-95"
                        >
                            <MenuItems class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-slate-50 py-1 shadow-md ring-1 ring-black ring-opacity-5 focus:outline-none">
                                <template v-if="!isAuthenticated">
                                    <MenuItem v-slot="{ active }">
                                        <Link :href="route('login')" :class="[active ? 'bg-gray-500' : '', 'block px-4 py-2 text-sm text-black']">
                                            Inloggen
                                        </Link>
                                    </MenuItem>
                                    <MenuItem v-slot="{ active }">
                                        <Link :href="route('register')" :class="[active ? 'bg-gray-500' : '', 'block px-4 py-2 text-sm text-black']">
                                            Registreren
                                        </Link>
                                    </MenuItem>
                                </template>
                            </MenuItems>
                        </Transition>
                    </Menu>
                </div>
            </div>
        </div>

        <!-- Enhanced Mobile Search -->
        <div v-if="showSearch && !menuOpen" class="sm:hidden px-4 pb-4 pt-2 bg-white border-t border-gray-200">
            <div class="relative">
                <MagnifyingGlassIcon class="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                <input 
                    type="search" 
                    placeholder="Zoek een product..." 
                    v-model="mobileSearch.searchQuery.value"
                    @keyup.enter="mobileSearch.performSearch()"
                    @input="handleMobileSearchInput"
                    @focus="mobileSearch.showSuggestions.value = true"
                    @blur="mobileSearch.hideSuggestions"
                    class="w-full pl-10 pr-10 py-2 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <button 
                    @click="mobileSearch.performSearch()"
                    class="absolute right-2 top-2 p-1 rounded-full hover:bg-blue-50 text-blue-500"
                >
                    <MagnifyingGlassIcon class="h-4 w-4" />
                </button>
                
                <!-- Mobile Suggestions -->
                <div 
                    v-if="mobileSearch.showSuggestions.value && mobileSearch.suggestions.value.length > 0" 
                    class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-50 max-h-60 overflow-y-auto"
                >
                    <div
                        v-for="suggestion in mobileSearch.suggestions.value"
                        :key="suggestion.id"
                        @mousedown="mobileSearch.selectSuggestion(suggestion)"
                        class="flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                    >
                        <img 
                            :src="`/storage/${suggestion.image_path}`" 
                            :alt="suggestion.name"
                            class="w-8 h-8 object-cover rounded mr-3"
                            @error="handleImageError"
                        />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ suggestion.name }}</p>
                            <p class="text-xs text-gray-500">€{{ suggestion.price.toFixed(2) }}</p>
                        </div>
                    </div>
                    
                    <div 
                        @mousedown="mobileSearch.performSearch()"
                        class="p-3 text-center text-sm text-blue-600 hover:bg-blue-50 cursor-pointer font-medium"
                    >
                        Alle resultaten bekijken
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <DisclosurePanel v-if="menuOpen" class="sm:hidden">
            <div class="space-y-1 px-2 pb-3 pt-2">
                <DisclosureButton v-for="item in navigation" :key="item.name" as="a" :href="item.href" :class="[item.current ? 'bg-slate-50 text-gray-700' : 'text-gray-700 hover:bg-gray-200 hover:text-gray-900', 'block rounded-md px-3 py-2 text-base font-medium']" :aria-current="item.current ? 'page' : undefined">
                    {{ item.name }}
                </DisclosureButton>
            </div>
        </DisclosurePanel>
    </Disclosure>

    <!-- Shopping Cart Component -->
    <ShoppingCart :isOpen="isCartOpen" @close="closeCart"/>
</template>

<script setup>
import { computed, ref, watch, nextTick } from 'vue'
import { Disclosure, DisclosureButton, DisclosurePanel, Menu, MenuButton, MenuItem, MenuItems, Transition } from '@headlessui/vue'
import { Bars3Icon, XMarkIcon, ShoppingCartIcon, UserIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import ShoppingCart from '@/Components/ShoppingCart.vue'
import { useCartStore } from '@/Stores/cart'
import NavLink from './NavLink.vue'
import { usePage, Link } from '@inertiajs/vue3'
import ApplicationLogo from './ApplicationLogo.vue'
import { useSearch } from '@/Composables/useSearch'

// Navigation items
const navigation = [
    { name: 'Producten', href: '/categories' },
    { name: 'Aanbiedingen', href: '#' },
]

// Component state
const activeButton = ref(null)
const menuOpen = ref(false)
const selectedSuggestionIndex = ref(-1)

// Search functionality
const search = useSearch()
const mobileSearch = useSearch()

// Cart functionality
const isCartOpen = ref(false)
const isCartUpdated = ref(false)
const cartStore = useCartStore()
const cartItemCount = computed(() => cartStore.totalItems)

// Computed properties
const showSearch = computed(() => activeButton.value === 'search')
const user = computed(() => usePage().props.auth.user)
const isAuthenticated = computed(() => !!user.value)

// Watch for cart updates to trigger animation
watch(cartItemCount, () => {
    isCartUpdated.value = true
    setTimeout(() => {
        isCartUpdated.value = false
    }, 300)
})

// Search input handlers
const handleSearchInput = (event) => {
    search.handleSearchInput(event.target.value)
    selectedSuggestionIndex.value = -1
}

const handleMobileSearchInput = (event) => {
    mobileSearch.handleSearchInput(event.target.value)
}

const handleSearchFocus = () => {
    if (search.searchQuery.value.length >= 2) {
        search.showSuggestions.value = true
    }
}

// Keyboard navigation for suggestions
const navigateSuggestions = (direction) => {
    const maxIndex = search.suggestions.value.length
    
    if (direction === 1) { // Down
        selectedSuggestionIndex.value = selectedSuggestionIndex.value < maxIndex 
            ? selectedSuggestionIndex.value + 1 
            : 0
    } else { // Up
        selectedSuggestionIndex.value = selectedSuggestionIndex.value > 0 
            ? selectedSuggestionIndex.value - 1 
            : maxIndex
    }

    // Handle selection with Enter key
    if (selectedSuggestionIndex.value === maxIndex) {
        // "View all results" option
        return
    } else if (selectedSuggestionIndex.value >= 0 && selectedSuggestionIndex.value < maxIndex) {
        // Specific product suggestion - could be handled with Enter key
        return
    }
}

// Clear search
const clearSearch = () => {
    search.clearSearch()
    selectedSuggestionIndex.value = -1
}

// Toggle functions
const toggleButton = (button) => {
    if (activeButton.value === button) {
        activeButton.value = null
        if (button === 'menu') {
            menuOpen.value = false
        }
    } else {
        activeButton.value = button
        if (button === 'menu') {
            menuOpen.value = true
        } else {
            menuOpen.value = false
        }
    }
}

// Cart functions
const toggleCart = () => {
    isCartOpen.value = !isCartOpen.value
}

const closeCart = () => {
    isCartOpen.value = false
}

// Image error handler
const handleImageError = (event) => {
    event.target.src = '/images/placeholder-product.jpg'
}
</script>

<style scoped>  
.scale-110 {
    animation: pulse 0.3s ease-in-out;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}
</style>