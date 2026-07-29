<script setup lang="ts">
import { computed } from 'vue'
import { route } from 'ziggy-js'
import HomeProductCard from '@/Frontend/pages/Home/components/HomeProductCard.vue'

type CosmeticProductCard = {
  id: number | string
  name: string
  slug?: string | null
  brand_name?: string | null
  category_name?: string | null
  country_name?: string | null
  country_flag_url?: string | null
  thumbnail_url: string | null
  hover_image_url: string | null
  regular_price: number | null
  sale_price?: number | null
  display_price: number | null
  koko_pay_percentage?: number | null
  koko_installment_price?: number | null
  has_discount: boolean
  discount_label?: string | null
  is_sold_out: boolean
  reviews_count?: number | null
  reviews_avg_rating?: number | null
  url?: string | null
  sku?: string | null
  product_type?: string | null
}

const props = defineProps<{
  products: CosmeticProductCard[]
  loading: boolean
  loadError?: string | null
  categoryName?: string | null
}>()

const emit = defineEmits<{
  (e: 'retry'): void
}>()

const skeletonCount = 4

function productUrl(product: CosmeticProductCard) {
  if (product.url) return product.url

  return route('frontend.cosmetic-products.show', {
    product: product.slug || product.id,
  })
}

const relatedCards = computed(() => props.products.map((product) => ({
  ...product,
  hover_image_url: product.hover_image_url || product.thumbnail_url,
  product_type: product.product_type || 'cosmetics',
  url: productUrl(product),
})))
</script>

<template>
  <section class="mx-auto max-w-7xl px-4 pt-14 pb-16 sm:px-6 sm:pt-16 sm:pb-18 lg:px-8 lg:pt-20 lg:pb-24">
    <div class="mb-6 sm:mb-8">
      <p
        v-if="categoryName"
        class="text-[11px] font-semibold uppercase tracking-[0.18em] text-neutral-500"
      >
        More from {{ categoryName }}
      </p>

      <h2 class="mt-2 text-2xl font-semibold tracking-[-0.02em] text-neutral-950 sm:text-3xl">
        You may also like
      </h2>
    </div>

    <div
      v-if="loadError"
      class="rounded-2xl border border-red-200 bg-red-50 px-6 py-12 text-center"
    >
      <h3 class="text-lg font-semibold text-red-700">
        Failed to load products
      </h3>
      <p class="mt-2 text-sm text-red-500">
        Please try again.
      </p>

      <button
        type="button"
        class="mt-5 inline-flex items-center justify-center rounded-lg bg-[#071f4f] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#0b2b62]"
        @click="emit('retry')"
      >
        Retry
      </button>
    </div>

    <template v-else>
      <div
        v-if="!loading && !relatedCards.length"
        class="rounded-2xl border border-neutral-200 bg-white px-6 py-14 text-center"
      >
        <h3 class="text-xl font-semibold text-neutral-900">
          No related products found
        </h3>
        <p class="mt-2 text-sm text-neutral-500">
          More cosmetics from this category will appear here.
        </p>
      </div>

      <div
        v-else
        class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-4 lg:gap-5"
      >
        <template v-if="loading">
          <HomeProductCard
            v-for="index in skeletonCount"
            :key="`related-cosmetic-skeleton-${index}`"
            skeleton
          />
        </template>

        <template v-else>
          <HomeProductCard
            v-for="product in relatedCards"
            :key="product.id"
            :product="product"
          />
        </template>
      </div>
    </template>
  </section>
</template>
