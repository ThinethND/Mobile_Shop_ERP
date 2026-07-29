<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import CategoryList from './partials/CategoryList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Home Need Categories" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Home Need Categories</h1>
        <p class="text-sm text-neutral-500">Manage household product groups like kitchen, cleaning, storage, decor, and daily essentials.</p>
      </div>

      <CategoryList />
    </div>
  </AppLayout>
</template>
