/**
 * Bestandsnaam: CheckoutLayout.vue
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-06-29
 * Tijd: 15:34:53
 * Doel: Geavanceerde checkout layout component voor e-commerce bestelling proces met step-by-step interface, session management, progress tracking en security features. Bevat responsive design, loading states, cart validatie, session monitoring en modal voor session expiry. Geoptimaliseerd voor conversie en gebruikersveiligheid tijdens checkout flow.
 */

<script setup>
import NavBar from '@/Components/NavBar.vue'
import Footer from '@/Components/Footer.vue'
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { ExclamationTriangleIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'
import { useCartStore } from '@/Stores/cart'
import axios from 'axios'

/**
 * COMPONENT EIGENSCHAPPEN
 * Configuratie voor checkout stappen, gebruiker en pagina titel
 */
const props = defineProps({
    user: Object,                    // Gebruiker informatie voor checkout personalisatie
    currentStep: {
        type: Number,
        required: true,
        validator: value => [1, 2, 3].includes(value)
        // Huidige checkout stap (1: bezorgmoment, 2: controleren, 3: bevestigen)
    },
    title: {
        type: String,
        default: 'Bestelling plaatsen'
        // Pagina titel voor checkout interface
    }
})

/**
 * STORE EN STATE MANAGEMENT
 * Reactive state voor cart, sessie monitoring en UI feedback
 */
const cartStore = useCartStore()                // Pinia store voor cart management
const showSessionModal = ref(false)            // Session expired modal zichtbaarheid
const sessionWarningShown = ref(false)         // Preventie dubbele waarschuwingen
let sessionCheckInterval = null                // Interval referentie voor session monitoring

/**
 * CHECKOUT STAPPEN DEFINITIE
 * Computed property die checkout flow stappen definieert met status
 */
const steps = computed(() => [
    { 
        number: 1, 
        title: 'Bezorgmoment', 
        description: 'Kies uw gewenste bezorgmoment', 
        route: '/checkout/delivery', 
        completed: props.currentStep > 1, 
        current: props.currentStep === 1,
        icon: '📅'
    },
    { 
        number: 2, 
        title: 'Controleren', 
        description: 'Controleer uw bestelling', 
        route: '/checkout/review', 
        completed: props.currentStep > 2, 
        current: props.currentStep === 2,
        icon: '👁️'
    },
    { 
        number: 3, 
        title: 'Bevestigen', 
        description: 'Bevestig en plaats uw bestelling', 
        route: '/checkout/confirm', 
        completed: false, 
        current: props.currentStep === 3,
        icon: '✅'
    }
])

/**
 * PROGRESS BEREKENING
 * Berekent voortgang percentage voor visuele feedback
 */
const progressPercentage = computed(() => {
    return (props.currentStep / steps.value.length) * 100
})

/**
 * HUIDIGE STAP DATA
 * Vindt data voor de momenteel actieve checkout stap
 */
const currentStepData = computed(() => {
    return steps.value.find(step => step.current)
})

/**
 * SESSION MONITORING SYSTEEM
 * Controleert gebruiker authenticatie en session tijd
 */
const checkSession = async () => {
    try {
        const { data } = await axios.get('/api/session-check')
        
        // Controleer of gebruiker nog geauthenticeerd is
        if (!data.authenticated) {
            showSessionExpiredModal()
            return
        }
        
        // Waarschuw bij minder dan 5 minuten resterende tijd
        if (data.time_remaining <= 300 && !sessionWarningShown.value) {
            showSessionWarning()
        }
    } catch (error) {
        console.error('Fout bij controleren sessie:', error)
    }
}

/**
 * SESSION EXPIRED MODAL
 * Toont modal wanneer gebruiker sessie is verlopen
 */
const showSessionExpiredModal = () => {
    showSessionModal.value = true
    if (sessionCheckInterval) {
        clearInterval(sessionCheckInterval)
        sessionCheckInterval = null
    }
}

/**
 * SESSION WAARSCHUWING
 * Toont waarschuwing voordat sessie daadwerkelijk verloopt
 */
const showSessionWarning = () => {
    sessionWarningShown.value = true
    // Hier zou je een toast notificatie kunnen tonen
    console.log('Sessie verloopt over 5 minuten')
}

/**
 * SESSION EXPIRED ACTIE HANDLER
 * Behandelt gebruiker keuze na session expiry
 * 
 * @param {string} action - Actie die gebruiker heeft gekozen ('login' of 'continue')
 */
const handleSessionExpiredAction = (action) => {
    if (action === 'login') {
        // Redirect naar login met return URL naar huidige checkout stap
        window.location.href = `/login?return_to=checkout/step${props.currentStep}`
    } else if (action === 'continue') {
        // Sluit modal en ga verder als gast
        showSessionModal.value = false
        router.get('/categories')
    }
}

/**
 * COMPONENT LIFECYCLE
 * Setup van cart loading, validatie en session monitoring
 */
onMounted(async () => {
    // Laad cart data en valideer voor checkout
    await cartStore.loadCart()
    
    // Redirect naar cart als geen items aanwezig
    if (!cartStore.hasItems) {
        router.get('/cart')
        return
    }
    
    // Start session monitoring elke 2 minuten
    sessionCheckInterval = setInterval(checkSession, 120000)
    checkSession()
})

/**
 * CLEANUP BIJ COMPONENT UNMOUNT
 * Ruimt session monitoring interval op
 */
onUnmounted(() => {
    if (sessionCheckInterval) {
        clearInterval(sessionCheckInterval)
    }
})

/**
 * CART ITEMS WATCHER
 * Monitort cart items en redirect bij lege cart
 */
watch(() => cartStore.hasItems, (hasItems) => {
    if (!hasItems && !cartStore.isLoading) {
        router.get('/cart')
    }
})
</script>

<template>
    <!-- 
        CHECKOUT LAYOUT CONTAINER
        Hoofdcontainer met gradient achtergrond voor premium checkout ervaring
    -->
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Hoofdnavigatie -->
        <NavBar />
        
        <!-- Checkout Main Content -->
        <main class="relative pt-16">
            <div class="max-w-6xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
                
                <!-- Header Sectie -->
                <!-- Checkout titel en beschrijving voor gebruiker context -->
                <header class="text-center mb-12">
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">
                        {{ title }}
                    </h1>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Voltooi uw bestelling veilig en eenvoudig in {{ steps.length }} stappen
                    </p>
                </header>

                <!-- Progress Sectie -->
                <!-- Visuele weergave van checkout voortgang met step indicators -->
                <section class="mb-12">
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                        
                        <!-- Progress Header -->
                        <!-- Bovenste balk met voortgang percentage en huidige stap -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-900">
                                    Voortgang bestelling
                                </h2>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-600">
                                        Stap {{ currentStep }} van {{ steps.length }}
                                    </span>
                                    <!-- Progress Bar -->
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div 
                                            class="bg-gradient-to-r from-blue-500 to-indigo-500 h-2 rounded-full transition-all duration-500 ease-out"
                                            :style="{ width: `${progressPercentage}%` }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Desktop Step Indicators -->
                        <!-- Horizontale step layout voor desktop met pijlen -->
                        <div class="hidden md:block">
                            <div class="grid grid-cols-3">
                                <div 
                                    v-for="(step, index) in steps" 
                                    :key="step.number"
                                    :class="[
                                        'relative px-8 py-6 transition-all duration-300',
                                        index < steps.length - 1 ? 'border-r border-gray-200' : '',
                                        step.current ? 'bg-gradient-to-br from-blue-50 to-indigo-50' : 
                                        step.completed ? 'bg-gradient-to-br from-green-50 to-emerald-50' : 
                                        'bg-white hover:bg-gray-50'
                                    ]"
                                >
                                    <div class="flex items-center space-x-4">
                                        
                                        <!-- Step Icon/Nummer -->
                                        <!-- Kleurcoded icoon gebaseerd op step status -->
                                        <div 
                                            :class="[
                                                'flex items-center justify-center w-12 h-12 rounded-full border-2 transition-all duration-300 shadow-sm',
                                                step.completed ? 'bg-green-500 border-green-500 text-white shadow-green-200' : 
                                                step.current ? 'bg-blue-500 border-blue-500 text-white shadow-blue-200' : 
                                                'bg-white border-gray-300 text-gray-500'
                                            ]"
                                        >
                                            <!-- Checkmark voor voltooide stappen -->
                                            <CheckCircleIcon 
                                                v-if="step.completed" 
                                                class="w-6 h-6" 
                                            />
                                            <!-- Nummer voor huidige/toekomstige stappen -->
                                            <span 
                                                v-else 
                                                class="text-lg font-bold"
                                            >
                                                {{ step.number }}
                                            </span>
                                        </div>

                                        <!-- Step Content -->
                                        <!-- Titel en beschrijving van checkout stap -->
                                        <div class="flex-1 min-w-0">
                                            <h3 
                                                :class="[
                                                    'text-base font-semibold truncate',
                                                    step.current ? 'text-blue-900' : 
                                                    step.completed ? 'text-green-900' : 
                                                    'text-gray-700'
                                                ]"
                                            >
                                                {{ step.title }}
                                            </h3>
                                            <p 
                                                :class="[
                                                    'text-sm mt-1',
                                                    step.current ? 'text-blue-700' : 
                                                    step.completed ? 'text-green-700' : 
                                                    'text-gray-500'
                                                ]"
                                            >
                                                {{ step.description }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Pijl tussen stappen -->
                                    <!-- Visuele verbinding tussen checkout stappen -->
                                    <div 
                                        v-if="index < steps.length - 1" 
                                        class="absolute -right-3 top-1/2 transform -translate-y-1/2 z-10"
                                    >
                                        <div class="w-6 h-6 bg-white border border-gray-200 rotate-45 shadow-sm"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Step Indicators -->
                        <!-- Compacte mobile layout met progress bar -->
                        <div class="md:hidden px-6 py-4">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <!-- Mobile Step Icon -->
                                    <div 
                                        :class="[
                                            'flex items-center justify-center w-10 h-10 rounded-full border-2',
                                            currentStepData?.completed ? 'bg-green-500 border-green-500 text-white' : 
                                            'bg-blue-500 border-blue-500 text-white'
                                        ]"
                                    >
                                        <span class="text-sm font-bold">{{ currentStep }}</span>
                                    </div>
                                    
                                    <!-- Mobile Step Info -->
                                    <div>
                                        <h3 class="font-semibold text-gray-900">
                                            {{ currentStepData?.title }}
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            {{ currentStepData?.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Mobile Progress Bar -->
                            <div class="bg-gray-200 rounded-full h-2 overflow-hidden">
                                <div 
                                    class="bg-gradient-to-r from-blue-500 to-indigo-500 h-full rounded-full transition-all duration-500 ease-out"
                                    :style="{ width: `${progressPercentage}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Content Sectie -->
                <!-- Hoofdinhoud van checkout stap met loading state -->
                <section class="relative">
                    
                    <!-- Loading State -->
                    <!-- Elegant loading interface tijdens cart/data ophalen -->
                    <div 
                        v-if="cartStore.isLoading" 
                        class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl shadow-lg border border-gray-200"
                    >
                        <!-- Animated Loading Spinner -->
                        <div class="relative">
                            <div class="animate-spin rounded-full h-16 w-16 border-4 border-blue-200 border-t-blue-600"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-full"></div>
                            </div>
                        </div>
                        
                        <!-- Loading Berichten -->
                        <p class="mt-6 text-lg font-medium text-gray-700">
                            Bestelling laden...
                        </p>
                        <p class="mt-2 text-sm text-gray-500">
                            Even geduld, we bereiden uw bestelling voor
                        </p>
                    </div>

                    <!-- Hoofdinhoud Container -->
                    <!-- Slot voor stap-specifieke content -->
                    <div v-else class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                        <slot />
                    </div>
                </section>
            </div>
        </main>

        <!-- Footer -->
        <Footer />

        <!-- Session Expired Modal -->
        <!-- Security modal voor session expiry met gebruiker acties -->
        <Teleport to="body">
            <div 
                v-if="showSessionModal" 
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
                role="dialog"
                aria-modal="true"
                aria-labelledby="session-modal-title"
            >
                <!-- Modal Container -->
                <div 
                    class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-100"
                    @click.stop
                >
                    <div class="p-6">
                        <!-- Modal Header met Warning Icon -->
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="rounded-full bg-amber-100 p-3">
                                    <ExclamationTriangleIcon class="h-8 w-8 text-amber-600" />
                                </div>
                            </div>
                            
                            <!-- Modal Content -->
                            <div class="flex-1">
                                <h3 
                                    id="session-modal-title"
                                    class="text-xl font-semibold text-gray-900 mb-2"
                                >
                                    Sessie verlopen
                                </h3>
                                <p class="text-gray-600 leading-relaxed">
                                    Je bent automatisch uitgelogd voor je veiligheid. 
                                    Je winkelwagen is veilig bewaard. Wil je opnieuw inloggen 
                                    of doorgaan als gast?
                                </p>
                            </div>
                        </div>
                        
                        <!-- Modal Acties -->
                        <!-- Gebruiker keuze tussen login en gast checkout -->
                        <div class="flex flex-col sm:flex-row gap-3 mt-8">
                            <!-- Gast Checkout Knop -->
                            <button 
                                @click="handleSessionExpiredAction('continue')" 
                                class="flex-1 px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200"
                            >
                                Verder als gast
                            </button>
                            
                            <!-- Login Knop -->
                            <button 
                                @click="handleSessionExpiredAction('login')" 
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg"
                            >
                                Opnieuw inloggen
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
/**
 * COMPONENT STYLING
 * Custom CSS voor animaties, transitions en accessibility
 */

/* Loading spinner animatie */
@keyframes spin {
    to { 
        transform: rotate(360deg); 
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Verbeterde focus styling voor accessibility */
button:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Smooth transitions voor interactieve elementen */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Modal slide-in animatie */
@media (prefers-reduced-motion: no-preference) {
    .fixed.z-50 > div {
        animation: modalSlideIn 0.3s ease-out;
    }
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-1rem) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>