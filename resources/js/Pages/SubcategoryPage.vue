<template>
    <NavBar/>
    <div class="bg-gray-100">
        <div class="relative category-banner">
            <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-black/20 z-10"></div>
            <img :src="resolveImagePath(props.bannerSrc)" alt="Category banner" class="w-full h-64 object-cover object-center" />
            <div class="absolute inset-0 z-20 flex flex-col items-center justify-center p-4">
                <h2 class="text-2xl font-bold tracking-tight h_text sm:text-2xl md:text-4xl lg:text-6xl text-center mb-4 md:mb-0 md:absolute md:left-6 md:top-1/2 md:-translate-y-1/2">
                    {{ props.categoryName }}
                </h2>
                <Link :href="route('AllCategories')" class="flex items-center px-4 py-2 text-xs font-medium text-white bg-gray-800 hover:bg-gray-900 rounded-full md:absolute md:right-4 md:top-1/2 md:transform md:-translate-y-1/2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Terug naar categorieën
                </Link>
            </div>
        </div>

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
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
    color: #F6EBD8;
}

.category-banner {
    margin-bottom: 2rem;
}

.group:hover {
    transform: scale(1.01);
    transition: all 0.2s ease-in-out;
}
</style>