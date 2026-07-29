<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import TypeList from './partials/TypeList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Shoe Types" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Shoe Types</h1>
        <p class="text-sm text-neutral-500">Manage shoe type records for product setup.</p>
      </div>

      <TypeList />
    </div>
  </AppLayout>
</template>