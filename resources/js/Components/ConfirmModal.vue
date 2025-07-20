<!-- 
Bestandsnaam: ConfirmModal.vue
Auteur: Fabio Vreede  
Versie: v1.0.2
Datum: 2025-07-20
Tijd: 21:55:27
Doel: Herbruikbare bevestigingsmodal component voor gevaarlijke acties zoals verwijderen.
      Ondersteunt customizable titel, bericht, knop teksten en emit events voor cancel/confirm.
-->

<script setup>
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

/**
 * Props voor de ConfirmationModal component
 */
const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Bevestiging'
  },
  message: {
    type: String,
    default: 'Weet je zeker dat je deze actie wilt uitvoeren?'
  },
  cancelText: {
    type: String,
    default: 'Annuleren'
  },
  confirmText: {
    type: String,
    default: 'Verwijderen'
  },
  confirmButtonClass: {
    type: String,
    default: 'bg-red-600 hover:bg-red-700'
  },
  loading: {
    type: Boolean,
    default: false
  },
  icon: {
    type: String,
    default: 'warning', // 'warning', 'danger', 'info'
    validator: (value) => ['warning', 'danger', 'info'].includes(value)
  }
})

/**
 * Emit definitie voor parent component communicatie
 */
const emit = defineEmits(['cancel', 'confirm'])

/**
 * Event handlers
 */
const handleCancel = () => {
  if (!props.loading) {
    emit('cancel')
  }
}

const handleConfirm = () => {
  if (!props.loading) {
    emit('confirm')
  }
}

/**
 * Icon klasses gebaseerd op type
 */
const iconClasses = {
  warning: 'bg-yellow-100 text-yellow-600',
  danger: 'bg-red-100 text-red-600', 
  info: 'bg-blue-100 text-blue-600'
}
</script>

<template>
  <!-- Modal Overlay -->
  <div
    v-if="visible"
    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75 transition-opacity"
    @click="handleCancel"
  >
    <!-- Modal Container -->
    <div
      class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all duration-300 scale-100"
      @click.stop
    >
      <!-- Modal Header -->
      <div class="flex items-center justify-start p-6 border-b border-gray-200">
        <!-- Icon -->
        <div :class="[
          'w-10 h-10 rounded-full flex items-center justify-center mr-4',
          iconClasses[icon]
        ]">
          <ExclamationTriangleIcon class="w-6 h-6" />
        </div>
        
        <!-- Title -->
        <h3 class="text-lg font-medium text-gray-900">
          {{ title }}
        </h3>
      </div>

      <!-- Modal Content -->
      <div class="p-6">
        <p class="text-gray-600 leading-relaxed">
          {{ message }}
        </p>
        
        <!-- Slot for custom content -->
        <div v-if="$slots.default" class="mt-4">
          <slot></slot>
        </div>
      </div>

      <!-- Modal Actions -->
      <div class="flex justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50">
        <!-- Cancel Button -->
        <button
          @click="handleCancel"
          :disabled="loading"
          class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          {{ cancelText }}
        </button>

        <!-- Confirm Button -->
        <button
          @click="handleConfirm"
          :disabled="loading"
          :class="[
            'inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors',
            confirmButtonClass
          ]"
        >
          <!-- Loading Spinner -->
          <svg
            v-if="loading"
            class="animate-spin -ml-1 mr-2 h-4 w-4"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            ></circle>
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
          </svg>
          
          {{ loading ? 'Bezig...' : confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Smooth modal entrance animation */
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

/* Loading spinner animation */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>