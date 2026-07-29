<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import ProductList from './partials/ProductList.vue'

defineProps<{
  activeType?: string | null
  productTypes: Array<{ value: string; label: string }>
}>()
</script>

<template>
  <AppLayout>
    <Head title="Motorcycle Products" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Motorcycle Products</h1>
        <p class="text-sm text-neutral-500">Add helmets, helmet accessories, bike accessories, and spare parts without mixing brand meanings.</p>
      </div>

      <ProductList :active-type="activeType" :product-types="productTypes" />
    </div>
  </AppLayout>
</template>
