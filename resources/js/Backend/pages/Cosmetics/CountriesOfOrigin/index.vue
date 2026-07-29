<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import CountryOfOriginList from './partials/CountryOfOriginList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Country of Origin" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Country of Origin</h1>
        <p class="text-sm text-neutral-500">Manage country entries and flag images.</p>
      </div>

      <CountryOfOriginList />
    </div>
  </AppLayout>
</template>

