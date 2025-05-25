<template>
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-2 md:p-4 bg-slate-50">
        <article v-for="product in filteredProducts" :key="product.id" class="bg-beige shadow-md rounded-lg p-2 md:p-4 flex items-start justify-between">
            <div class="flex-1 pr-4">
                <h2 class="text-lg md:text-xl font-bold text-orange-700">{{ product.name }}</h2>
                <p class="text-gray-600 mb-2 md:mb-4">{{ product.short_description }}</p>
                <Link :href="route('product.show', { product: product.id })" class="inline-block bg-orange-600 hover:bg-orange-700 text-white text-sm md:text-base font-medium py-2 px-4 rounded-md shadow-lg transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                    Bekijk product
                </Link>
            </div>
            <div class="flex-shrink-0 flex flex-col items-center">
                <img :src="resolveImagePath(product.image_path)" :alt="product.name" class="w-24 h-24 md:w-32 md:h-32 object-cover rounded-md border border-orange-200"/>
                <p class="text-orange-600 font-bold mt-1 text-base md:text-lg">{{ formatCurrency(product.price) }}</p>
            </div>
        </article>
    </section>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

// Define props to receive data from Laravel controller
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
    },
    featuredProductNames: {
        type: Array,
        required: false,
        default: () => ['Biologische pompoen', 'Espresso Brownies', 'Red Velvet Muffins']
    }
});

// Computed property to filter products (if you want to show only specific featured products)
const filteredProducts = computed(() => {
    if (props.featuredProductNames.length === 0) {
        return props.products;
    }
    
    return props.products.filter(product => 
        props.featuredProductNames.includes(product.name)
    );
});

const resolveImagePath = (path) => {
    if (!path) return '';
    const cleanPath = path.replace(/^assets\//, '');
    return `/storage/${cleanPath}`;
};

const formatCurrency = (price) => 
    new Intl.NumberFormat('nl-NL', { style: 'currency', currency: 'EUR' }).format(price);
</script>