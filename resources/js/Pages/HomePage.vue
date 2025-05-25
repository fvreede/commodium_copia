/**
 * Bestandsnaam: HomePage.vue
 * Auteur: Fabio Vreede
 * Versie: v1.1.0
 * Datum: 2024-10-08
 * Tijd: 22:12:18
 * Beschrijving: Deze view definieert de HomePage, met secties zoals de navigatiebalk, promotiesectie, productenraster, nieuwsrubriek en footer.
 * Update: Aangepast om data van Laravel backend te ontvangen in plaats van mock data
 */

<script setup>
// Importeren van de NavBar component
import NavBar from '@/Components/NavBar.vue';

// Importeren van de PromoSection component
import PromoSection from '@/Components/PromoSection.vue';

// Importeren van de ProductGrid component
import ProductGrid from '@/Components/ProductGrid.vue';

// Importeren van de NewsSection component
import NewsSection from '@/Components/NewsSection.vue';

// Importeren van de Footer component
import Footer from '@/Components/Footer.vue';

// Props ontvangen van Laravel controller
const props = defineProps({
    products: {
        type: Array,
        required: true,
        default: () => []
    },
    subcategories: {
        type: Array,
        required: false,
        default: () => []
    }
});
</script>

<template>
    <!-- Header sectie met de navigatiebalk -->
    <header class="shadow-md">
        <NavBar />
    </header>

    <!-- Hoofdinhoud sectie met promotiesectie, productraster en nieuwrubriek -->
    <main>
        <PromoSection />
        <ProductGrid 
            :products="products" 
            :subcategories="subcategories"
            :featured-product-names="['Biologische pompoen', 'Espresso Brownies', 'Red Velvet Muffins']"
        />
        <NewsSection />
    </main>

    <!-- Footer sectie -->
    <footer>
        <Footer />
    </footer>
</template>