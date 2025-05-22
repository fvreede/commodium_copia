<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const imagePreview = ref(null);
const imagePosition = ref({ x: 0, y: 0});
const isDragging = ref(false);
const startPosition = ref({ x: 0, y: 0});
const imageRef = ref(null);

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
    
    const deltaX = currentX - startPosition.value.x;
    const deltaY = currentY - startPosition.value.y;

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

const hasSavedChanges = () => {
    return form.name !== '' || form.description !== '' || form.image !== null;
};

onMounted(() => {
    // Add event listeners for drag and drop functionality
    document.addEventListener('mousemove', doDrag);
    document.addEventListener('mouseup', stopDrag);
    document.addEventListener('touchmove', doDrag);
    document.addEventListener('touchend', stopDrag);

    // Prevent page from unloading when there are unsaved changes
    window.addEventListener('beforeunload', handleBeforeUnload);
});

const handleBeforeUnload = (e) => {
    if (hasSavedChanges()) {
        event.preventDefault();
        event.returnValue = '';
    }
};

onUnmounted(() => {
    // Remove event listeners when component is unmounted
    document.removeEventListener('mousemove', doDrag);
    document.removeEventListener('mouseup', stopDrag);
    document.removeEventListener('touchmove', doDrag);
    document.removeEventListener('touchend', stopDrag);
});

const handleImageChange = (e) => {
    const file = e.target.files[0];
    form.image = file;

    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
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
    form.post(route('admin.categories.store'), {
        imagePosition: imagePosition.value
    });
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
                                        <div class="group relative">
                                            <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-lg bg-gray-200 cursor-move relative">
                                                <img 
                                                    ref="imageRef"
                                                    :src="imagePreview" 
                                                    :style="{
                                                        transform: `translate(${imagePosition.x}px, ${imagePosition.y}px)`,
                                                        cursor: isDragging ? 'grabbing' : 'grab',
                                                    }"
                                                    class="h-full w-full object-cover object-center absolute"
                                                    @mousedown="startDrag"
                                                    @touchstart="startDrag"
                                                    draggable="false"
                                                />
                                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-300"></div>
                                            </div>
                                            <h3 class="mt-2 text-sm text-gray-700 truncate">
                                                {{ form.name || 'Category Name' }}
                                            </h3>
                                            <p class="mt-1 text-xs sm:text-sm font-medium text-gray-900 line-clamp-2">
                                                {{ form.description || 'Category description will appear here' }}
                                            </p>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500 italic">
                                            Drag to adjust image position
                                        </p>
                                        <InputError :message="form.errors.image" class="mt-2" />
                                        <InputError :message="form.errors['imagePosition.x']" class="mt-2" />
                                        <InputError :message="form.errors['imagePosition.y']" class="mt-2" />
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

img {
    user-select: none;
    -webkit-user-drag: none;
}
</style>