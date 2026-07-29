<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import SubcategoryList from './partials/SubcategoryList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Shoe Subcategories" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Shoe Subcategories</h1>
        <p class="text-sm text-neutral-500">Manage subcategories mapped to shoe categories.</p>
      </div>

      <SubcategoryList />
    </div>
  </AppLayout>
</template>