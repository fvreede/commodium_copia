<!-- LogoutSuccessModal.vue -->
<template>
  <div v-if="show" class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Background overlay -->
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div 
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
        @click="close"
        aria-hidden="true"
      ></div>

      <!-- Modal positioning -->
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <!-- Modal content -->
      <div class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full sm:p-6">
        <div>
          <!-- Success icon -->
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
            <CheckCircleIcon class="h-6 w-6 text-green-600"/>
          </div>
          
          <!-- Modal content -->
          <div class="mt-3 text-center sm:mt-5">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
              Succesvol uitgelogd
            </h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">
                Je bent succesvol uitgelogd. Bedankt voor je bezoek!
              </p>
            </div>
          </div>
        </div>
        
        <!-- Action button -->
        <div class="mt-5 sm:mt-6">
          <button 
            @click="close"
            type="button" 
            class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm transition-colors duration-200"
          >
            Doorgaan met winkelen
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'
import { CheckCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

const close = () => {
  emit('close')
}

// Auto-close after 3 seconds
if (props.show) {
  setTimeout(() => {
    close()
  }, 3000)
}
</script>