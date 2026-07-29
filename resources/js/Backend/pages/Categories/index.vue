<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import CategoryList from './partials/CategoryList.vue'

defineProps<{
  message?: string
}>()
</script>

<template>
  <AppLayout>
    <Head title="Categories" />

    <div class="p-6 space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold">Categories</h1>
          <p class="text-sm text-neutral-500">
            Manage category name, image, and status.
          </p>
        </div>
      </div>

      <CategoryList />
    </div>
  </AppLayout>
</template>