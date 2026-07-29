<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import CategoryList from './partials/CategoryList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Fashion Categories" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Fashion Categories</h1>
        <p class="text-sm text-neutral-500">Manage groups for necklaces, bags, fashion wear, sunglasses, and related accessories.</p>
      </div>

      <CategoryList />
    </div>
  </AppLayout>
</template>
