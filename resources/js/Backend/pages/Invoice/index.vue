<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import InvoiceList from './partials/InvoiceList.vue'

defineProps<{
  message?: string
}>()
</script>

<template>
  <AppLayout>
    <Head title="Orders" />

    <div class="p-6 space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold">Orders</h1>
          <p class="text-sm text-neutral-500">
            Manage website orders, invoice records, PDFs, and status emails.
          </p>
        </div>
      </div>

      <InvoiceList />
    </div>
  </AppLayout>
</template>
