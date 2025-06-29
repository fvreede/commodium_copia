<script setup>
import NavBar from '@/Components/NavBar.vue'
import Footer from '@/Components/Footer.vue'
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { ExclamationTriangleIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'
import { useCartStore } from '@/Stores/cart'
import axios from 'axios'

// Props
const props = defineProps({
  user: Object,
  currentStep: {
    type: Number,
    required: true,
    validator: value => [1, 2, 3].includes(value)
  },
  title: {
    type: String,
    default: 'Bestelling plaatsen'
  }
})

// Store & State
const cartStore = useCartStore()
const showSessionModal = ref(false)
const sessionWarningShown = ref(false)
let sessionCheckInterval = null

// Computed
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

const progressPercentage = computed(() => {
  return (props.currentStep / steps.value.length) * 100
})

const currentStepData = computed(() => {
  return steps.value.find(step => step.current)
})

// Methods
const checkSession = async () => {
  try {
    const { data } = await axios.get('/api/session-check')
    
    if (!data.authenticated) {
      showSessionExpiredModal()
      return
    }
    
    if (data.time_remaining <= 300 && !sessionWarningShown.value) {
      showSessionWarning()
    }
  } catch (error) {
    console.error('Fout bij controleren sessie:', error)
  }
}

const showSessionExpiredModal = () => {
  showSessionModal.value = true
  if (sessionCheckInterval) {
    clearInterval(sessionCheckInterval)
    sessionCheckInterval = null
  }
}

const showSessionWarning = () => {
  sessionWarningShown.value = true
  // You could show a toast notification here
  console.log('Sessie verloopt over 5 minuten')
}

const handleSessionExpiredAction = (action) => {
  if (action === 'login') {
    window.location.href = `/login?return_to=checkout/step${props.currentStep}`
  } else if (action === 'continue') {
    showSessionModal.value = false
    router.get('/categories')
  }
}

// Lifecycle
onMounted(async () => {
  await cartStore.loadCart()
  
  if (!cartStore.hasItems) {
    router.get('/cart')
    return
  }
  
  // Start session monitoring
  sessionCheckInterval = setInterval(checkSession, 120000) // Check every 2 minutes
  checkSession()
})

onUnmounted(() => {
  if (sessionCheckInterval) {
    clearInterval(sessionCheckInterval)
  }
})

// Watchers
watch(() => cartStore.hasItems, (hasItems) => {
  if (!hasItems && !cartStore.isLoading) {
    router.get('/cart')
  }
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <NavBar />
    
    <main class="relative pt-16">
      <div class="max-w-6xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <header class="text-center mb-12">
          <h1 class="text-4xl font-bold text-gray-900 mb-4">
            {{ title }}
          </h1>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Voltooi uw bestelling veilig en eenvoudig in {{ steps.length }} stappen
          </p>
        </header>

        <!-- Progress Section -->
        <section class="mb-12">
          <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <!-- Progress Header -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">
                  Voortgang bestelling
                </h2>
                <div class="flex items-center space-x-2">
                  <span class="text-sm font-medium text-gray-600">
                    Stap {{ currentStep }} van {{ steps.length }}
                  </span>
                  <div class="w-16 bg-gray-200 rounded-full h-2">
                    <div 
                      class="bg-gradient-to-r from-blue-500 to-indigo-500 h-2 rounded-full transition-all duration-500 ease-out"
                      :style="{ width: `${progressPercentage}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Desktop Steps -->
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
                    <!-- Step Icon/Number -->
                    <div 
                      :class="[
                        'flex items-center justify-center w-12 h-12 rounded-full border-2 transition-all duration-300 shadow-sm',
                        step.completed ? 'bg-green-500 border-green-500 text-white shadow-green-200' : 
                        step.current ? 'bg-blue-500 border-blue-500 text-white shadow-blue-200' : 
                        'bg-white border-gray-300 text-gray-500'
                      ]"
                    >
                      <CheckCircleIcon 
                        v-if="step.completed" 
                        class="w-6 h-6" 
                      />
                      <span 
                        v-else 
                        class="text-lg font-bold"
                      >
                        {{ step.number }}
                      </span>
                    </div>

                    <!-- Step Content -->
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

                  <!-- Arrow -->
                  <div 
                    v-if="index < steps.length - 1" 
                    class="absolute -right-3 top-1/2 transform -translate-y-1/2 z-10"
                  >
                    <div class="w-6 h-6 bg-white border border-gray-200 rotate-45 shadow-sm"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Mobile Steps -->
            <div class="md:hidden px-6 py-4">
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                  <div 
                    :class="[
                      'flex items-center justify-center w-10 h-10 rounded-full border-2',
                      currentStepData?.completed ? 'bg-green-500 border-green-500 text-white' : 
                      'bg-blue-500 border-blue-500 text-white'
                    ]"
                  >
                    <span class="text-sm font-bold">{{ currentStep }}</span>
                  </div>
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
              
              <div class="bg-gray-200 rounded-full h-2 overflow-hidden">
                <div 
                  class="bg-gradient-to-r from-blue-500 to-indigo-500 h-full rounded-full transition-all duration-500 ease-out"
                  :style="{ width: `${progressPercentage}%` }"
                ></div>
              </div>
            </div>
          </div>
        </section>

        <!-- Content Section -->
        <section class="relative">
          <!-- Loading State -->
          <div 
            v-if="cartStore.isLoading" 
            class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl shadow-lg border border-gray-200"
          >
            <div class="relative">
              <div class="animate-spin rounded-full h-16 w-16 border-4 border-blue-200 border-t-blue-600"></div>
              <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-8 h-8 bg-blue-100 rounded-full"></div>
              </div>
            </div>
            <p class="mt-6 text-lg font-medium text-gray-700">
              Bestelling laden...
            </p>
            <p class="mt-2 text-sm text-gray-500">
              Even geduld, we bereiden uw bestelling voor
            </p>
          </div>

          <!-- Main Content -->
          <div v-else class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <slot />
          </div>
        </section>
      </div>
    </main>

    <Footer />

    <!-- Session Expired Modal -->
    <Teleport to="body">
      <div 
        v-if="showSessionModal" 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        aria-labelledby="session-modal-title"
      >
        <div 
          class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-100"
          @click.stop
        >
          <div class="p-6">
            <div class="flex items-start space-x-4">
              <div class="flex-shrink-0">
                <div class="rounded-full bg-amber-100 p-3">
                  <ExclamationTriangleIcon class="h-8 w-8 text-amber-600" />
                </div>
              </div>
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
            
            <div class="flex flex-col sm:flex-row gap-3 mt-8">
              <button 
                @click="handleSessionExpiredAction('continue')" 
                class="flex-1 px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200"
              >
                Verder als gast
              </button>
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
/* Custom animations */
@keyframes spin {
  to { 
    transform: rotate(360deg); 
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Enhanced focus styles */
button:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Smooth transitions for interactive elements */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Modal animation */
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