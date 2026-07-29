<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import BikeModelList from './partials/BikeModelList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Bike Brands & Models" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Bike Brands & Models</h1>
        <p class="text-sm text-neutral-500">Use this only for compatibility: which bike a product fits.</p>
      </div>

      <BikeModelList />
    </div>
  </AppLayout>
</template>
