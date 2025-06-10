<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, reactive, onMounted, onUnmounted, computed } from 'vue';
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

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
        if (!confirm('Je hebt niet-opgeslagen wijzigingen. Weet je zeker dat je deze pagina wilt verlaten?')) {
            return;
        }
    }
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
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <section class="max-w-xl">
                            <header>
                                <h2 class="text-lg font-medium text-gray-900">Create Category</h2>
                                <p class="mt-1 text-sm text-gray-600">
                                    Add a new category to organize products.
                                </p>
                            </header>

                            <form @submit.prevent="submit" class="mt-6 space-y-6">
                                <div>
                                    <InputLabel for="name" value="Category Name" />
                                    <TextInput
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                        autofocus
                                    />
                                    <InputError :message="form.errors.name" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="description" value="Description" />
                                    <textarea 
                                        id="description"
                                        v-model="form.description"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm resize-none"
                                        rows="3"
                                        required
                                    />
                                    <InputError :message="form.errors.description" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="image" value="Category Image" />
                                    <input 
                                        type="file"
                                        id="image"
                                        @input="handleImageChange"
                                        accept="image/*"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <p class="mt-1 text-sm text-gray-500">
                                        Square image recommended (will be displayed in a 1:1 ratio)
                                    </p>
                                    <InputError :message="form.errors.image" class="mt-2" />

                                    <!-- Image Preview -->
                                    <div v-if="imagePreview" class="mt-4">
                                        <!-- Zoom Controls -->
                                        <div class="flex items-center gap-2 mb-3 p-2 bg-gray-50 rounded-lg">
                                            <button
                                                type="button"
                                                @click="zoomOut"
                                                :disabled="imageZoom <= minZoom"
                                                class="px-2 py-1 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                −
                                            </button>
                                            <span class="text-sm text-gray-600 min-w-[3rem] text-center">
                                                {{ Math.round(imageZoom * 100) }}%
                                            </span>
                                            <button
                                                type="button"
                                                @click="zoomIn"
                                                :disabled="imageZoom >= maxZoom"
                                                class="px-2 py-1 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                +
                                            </button>
                                            <button
                                                type="button"
                                                @click="resetZoom"
                                                class="px-3 py-1 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50 ml-2"
                                            >
                                                Reset
                                            </button>
                                        </div>

                                        <div class="group relative">
                                            <div 
                                                ref="containerRef"
                                                class="aspect-square w-full overflow-hidden rounded-lg bg-gray-200 relative"
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
                                                
                                                <!-- Alignment guides (optional visual feedback) -->
                                                <div class="absolute inset-0 pointer-events-none">
                                                    <!-- Center lines -->
                                                    <div class="absolute left-1/2 top-0 bottom-0 w-px bg-blue-300 opacity-0 group-hover:opacity-30 transition-opacity transform -translate-x-px"></div>
                                                    <div class="absolute top-1/2 left-0 right-0 h-px bg-blue-300 opacity-0 group-hover:opacity-30 transition-opacity transform -translate-y-px"></div>
                                                </div>
                                            </div>
                                            <h3 class="mt-2 text-sm text-gray-700 truncate">
                                                {{ form.name || 'Category Name' }}
                                            </h3>
                                            <p class="mt-1 text-xs sm:text-sm font-medium text-gray-900 line-clamp-2">
                                                {{ form.description || 'Category description will appear here' }}
                                            </p>
                                        </div>
                                        <div class="mt-2 space-y-1">
                                            <p class="text-sm text-gray-500 italic">
                                                Drag to reposition • Scroll to zoom • Magnetic border alignment
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                Position: {{ Math.round(imagePosition.x) }}, {{ Math.round(imagePosition.y) }} • 
                                                Zoom: {{ Math.round(imageZoom * 100) }}%
                                            </p>
                                        </div>
                                        <InputError :message="form.errors.image" class="mt-2" />
                                        <InputError :message="form.errors['imagePosition.x']" class="mt-2" />
                                        <InputError :message="form.errors['imagePosition.y']" class="mt-2" />
                                        <InputError :message="form.errors['imageZoom']" class="mt-2" />
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <PrimaryButton :disabled="form.processing">
                                        Create Category
                                    </PrimaryButton>
                                    <SecondaryButton
                                        type="button"
                                        @click="handleBack"
                                    >
                                        Cancel
                                    </SecondaryButton>
                                </div>
                            </form>
                        </section>
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
</style>