<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import BrandList from './partials/BrandList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Shoe Brands" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Shoe Brands</h1>
        <p class="text-sm text-neutral-500">Manage shoe brand entries and brand media.</p>
      </div>

      <BrandList />
    </div>
  </AppLayout>
</template>