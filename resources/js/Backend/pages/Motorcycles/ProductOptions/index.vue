<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import OptionList from './partials/OptionList.vue'

defineProps<{
  activeType: string
  optionTypes: Array<{ value: string; label: string }>
}>()
</script>

<template>
  <AppLayout>
    <Head title="Motorcycle Product Options" />

    <div class="space-y-4 p-6">
      <div>
        <h1 class="text-2xl font-bold">Motorcycle Product Options</h1>
        <p class="text-sm text-neutral-500">Manage helmet types, sizes, colors, accessory types, and part types in one simple tabbed page.</p>
      </div>

      <OptionList :active-type="activeType" :option-types="optionTypes" />
    </div>
  </AppLayout>
</template>
