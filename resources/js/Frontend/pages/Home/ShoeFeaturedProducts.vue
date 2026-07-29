<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

type FeaturedShoe = {
  id: number | string
  name: string
  slug?: string | null
  brand_name?: string | null
  thumbnail_url: string | null
  hover_image_url: string | null
  currency?: string | null
  regular_price: number | null
  sale_price: number | null
  display_price: number | null
  has_discount: boolean
  discount_label?: string | null
  is_sold_out: boolean
  reviews_count?: number | null
  reviews_avg_rating?: number | null
  status?: string | null
  stock_status?: string | null
}

const props = defineProps<{
  products?: FeaturedShoe[]
}>()

const sectionRef = ref<HTMLElement | null>(null)
const imageLoadTotal = ref(0)
const imageLoadDone = ref(0)
const loading = ref(false)
const hasLoadedOnce = ref(false)
const loadError = ref(false)
const loadedProducts = ref<FeaturedShoe[]>([])

let sectionObserver: IntersectionObserver | null = null

const visibleProducts = computed(() => loadedProducts.value.slice(0, 8))

function formatPrice(value: number | null | undefined) {
  if (value === null || typeof value === 'undefined' || Number.isNaN(Number(value))) {
    return 'Rs 0.00'
  }

  return `Rs ${Number(value).toLocaleString('en-LK', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}

function clampRating(value: number | null | undefined) {
  const rating = Number(value ?? 0)

  if (!Number.isFinite(rating)) {
    return 0
  }

  return Math.max(0, Math.min(5, rating))
}

function formatRating(value: number | null | undefined) {
  return clampRating(value).toFixed(1)
}

function starFillStyle(value: number | null | undefined, starNumber: number) {
  const rating = clampRating(value)
  const fill = Math.max(0, Math.min(1, rating - (starNumber - 1)))
  const unfilledPercent = (1 - fill) * 100

  return {
    clipPath: `inset(0 ${unfilledPercent}% 0 0)`,
  }
}

function ratingAriaLabel(value: number | null | undefined, count: number | null | undefined) {
  const rating = formatRating(value)
  const reviews = Number(count ?? 0)

  if (reviews > 0) {
    return `${rating} out of 5 stars based on ${reviews} reviews`
  }

  return `${rating} out of 5 stars`
}

function productHref(product: FeaturedShoe) {
  return route('frontend.shoe-products.show', {
    product: product.slug || product.id,
  })
}

function preloadImages() {
  const products = visibleProducts.value ?? []

  const urls = products
    .flatMap((product) => [product.thumbnail_url, product.hover_image_url])
    .filter((url): url is string => !!url)

  imageLoadTotal.value = urls.length
  imageLoadDone.value = 0

  if (!urls.length) {
    loading.value = false
    return
  }

  urls.forEach((url) => {
    const img = new Image()

    const markDone = () => {
      imageLoadDone.value += 1

      if (imageLoadDone.value >= imageLoadTotal.value) {
        loading.value = false
      }
    }

    img.onload = markDone
    img.onerror = markDone
    img.src = url
  })
}

async function fetchFeaturedShoes() {
  if (loading.value || hasLoadedOnce.value) return

  loading.value = true
  loadError.value = false

  try {
    const response = await fetch('/home/featured-shoes', {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })

    if (!response.ok) {
      throw new Error('Failed to fetch featured shoes')
    }

    const data = await response.json()
    loadedProducts.value = Array.isArray(data?.products) ? data.products : []
    hasLoadedOnce.value = true
    preloadImages()
  } catch (error) {
    console.error('Featured shoes fetch error:', error)
    loadError.value = true
    loadedProducts.value = []
    loading.value = false
  }
}

onMounted(() => {
  if ((props.products ?? []).length > 0) {
    loadedProducts.value = props.products ?? []
    hasLoadedOnce.value = true
    preloadImages()
    return
  }

  if ('IntersectionObserver' in window && sectionRef.value) {
    sectionObserver = new IntersectionObserver(
      (entries) => {
        const entry = entries[0]
        if (entry?.isIntersecting) {
          fetchFeaturedShoes()
          if (sectionObserver) {
            sectionObserver.disconnect()
            sectionObserver = null
          }
        }
      },
      {
        root: null,
        rootMargin: '220px 0px',
        threshold: 0.12,
      }
    )

    sectionObserver.observe(sectionRef.value)
  }
})

onBeforeUnmount(() => {
  if (sectionObserver) {
    sectionObserver.disconnect()
    sectionObserver = null
  }
})
</script>

<template>
  <section ref="sectionRef" class="mx-auto max-w-7xl px-3 py-8 sm:px-6 sm:py-12 lg:px-8">
    <div class="mb-5 flex items-end justify-between gap-3 sm:mb-7">
      <div>
        <h2 class="text-2xl font-semibold tracking-[-0.03em] text-gray-900 sm:text-4xl">
          Featured Footwear
        </h2>
        <p class="mt-1 text-sm text-neutral-500 sm:text-base">
         Handpicked shoes from our latest collection.
        </p>
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4 xl:gap-5">
      <div
        v-for="index in 8"
        :key="`shoe-skeleton-${index}`"
        class="overflow-hidden rounded-[20px] border border-neutral-200 bg-white shadow-sm sm:rounded-[24px]"
      >
        <div class="relative h-[180px] animate-pulse bg-white sm:h-[240px] xl:h-[260px]">
          <div class="absolute left-3 top-3 h-5 w-16 rounded bg-neutral-200 sm:left-4 sm:top-4 sm:h-6 sm:w-20" />
          <div class="absolute left-3 top-10 h-5 w-14 rounded bg-neutral-200 sm:left-4 sm:top-12 sm:h-6 sm:w-16" />
        </div>

        <div class="space-y-2 p-3 sm:space-y-3 sm:p-4">
          <div class="h-4 w-3/4 animate-pulse rounded bg-neutral-200 sm:h-5" />
          <div class="h-4 w-full animate-pulse rounded bg-neutral-100" />
          <div class="h-4 w-2/3 animate-pulse rounded bg-neutral-200" />
        </div>
      </div>
    </div>

    <div
      v-else-if="loadError"
      class="rounded-[24px] border border-red-200 bg-red-50 px-6 py-12 text-center"
    >
      <h3 class="text-lg font-semibold text-red-700">Failed to load featured shoes</h3>
      <p class="mt-2 text-sm text-red-500">
        Please scroll again or refresh the page.
      </p>
    </div>

    <div
      v-else-if="visibleProducts.length"
      class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4 xl:gap-5"
    >
      <Link
        v-for="product in visibleProducts"
        :key="product.id"
        :href="productHref(product)"
        class="block"
      >
        <article
          class="shoe-product-card group overflow-hidden rounded-[20px] border border-neutral-200 bg-white shadow-sm sm:rounded-[24px]"
        >
          <div class="relative overflow-hidden bg-white">
            <div class="absolute left-3 top-3 z-20 flex flex-col gap-1.5 sm:left-4 sm:top-4 sm:gap-2">
              <span
                v-if="product.has_discount && product.discount_label"
                class="inline-flex w-fit items-center rounded-md bg-[#ef5a4f] px-2.5 py-1 text-[10px] font-semibold text-white shadow-sm sm:px-3 sm:text-xs"
              >
                {{ product.discount_label }}
              </span>

              <span
                v-if="product.is_sold_out"
                class="inline-flex w-fit items-center rounded-md bg-[#bdbdbd] px-2.5 py-1 text-[10px] font-semibold text-white shadow-sm sm:px-3 sm:text-xs"
              >
                Sold Out
              </span>
            </div>

            <div class="absolute right-3 top-3 z-20 text-right sm:right-4 sm:top-4">
              <div class="text-[9px] font-semibold uppercase tracking-[0.14em] text-neutral-700 sm:text-xs sm:tracking-[0.16em]">
                {{ product.brand_name || 'Featured' }}
              </div>
              <div class="text-[9px] text-neutral-500 sm:text-[11px]">
                shoe collection
              </div>
            </div>

            <div class="relative flex h-[180px] items-center justify-center px-3 pt-10 pb-3 sm:h-[240px] sm:px-4 sm:pt-12 sm:pb-4 xl:h-[260px]">
              <img
                :src="product.thumbnail_url || product.hover_image_url || ''"
                :alt="`${product.name} - DezeStore`"
                class="shoe-main-image max-h-full max-w-full object-contain"
                :class="{ 'opacity-0': !product.thumbnail_url && !product.hover_image_url }"
              />

              <img
                v-if="product.hover_image_url"
                :src="product.hover_image_url"
                :alt="`${product.name} - DezeStore alternate view`"
                class="shoe-hover-image max-h-full max-w-full object-contain"
              />
            </div>
          </div>

          <div class="bg-white p-3 sm:p-4">
            <h3
              class="line-clamp-2 min-h-[40px] text-[14px] font-medium leading-snug text-neutral-900 sm:min-h-[46px] sm:text-[16px] xl:min-h-[50px] xl:text-[17px]"
            >
              {{ product.name }}
            </h3>

            <div class="mt-2 min-h-[23px] space-y-1 sm:mt-3 sm:min-h-[26px]">
              <p
                v-if="product.has_discount && product.regular_price !== null && product.display_price !== null"
                class="flex flex-wrap items-center gap-1.5 text-[12px] leading-5 sm:gap-2 sm:text-[14px] sm:leading-6"
              >
                <span class="font-semibold text-neutral-400 line-through">
                  {{ formatPrice(product.regular_price) }}
                </span>

                <span class="font-bold text-[#ef5a4f]">
                  {{ formatPrice(product.display_price) }}
                </span>
              </p>

              <p
                v-else
                class="text-[15px] font-bold text-neutral-900 sm:text-[17px]"
              >
                {{ formatPrice(product.display_price) }}
              </p>
            </div>

            <div class="mt-2 flex flex-col gap-1">
              <div class="min-h-[18px]">
                <div
                  v-if="clampRating(product.reviews_avg_rating) > 0"
                  class="product-rating"
                  :aria-label="ratingAriaLabel(product.reviews_avg_rating, product.reviews_count)"
                  role="img"
                >
                  <div class="product-rating-stars">
                    <span
                      v-for="starNumber in 5"
                      :key="`rating-star-${product.id}-${starNumber}`"
                      class="product-rating-star"
                      aria-hidden="true"
                    >
                      <svg
                        viewBox="0 0 24 24"
                        class="product-rating-star-base"
                      >
                        <path
                          d="M12 2.25l2.917 5.91 6.523.948-4.72 4.6 1.114 6.497L12 17.118 6.166 20.205l1.114-6.497-4.72-4.6 6.523-.948L12 2.25z"
                        />
                      </svg>

                      <span
                        class="product-rating-star-fill"
                        :style="starFillStyle(product.reviews_avg_rating, starNumber)"
                      >
                        <svg
                          viewBox="0 0 24 24"
                          class="product-rating-star-top"
                        >
                          <path
                            d="M12 2.25l2.917 5.91 6.523.948-4.72 4.6 1.114 6.497L12 17.118 6.166 20.205l1.114-6.497-4.72-4.6 6.523-.948L12 2.25z"
                          />
                        </svg>
                      </span>
                    </span>
                  </div>

                  <span class="product-rating-value">
                    {{ formatRating(product.reviews_avg_rating) }}
                    <span class="ml-1 text-[11px] font-medium text-slate-400">
                      ({{ product.reviews_count ?? 0 }})
                    </span>
                  </span>
                </div>
              </div>

              <div class="flex items-center gap-2 text-xs">
                <span
                  class="h-2 w-2 rounded-full"
                  :class="product.is_sold_out ? 'bg-red-500' : 'bg-emerald-500'"
                />
                <span :class="product.is_sold_out ? 'text-red-600' : 'text-emerald-600'">
                  {{ product.is_sold_out ? 'Out of stock' : 'In stock' }}
                </span>
              </div>
            </div>
          </div>
        </article>
      </Link>
    </div>

    <div class="mt-8 flex justify-center">
      <Link
        :href="route('frontend.shoe-products.index')"
        prefetch="mount"
        class="inline-flex items-center justify-center rounded-full border border-neutral-900 bg-neutral-900 px-7 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-neutral-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-neutral-900 focus-visible:ring-offset-2"
      >
        Explore All
      </Link>
    </div>
  </section>
</template>

<style scoped>
.shoe-product-card {
  transition:
    transform 0.4s ease,
    box-shadow 0.4s ease;
}

.shoe-product-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 18px 42px rgba(15, 23, 42, 0.1);
}

.shoe-main-image,
.shoe-hover-image {
  position: absolute;
  max-width: calc(100% - 1.5rem);
  max-height: calc(100% - 1.5rem);
  object-fit: contain;
  transition:
    opacity 0.4s ease,
    transform 0.4s ease;
}

.shoe-main-image {
  opacity: 1;
  transform: scale(1);
}

.shoe-hover-image {
  opacity: 0;
  transform: scale(1.02);
}

.shoe-product-card:hover .shoe-main-image {
  opacity: 0;
  transform: scale(1.01);
}

.shoe-product-card:hover .shoe-hover-image {
  opacity: 1;
  transform: scale(1);
}

.product-rating {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  min-height: 18px;
}

.product-rating-stars {
  display: inline-flex;
  align-items: center;
  gap: 2px;
  flex-shrink: 0;
  line-height: 0;
}

.product-rating-star {
  position: relative;
  display: inline-flex;
  width: 15px;
  height: 15px;
  flex: 0 0 15px;
}

.product-rating-star-base,
.product-rating-star-top {
  display: block;
  width: 100%;
  height: 100%;
}

.product-rating-star-base {
  color: #d1d5db;
  fill: currentColor;
}

.product-rating-star-fill {
  position: absolute;
  inset: 0;
  display: block;
  width: 100%;
  height: 100%;
}

.product-rating-star-top {
  color: #f2a536;
  fill: currentColor;
}

.product-rating-value {
  font-size: 13px;
  line-height: 1;
  font-weight: 700;
  color: #111827;
  letter-spacing: -0.02em;
  font-variant-numeric: tabular-nums;
}

@media (min-width: 640px) {
  .shoe-main-image,
  .shoe-hover-image {
    max-width: calc(100% - 2rem);
    max-height: calc(100% - 2rem);
  }

  .product-rating-star {
    width: 17px;
    height: 17px;
    flex-basis: 17px;
  }

  .product-rating-stars {
    gap: 3px;
  }

  .product-rating-value {
    font-size: 14px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .shoe-product-card,
  .shoe-main-image,
  .shoe-hover-image {
    transition: none !important;
    transform: none !important;
  }
}
</style>
