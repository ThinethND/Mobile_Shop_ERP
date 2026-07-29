<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import CosmeticProductList from './partials/CosmeticProductList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Cosmetic Products" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Cosmetic Products</h1>
        <p class="text-sm text-neutral-500">Manage cosmetic products, pricing, inventory, and media.</p>
      </div>

      <CosmeticProductList />
    </div>
  </AppLayout>
</template>

