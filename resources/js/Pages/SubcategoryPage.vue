<!-- Enhanced SubcategoryPage.vue -->
<template>
    <NavBar/>
    <div class="bg-gray-100">
        <!-- Enhanced Responsive Banner -->
        <div class="relative category-banner overflow-hidden pt-16">
            <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent z-10"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent z-10"></div>
            <img :src="resolveImagePath(props.bannerSrc)" 
                 alt="Category banner" 
                 class="w-full h-48 sm:h-72 md:h-80 object-cover object-center transform scale-105 transition-transform duration-700 hover:scale-110" />
                <div class="absolute inset-0 top-16 sm:top-16 z-20 flex flex-col items-start justify-center p-6 sm:p-8">
                <div class="max-w-7xl w-full mx-auto flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="mb-6 md:mb-0">
                        <h2 class="text-2xl font-bold tracking-tight h_text sm:text-3xl md:text-5xl lg:text-6xl mb-2">
                            {{ props.categoryName }}
                        </h2>
                    </div>
                    <Link :href="route('AllCategories')" 
                          class="group flex items-center px-6 py-3 text-sm font-medium text-white bg-white/10 backdrop-blur-sm hover:bg-white/20 rounded-full border border-white/20 transition-all duration-300 hover:scale-105 min-h-[48px]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Terug naar categorieën
                    </Link>
                </div>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 sm:py-24 lg:max-w-7xl lg:px-8">
            <div v-for="subcategory in props.subcategories" :key="subcategory.id" class="mb-12">
                <h3 class="text-xl font-semibold leading-6 text-gray-900">{{ subcategory.name }}</h3>

                <div class="mt-6 grid grid-cols-2 gap-x-4 gap-y-8 lg:gap-y-12 sm:grid-cols-3 sm:gap-6 lg:grid-cols-4 lg:gap-x-8">
                    <div v-for="product in subcategory.products" :key="product.id" class="group relative">
                        <Link :href="route('product.show', { 
                            id: product.id, 
                            subcategoryName: subcategory.name, 
                            categoryId: props.categoryId 
                        })">
                            <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-md bg-gray-200 group-hover:opacity-75">
                                <img :src="resolveImagePath(product.imageSrc)" :alt="product.name" class="w-full h-full object-cover object-center" />
                            </div>
                            
                            <div class="mt-4 flex flex-col sm:flex-row sm:justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 sm:text-base">{{ product.name }}</h4>
                                    <p class="mt-1 text-xs text-gray-500 sm:text-sm line-clamp-2">{{ product.description }}</p>
                                </div>
                                <p class="mt-2 text-lg font-semibold text-gray-900 sm:mt-0 sm:text-xl">€{{ formatPrice(product.price) }}</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <Footer/>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import NavBar from '@/Components/NavBar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    categoryId: String,
    categoryName: String,
    bannerSrc: String,
    subcategories: Array,
});

const formatPrice = (price) => {
    if (typeof price !== 'number' || isNaN(price)) {
        price = 0;
    }
    return new Intl.NumberFormat('nl-NL', { style: 'currency', currency: 'EUR' })
        .format(price)
        .replace('€', '');
};

const resolveImagePath = (path) => {
    if (!path) return '';
    const cleanPath = path.replace(/^assets\//, '');
    return `/storage/${cleanPath}`;
};
</script>

<style scoped>
.h_text {
    font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.8);
    color: #F6EBD8;
}

.category-banner {
    margin-bottom: 2rem;
}

.group:hover {
    transform: scale(1.01);
    transition: all 0.2s ease-in-out;
}

.line-clamp-2 {
    display: -webkit-box;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>