<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import ProductTypeList from './partials/ProductTypeList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Fashion Product Types" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Fashion Product Types</h1>
        <p class="text-sm text-neutral-500">Create reusable types like necklaces, bags, fashion wear, sunglasses, belts, and scarves.</p>
      </div>

      <ProductTypeList />
    </div>
  </AppLayout>
</template>
