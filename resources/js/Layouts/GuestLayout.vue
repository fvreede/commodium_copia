<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-gray-50 pt-6 sm:pt-0"
    >
        <div>
            <Link href="/" class="transition-transform duration-300 hover:scale-105">
                <ApplicationLogo />
            </Link>
        </div>

        <div
            class="mt-6 w-full max-w-sm bg-white px-6 py-8 shadow-lg rounded-lg border border-gray-200"
        >
            <slot />
        </div>
    </div>
</template>
