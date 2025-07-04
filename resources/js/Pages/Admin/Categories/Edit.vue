<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { PencilIcon, PhotoIcon, ArrowLeftIcon, MagnifyingGlassMinusIcon, MagnifyingGlassPlusIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    category: Object
});

const getImageUrl = (imagePath) => {
    if (!imagePath) return null;
    if (imagePath.startsWith('data:')) return imagePath;
    return `/storage/${imagePath}`;
};

const imagePreview = ref(getImageUrl(props.category.image_path));
const imagePosition = ref({ x: 0, y: 0 });
const zoom = ref(1);
const isDragging = ref(false);
const startPosition = ref({ x: 0, y: 0, initialX: 0, initialY: 0 });
const imageRef = ref(null);

// Zoom constraints
const minZoom = 0.5;
const maxZoom = 3;
const zoomStep = 0.1;

const handleWheel = (e) => {
    e.preventDefault();
    const delta = e.deltaY > 0 ? -zoomStep : zoomStep;
    zoom.value = Math.max(minZoom, Math.min(maxZoom, zoom.value + delta));
};

const handleTouchStart = (e) => {
    if (e.touches.length === 2) {
        const touch1 = e.touches[0];
        const touch2 = e.touches[1];
        const distance = Math.hypot(
            touch2.clientX - touch1.clientX,
            touch2.clientY - touch1.clientY
        );
        startPosition.value = {
            ...startPosition.value,
            pinchDistance: distance,
            initialZoom: zoom.value
        };
    } else {
        startDrag(e);
    }
};

const handleTouchMove = (e) => {
    if (e.touches.length === 2) {
        e.preventDefault();
        const touch1 = e.touches[0];
        const touch2 = e.touches[1];
        const distance = Math.hypot(
            touch2.clientX - touch1.clientX,
            touch2.clientY - touch1.clientY
        );
        
        const scale = distance / startPosition.value.pinchDistance;
        zoom.value = Math.max(minZoom, Math.min(maxZoom, startPosition.value.initialZoom * scale));
    } else {
        doDrag(e);
    }
};

const startDrag = (e) => {
    isDragging.value = true;
    startPosition.value = {
        x: e.type === 'mousedown' ? e.clientX : e.touches[0].clientX,
        y: e.type === 'mousedown' ? e.clientY : e.touches[0].clientY,
        initialX: imagePosition.value.x,
        initialY: imagePosition.value.y,
    };
    e.preventDefault();
};

const doDrag = (e) => {
    if (!isDragging.value) return;

    e.preventDefault();
    
    const currentX = e.type === 'mousemove' ? e.clientX : e.touches[0].clientX;
    const currentY = e.type === 'mousemove' ? e.clientY : e.touches[0].clientY;
    
    const deltaX = (currentX - startPosition.value.x);
    const deltaY = (currentY - startPosition.value.y);

    imagePosition.value = {
        x: startPosition.value.initialX + deltaX,
        y: startPosition.value.initialY + deltaY
    };
};

const stopDrag = () => {
    isDragging.value = false;
};

// Zoom functions
const zoomIn = () => {
    if (zoom.value < maxZoom) {
        zoom.value = Math.min(zoom.value + zoomStep, maxZoom);
    }
};

const zoomOut = () => {
    if (zoom.value > minZoom) {
        zoom.value = Math.max(zoom.value - zoomStep, minZoom);
    }
};

const resetImagePosition = () => {
    imagePosition.value = { x: 0, y: 0 };
    zoom.value = 1;
};

// Computed style for image transform
const imageTransform = computed(() => {
    return `translate(${imagePosition.value.x}px, ${imagePosition.value.y}px) scale(${zoom.value})`;
});

const form = useForm({
    name: props.category.name,
    description: props.category.description,
    image: null,
});

const hasSavedChanges = () => {
    return form.name !== props.category.name || 
           form.description !== props.category.description || 
           form.image !== null;
};

const handleBeforeUnload = (e) => {
    if (hasSavedChanges()) {
        e.preventDefault();
        e.returnValue = '';
    }
};

onMounted(() => {
    document.addEventListener('mousemove', doDrag, { passive: false });
    document.addEventListener('mouseup', stopDrag);
    document.addEventListener('touchmove', doDrag, { passive: false });
    document.addEventListener('touchend', stopDrag);
    window.addEventListener('beforeunload', handleBeforeUnload);
});

onUnmounted(() => {
    document.removeEventListener('mousemove', doDrag);
    document.removeEventListener('mouseup', stopDrag);
    document.removeEventListener('touchmove', doDrag);
    document.removeEventListener('touchend', stopDrag);
    window.removeEventListener('beforeunload', handleBeforeUnload);
});

const handleImageChange = (e) => {
    const file = e.target.files[0];
    form.image = file;

    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
            // Reset position and zoom when new image is loaded
            imagePosition.value = { x: 0, y: 0 };
            zoom.value = 1;
        };
        reader.readAsDataURL(file);
    }
};

const handleBack = () => {
    if (hasSavedChanges()) {
        if (!confirm('You have unsaved changes. Are you sure you want to leave this page?')) {
            return;
        }
    }
    // Remove the beforeunload listener to prevent double warning
    window.removeEventListener('beforeunload', handleBeforeUnload);
    window.location = route('admin.categories.index');
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put',
        'imagePosition.x': imagePosition.value.x,
        'imagePosition.y': imagePosition.value.y,
        'imageZoom': zoom.value,
    })).post(route('admin.categories.update', props.category.id));
};
</script>

<template>
    <Head title="Edit Category" />

    <AdminLayout>
        <div class="py-4 sm:py-6 lg:py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <!-- Header -->
                    <div class="px-4 py-4 sm:px-6 border-b border-gray-200">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg">
                                <PencilIcon class="w-5 h-5 text-blue-600" />
                            </div>
                            <div>
                                <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Edit Category</h2>
                                <p class="text-sm text-gray-600">Update category information and preview changes</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6">
                        <div class="flex flex-col lg:flex-row lg:space-x-8 space-y-8 lg:space-y-0">
                            <!-- Left side - Form -->
                            <section class="w-full lg:w-1/2">
                                <form @submit.prevent="submit" class="space-y-6">
                                    <!-- Category Name -->
                                    <div>
                                        <InputLabel for="name" value="Category Name" class="text-sm font-medium text-gray-700" />
                                        <TextInput
                                            id="name"
                                            v-model="form.name"
                                            type="text"
                                            class="mt-2 block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Enter category name"
                                            required
                                            :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.name }"
                                        />
                                        <InputError :message="form.errors.name" class="mt-2" />
                                    </div>

                                    <!-- Description -->
                                    <div>
                                        <InputLabel for="description" value="Description" class="text-sm font-medium text-gray-700" />
                                        <textarea 
                                            id="description"
                                            v-model="form.description"
                                            class="mt-2 block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                            rows="3"
                                            placeholder="Enter category description"
                                            required
                                            :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.description }"
                                        />
                                        <InputError :message="form.errors.description" class="mt-2" />
                                    </div>

                                    <!-- Image Upload -->
                                    <div>
                                        <InputLabel for="image" value="Category Image" class="text-sm font-medium text-gray-700" />
                                        <div class="mt-2">
                                            <input 
                                                type="file"
                                                id="image"
                                                @input="handleImageChange"
                                                accept="image/*"
                                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer"
                                            />
                                            <p class="mt-2 text-sm text-gray-500">
                                                Leave empty to keep current image. Square images work best.
                                            </p>
                                            <InputError :message="form.errors.image" class="mt-2" />
                                        </div>
                                    </div>

                                    <!-- Form Actions -->
                                    <div class="pt-6 border-t border-gray-200">
                                        <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                                            <PrimaryButton 
                                                type="submit"
                                                :disabled="form.processing"
                                                class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 text-sm font-medium transition-colors"
                                            >
                                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <PencilIcon v-else class="w-4 h-4 mr-2" />
                                                {{ form.processing ? 'Updating...' : 'Update Category' }}
                                            </PrimaryButton>
                                            <SecondaryButton
                                                type="button"
                                                @click="handleBack"
                                                class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                            >
                                                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                                                Cancel
                                            </SecondaryButton>
                                        </div>
                                    </div>
                                </form>
                            </section>

                            <!-- Divider (hidden on mobile) -->
                            <div class="hidden lg:block w-px bg-gray-200"></div>

                            <!-- Right side - Preview -->
                            <section class="w-full lg:w-1/2">
                                <div class="lg:sticky lg:top-6">
                                    <header class="mb-4">
                                        <h3 class="text-lg font-medium text-gray-900">Live Preview</h3>
                                        <p class="text-sm text-gray-600">See how your category will appear</p>
                                    </header>
                                    
                                    <div v-if="imagePreview" class="space-y-4">
                                        <!-- Mobile-Friendly Zoom Controls -->
                                        <div class="p-3 bg-gray-50 rounded-lg">
                                            <h4 class="text-sm font-medium text-gray-700 mb-3">Image Editor</h4>
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-2">
                                                    <button
                                                        type="button"
                                                        @click="zoomOut"
                                                        :disabled="zoom <= minZoom"
                                                        class="flex items-center justify-center w-10 h-10 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                                    >
                                                        <MagnifyingGlassMinusIcon class="w-4 h-4" />
                                                    </button>
                                                    <span class="text-sm text-gray-600 min-w-[3.5rem] text-center font-medium">
                                                        {{ Math.round(zoom * 100) }}%
                                                    </span>
                                                    <button
                                                        type="button"
                                                        @click="zoomIn"
                                                        :disabled="zoom >= maxZoom"
                                                        class="flex items-center justify-center w-10 h-10 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                                    >
                                                        <MagnifyingGlassPlusIcon class="w-4 h-4" />
                                                    </button>
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="resetImagePosition"
                                                    class="flex items-center gap-2 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                                >
                                                    <ArrowPathIcon class="w-4 h-4" />
                                                    <span class="hidden sm:inline">Reset</span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Image Preview Container -->
                                        <div class="group relative bg-gray-100 rounded-lg overflow-hidden">
                                            <div class="aspect-square w-full overflow-hidden relative bg-gray-200">
                                                <div 
                                                    class="absolute inset-0 cursor-move overflow-hidden"
                                                    @mousedown="startDrag"
                                                    @wheel.prevent="handleWheel"
                                                    @touchstart="handleTouchStart"
                                                    @touchmove.prevent="handleTouchMove"
                                                    @touchend="stopDrag"
                                                >
                                                    <img 
                                                        ref="imageRef"
                                                        :src="imagePreview" 
                                                        :style="{
                                                            transform: imageTransform,
                                                            cursor: isDragging ? 'grabbing' : 'grab',
                                                            transformOrigin: 'center center',
                                                        }"
                                                        class="min-h-full min-w-full object-cover select-none transition-transform"
                                                        :class="{ 'duration-200 ease-out': !isDragging }"
                                                        draggable="false"
                                                    />
                                                </div>
                                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-300 pointer-events-none"></div>
                                            </div>

                                            <!-- Category Preview Info -->
                                            <div class="p-4 bg-white border-t border-gray-200">
                                                <h3 class="text-sm font-medium text-gray-900 truncate">
                                                    {{ form.name || 'Category Name' }}
                                                </h3>
                                                <p class="mt-1 text-xs text-gray-600 line-clamp-2">
                                                    {{ form.description || 'Category description will appear here' }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Instructions -->
                                        <div class="p-3 bg-blue-50 rounded-lg">
                                            <div class="flex items-start space-x-2">
                                                <PhotoIcon class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" />
                                                <div class="text-sm text-blue-800">
                                                    <p class="font-medium mb-1">Image Editor Instructions:</p>
                                                    <ul class="text-xs space-y-1 text-blue-700">
                                                        <li>• Drag to reposition the image</li>
                                                        <li>• Use zoom controls or scroll to zoom</li>
                                                        <li>• Pinch to zoom on touch devices</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Debug Info (hidden on mobile) -->
                                        <div class="hidden sm:block text-xs text-gray-400">
                                            Position: {{ Math.round(imagePosition.x) }}, {{ Math.round(imagePosition.y) }} • 
                                            Zoom: {{ Math.round(zoom * 100) }}%
                                        </div>

                                        <!-- Image Error Messages -->
                                        <InputError :message="form.errors['imagePosition.x']" />
                                        <InputError :message="form.errors['imagePosition.y']" />
                                        <InputError :message="form.errors['imageZoom']" />
                                    </div>

                                    <!-- No Image State -->
                                    <div v-else class="aspect-square w-full bg-gray-100 rounded-lg flex items-center justify-center">
                                        <div class="text-center">
                                            <PhotoIcon class="mx-auto h-12 w-12 text-gray-400 mb-4" />
                                            <p class="text-gray-500">No image selected</p>
                                            <p class="text-sm text-gray-400 mt-1">Upload an image to see preview</p>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.cursor-move {
    cursor: move;
    cursor: grab;
}

.select-none {
    user-select: none;
    -webkit-user-select: none;
    -webkit-user-drag: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

img {
    user-select: none;
    -webkit-user-drag: none;
}

/* Square aspect ratio container */
.aspect-square {
    aspect-ratio: 1 / 1;
}

/* Line clamp utility */
.line-clamp-2 {
    display: -webkit-box;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>