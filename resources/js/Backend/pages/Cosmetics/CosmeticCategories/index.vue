<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import CosmeticCategoryList from './partials/CosmeticCategoryList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Cosmetic Categories" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Cosmetic Categories</h1>
        <p class="text-sm text-neutral-500">Manage cosmetic categories and category slugs.</p>
      </div>

      <CosmeticCategoryList />
    </div>
  </AppLayout>
</template>

