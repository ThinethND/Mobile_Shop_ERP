<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import ProductList from './partials/ProductList.vue'
import { route } from 'ziggy-js'
</script>

<template>
  <AppLayout>
    <Head title="Electronics & Gadgets" />

    <div class="p-6 space-y-4">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">Electronics & Gadgets</h1>
          <p class="text-sm text-neutral-500">Manage mobile phones, watches, AirPods, analog watches, and accessories.</p>
        </div>

        <Link
          :href="route('products.create')"
          class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-[#38bdf8] px-4 py-2 text-sm font-medium text-[#38bdf8] hover:bg-[#38bdf8] hover:text-white transition"
        >
          + New Electronics Product
        </Link>
      </div>

      <ProductList />
    </div>
  </AppLayout>
</template>
