/**
 * Bestandsnaam: ProductGrid.vue
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-05-25
 * Tijd: 20:38:26
 * Doel: Responsive product grid component voor e-commerce website met featured product filtering. Toont product overzicht met afbeeldingen, beschrijvingen, prijzen en directe product links. Bevat responsive design (1/2/3 kolommen), Nederlandse currency formatting en optimale mobile/desktop experience voor product discovery en conversie.
 */

<template>
    <!-- 
        PRODUCT GRID CONTAINER
        Responsive grid layout die zich aanpast aan schermgrootte voor optimale product weergave
    -->
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-2 md:p-4 bg-slate-50">
        
        <!-- 
            INDIVIDUELE PRODUCT CARD
            Product weergave met afbeelding, details en call-to-action
        -->
        <article 
            v-for="product in filteredProducts" 
            :key="product.id" 
            class="bg-beige shadow-md rounded-lg p-2 md:p-4 flex items-start justify-between"
        >
            <!-- Product Informatie Sectie -->
            <!-- Linkerzijde met naam, beschrijving en actie knop -->
            <div class="flex-1 pr-4">
                <!-- Product Naam -->
                <!-- Prominente titel met merk kleuring -->
                <h2 class="text-lg md:text-xl font-bold text-orange-700">{{ product.name }}</h2>
                
                <!-- Product Beschrijving -->
                <!-- Korte beschrijving voor product context -->
                <p class="text-gray-600 mb-2 md:mb-4">{{ product.short_description }}</p>
                
                <!-- Bekijk Product Knop -->
                <!-- Call-to-action met hover animaties voor betere conversie -->
                <Link 
                    :href="route('product.show', { product: product.id })" 
                    class="inline-block bg-orange-600 hover:bg-orange-700 text-white text-sm md:text-base font-medium py-2 px-4 rounded-md shadow-lg transition duration-300 hover:-translate-y-1 hover:shadow-xl"
                >
                    Bekijk product
                </Link>
            </div>
            
            <!-- Product Visual Sectie -->
            <!-- Rechterzijde met afbeelding en prijs informatie -->
            <div class="flex-shrink-0 flex flex-col items-center">
                <!-- Product Afbeelding -->
                <!-- Responsive afbeelding met consistent formaat -->
                <img 
                    :src="resolveImagePath(product.image_path)" 
                    :alt="product.name" 
                    class="w-24 h-24 md:w-32 md:h-32 object-cover rounded-md border border-orange-200"
                />
                
                <!-- Product Prijs -->
                <!-- Prominente prijs weergave in Nederlandse valuta formatting -->
                <p class="text-orange-600 font-bold mt-1 text-base md:text-lg">{{ formatCurrency(product.price) }}</p>
            </div>
        </article>
    </section>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

/**
 * COMPONENT EIGENSCHAPPEN
 * Data configuratie van parent component voor product weergave en filtering
 */
const props = defineProps({
    products: {
        type: Array,
        required: true,
        default: () => []
        // Complete product array van Laravel controller
    },
    subcategories: {
        type: Array,
        required: false,
        default: () => []
        // Subcategorie data voor eventuele filtering (toekomstig gebruik)
    },
    featuredProductNames: {
        type: Array,
        required: false,
        default: () => ['Biologische pompoen', 'Espresso Brownies', 'Red Velvet Muffins']
        // Array van featured product namen voor filtering
    }
});

/**
 * PRODUCT FILTERING LOGICA
 * Computed property voor featured product selectie en weergave
 */
const filteredProducts = computed(() => {
    // Als geen featured products gespecificeerd, toon alle products
    if (props.featuredProductNames.length === 0) {
        return props.products;
    }
    
    // Filter products op basis van featured product namen
    return props.products.filter(product =>
        props.featuredProductNames.includes(product.name)
    );
});

/**
 * AFBEELDING PATH RESOLVER
 * Intelligent path handling voor product afbeeldingen met storage integratie
 */
const resolveImagePath = (path) => {
    if (!path) return '';
    
    // Verwijder 'assets/' prefix als aanwezig voor clean path
    const cleanPath = path.replace(/^assets\//, '');
    
    // Return storage path voor Laravel storage link
    return `/storage/${cleanPath}`;
};

/**
 * NEDERLANDSE VALUTA FORMATTING
 * Formatteert prijzen volgens Nederlandse standaarden met Euro symbool
 */
const formatCurrency = (price) =>
    new Intl.NumberFormat('nl-NL', { 
        style: 'currency', 
        currency: 'EUR' 
    }).format(price);
</script>