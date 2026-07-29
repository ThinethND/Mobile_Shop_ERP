<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import ProductList from './partials/ProductList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Shoe Products" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Shoe Products</h1>
        <p class="text-sm text-neutral-500">Manage shoe product setup and merchandising fields.</p>
      </div>

      <ProductList />
    </div>
  </AppLayout>
</template>