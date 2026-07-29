<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import CategoryList from './partials/CategoryList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Shoe Categories" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Shoe Categories</h1>
        <p class="text-sm text-neutral-500">Manage top-level shoe categories.</p>
      </div>

      <CategoryList />
    </div>
  </AppLayout>
</template>