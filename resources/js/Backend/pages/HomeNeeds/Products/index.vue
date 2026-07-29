<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import ProductList from './partials/ProductList.vue'
import { route } from 'ziggy-js'

type Option = { id: number; name: string; value: number; label: string }

defineProps<{
  categories: Option[]
  brands: Option[]
  warranties: Option[]
}>()
</script>

<template>
  <AppLayout>
    <Head title="Home Need Products" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">Home Need Products</h1>
          <p class="text-sm text-neutral-500">Add and manage household items, kitchen goods, cleaning supplies, storage, decor, and daily essentials.</p>
        </div>

        <Link
          :href="route('admin.home-needs.products.create')"
          class="inline-flex w-full items-center justify-center rounded-full border border-[#38bdf8] px-4 py-2 text-sm font-medium text-[#38bdf8] transition hover:bg-[#38bdf8] hover:text-white sm:w-auto"
        >
          + Add Home Need Product
        </Link>
      </div>

      <ProductList :categories="categories" :brands="brands" :warranties="warranties" />
    </div>
  </AppLayout>
</template>
