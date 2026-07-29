<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import CosmeticBrandList from './partials/CosmeticBrandList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Cosmetic Brands" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Cosmetic Brands</h1>
        <p class="text-sm text-neutral-500">Manage cosmetic brand entries and brand media.</p>
      </div>

      <CosmeticBrandList />
    </div>
  </AppLayout>
</template>

