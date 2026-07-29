<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import SizeVolumeList from './partials/SizeVolumeList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Sizes / Volume" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Sizes / Volume</h1>
        <p class="text-sm text-neutral-500">Manage cosmetic size and volume options.</p>
      </div>

      <SizeVolumeList />
    </div>
  </AppLayout>
</template>

