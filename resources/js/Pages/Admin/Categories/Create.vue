<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, reactive, onMounted, onUnmounted, computed } from 'vue';
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { FolderPlusIcon, PhotoIcon, ArrowLeftIcon, MagnifyingGlassMinusIcon, MagnifyingGlassPlusIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

const imagePreview = ref(null);
const imagePosition = reactive({ x: 0, y: 0});
const imageZoom = ref(1);
const isDragging = ref(false);
const startPosition = reactive({ x: 0, y: 0});
const imageRef = ref(null);
const containerRef = ref(null);

// Zoom constraints
const minZoom = 0.5;
const maxZoom = 3;
const zoomStep = 0.1;

// Auto-align settings
const alignThreshold = 10; // pixels
const alignAnimationDuration = 200; // ms

const startDrag = (e) => {
    isDragging.value = true;
    startPosition.x = e.type === 'mousedown' ? e.clientX : e.touches[0].clientX;
    startPosition.y = e.type === 'mousedown' ? e.clientY : e.touches[0].clientY;
    startPosition.initialX = imagePosition.x;
    startPosition.initialY = imagePosition.y;
    
    // Prevent default to avoid image selection/drag behavior
    e.preventDefault();
};

const doDrag = (e) => {
    if (!isDragging.value) return;

    e.preventDefault();
    
    const currentX = e.type === 'mousemove' ? e.clientX : e.touches[0].clientX;
    const currentY = e.type === 'mousemove' ? e.clientY : e.touches[0].clientY;
    
    const deltaX = currentX - startPosition.x;
    const deltaY = currentY - startPosition.y;

    imagePosition.x = startPosition.initialX + deltaX;
    imagePosition.y = startPosition.initialY + deltaY;
};

const stopDrag = () => {
    if (isDragging.value) {
        isDragging.value = false;
        autoAlign();
    }
};

// Auto-align function with magnetic border snapping
const autoAlign = () => {
    if (!containerRef.value || !imageRef.value) return;

    const container = containerRef.value;
    const containerWidth = container.clientWidth;
    const containerHeight = container.clientHeight;

    // Calculate the actual scaled image dimensions
    const imageNaturalWidth = imageRef.value.naturalWidth;
    const imageNaturalHeight = imageRef.value.naturalHeight;
    
    // Calculate how the image fits in the container (object-cover behavior)
    const containerAspect = containerWidth / containerHeight;
    const imageAspect = imageNaturalWidth / imageNaturalHeight;
    
    let scaledImageWidth, scaledImageHeight;
    
    if (imageAspect > containerAspect) {
        // Image is wider - height fills container
        scaledImageHeight = containerHeight;
        scaledImageWidth = scaledImageHeight * imageAspect;
    } else {
        // Image is taller - width fills container
        scaledImageWidth = containerWidth;
        scaledImageHeight = scaledImageWidth / imageAspect;
    }
    
    // Apply zoom to the scaled dimensions
    scaledImageWidth *= imageZoom.value;
    scaledImageHeight *= imageZoom.value;

    let targetX = imagePosition.x;
    let targetY = imagePosition.y;

    // Horizontal alignment and boundary constraints
    if (scaledImageWidth > containerWidth) {
        // Image is larger than container - constrain within bounds
        const maxMoveRight = (scaledImageWidth - containerWidth) / 2;
        const maxMoveLeft = -maxMoveRight;
        
        if (imagePosition.x > maxMoveRight) {
            targetX = maxMoveRight;
        } else if (imagePosition.x < maxMoveLeft) {
            targetX = maxMoveLeft;
        }
        
        // Snap to center if close
        if (Math.abs(imagePosition.x) < alignThreshold) {
            targetX = 0;
        }
        // Snap to edges if close
        if (Math.abs(imagePosition.x - maxMoveRight) < alignThreshold) {
            targetX = maxMoveRight;
        }
        if (Math.abs(imagePosition.x - maxMoveLeft) < alignThreshold) {
            targetX = maxMoveLeft;
        }
    } else {
        // Image is smaller than container - snap to container edges or center
        const halfContainer = containerWidth / 2;
        const halfImage = scaledImageWidth / 2;
        
        // Calculate edge positions (where image edge aligns with container edge)
        const leftEdgePosition = -halfContainer + halfImage;
        const rightEdgePosition = halfContainer - halfImage;
        
        // Snap to left edge
        if (Math.abs(imagePosition.x - leftEdgePosition) < alignThreshold) {
            targetX = leftEdgePosition;
        }
        // Snap to right edge  
        else if (Math.abs(imagePosition.x - rightEdgePosition) < alignThreshold) {
            targetX = rightEdgePosition;
        }
        // Snap to center
        else if (Math.abs(imagePosition.x) < alignThreshold) {
            targetX = 0;
        }
        
        // Constrain to not go beyond reasonable bounds
        const maxOffset = halfContainer + halfImage;
        if (imagePosition.x > maxOffset) targetX = maxOffset;
        if (imagePosition.x < -maxOffset) targetX = -maxOffset;
    }

    // Vertical alignment and boundary constraints  
    if (scaledImageHeight > containerHeight) {
        // Image is larger than container - constrain within bounds
        const maxMoveDown = (scaledImageHeight - containerHeight) / 2;
        const maxMoveUp = -maxMoveDown;
        
        if (imagePosition.y > maxMoveDown) {
            targetY = maxMoveDown;
        } else if (imagePosition.y < maxMoveUp) {
            targetY = maxMoveUp;
        }
        
        // Snap to center if close
        if (Math.abs(imagePosition.y) < alignThreshold) {
            targetY = 0;
        }
        // Snap to edges if close
        if (Math.abs(imagePosition.y - maxMoveDown) < alignThreshold) {
            targetY = maxMoveDown;
        }
        if (Math.abs(imagePosition.y - maxMoveUp) < alignThreshold) {
            targetY = maxMoveUp;
        }
    } else {
        // Image is smaller than container - snap to container edges or center
        const halfContainer = containerHeight / 2;
        const halfImage = scaledImageHeight / 2;
        
        // Calculate edge positions (where image edge aligns with container edge)
        const topEdgePosition = -halfContainer + halfImage;
        const bottomEdgePosition = halfContainer - halfImage;
        
        // Snap to top edge
        if (Math.abs(imagePosition.y - topEdgePosition) < alignThreshold) {
            targetY = topEdgePosition;
        }
        // Snap to bottom edge
        else if (Math.abs(imagePosition.y - bottomEdgePosition) < alignThreshold) {
            targetY = bottomEdgePosition;
        }
        // Snap to center
        else if (Math.abs(imagePosition.y) < alignThreshold) {
            targetY = 0;
        }
        
        // Constrain to not go beyond reasonable bounds
        const maxOffset = halfContainer + halfImage;
        if (imagePosition.y > maxOffset) targetY = maxOffset;
        if (imagePosition.y < -maxOffset) targetY = -maxOffset;
    }

    // Animate to target position if different from current
    if (Math.abs(targetX - imagePosition.x) > 0.5 || Math.abs(targetY - imagePosition.y) > 0.5) {
        animateToPosition(targetX, targetY);
    }
};

// Smooth animation to target position
const animateToPosition = (targetX, targetY) => {
    const startX = imagePosition.x;
    const startY = imagePosition.y;
    const startTime = performance.now();

    const animate = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / alignAnimationDuration, 1);
        
        // Ease-out function
        const easeOut = 1 - Math.pow(1 - progress, 3);
        
        imagePosition.x = startX + (targetX - startX) * easeOut;
        imagePosition.y = startY + (targetY - startY) * easeOut;

        if (progress < 1) {
            requestAnimationFrame(animate);
        } else {
            imagePosition.x = targetX;
            imagePosition.y = targetY;
        }
    };

    requestAnimationFrame(animate);
};

// Zoom functions
const zoomIn = () => {
    if (imageZoom.value < maxZoom) {
        imageZoom.value = Math.min(imageZoom.value + zoomStep, maxZoom);
        setTimeout(() => autoAlign(), 50);
    }
};

const zoomOut = () => {
    if (imageZoom.value > minZoom) {
        imageZoom.value = Math.max(imageZoom.value - zoomStep, minZoom);
        setTimeout(() => autoAlign(), 50);
    }
};

const resetZoom = () => {
    imageZoom.value = 1;
    imagePosition.x = 0;
    imagePosition.y = 0;
};

// Handle wheel zoom with auto-align after zoom
const handleWheel = (e) => {
    e.preventDefault();
    
    const delta = e.deltaY > 0 ? -zoomStep : zoomStep;
    const newZoom = Math.max(minZoom, Math.min(maxZoom, imageZoom.value + delta));
    
    if (newZoom !== imageZoom.value) {
        imageZoom.value = newZoom;
        // Auto-align after zoom to ensure image stays within bounds
        setTimeout(() => autoAlign(), 50);
    }
};

// Computed style for image transform
const imageTransform = computed(() => {
    return `translate(${imagePosition.x}px, ${imagePosition.y}px) scale(${imageZoom.value})`;
});

const form = useForm({
    name: '',
    description: '',
    image: null,
});

const hasSavedChanges = () => {
    return form.name !== '' || form.description !== '' || form.image !== null;
};

const handleBeforeUnload = (e) => {
    if (hasSavedChanges()) {
        e.preventDefault();
        e.returnValue = '';
    }
};

onMounted(() => {
    // Add event listeners for drag and drop functionality
    document.addEventListener('mousemove', doDrag, { passive: false });
    document.addEventListener('mouseup', stopDrag);
    document.addEventListener('touchmove', doDrag, { passive: false });
    document.addEventListener('touchend', stopDrag);

    // Prevent page from unloading when there are unsaved changes
    window.addEventListener('beforeunload', handleBeforeUnload);
});

onUnmounted(() => {
    // Remove event listeners when component is unmounted
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
            // Reset image position and zoom when new image is loaded
            imagePosition.x = 0;
            imagePosition.y = 0;
            imageZoom.value = 1;
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
        'imagePosition.x': imagePosition.x,
        'imagePosition.y': imagePosition.y,
        'imageZoom': imageZoom.value,
    })).post(route('admin.categories.store'));
};
</script>

<template>
    <Head title="Create Category" />

    <AdminLayout>
        <div class="py-4 sm:py-6 lg:py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <!-- Header -->
                    <div class="px-4 py-4 sm:px-6 border-b border-gray-200">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg">
                                <FolderPlusIcon class="w-5 h-5 text-blue-600" />
                            </div>
                            <div>
                                <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Create Category</h2>
                                <p class="text-sm text-gray-600">Add a new category to organize products</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <div class="p-4 sm:p-6">
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
                                    autofocus
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
                                        required
                                    />
                                    <p class="mt-2 text-sm text-gray-500">
                                        Square images work best (displayed in 1:1 ratio)
                                    </p>
                                    <InputError :message="form.errors.image" class="mt-2" />
                                </div>

                                <!-- Image Preview with Editor -->
                                <div v-if="imagePreview" class="mt-6">
                                    <!-- Mobile-Friendly Zoom Controls -->
                                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3">Image Editor</h4>
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-2">
                                                <button
                                                    type="button"
                                                    @click="zoomOut"
                                                    :disabled="imageZoom <= minZoom"
                                                    class="flex items-center justify-center w-10 h-10 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                                >
                                                    <MagnifyingGlassMinusIcon class="w-4 h-4" />
                                                </button>
                                                <span class="text-sm text-gray-600 min-w-[3.5rem] text-center font-medium">
                                                    {{ Math.round(imageZoom * 100) }}%
                                                </span>
                                                <button
                                                    type="button"
                                                    @click="zoomIn"
                                                    :disabled="imageZoom >= maxZoom"
                                                    class="flex items-center justify-center w-10 h-10 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                                >
                                                    <MagnifyingGlassPlusIcon class="w-4 h-4" />
                                                </button>
                                            </div>
                                            <button
                                                type="button"
                                                @click="resetZoom"
                                                class="flex items-center gap-2 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                            >
                                                <ArrowPathIcon class="w-4 h-4" />
                                                <span class="hidden sm:inline">Reset</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Image Preview Container -->
                                    <div class="group relative bg-gray-100 rounded-lg overflow-hidden">
                                        <div 
                                            ref="containerRef"
                                            class="aspect-square w-full overflow-hidden relative bg-gray-200"
                                            @wheel="handleWheel"
                                        >
                                            <div 
                                                class="absolute inset-0 cursor-move overflow-hidden"
                                                @mousedown="startDrag" 
                                                @touchstart="startDrag"
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
                                            
                                            <!-- Alignment guides -->
                                            <div class="absolute inset-0 pointer-events-none">
                                                <!-- Center lines -->
                                                <div class="absolute left-1/2 top-0 bottom-0 w-px bg-blue-300 opacity-0 group-hover:opacity-30 transition-opacity transform -translate-x-px"></div>
                                                <div class="absolute top-1/2 left-0 right-0 h-px bg-blue-300 opacity-0 group-hover:opacity-30 transition-opacity transform -translate-y-px"></div>
                                            </div>
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
                                    <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                                        <div class="flex items-start space-x-2">
                                            <PhotoIcon class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" />
                                            <div class="text-sm text-blue-800">
                                                <p class="font-medium mb-1">Image Editor Instructions:</p>
                                                <ul class="text-xs space-y-1 text-blue-700">
                                                    <li>• Drag to reposition the image</li>
                                                    <li>• Use zoom controls or scroll to zoom</li>
                                                    <li>• Image auto-aligns to borders and center</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Debug Info (hidden on mobile) -->
                                    <div class="hidden sm:block mt-2 text-xs text-gray-400">
                                        Position: {{ Math.round(imagePosition.x) }}, {{ Math.round(imagePosition.y) }} • 
                                        Zoom: {{ Math.round(imageZoom * 100) }}%
                                    </div>

                                    <!-- Image Error Messages -->
                                    <InputError :message="form.errors['imagePosition.x']" class="mt-2" />
                                    <InputError :message="form.errors['imagePosition.y']" class="mt-2" />
                                    <InputError :message="form.errors['imageZoom']" class="mt-2" />
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
                                        <FolderPlusIcon v-else class="w-4 h-4 mr-2" />
                                        {{ form.processing ? 'Creating...' : 'Create Category' }}
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