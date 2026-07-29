<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import ColorList from './partials/ColorList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Shoe Colors" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Shoe Colors</h1>
        <p class="text-sm text-neutral-500">Manage shoe color options for product assignment.</p>
      </div>

      <ColorList />
    </div>
  </AppLayout>
</template>