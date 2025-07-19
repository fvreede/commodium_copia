/**
 * Bestandsnaam: Create.vue (Pages/Admin/Categories)
 * Auteur: Fabio Vreede
 * Versie: v1.0.4
 * Datum: 2025-07-04
 * Tijd: 16:58:38
 * Doel: Dit component biedt een administratieve interface voor het aanmaken van nieuwe categorieën
 *       met geavanceerde afbeelding editor functionaliteiten inclusief drag & drop, zoom, en 
 *       auto-align features voor optimale afbeelding positionering.
 */

<script setup>
// Vue compositie API en Inertia.js imports
import { Head, useForm } from '@inertiajs/vue3';
import { ref, reactive, onMounted, onUnmounted, computed } from 'vue';

// Layout en component imports
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

// Heroicons imports voor UI iconen
import { 
    FolderPlusIcon, 
    PhotoIcon, 
    ArrowLeftIcon, 
    MagnifyingGlassMinusIcon, 
    MagnifyingGlassPlusIcon, 
    ArrowPathIcon 
} from '@heroicons/vue/24/outline';

// ========== AFBEELDING EDITOR STATE MANAGEMENT ==========

// Afbeelding preview en positionering state
const imagePreview = ref(null);                    // Base64 string van geüploade afbeelding
const imagePosition = reactive({ x: 0, y: 0});     // Huidige afbeelding positie (px)
const imageZoom = ref(1);                          // Huidige zoom level (1 = 100%)

// Drag en drop state
const isDragging = ref(false);                     // Of gebruiker momenteel sleept
const startPosition = reactive({ x: 0, y: 0});     // Start positie van drag operatie

// DOM referenties voor afbeelding manipulatie
const imageRef = ref(null);                        // Referentie naar img element
const containerRef = ref(null);                    // Referentie naar container element

// ========== ZOOM EN ALIGNMENT CONFIGURATIE ==========

// Zoom beperkingen en stappen
const minZoom = 0.5;                               // Minimale zoom (50%)
const maxZoom = 3;                                 // Maximale zoom (300%)
const zoomStep = 0.1;                              // Zoom stap grootte (10%)

// Auto-align instellingen voor magnetische snapping
const alignThreshold = 10;                         // Snap afstand in pixels
const alignAnimationDuration = 200;                // Animatie duur in milliseconden

// ========== DRAG & DROP EVENT HANDLERS ==========

/**
 * Start drag operatie - ondersteunt zowel mouse als touch events
 * @param {Event} e - Mouse of touch event
 */
const startDrag = (e) => {
    isDragging.value = true;
    
    // Bepaal start positie gebaseerd op event type
    startPosition.x = e.type === 'mousedown' ? e.clientX : e.touches[0].clientX;
    startPosition.y = e.type === 'mousedown' ? e.clientY : e.touches[0].clientY;
    
    // Sla huidige afbeelding positie op
    startPosition.initialX = imagePosition.x;
    startPosition.initialY = imagePosition.y;
    
    // Voorkom standaard browser gedrag (selectie, drag)
    e.preventDefault();
};

/**
 * Voer drag operatie uit - update afbeelding positie tijdens slepen
 * @param {Event} e - Mouse move of touch move event
 */
const doDrag = (e) => {
    if (!isDragging.value) return;

    e.preventDefault();
    
    // Bereken huidige cursor/finger positie
    const currentX = e.type === 'mousemove' ? e.clientX : e.touches[0].clientX;
    const currentY = e.type === 'mousemove' ? e.clientY : e.touches[0].clientY;
    
    // Bereken verschil ten opzichte van start positie
    const deltaX = currentX - startPosition.x;
    const deltaY = currentY - startPosition.y;

    // Update afbeelding positie
    imagePosition.x = startPosition.initialX + deltaX;
    imagePosition.y = startPosition.initialY + deltaY;
};

/**
 * Stop drag operatie en trigger auto-align
 */
const stopDrag = () => {
    if (isDragging.value) {
        isDragging.value = false;
        autoAlign(); // Automatisch uitlijnen na slepen
    }
};

// ========== AUTO-ALIGN FUNCTIONALITEIT ==========

/**
 * Intelligente auto-align functie met magnetische border snapping
 * Houdt afbeelding binnen grenzen en snapt naar edges/center
 */
const autoAlign = () => {
    if (!containerRef.value || !imageRef.value) return;

    const container = containerRef.value;
    const containerWidth = container.clientWidth;
    const containerHeight = container.clientHeight;

    // Bereken werkelijke geschaalde afbeelding dimensies
    const imageNaturalWidth = imageRef.value.naturalWidth;
    const imageNaturalHeight = imageRef.value.naturalHeight;
    
    // Bereken hoe afbeelding in container past (object-cover gedrag)
    const containerAspect = containerWidth / containerHeight;
    const imageAspect = imageNaturalWidth / imageNaturalHeight;
    
    let scaledImageWidth, scaledImageHeight;
    
    if (imageAspect > containerAspect) {
        // Afbeelding is breder - hoogte vult container
        scaledImageHeight = containerHeight;
        scaledImageWidth = scaledImageHeight * imageAspect;
    } else {
        // Afbeelding is hoger - breedte vult container
        scaledImageWidth = containerWidth;
        scaledImageHeight = scaledImageWidth / imageAspect;
    }
    
    // Pas zoom toe op geschaalde dimensies
    scaledImageWidth *= imageZoom.value;
    scaledImageHeight *= imageZoom.value;

    let targetX = imagePosition.x;
    let targetY = imagePosition.y;

    // ========== HORIZONTALE UITLIJNING EN GRENZEN ==========
    if (scaledImageWidth > containerWidth) {
        // Afbeelding is groter dan container - beperk binnen grenzen
        const maxMoveRight = (scaledImageWidth - containerWidth) / 2;
        const maxMoveLeft = -maxMoveRight;
        
        // Hard boundaries
        if (imagePosition.x > maxMoveRight) {
            targetX = maxMoveRight;
        } else if (imagePosition.x < maxMoveLeft) {
            targetX = maxMoveLeft;
        }
        
        // Magnetische snapping naar center en edges
        if (Math.abs(imagePosition.x) < alignThreshold) {
            targetX = 0; // Snap naar center
        }
        if (Math.abs(imagePosition.x - maxMoveRight) < alignThreshold) {
            targetX = maxMoveRight; // Snap naar rechter edge
        }
        if (Math.abs(imagePosition.x - maxMoveLeft) < alignThreshold) {
            targetX = maxMoveLeft; // Snap naar linker edge
        }
    } else {
        // Afbeelding is kleiner dan container - snap naar container edges
        const halfContainer = containerWidth / 2;
        const halfImage = scaledImageWidth / 2;
        
        // Bereken edge posities (waar afbeelding edge uitlijnt met container edge)
        const leftEdgePosition = -halfContainer + halfImage;
        const rightEdgePosition = halfContainer - halfImage;
        
        // Magnetische snapping naar verschillende posities
        if (Math.abs(imagePosition.x - leftEdgePosition) < alignThreshold) {
            targetX = leftEdgePosition; // Snap naar linker edge
        }
        else if (Math.abs(imagePosition.x - rightEdgePosition) < alignThreshold) {
            targetX = rightEdgePosition; // Snap naar rechter edge
        }
        else if (Math.abs(imagePosition.x) < alignThreshold) {
            targetX = 0; // Snap naar center
        }
        
        // Beperk tot redelijke grenzen
        const maxOffset = halfContainer + halfImage;
        if (imagePosition.x > maxOffset) targetX = maxOffset;
        if (imagePosition.x < -maxOffset) targetX = -maxOffset;
    }

    // ========== VERTICALE UITLIJNING EN GRENZEN ==========
    if (scaledImageHeight > containerHeight) {
        // Afbeelding is groter dan container - beperk binnen grenzen
        const maxMoveDown = (scaledImageHeight - containerHeight) / 2;
        const maxMoveUp = -maxMoveDown;
        
        // Hard boundaries
        if (imagePosition.y > maxMoveDown) {
            targetY = maxMoveDown;
        } else if (imagePosition.y < maxMoveUp) {
            targetY = maxMoveUp;
        }
        
        // Magnetische snapping naar center en edges
        if (Math.abs(imagePosition.y) < alignThreshold) {
            targetY = 0; // Snap naar center
        }
        if (Math.abs(imagePosition.y - maxMoveDown) < alignThreshold) {
            targetY = maxMoveDown; // Snap naar onder edge
        }
        if (Math.abs(imagePosition.y - maxMoveUp) < alignThreshold) {
            targetY = maxMoveUp; // Snap naar boven edge
        }
    } else {
        // Afbeelding is kleiner dan container - snap naar container edges
        const halfContainer = containerHeight / 2;
        const halfImage = scaledImageHeight / 2;
        
        // Bereken edge posities
        const topEdgePosition = -halfContainer + halfImage;
        const bottomEdgePosition = halfContainer - halfImage;
        
        // Magnetische snapping naar verschillende posities
        if (Math.abs(imagePosition.y - topEdgePosition) < alignThreshold) {
            targetY = topEdgePosition; // Snap naar boven edge
        }
        else if (Math.abs(imagePosition.y - bottomEdgePosition) < alignThreshold) {
            targetY = bottomEdgePosition; // Snap naar onder edge
        }
        else if (Math.abs(imagePosition.y) < alignThreshold) {
            targetY = 0; // Snap naar center
        }
        
        // Beperk tot redelijke grenzen
        const maxOffset = halfContainer + halfImage;
        if (imagePosition.y > maxOffset) targetY = maxOffset;
        if (imagePosition.y < -maxOffset) targetY = -maxOffset;
    }

    // Animeer naar doel positie als deze verschilt van huidige
    if (Math.abs(targetX - imagePosition.x) > 0.5 || Math.abs(targetY - imagePosition.y) > 0.5) {
        animateToPosition(targetX, targetY);
    }
};

/**
 * Soepele animatie naar doel positie met ease-out curve
 * @param {number} targetX - Doel X positie
 * @param {number} targetY - Doel Y positie
 */
const animateToPosition = (targetX, targetY) => {
    const startX = imagePosition.x;
    const startY = imagePosition.y;
    const startTime = performance.now();

    const animate = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / alignAnimationDuration, 1);
        
        // Ease-out functie voor natuurlijke beweging
        const easeOut = 1 - Math.pow(1 - progress, 3);
        
        // Interpoleer tussen start en doel positie
        imagePosition.x = startX + (targetX - startX) * easeOut;
        imagePosition.y = startY + (targetY - startY) * easeOut;

        // Continue animatie of finaliseer
        if (progress < 1) {
            requestAnimationFrame(animate);
        } else {
            imagePosition.x = targetX;
            imagePosition.y = targetY;
        }
    };

    requestAnimationFrame(animate);
};

// ========== ZOOM FUNCTIONALITEITEN ==========

/**
 * Zoom in functie met auto-align
 */
const zoomIn = () => {
    if (imageZoom.value < maxZoom) {
        imageZoom.value = Math.min(imageZoom.value + zoomStep, maxZoom);
        setTimeout(() => autoAlign(), 50); // Korte vertraging voor smooth UX
    }
};

/**
 * Zoom out functie met auto-align
 */
const zoomOut = () => {
    if (imageZoom.value > minZoom) {
        imageZoom.value = Math.max(imageZoom.value - zoomStep, minZoom);
        setTimeout(() => autoAlign(), 50);
    }
};

/**
 * Reset zoom en positie naar standaard waarden
 */
const resetZoom = () => {
    imageZoom.value = 1;
    imagePosition.x = 0;
    imagePosition.y = 0;
};

/**
 * Behandel muiswiel zoom met auto-align na zoom
 * @param {WheelEvent} e - Muiswiel event
 */
const handleWheel = (e) => {
    e.preventDefault();
    
    const delta = e.deltaY > 0 ? -zoomStep : zoomStep;
    const newZoom = Math.max(minZoom, Math.min(maxZoom, imageZoom.value + delta));
    
    if (newZoom !== imageZoom.value) {
        imageZoom.value = newZoom;
        // Auto-align na zoom om afbeelding binnen grenzen te houden
        setTimeout(() => autoAlign(), 50);
    }
};

/**
 * Computed property voor afbeelding transform style
 * Combineert translate en scale in één transform
 */
const imageTransform = computed(() => {
    return `translate(${imagePosition.x}px, ${imagePosition.y}px) scale(${imageZoom.value})`;
});

// ========== FORM MANAGEMENT ==========

// Inertia.js form met alle benodigde velden
const form = useForm({
    name: '',
    description: '',
    image: null,
});

/**
 * Controleert of er onopgeslagen wijzigingen zijn
 * @returns {boolean} True als er wijzigingen zijn
 */
const hasSavedChanges = () => {
    return form.name !== '' || form.description !== '' || form.image !== null;
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
 * Component mounted - registreer event listeners
 */
onMounted(() => {
    // Event listeners voor drag en drop functionaliteit
    document.addEventListener('mousemove', doDrag, { passive: false });
    document.addEventListener('mouseup', stopDrag);
    document.addEventListener('touchmove', doDrag, { passive: false });
    document.addEventListener('touchend', stopDrag);

    // Voorkom pagina verlaten bij onopgeslagen wijzigingen
    window.addEventListener('beforeunload', handleBeforeUnload);
});

/**
 * Component unmounted - verwijder event listeners
 */
onUnmounted(() => {
    // Opruimen van event listeners bij component vernietiging
    document.removeEventListener('mousemove', doDrag);
    document.removeEventListener('mouseup', stopDrag);
    document.removeEventListener('touchmove', doDrag);
    document.removeEventListener('touchend', stopDrag);
    window.removeEventListener('beforeunload', handleBeforeUnload);
});

// ========== EVENT HANDLERS ==========

/**
 * Behandel afbeelding upload en genereer preview
 * @param {Event} e - File input change event
 */
const handleImageChange = (e) => {
    const file = e.target.files[0];
    form.image = file;

    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
            // Reset afbeelding positie en zoom bij nieuwe afbeelding
            imagePosition.x = 0;
            imagePosition.y = 0;
            imageZoom.value = 1;
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
 */
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
                    
                    <!-- Page Header Sectie -->
                    <div class="px-4 py-4 sm:px-6 border-b border-gray-200">
                        <div class="flex items-center space-x-3">
                            <!-- Header Icoon -->
                            <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg">
                                <FolderPlusIcon class="w-5 h-5 text-blue-600" />
                            </div>
                            <!-- Header Tekst -->
                            <div>
                                <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Create Category</h2>
                                <p class="text-sm text-gray-600">Add a new category to organize products</p>
                            </div>
                        </div>
                    </div>

                    <!-- Formulier Sectie -->
                    <div class="p-4 sm:p-6">
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
                                    autofocus
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
                                        required
                                    />
                                    <p class="mt-2 text-sm text-gray-500">
                                        Square images work best (displayed in 1:1 ratio)
                                    </p>
                                    <InputError :message="form.errors.image" class="mt-2" />
                                </div>

                                <!-- Afbeelding Preview met Geavanceerde Editor -->
                                <div v-if="imagePreview" class="mt-6">
                                    
                                    <!-- Mobiel-Vriendelijke Zoom Controls -->
                                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3">Image Editor</h4>
                                        <div class="flex items-center justify-between gap-3">
                                            <!-- Zoom Controls -->
                                            <div class="flex items-center gap-2">
                                                <!-- Zoom Out Knop -->
                                                <button
                                                    type="button"
                                                    @click="zoomOut"
                                                    :disabled="imageZoom <= minZoom"
                                                    class="flex items-center justify-center w-10 h-10 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                                >
                                                    <MagnifyingGlassMinusIcon class="w-4 h-4" />
                                                </button>
                                                <!-- Zoom Percentage Display -->
                                                <span class="text-sm text-gray-600 min-w-[3.5rem] text-center font-medium">
                                                    {{ Math.round(imageZoom * 100) }}%
                                                </span>
                                                <!-- Zoom In Knop -->
                                                <button
                                                    type="button"
                                                    @click="zoomIn"
                                                    :disabled="imageZoom >= maxZoom"
                                                    class="flex items-center justify-center w-10 h-10 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                                >
                                                    <MagnifyingGlassPlusIcon class="w-4 h-4" />
                                                </button>
                                            </div>
                                            <!-- Reset Knop -->
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

                                    <!-- Afbeelding Preview Container met Editor -->
                                    <div class="group relative bg-gray-100 rounded-lg overflow-hidden">
                                        <!-- Vierkante Afbeelding Container -->
                                        <div 
                                            ref="containerRef"
                                            class="aspect-square w-full overflow-hidden relative bg-gray-200"
                                            @wheel="handleWheel"
                                        >
                                            <!-- Draggable Afbeelding Container -->
                                            <div 
                                                class="absolute inset-0 cursor-move overflow-hidden"
                                                @mousedown="startDrag" 
                                                @touchstart="startDrag"
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
                                            
                                            <!-- Uitlijning Hulplijnen -->
                                            <div class="absolute inset-0 pointer-events-none">
                                                <!-- Verticale Center Lijn -->
                                                <div class="absolute left-1/2 top-0 bottom-0 w-px bg-blue-300 opacity-0 group-hover:opacity-30 transition-opacity transform -translate-x-px"></div>
                                                <!-- Horizontale Center Lijn -->
                                                <div class="absolute top-1/2 left-0 right-0 h-px bg-blue-300 opacity-0 group-hover:opacity-30 transition-opacity transform -translate-y-px"></div>
                                            </div>
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

                                    <!-- Debug Informatie (verborgen op mobiel) -->
                                    <div class="hidden sm:block mt-2 text-xs text-gray-400">
                                        Position: {{ Math.round(imagePosition.x) }}, {{ Math.round(imagePosition.y) }} • 
                                        Zoom: {{ Math.round(imageZoom * 100) }}%
                                    </div>

                                    <!-- Afbeelding Error Berichten -->
                                    <InputError :message="form.errors['imagePosition.x']" class="mt-2" />
                                    <InputError :message="form.errors['imagePosition.y']" class="mt-2" />
                                    <InputError :message="form.errors['imageZoom']" class="mt-2" />
                                </div>
                            </div>

                            <!-- Formulier Actie Knoppen -->
                            <div class="pt-6 border-t border-gray-200">
                                <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                                    <!-- Create Category Knop -->
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
                                        <FolderPlusIcon v-else class="w-4 h-4 mr-2" />
                                        {{ form.processing ? 'Creating...' : 'Create Category' }}
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