<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import SizeTypeList from './partials/SizeTypeList.vue'
</script>

<template>
  <AppLayout>
    <Head title="Shoe Size Types" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Shoe Size Types</h1>
        <p class="text-sm text-neutral-500">Manage supported size systems like UK, US, and EU.</p>
      </div>

      <SizeTypeList />
    </div>
  </AppLayout>
</template>