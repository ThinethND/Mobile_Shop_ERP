<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'
import ReviewList from './partials/ReviewList.vue'

type ProductPayload = {
  id: number
  name: string
  thumbnail_url?: string | null
  reviews_count: number
  reviews_avg_rating?: number | null
}

const props = defineProps<{
  product: ProductPayload
}>()

const avgDisplay = computed(() => {
  const avg = props.product.reviews_avg_rating
  if (avg === null || avg === undefined) return '-'
  return (Math.round(avg * 10) / 10).toFixed(1)
})
</script>

<template>
  <AppLayout>
    <Head :title="`Reviews - ${product.name}`" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
          <div
            class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl border border-neutral-200 bg-neutral-50"
          >
            <img v-if="product.thumbnail_url" :src="product.thumbnail_url" class="h-full w-full object-cover" />
            <span v-else class="text-xs text-neutral-400">No Image</span>
          </div>

          <div>
            <h1 class="text-2xl font-bold">{{ product.name }}</h1>
            <div class="text-sm text-neutral-500">
              <span class="font-medium text-neutral-700">{{ avgDisplay }}</span>
              / 5 • {{ product.reviews_count }} review{{ product.reviews_count === 1 ? '' : 's' }}
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
          <Link
            :href="route('admin.shoes.product-reviews.index')"
            class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto"
          >
            Back
          </Link>

          <Link
            :href="route('admin.shoes.product-reviews.reviews.create', product.id)"
            class="inline-flex w-full items-center justify-center rounded-full bg-[#38bdf8] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#7dd3fc] sm:w-auto"
          >
            + Add Review
          </Link>
        </div>
      </div>

      <ReviewList :product-id="product.id" />
    </div>
  </AppLayout>
</template>

