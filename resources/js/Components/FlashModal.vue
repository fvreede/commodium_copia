<script setup>
import { ref, onUnmounted, watch } from 'vue';
import PrimaryButton from './PrimaryButton.vue';
import { CheckIcon, ExclamationCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    show: Boolean,
    message: String,
    type: {
        type: String,
        default: 'success'
    },
    duration: {
        type: Number,
        default: 4000,
    },
});

const emit = defineEmits(['close']);
const isVisible = ref(false);
let timeoutId = null;
const isHovering = ref(false);

const closeModal = () => {
    isVisible.value = false;
    emit('close');
    if (timeoutId) {
        clearTimeout(timeoutId);
        timeoutId = null;
    }
}

const startAutoCloseTimer = () => {
if (props.duration > 0 && !isHovering.value) {
        // Clear any existing timeout
        if (timeoutId) {
            clearTimeout(timeoutId);
        }
        
        // Set a new timeout for auto-closing
        timeoutId = setTimeout(() => {
            closeModal();
        }, props.duration);
    }
};

const onMouseEnter = () => {
    isHovering.value = true;
    if (timeoutId) {
        clearTimeout(timeoutId);
        timeoutId = null;
    }
};

const onMouseLeave = () => {
    isHovering.value = false;
    startAutoCloseTimer();
};

watch(() => props.show, (newValue) => {
    if (newValue) {
        isVisible.value = true;
        startAutoCloseTimer();
    } else {
        isVisible.value = false;
    }
});

onUnmounted(() => {
    if (timeoutId) {
        clearTimeout(timeoutId);
    }
});
</script>

<template>
    <Transition
        enter-active-class="ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" class="fixed inset-0 flex items-center justify-center z-50">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-sm sm:p-6" @mouseenter="onMouseEnter" @mouseleave="onMouseLeave">
                <div>
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full"
                        :class="{
                            'bg-green-200': type === 'success',
                            'bg-red-200': type === 'error',
                            'bg-yellow-200': type === 'warning'
                        }"
                    >
                        <!-- Success Icon -->
                        <CheckIcon v-if="type ==='success'" class="h-6 w-6 text-green-600"/>
                        <!-- Error Icon -->
                        <ExclamationCircleIcon v-if="type === 'error'" class="h-6 w-6 text-red-600"/>
                        <!-- Warning Icon -->
                        <ExclamationTriangleIcon v-if="type === 'warning'" class="h-6 w-6 text-yellow-600"/>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <div class="text-base font-semibold leading-6 text-gray-900">
                            {{ message }}
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6">
                    <PrimaryButton class="w-full justify-center" @click="closeModal">
                        Close
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </Transition>
</template>