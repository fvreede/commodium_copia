
<script setup>
const props = defineProps({
  visible: Boolean,
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
  }
});
</script>

<template>
  <div
    v-if="visible"
    class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center"
  >
    <div
      class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full transform transition-all duration-300"
    >
      <h2 class="text-lg font-semibold text-gray-900 mb-4">
        {{ title }}
      </h2>
      <p class="text-gray-700 mb-6">
        {{ message }}
      </p>
      <div class="flex justify-end space-x-3">
        <button
          @click="$emit('cancel')"
          class="px-4 py-2 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200"
        >
          {{ cancelText }}
        </button>
        <button
          @click="$emit('confirm')"
          class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700"
        >
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>