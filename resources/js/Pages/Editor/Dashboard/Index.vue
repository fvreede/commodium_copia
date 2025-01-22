<script setup>
import EditorLayout from '@/Layouts/Editor/EditorLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Editor Dashboard" />
    <EditorLayout>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5">
            <div class="flex flex-col items-center justify-center text-center py-12 px-6 bg-white rounded-md shadow-sm">
                <h1 class="text-2xl font-bold">Dashboard</h1>
                <p>Welcome to your editor dashboard.</p>
            </div>
        </div>
        
        <div class="mt-4 grid grid-cols-12 gap-4 md:mt-6 md:gap-6 2xl:mt7.5 2xl:gap-7.5">
            <!-- Your dashboard components go here -->
            
        </div>
    </EditorLayout>
</template>