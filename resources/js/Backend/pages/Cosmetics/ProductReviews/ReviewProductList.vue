<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import ProductList from './partials/ProductList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Product Reviews" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Product Reviews</h1>
        <p class="text-sm text-neutral-500">Pick a product, then add reviews or browse review lists.</p>
      </div>

      <ProductList />
    </div>
  </AppLayout>
</template>

