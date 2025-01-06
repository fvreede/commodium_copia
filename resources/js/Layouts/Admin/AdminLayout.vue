<script setup>
import Navbar from '@/Components/Admin/Layout/Navbar.vue';
import Sidebar from '@/Components/Admin/Layout/Sidebar.vue';
import Footer from '@/Components/Admin/Layout/Footer.vue';
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <Navbar />
    <div class="flex">
      <Sidebar />
      <main class="flex-1 p-8">
        <slot></slot>
      </main>
    </div>
    <Footer />
  </div>
</template>