<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import ReviewList from './partials/ReviewList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Motorcycle Product Reviews" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Motorcycle Product Reviews</h1>
        <p class="text-sm text-neutral-500">Manage reviews for helmets, accessories, and spare parts.</p>
      </div>

      <ReviewList />
    </div>
  </AppLayout>
</template>
