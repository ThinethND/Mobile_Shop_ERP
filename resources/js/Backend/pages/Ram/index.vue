<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import RamList from './partials/RamList.vue'
import { route } from 'ziggy-js'
</script>

<template>
  <AppLayout>
    <Head title="RAM Options" />

    <div class="p-6 space-y-4">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">RAM Options</h1>
          <p class="text-sm text-neutral-500">Manage RAM values (GB) and status.</p>
        </div>

        <Link
          :href="route('ram.create')"
          class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-[#38bdf8] px-4 py-2 text-sm font-medium text-[#38bdf8] hover:bg-[#38bdf8] hover:text-white transition"
        >
          + New RAM
        </Link>
      </div>

      <RamList />
    </div>
  </AppLayout>
</template>
