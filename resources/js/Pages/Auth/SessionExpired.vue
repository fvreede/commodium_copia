<template>
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Logo -->
            <div class="flex justify-center">
                <ApplicationLogo class="h-12 w-auto" />
            </div>
            
            <!-- Main content -->
            <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                <div class="bg-white py-8 px-4 shadow-lg sm:rounded-lg sm:px-10">
                    <!-- Icon -->
                    <div class="flex justify-center mb-6">
                        <div class="rounded-full bg-amber-100 p-3">
                            <ExclamationTriangleIcon class="h-8 w-8 text-amber-600" />
                        </div>
                    </div>
                    
                    <!-- Title -->
                    <h2 class="text-center text-2xl font-bold text-gray-900 mb-2">
                        Je sessie is verlopen
                    </h2>
                    
                    <!-- Message -->
                    <p class="text-center text-gray-600 mb-6">
                        Je bent automatisch uitgelogd voor je veiligheid. Maak je geen zorgen - je winkelmandje is bewaard.
                    </p>
                    
                    <!-- Action buttons -->
                    <div class="space-y-3">
                        <!-- Primary button - Login -->
                        <form @submit.prevent="handleAction('login')" method="POST" :action="route('session.expiry.handle')">
                            <button 
                                type="submit"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
                            >
                                <UserIcon class="h-5 w-5 mr-2" />
                                Opnieuw inloggen
                            </button>
                        </form>
                        
                        <!-- Secondary button - Continue as guest -->
                        <form @submit.prevent="handleAction('continue_shopping')" method="POST" :action="route('session.expiry.handle')">
                            <button 
                                type="submit"
                                class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
                            >
                                <ShoppingCartIcon class="h-5 w-5 mr-2" />
                                Verder winkelen als gast
                            </button>
                        </form>
                    </div>
                    
                    <!-- Additional info -->
                    <div class="mt-6 p-4 bg-blue-50 rounded-md">
                        <div class="flex">
                            <InformationCircleIcon class="h-5 w-5 text-blue-400 mt-0.5" />
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">
                                    Waarom gebeurt dit?
                                </h3>
                                <p class="mt-1 text-sm text-blue-700">
                                    Voor je veiligheid loggen we je automatisch uit na een periode van inactiviteit.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick navigation -->
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4 text-center">
                    Of ga direct naar
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <form @submit.prevent="handleAction('view_categories')" method="POST" :action="route('session.expiry.handle')">
                        <button 
                            type="submit"
                            class="w-full flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:border-green-300 hover:bg-green-50 transition-colors duration-200"
                        >
                            <TagIcon class="h-6 w-6 text-gray-600 mb-2" />
                            <span class="text-sm font-medium text-gray-900">Alle producten</span>
                        </button>
                    </form>
                    <form @submit.prevent="handleAction('view_cart')" method="POST" :action="route('session.expiry.handle')">
                        <button 
                            type="submit"
                            class="w-full flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:border-green-300 hover:bg-green-50 transition-colors duration-200"
                        >
                            <ShoppingBagIcon class="h-6 w-6 text-gray-600 mb-2" />
                            <span class="text-sm font-medium text-gray-900">Winkelwagen</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Weekly deals section -->
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
            <div class="bg-gradient-to-r from-green-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                <h3 class="text-xl font-bold mb-2">🎉 Weekaanbiedingen</h3>
                <p class="text-green-100 mb-4">
                    Mis onze beste deals niet! Bekijk wat er deze week in de aanbieding is.
                </p>
                <form @submit.prevent="handleAction('view_deals')" method="POST" :action="route('session.expiry.handle')">
                    <button 
                        type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-green-600 bg-white hover:bg-green-50 transition-colors duration-200"
                    >
                        Aanbiedingen bekijken
                        <ArrowRightIcon class="ml-2 h-4 w-4" />
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import { 
    ExclamationTriangleIcon, 
    UserIcon, 
    ShoppingCartIcon, 
    InformationCircleIcon,
    TagIcon,
    ShoppingBagIcon,
    ArrowRightIcon
} from '@heroicons/vue/24/outline'

// Optional: Add some analytics or tracking
import { onMounted } from 'vue'

onMounted(() => {
    // Track session expiry event if you have analytics
    console.log('Session expired page loaded')
})

const handleAction = (action) => {
    router.post(route('session.expiry.handle'), {
        action: action
    })
}
</script>