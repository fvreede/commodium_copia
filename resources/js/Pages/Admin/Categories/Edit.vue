/**
 * Bestandsnaam: Edit.vue (Pages/Admin/Categories)
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-07-04
 * Tijd: 16:58:38
 * Doel: Dit component biedt een administratieve interface voor het bewerken van bestaande categorieën
 *       met split-screen layout (formulier links, live preview rechts) en geavanceerde afbeelding 
 *       editor inclusief touch support, pinch-to-zoom, en drag & drop functionaliteiten.
 */

<script setup>
// Vue compositie API en Inertia.js imports
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';

// Layout en component imports
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

// Heroicons imports voor UI iconen
import { 
    PencilIcon, 
    PhotoIcon, 
    ArrowLeftIcon, 
    MagnifyingGlassMinusIcon, 
    MagnifyingGlassPlusIcon, 
    ArrowPathIcon 
} from '@heroicons/vue/24/outline';

// ========== PROPS DEFINITIE ==========

// Props van server - bestaande categorie data
const props = defineProps({
    category: Object               // Bestaande categorie object met naam, beschrijving en afbeelding
});

// ========== UTILITY FUNCTIES ==========

/**
 * Genereert de juiste URL voor afbeelding weergave
 * Behandelt zowel data URLs (base64) als storage paths
 * @param {string} imagePath - Pad naar afbeelding of data URL
 * @returns {string|null} Volledige URL of null als geen afbeelding
 */
const getImageUrl = (imagePath) => {
    if (!imagePath) return null;
    if (imagePath.startsWith('data:')) return imagePath;  // Base64 data URL
    return `/storage/${imagePath}`;                       // Storage pad
};

// ========== AFBEELDING EDITOR STATE MANAGEMENT ==========

// Afbeelding preview en positionering state
const imagePreview = ref(getImageUrl(props.category.image_path));  // Huidige afbeelding preview
const imagePosition = ref({ x: 0, y: 0 });                        // Afbeelding positie (px)
const zoom = ref(1);                                               // Zoom level (1 = 100%)

// Drag en drop state
const isDragging = ref(false);                                     // Of gebruiker momenteel sleept
const startPosition = ref({ x: 0, y: 0, initialX: 0, initialY: 0 }); // Start posities voor drag

// DOM referentie
const imageRef = ref(null);                                        // Referentie naar img element

// ========== ZOOM EN TOUCH CONFIGURATIE ==========

// Zoom beperkingen en stappen
const minZoom = 0.5;                                               // Minimale zoom (50%)
const maxZoom = 3;                                                 // Maximale zoom (300%)
const zoomStep = 0.1;                                              // Zoom stap grootte (10%)

// ========== ZOOM EVENT HANDLERS ==========

/**
 * Behandel muiswiel zoom functionaliteit
 * @param {WheelEvent} e - Muiswiel event
 */
const handleWheel = (e) => {
    e.preventDefault();
    const delta = e.deltaY > 0 ? -zoomStep : zoomStep;
    zoom.value = Math.max(minZoom, Math.min(maxZoom, zoom.value + delta));
};

/**
 * Behandel touch start - ondersteunt zowel pinch-to-zoom als drag
 * @param {TouchEvent} e - Touch start event
 */
const handleTouchStart = (e) => {
    if (e.touches.length === 2) {
        // Twee vingers - pinch-to-zoom mode
        const touch1 = e.touches[0];
        const touch2 = e.touches[1];
        const distance = Math.hypot(
            touch2.clientX - touch1.clientX,
            touch2.clientY - touch1.clientY
        );
        startPosition.value = {
            ...startPosition.value,
            pinchDistance: distance,     // Initiële afstand tussen vingers
            initialZoom: zoom.value      // Zoom level bij start
        };
    } else {
        // Eén vinger - drag mode
        startDrag(e);
    }
};

/**
 * Behandel touch move - pinch zoom of drag gebaseerd op aantal vingers
 * @param {TouchEvent} e - Touch move event
 */
const handleTouchMove = (e) => {
    if (e.touches.length === 2) {
        // Pinch-to-zoom berekening
        e.preventDefault();
        const touch1 = e.touches[0];
        const touch2 = e.touches[1];
        const distance = Math.hypot(
            touch2.clientX - touch1.clientX,
            touch2.clientY - touch1.clientY
        );
        
        // Bereken zoom scale gebaseerd op vinger afstand
        const scale = distance / startPosition.value.pinchDistance;
        zoom.value = Math.max(minZoom, Math.min(maxZoom, startPosition.value.initialZoom * scale));
    } else {
        // Reguliere drag operatie
        doDrag(e);
    }
};

// ========== DRAG & DROP EVENT HANDLERS ==========

/**
 * Start drag operatie - ondersteunt zowel mouse als touch events
 * @param {Event} e - Mouse of touch event
 */
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

/**
 * Voer drag operatie uit - update afbeelding positie tijdens slepen
 * @param {Event} e - Mouse move of touch move event
 */
const doDrag = (e) => {
    if (!isDragging.value) return;

    e.preventDefault();
    
    // Bepaal huidige cursor/finger positie
    const currentX = e.type === 'mousemove' ? e.clientX : e.touches[0].clientX;
    const currentY = e.type === 'mousemove' ? e.clientY : e.touches[0].clientY;
    
    // Bereken verschil en update positie
    const deltaX = (currentX - startPosition.value.x);
    const deltaY = (currentY - startPosition.value.y);

    imagePosition.value = {
        x: startPosition.value.initialX + deltaX,
        y: startPosition.value.initialY + deltaY
    };
};

/**
 * Stop drag operatie
 */
const stopDrag = () => {
    isDragging.value = false;
};

// ========== ZOOM CONTROL FUNCTIES ==========

/**
 * Zoom in functie met boundary checking
 */
const zoomIn = () => {
    if (zoom.value < maxZoom) {
        zoom.value = Math.min(zoom.value + zoomStep, maxZoom);
    }
};

/**
 * Zoom out functie met boundary checking
 */
const zoomOut = () => {
    if (zoom.value > minZoom) {
        zoom.value = Math.max(zoom.value - zoomStep, minZoom);
    }
};

/**
 * Reset afbeelding positie en zoom naar standaard waarden
 */
const resetImagePosition = () => {
    imagePosition.value = { x: 0, y: 0 };
    zoom.value = 1;
};

/**
 * Computed property voor afbeelding transform style
 * Combineert translate en scale in één CSS transform
 */
const imageTransform = computed(() => {
    return `translate(${imagePosition.value.x}px, ${imagePosition.value.y}px) scale(${zoom.value})`;
});

// ========== FORM MANAGEMENT ==========

// Inertia.js form met bestaande categorie data als initiële waarden
const form = useForm({
    name: props.category.name,
    description: props.category.description,
    image: null,                               // Nieuwe afbeelding (null = behoud huidige)
});

/**
 * Controleert of er onopgeslagen wijzigingen zijn
 * Vergelijkt huidige form waarden met originele categorie data
 * @returns {boolean} True als er wijzigingen zijn
 */
const hasSavedChanges = () => {
    return form.name !== props.category.name || 
           form.description !== props.category.description || 
           form.image !== null;
};

/**
 * Behandel browser beforeunload event voor onopgeslagen wijzigingen
 * @param {BeforeUnloadEvent} e - Browser beforeunload event
 */
const handleBeforeUnload = (e) => {
    if (hasSavedChanges()) {
        e.preventDefault();
        e.returnValue = '';
    }
};

// ========== LIFECYCLE HOOKS ==========

/**
 * Component mounted - registreer globale event listeners
 */
onMounted(() => {
    // Event listeners voor drag en drop functionaliteit
    document.addEventListener('mousemove', doDrag, { passive: false });
    document.addEventListener('mouseup', stopDrag);
    document.addEventListener('touchmove', doDrag, { passive: false });
    document.addEventListener('touchend', stopDrag);
    
    // Waarschuwing bij verlaten pagina met onopgeslagen wijzigingen
    window.addEventListener('beforeunload', handleBeforeUnload);
});

/**
 * Component unmounted - cleanup event listeners
 */
onUnmounted(() => {
    // Opruimen van globale event listeners
    document.removeEventListener('mousemove', doDrag);
    document.removeEventListener('mouseup', stopDrag);
    document.removeEventListener('touchmove', doDrag);
    document.removeEventListener('touchend', stopDrag);
    window.removeEventListener('beforeunload', handleBeforeUnload);
});

// ========== EVENT HANDLERS ==========

/**
 * Behandel nieuwe afbeelding upload en genereer preview
 * @param {Event} e - File input change event
 */
const handleImageChange = (e) => {
    const file = e.target.files[0];
    form.image = file;

    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
            // Reset positie en zoom bij nieuwe afbeelding
            imagePosition.value = { x: 0, y: 0 };
            zoom.value = 1;
        };
        reader.readAsDataURL(file);
    }
};

/**
 * Behandel terug navigatie met onopgeslagen wijzigingen check
 */
const handleBack = () => {
    if (hasSavedChanges()) {
        if (!confirm('You have unsaved changes. Are you sure you want to leave this page?')) {
            return;
        }
    }
    // Verwijder beforeunload listener om dubbele waarschuwing te voorkomen
    window.removeEventListener('beforeunload', handleBeforeUnload);
    window.location = route('admin.categories.index');
};

/**
 * Submit form inclusief afbeelding positionering data
 * Gebruikt PUT method voor update operatie
 */
const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put',                        // Laravel method spoofing voor PUT
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
                    
                    <!-- Page Header Sectie -->
                    <div class="px-4 py-4 sm:px-6 border-b border-gray-200">
                        <div class="flex items-center space-x-3">
                            <!-- Header Icoon -->
                            <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg">
                                <PencilIcon class="w-5 h-5 text-blue-600" />
                            </div>
                            <!-- Header Tekst -->
                            <div>
                                <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Edit Category</h2>
                                <p class="text-sm text-gray-600">Update category information and preview changes</p>
                            </div>
                        </div>
                    </div>

                    <!-- Hoofdinhoud Container -->
                    <div class="p-4 sm:p-6">
                        <!-- Split-Screen Layout: Formulier Links, Preview Rechts -->
                        <div class="flex flex-col lg:flex-row lg:space-x-8 space-y-8 lg:space-y-0">
                            
                            <!-- Linkerkant - Formulier Sectie -->
                            <section class="w-full lg:w-1/2">
                                <form @submit.prevent="submit" class="space-y-6">
                                    
                                    <!-- Categorie Naam Veld -->
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

                                    <!-- Beschrijving Veld -->
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

                                    <!-- Afbeelding Upload Sectie -->
                                    <div>
                                        <InputLabel for="image" value="Category Image" class="text-sm font-medium text-gray-700" />
                                        <div class="mt-2">
                                            <!-- File Input -->
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

                                    <!-- Formulier Actie Knoppen -->
                                    <div class="pt-6 border-t border-gray-200">
                                        <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                                            <!-- Update Category Knop -->
                                            <PrimaryButton 
                                                type="submit"
                                                :disabled="form.processing"
                                                class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 text-sm font-medium transition-colors"
                                            >
                                                <!-- Loading Spinner -->
                                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <!-- Normaal Icoon -->
                                                <PencilIcon v-else class="w-4 h-4 mr-2" />
                                                {{ form.processing ? 'Updating...' : 'Update Category' }}
                                            </PrimaryButton>
                                            
                                            <!-- Cancel Knop -->
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

                            <!-- Scheidingslijn (verborgen op mobiel) -->
                            <div class="hidden lg:block w-px bg-gray-200"></div>

                            <!-- Rechterkant - Live Preview Sectie -->
                            <section class="w-full lg:w-1/2">
                                <!-- Sticky Container voor Desktop -->
                                <div class="lg:sticky lg:top-6">
                                    
                                    <!-- Preview Header -->
                                    <header class="mb-4">
                                        <h3 class="text-lg font-medium text-gray-900">Live Preview</h3>
                                        <p class="text-sm text-gray-600">See how your category will appear</p>
                                    </header>
                                    
                                    <!-- Preview Content - Met Afbeelding -->
                                    <div v-if="imagePreview" class="space-y-4">
                                        
                                        <!-- Mobiel-Vriendelijke Zoom Controls -->
                                        <div class="p-3 bg-gray-50 rounded-lg">
                                            <h4 class="text-sm font-medium text-gray-700 mb-3">Image Editor</h4>
                                            <div class="flex items-center justify-between gap-3">
                                                <!-- Zoom Controls -->
                                                <div class="flex items-center gap-2">
                                                    <!-- Zoom Out Knop -->
                                                    <button
                                                        type="button"
                                                        @click="zoomOut"
                                                        :disabled="zoom <= minZoom"
                                                        class="flex items-center justify-center w-10 h-10 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                                    >
                                                        <MagnifyingGlassMinusIcon class="w-4 h-4" />
                                                    </button>
                                                    <!-- Zoom Percentage Display -->
                                                    <span class="text-sm text-gray-600 min-w-[3.5rem] text-center font-medium">
                                                        {{ Math.round(zoom * 100) }}%
                                                    </span>
                                                    <!-- Zoom In Knop -->
                                                    <button
                                                        type="button"
                                                        @click="zoomIn"
                                                        :disabled="zoom >= maxZoom"
                                                        class="flex items-center justify-center w-10 h-10 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                                    >
                                                        <MagnifyingGlassPlusIcon class="w-4 h-4" />
                                                    </button>
                                                </div>
                                                <!-- Reset Knop -->
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

                                        <!-- Afbeelding Preview Container met Geavanceerde Editor -->
                                        <div class="group relative bg-gray-100 rounded-lg overflow-hidden">
                                            <!-- Vierkante Afbeelding Container -->
                                            <div class="aspect-square w-full overflow-hidden relative bg-gray-200">
                                                <!-- Draggable Afbeelding Container met Touch Support -->
                                                <div 
                                                    class="absolute inset-0 cursor-move overflow-hidden"
                                                    @mousedown="startDrag"
                                                    @wheel.prevent="handleWheel"
                                                    @touchstart="handleTouchStart"
                                                    @touchmove.prevent="handleTouchMove"
                                                    @touchend="stopDrag"
                                                >
                                                    <!-- Hoofdafbeelding Element -->
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
                                                <!-- Hover Overlay Effect -->
                                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-300 pointer-events-none"></div>
                                            </div>

                                            <!-- Categorie Preview Info Sectie -->
                                            <div class="p-4 bg-white border-t border-gray-200">
                                                <h3 class="text-sm font-medium text-gray-900 truncate">
                                                    {{ form.name || 'Category Name' }}
                                                </h3>
                                                <p class="mt-1 text-xs text-gray-600 line-clamp-2">
                                                    {{ form.description || 'Category description will appear here' }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Gebruikers Instructies -->
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

                                        <!-- Debug Informatie (verborgen op mobiel) -->
                                        <div class="hidden sm:block text-xs text-gray-400">
                                            Position: {{ Math.round(imagePosition.x) }}, {{ Math.round(imagePosition.y) }} • 
                                            Zoom: {{ Math.round(zoom * 100) }}%
                                        </div>

                                        <!-- Afbeelding Error Berichten -->
                                        <InputError :message="form.errors['imagePosition.x']" />
                                        <InputError :message="form.errors['imagePosition.y']" />
                                        <InputError :message="form.errors['imageZoom']" />
                                    </div>

                                    <!-- Lege Staat - Geen Afbeelding -->
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
/* Cursor stijlen voor drag functionaliteit */
.cursor-move {
    cursor: move;
    cursor: grab;
}

/* Selectie preventie voor afbeeldingen */
.select-none {
    user-select: none;
    -webkit-user-select: none;
    -webkit-user-drag: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

/* Extra selectie preventie voor img elementen */
img {
    user-select: none;
    -webkit-user-drag: none;
}

/* Vierkante aspect ratio container utility */
.aspect-square {
    aspect-ratio: 1 / 1;
}

/* Text truncatie utility voor lange teksten */
.line-clamp-2 {
    display: -webkit-box;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>