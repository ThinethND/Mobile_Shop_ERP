<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import BrandList from './partials/BrandList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Helmet Brands" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Helmet Brands</h1>
        <p class="text-sm text-neutral-500">Helmet brands are used only for helmet products and helmet compatibility.</p>
      </div>

      <BrandList />
    </div>
  </AppLayout>
</template>
