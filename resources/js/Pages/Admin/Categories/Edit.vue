<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

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

const handleWheel = (e) => {
    e.preventDefault();
    const delta = e.deltaY * -0.01;
    zoom.value = Math.max(0.5, Math.min(3, zoom.value + delta));
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
        zoom.value = Math.max(0.5, Math.min(3, startPosition.value.initialZoom * scale));
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
};

const doDrag = (e) => {
    if (!isDragging.value) return;

    e.preventDefault();
    
    const currentX = e.type === 'mousemove' ? e.clientX : e.touches[0].clientX;
    const currentY = e.type === 'mousemove' ? e.clientY : e.touches[0].clientY;
    
    const deltaX = (currentX - startPosition.value.x) / zoom.value;
    const deltaY = (currentY - startPosition.value.y) / zoom.value;

    imagePosition.value = {
        x: startPosition.value.initialX + deltaX,
        y: startPosition.value.initialY + deltaY
    };
};

const stopDrag = () => {
    isDragging.value = false;
};

const form = useForm({
    name: '',
    description: '',
    image: null,
});

const placeHolderName = props.category.name;
const placeHolderDescription = props.category.description;

const hasSavedChanges = () => {
    return form.name !== props.category.name || 
           form.description !== props.category.description || 
           form.image !== null;
};

onMounted(() => {
    document.addEventListener('mousemove', doDrag);
    document.addEventListener('mouseup', stopDrag);
    document.addEventListener('touchmove', doDrag);
    document.addEventListener('touchend', stopDrag);
    window.addEventListener('beforeunload', handleBeforeUnload);
    imageRef.value?.addEventListener('wheel', handleWheel, { passive: false });
});

const handleBeforeUnload = (e) => {
    if (hasSavedChanges()) {
        e.preventDefault();
        e.returnValue = '';
    }
};

onUnmounted(() => {
    document.removeEventListener('mousemove', doDrag);
    document.removeEventListener('mouseup', stopDrag);
    document.removeEventListener('touchmove', doDrag);
    document.removeEventListener('touchend', stopDrag);
    window.removeEventListener('beforeunload', handleBeforeUnload);
    imageRef.value?.removeEventListener('wheel', handleWheel);
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
        if (!confirm('Je hebt niet-opgeslagen wijzigingen. Weet je zeker dat je deze pagina wilt verlaten?')) {
            return;
        }
    }
    window.location = route('admin.categories.index');
};

const submit = () => {
    form.post(route('admin.categories.update', props.category.id), {
        _method: 'put',
        imagePosition: imagePosition.value,
        imageZoom: zoom.value
    });
};

const resetImagePosition = () => {
    imagePosition.value = { x: 0, y: 0 };
    zoom.value = 1;
};

// TODO: fix image preview's entire image issues
</script>

<template>
    <Head title="Edit Category" />

    <AdminLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <div class="flex flex-col lg:flex-row lg:space-x-8">
                            <!-- Left side - Form -->
                            <section class="w-full lg:w-1/2">
                                <header>
                                    <h2 class="text-lg font-medium text-gray-900">Edit Category</h2>
                                    <p class="mt-1 text-sm text-gray-600">
                                        Update category information.
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
                                            :placeholder="placeHolderName"
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
                                            :placeholder="placeHolderDescription"
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
                                        />
                                        <p class="mt-1 text-sm text-gray-500">
                                            Leave empty to keep current image. Square image recommended (will be displayed in a 1:1 ratio)
                                        </p>
                                        <InputError :message="form.errors.image" class="mt-2" />
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <PrimaryButton :disabled="form.processing">
                                            Update Category
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

                            <div class="hidden lg:block w-px bg-gray-200"></div>

                            <!-- Right side - Preview -->
                            <section class="w-full lg:w-1/2 mt-8 lg:mt-0">
                                <header class="mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Preview</h3>
                                </header>
                                
                                <div v-if="imagePreview" class="space-y-4">
                                    <div class="group relative">
                                        <!-- This container maintains aspect ratio and clips content -->
                                        <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-lg bg-gray-200">
                                            <!-- This wrapper handles the zoom and pan transforms -->
                                            <div class="w-full h-full"
                                                :style="{
                                                    transform: `translate(${imagePosition.x}px, ${imagePosition.y}px) scale(${zoom})`,
                                                    transition: isDragging ? 'none' : 'transform 0.1s'
                                                }">
                                                <img 
                                                    ref="imageRef"
                                                    :src="imagePreview" 
                                                    class="h-full w-full object-cover object-center"
                                                    :style="{
                                                        cursor: isDragging ? 'grabbing' : 'grab'
                                                    }"
                                                    @mousedown="startDrag"
                                                    @wheel.prevent="handleWheel"
                                                    @touchstart="handleTouchStart"
                                                    @touchmove.prevent="handleTouchMove"
                                                    @touchend="stopDrag"
                                                    draggable="false"
                                                />
                                            </div>
                                        </div>

                                        <div class="mt-4 space-y-2">
                                            <h3 class="text-sm text-gray-700 font-medium">
                                                {{ form.name || placeHolderName }}
                                            </h3>
                                            <p class="text-sm text-gray-600 line-clamp-2">
                                                {{ form.description || placeHolderDescription }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex justify-between items-center">
                                        <p class="text-sm text-gray-500 italic">
                                            Scroll to zoom • Drag to adjust position
                                        </p>
                                        <SecondaryButton @click="resetImagePosition">
                                            Reset Position
                                        </SecondaryButton>
                                    </div>
                                    <InputError :message="form.errors['imagePosition.x']" />
                                    <InputError :message="form.errors['imagePosition.y']" />
                                    <InputError :message="form.errors['imageZoom']" />
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

img {
    user-select: none;
    -webkit-user-drag: none;
}
</style>