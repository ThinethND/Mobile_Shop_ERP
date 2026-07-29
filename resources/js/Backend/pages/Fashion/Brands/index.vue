<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import BrandList from './partials/BrandList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Fashion Brands" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Fashion Brands</h1>
        <p class="text-sm text-neutral-500">Manage brand names, logos, and active status for fashion products and accessories.</p>
      </div>

      <BrandList />
    </div>
  </AppLayout>
</template>
