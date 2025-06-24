<!-- Updated NavBar.vue with better mobile UX -->
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

                    <!-- Desktop search bar -->
                    <div class="hidden sm:ml-6 sm:block flex-1 max-w-2xl">
                        <div class="relative max-w-md">
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
                            <MagnifyingGlassIcon 
                                class="absolute right-3 top-2.5 h-4 w-4 text-gray-400 cursor-pointer" 
                                @click="performSearch"
                            />
                            
                            <!-- Search Suggestions Dropdown -->
                            <div 
                                v-if="showSuggestions && suggestions.length > 0" 
                                class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-lg z-50 max-h-80 overflow-y-auto"
                            >
                                <div
                                    v-for="suggestion in suggestions"
                                    :key="suggestion.id"
                                    @mousedown="selectSuggestion(suggestion)"
                                    class="flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                >
                                    <img 
                                        :src="`/storage/${suggestion.image_path}`" 
                                        :alt="suggestion.name"
                                        class="w-10 h-10 object-cover rounded-md mr-3"
                                        @error="handleImageError"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ suggestion.name }}</p>
                                        <p class="text-xs text-gray-500">{{ suggestion.category_name }} • {{ suggestion.subcategory_name }}</p>
                                    </div>
                                    <div class="text-sm font-semibold text-green-600">
                                        €{{ suggestion.price.toFixed(2) }}
                                    </div>
                                </div>
                                
                                <!-- View all results option -->
                                <div 
                                    @mousedown="performSearch"
                                    class="p-3 text-center text-sm text-blue-600 hover:bg-blue-50 cursor-pointer font-medium"
                                >
                                    Alle resultaten bekijken voor "{{ searchQuery }}"
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop navigation -->
                    <div class="hidden sm:ml-6 sm:block">
                        <div class="flex space-x-4">
                            <NavLink v-for="item in navigation" :key="item.name" :href="item.href">{{ item.name }}</NavLink>
                        </div>
                    </div>
                </div>

                <!-- Mobile: Cart + Hamburger menu -->
                <div class="flex items-center space-x-2 sm:hidden">
                    <!-- Mobile cart button -->
                    <button @click="toggleCart" type="button" class="relative rounded-full bg-slate-50 p-2 text-gray-700 hover:bg-slate-200 hover:text-gray-900">
                        <ShoppingCartIcon class="h-6 w-6" />
                        <span v-if="cartItemCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs">
                            {{ cartItemCount }}
                        </span>
                    </button>
                    
                    <!-- Mobile hamburger menu -->
                    <DisclosureButton @click="toggleButton('menu')" class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-700 hover:bg-slate-200 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-black">
                        <span class="absolute -inset-0.5"/>
                        <span class="sr-only">Open main menu</span>
                        <Bars3Icon v-if="!menuOpen" class="block h-6 w-6" aria-hidden="true"/>
                        <XMarkIcon v-else class="block h-6 w-6" aria-hidden="true"/>
                    </DisclosureButton>
                </div>

                <!-- Desktop: Cart + Profile -->
                <div class="hidden sm:flex sm:items-center sm:ml-6 sm:space-x-3">
                    <!-- Desktop cart -->
                    <div class="relative">
                        <button @click="toggleCart" type="button" class="relative rounded-full bg-slate-50 p-1 text-gray-700 hover:bg-slate-200 hover:text-gray-900">
                            <ShoppingCartIcon class="h-6 w-6" />
                            <span v-if="cartItemCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs">
                                {{ cartItemCount }}
                            </span>
                        </button>
                    </div>
                    
                    <!-- Desktop profile menu -->
                    <Menu as="div" class="relative">
                        <div>
                            <MenuButton class="relative flex rounded-full bg-slate-50 p-1 text-gray-700 hover:bg-slate-200 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-200">
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
                                <!-- Your existing desktop profile menu items -->
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
                                
                                <template v-else>
                                    <div class="px-4 py-2 border-b border-gray-200">
                                        <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                                        <p class="text-xs text-gray-500">{{ user.email }}</p>
                                    </div>
                                    <MenuItem v-slot="{ active }">
                                        <Link :href="route('dashboard')" :class="[active ? 'bg-gray-500' : '', 'block px-4 py-2 text-sm text-black']">
                                            Dashboard
                                        </Link>
                                    </MenuItem>
                                    <MenuItem v-slot="{ active }">
                                        <Link :href="route('profile.edit')" :class="[active ? 'bg-gray-500' : '', 'block px-4 py-2 text-sm text-black']">
                                            Profiel Instellingen
                                        </Link>  
                                    </MenuItem>
                                    <MenuItem v-slot="{ active }">
                                        <Link :href="route('logout')" method="post" :class="[active ? 'bg-gray-500' : '', 'block px-4 py-2 text-sm text-black']">
                                            Uitloggen
                                        </Link>
                                    </MenuItem>
                                </template>
                            </MenuItems>
                        </Transition>
                    </Menu>
                </div>
            </div>
        </div>

        <!-- Mobile search -->
        <div v-if="showSearch && !menuOpen" class="sm:hidden px-4 pb-3 pt-2">
            <div class="relative">
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
                <MagnifyingGlassIcon 
                    class="absolute right-3 top-2.5 h-4 w-4 text-gray-400 cursor-pointer" 
                    @click="performMobileSearch"
                />
                
                <!-- Mobile Search Suggestions -->
                <div 
                    v-if="showMobileSuggestions && mobileSuggestions.length > 0" 
                    class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-lg z-50 max-h-60 overflow-y-auto"
                >
                    <div
                        v-for="suggestion in mobileSuggestions"
                        :key="suggestion.id"
                        @mousedown="selectMobileSuggestion(suggestion)"
                        class="flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                    >
                        <img 
                            :src="`/storage/${suggestion.image_path}`" 
                            :alt="suggestion.name"
                            class="w-8 h-8 object-cover rounded mr-2"
                            @error="handleImageError"
                        />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ suggestion.name }}</p>
                            <p class="text-xs text-gray-500">€{{ suggestion.price.toFixed(2) }}</p>
                        </div>
                    </div>
                    
                    <div 
                        @mousedown="performMobileSearch"
                        class="p-3 text-center text-sm text-blue-600 hover:bg-blue-50 cursor-pointer font-medium"
                    >
                        Alle resultaten bekijken
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile hamburger menu with profile options -->
        <DisclosurePanel v-if="menuOpen" class="sm:hidden">
            <div class="space-y-1 px-2 pb-3 pt-2">
                <!-- Navigation items -->
                <DisclosureButton 
                    v-for="item in navigation" 
                    :key="item.name" 
                    as="a" 
                    :href="item.href" 
                    class="text-gray-700 hover:bg-gray-200 hover:text-gray-900 block rounded-md px-3 py-2 text-base font-medium"
                >
                    {{ item.name }}
                </DisclosureButton>
                
                <!-- Divider -->
                <hr class="my-2 border-gray-300">
                
                <!-- Profile section -->
                <div class="pt-2">
                    <template v-if="!isAuthenticated">
                        <DisclosureButton 
                            as="a" 
                            :href="route('login')"
                            class="text-gray-700 hover:bg-gray-200 hover:text-gray-900 flex items-center rounded-md px-3 py-2 text-base font-medium"
                        >
                            <UserIcon class="h-5 w-5 mr-3" />
                            Inloggen
                        </DisclosureButton>
                        <DisclosureButton 
                            as="a" 
                            :href="route('register')"
                            class="text-gray-700 hover:bg-gray-200 hover:text-gray-900 block rounded-md px-3 py-2 text-base font-medium ml-8"
                        >
                            Registreren
                        </DisclosureButton>
                    </template>
                    
                    <template v-else>
                        <!-- User info -->
                        <div class="px-3 py-2 border-b border-gray-200 mb-2">
                            <div class="flex items-center">
                                <UserIcon class="h-8 w-8 text-gray-400 mr-3" />
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                                    <p class="text-xs text-gray-500">{{ user.email }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Profile menu items -->
                        <DisclosureButton 
                            as="a" 
                            :href="route('dashboard')"
                            class="text-gray-700 hover:bg-gray-200 hover:text-gray-900 block rounded-md px-3 py-2 text-base font-medium"
                        >
                            Dashboard
                        </DisclosureButton>
                        <DisclosureButton 
                            as="a" 
                            :href="route('profile.edit')"
                            class="text-gray-700 hover:bg-gray-200 hover:text-gray-900 block rounded-md px-3 py-2 text-base font-medium"
                        >
                            Profiel Instellingen
                        </DisclosureButton>
                        <DisclosureButton 
                            as="a" 
                            :href="route('logout')" 
                            method="post"
                            class="text-red-600 hover:bg-red-50 hover:text-red-700 block rounded-md px-3 py-2 text-base font-medium"
                        >
                            Uitloggen
                        </DisclosureButton>
                    </template>
                </div>
            </div>
        </DisclosurePanel>
    </Disclosure>

    <!-- Shopping cart component -->
    <ShoppingCart :isOpen="isCartOpen" @close="closeCart"/>

    <!-- Logout success modal -->
    <LogoutSuccessModal 
        :show="showLogoutModal" 
        @close="closeLogoutModal" 
    />
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Disclosure, DisclosureButton, DisclosurePanel, Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { Bars3Icon, XMarkIcon, ShoppingCartIcon, UserIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import ShoppingCart from '@/Components/ShoppingCart.vue'
import { useCartStore } from '@/Stores/cart'
import NavLink from './NavLink.vue'
import { usePage, Link, router } from '@inertiajs/vue3'
import ApplicationLogo from './ApplicationLogo.vue'
import axios from 'axios'
import LogoutSuccessModal from '@/Components/LogoutSuccessModal.vue'

// Navigation options
const navigation = [
    { name: 'Producten', href: '/categories' },
    { name: 'Bestellen', href: '/checkout' },
]

const activeButton = ref(null)
const menuOpen = ref(false)

// Search functionality
const searchQuery = ref('')
const mobileSearchQuery = ref('')
const suggestions = ref([])
const mobileSuggestions = ref([])
const showSuggestions = ref(false)
const showMobileSuggestions = ref(false)
const searchTimeout = ref(null)

// Shopping cart variables
const isCartOpen = ref(false)
const isCartUpdated = ref(false)

// Connect cart store
const cartStore = useCartStore()
const cartItemCount = computed(() => cartStore.totalItems)

// Animate cart icon when items change
watch(cartItemCount, () => {
    isCartUpdated.value = true
    setTimeout(() => {
        isCartUpdated.value = false
    }, 300)
})

// Computed properties
const showSearch = computed(() => activeButton.value === 'search')
const profileOpen = computed(() => activeButton.value === 'profile')

// Search functions
const performSearch = () => {
    if (searchQuery.value.trim()) {
        router.get('/search', { q: searchQuery.value.trim() })
        showSuggestions.value = false
    }
}

const performMobileSearch = () => {
    if (mobileSearchQuery.value.trim()) {
        router.get('/search', { q: mobileSearchQuery.value.trim() })
        showMobileSuggestions.value = false
        activeButton.value = null // Close mobile search
    }
}

const handleSearchInput = () => {
    clearTimeout(searchTimeout.value)
    
    if (searchQuery.value.length >= 2) {
        searchTimeout.value = setTimeout(() => {
            fetchSuggestions(searchQuery.value)
        }, 300)
    } else {
        suggestions.value = []
        showSuggestions.value = false
    }
}

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

const selectSuggestion = (suggestion) => {
    router.get(`/product/${suggestion.id}`)
    showSuggestions.value = false
    searchQuery.value = ''
}

const selectMobileSuggestion = (suggestion) => {
    router.get(`/product/${suggestion.id}`)
    showMobileSuggestions.value = false
    mobileSearchQuery.value = ''
    activeButton.value = null
}

const hideSuggestions = () => {
    setTimeout(() => {
        showSuggestions.value = false
    }, 200)
}

const hideMobileSuggestions = () => {
    setTimeout(() => {
        showMobileSuggestions.value = false
    }, 200)
}

const handleImageError = (event) => {
    event.target.src = '/images/placeholder-product.jpg'
}

// Toggle button function
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

// Cart management
const toggleCart = () => {
    console.log('toggleCart called, current state:', isCartOpen.value)
    isCartOpen.value = !isCartOpen.value
}

const closeCart = () => {
    console.log('closeCart called')
    isCartOpen.value = false
}

// User authentication
const user = computed(() => usePage().props.auth.user);
const isAuthenticated = computed(() => !!user.value);
const canLogin = computed(() => usePage().props.auth.canLogin);
const canRegister = computed(() => usePage().props.auth.canRegister);

const showLogoutModal = ref(false)

// Watch for logout success
watch(() => usePage().props.flash, (flash) => {
    if (flash && flash.logout && flash.logout.success) {
        showLogoutModal.value = true
    }
}, { deep: true })

const closeLogoutModal = () => {
    showLogoutModal.value = false
}
</script>

<style scoped>  
/* Animation for cart update */
.scale-110 {
    animation: pulse 0.3s ease-in-out;
}

/* Pulse animation keyframes */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}
</style>