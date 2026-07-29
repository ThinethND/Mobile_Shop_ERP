<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import CategoryList from './partials/CategoryList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Motorcycle Product Categories" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Motorcycle Product Categories</h1>
        <p class="text-sm text-neutral-500">Manage helmets, accessories, spare parts, riding gear, and subcategories.</p>
      </div>

      <CategoryList />
    </div>
  </AppLayout>
</template>
