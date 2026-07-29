<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import MaterialList from './partials/MaterialList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Shoe Materials" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Shoe Materials</h1>
        <p class="text-sm text-neutral-500">Manage shoe material options for products.</p>
      </div>

      <MaterialList />
    </div>
  </AppLayout>
</template>