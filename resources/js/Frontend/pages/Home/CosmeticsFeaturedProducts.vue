<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { route } from 'ziggy-js'

type BrandItem = {
  id: number | string
  name: string
  slug?: string | null
  logo_url?: string | null
}

type CosmeticProductCard = {
  id: number | string
  name: string
  slug?: string | null
  brand_name?: string | null
  category_name?: string | null
  thumbnail_url: string | null
  hover_image_url: string | null
  regular_price: number | null
  display_price: number | null
  has_discount: boolean
  discount_label?: string | null
  is_sold_out: boolean
  reviews_count?: number | null
  reviews_avg_rating?: number | null
  country_name?: string | null
  country_flag_url?: string | null
  url?: string | null
}

const sectionRef = ref<HTMLElement | null>(null)
const loadingBrands = ref(true)
const loadingProducts = ref(false)
const brandsLoaded = ref<BrandItem[]>([])
const productsLoaded = ref<CosmeticProductCard[]>([])
const selectedBrand = ref<string | null>(null)
const hasLoadedProductsOnce = ref(false)
const loadError = ref(false)
const brandLoadError = ref(false)

const imageLoadTotal = ref(0)
const imageLoadDone = ref(0)

let sectionObserver: IntersectionObserver | null = null
let delayedLoadTimer: number | null = null

const navBrands = computed(() => brandsLoaded.value)
const visibleProducts = computed(() => productsLoaded.value.slice(0, 8))
const showSkeletons = computed(() => loadingProducts.value)

function normalizeName(value: string | null | undefined) {
  return String(value ?? '').trim().toLowerCase()
}

function isActiveNav(name?: string | null) {
  return normalizeName(selectedBrand.value) === normalizeName(name)
}

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

function productUrl(product: CosmeticProductCard) {
  return product.url || route('frontend.cosmetic-products.show', {
    product: product.slug || product.id,
  })
}

function updateUrl(brand?: string | null) {
  if (typeof window === 'undefined') return

  const url = new URL(window.location.href)

  if (brand && String(brand).trim() !== '') {
    url.searchParams.set('cosmetic_brand', brand)
  } else {
    url.searchParams.delete('cosmetic_brand')
  }

  url.hash = 'cosmetics-products-section'
  window.history.replaceState({}, '', url.toString())
}

function scrollToSection() {
  sectionRef.value?.scrollIntoView({
    behavior: 'smooth',
    block: 'start',
  })
}

function preloadImages() {
  const urls = visibleProducts.value
    .flatMap((product) => [product.thumbnail_url, product.hover_image_url, product.country_flag_url])
    .filter((url): url is string => !!url)

  imageLoadTotal.value = urls.length
  imageLoadDone.value = 0

  if (!urls.length) {
    loadingProducts.value = false
    return
  }

  urls.forEach((url) => {
    const img = new Image()

    const markDone = () => {
      imageLoadDone.value += 1
      if (imageLoadDone.value >= imageLoadTotal.value) {
        loadingProducts.value = false
      }
    }

    img.onload = markDone
    img.onerror = markDone
    img.src = url
  })
}

async function fetchBrands() {
  loadingBrands.value = true
  brandLoadError.value = false

  try {
    const response = await fetch('/home/cosmetic-brands', {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })

    if (!response.ok) {
      throw new Error('Failed to fetch brands')
    }

    const data = await response.json()
    brandsLoaded.value = Array.isArray(data?.brands) ? data.brands : []
  } catch (error) {
    console.error('Cosmetic brands fetch error:', error)
    brandLoadError.value = true
    brandsLoaded.value = []
  } finally {
    loadingBrands.value = false
  }
}

async function fetchProducts(brand?: string | null) {
  loadingProducts.value = true
  loadError.value = false

  try {
    const url = new URL('/home/cosmetic-products', window.location.origin)

    if (brand && String(brand).trim() !== '') {
      url.searchParams.set('brand', brand)
    }

    const response = await fetch(url.toString(), {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })

    if (!response.ok) {
      throw new Error('Failed to fetch products')
    }

    const data = await response.json()
    productsLoaded.value = Array.isArray(data?.products) ? data.products : []
    hasLoadedProductsOnce.value = true
    preloadImages()
  } catch (error) {
    console.error('Cosmetic products fetch error:', error)
    loadError.value = true
    productsLoaded.value = []
    loadingProducts.value = false
  }
}

async function ensureProductsLoaded() {
  if (hasLoadedProductsOnce.value || loadingProducts.value) return
  await fetchProducts(selectedBrand.value)
}

async function onBrandClick(brand?: string | null) {
  selectedBrand.value = brand ?? null
  updateUrl(selectedBrand.value)
  scrollToSection()
  await nextTick()
  await fetchProducts(selectedBrand.value)
}

onMounted(async () => {
  if (typeof window !== 'undefined') {
    const url = new URL(window.location.href)
    selectedBrand.value = url.searchParams.get('cosmetic_brand') || null
  }

  await fetchBrands()

  delayedLoadTimer = window.setTimeout(() => {
    ensureProductsLoaded()
  }, 1400)

  if ('IntersectionObserver' in window && sectionRef.value) {
    sectionObserver = new IntersectionObserver(
      (entries) => {
        const entry = entries[0]
        if (entry?.isIntersecting) {
          ensureProductsLoaded()
        }
      },
      {
        root: null,
        rootMargin: '200px 0px',
        threshold: 0.1,
      }
    )

    sectionObserver.observe(sectionRef.value)
  }

  if (window.location.hash === '#cosmetics-products-section') {
    setTimeout(() => {
      scrollToSection()
    }, 260)
  }
})

onBeforeUnmount(() => {
  sectionObserver?.disconnect()
  sectionObserver = null

  if (delayedLoadTimer) {
    window.clearTimeout(delayedLoadTimer)
    delayedLoadTimer = null
  }
})
</script>

<template>
  <section
    id="cosmetics-products-section"
    ref="sectionRef"
    class="mx-auto max-w-7xl px-3 py-8 sm:px-6 sm:py-12 lg:px-8"
  >
    <div class="mb-5 flex flex-col gap-4 sm:mb-7">
      <div>
        <h2 class="text-2xl font-semibold tracking-[-0.03em] text-gray-900 sm:text-4xl">
          Cosmetics Collection
        </h2>
        <p class="mt-1 text-sm text-neutral-500 sm:text-base">
          Explore skincare and beauty essentials 
        </p>
      </div>

      <div class="overflow-x-auto pb-1 no-scrollbar-mobile">
        <div class="inline-flex min-w-full gap-2 sm:gap-3">
          <button
            type="button"
            class="whitespace-nowrap rounded-full border px-4 py-2 text-sm font-semibold transition"
            :class="!selectedBrand
              ? 'border-black bg-black text-white'
              : 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100'"
            @click="onBrandClick(null)"
          >
            All
          </button>

          <template v-if="!loadingBrands && navBrands.length">
            <button
              v-for="brand in navBrands"
              :key="brand.id"
              type="button"
              class="whitespace-nowrap rounded-full border px-4 py-2 text-sm font-semibold transition"
              :class="isActiveNav(brand.name)
                ? 'border-black bg-black text-white'
                : 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100'"
              @click="onBrandClick(brand.name)"
            >
              {{ brand.name }}
            </button>
          </template>

          <template v-else-if="loadingBrands">
            <span
              v-for="index in 4"
              :key="`brand-pill-${index}`"
              class="inline-block h-[40px] w-[120px] animate-pulse rounded-full border border-neutral-200 bg-neutral-100"
            />
          </template>
        </div>
      </div>
    </div>

    <div
      v-if="showSkeletons"
      class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4 xl:gap-5"
    >
      <div
        v-for="index in 8"
        :key="`cosmetic-skeleton-${index}`"
        class="overflow-hidden rounded-[20px] border border-neutral-200 bg-white shadow-sm sm:rounded-[24px]"
      >
        <div class="relative h-[180px] animate-pulse bg-white sm:h-[240px] xl:h-[260px]">
          <div class="absolute left-3 top-3 h-5 w-16 rounded bg-neutral-200 sm:left-4 sm:top-4 sm:h-6 sm:w-20" />
          <div class="absolute right-3 top-3 h-4 w-14 rounded bg-neutral-200 sm:right-4 sm:top-4 sm:w-20" />
        </div>

        <div class="space-y-2 p-3 sm:space-y-3 sm:p-4">
          <div class="h-4 w-4/5 animate-pulse rounded bg-neutral-200 sm:h-5" />
          <div class="h-4 w-2/3 animate-pulse rounded bg-neutral-100 sm:h-5" />
          <div class="h-4 w-1/2 animate-pulse rounded bg-neutral-200 sm:h-5" />
        </div>
      </div>
    </div>

    <div
      v-else-if="loadError"
      class="rounded-[24px] border border-red-200 bg-red-50 px-6 py-12 text-center"
    >
      <h3 class="text-lg font-semibold text-red-700">Failed to load cosmetics</h3>
      <p class="mt-2 text-sm text-red-500">
        Please try again.
      </p>
    </div>

    <div
      v-else-if="visibleProducts.length"
      class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4 xl:gap-5"
    >
      <Link
        v-for="product in visibleProducts"
        :key="product.id"
        :href="productUrl(product)"
        class="product-card group block overflow-hidden rounded-[20px] border border-neutral-200 bg-white shadow-sm outline-none focus-visible:ring-2 focus-visible:ring-neutral-900 sm:rounded-[24px]"
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
              {{ product.brand_name || 'Brand' }}
            </div>
            <div class="text-[9px] text-neutral-500 sm:text-[11px]">
              cosmetic collection
            </div>
          </div>

          <div class="relative flex h-[180px] items-center justify-center px-3 pb-3 pt-10 sm:h-[240px] sm:px-4 sm:pb-4 sm:pt-12 xl:h-[260px]">
            <img
              :src="product.thumbnail_url || product.hover_image_url || ''"
              :alt="`${product.name} - DezeStore`"
              class="product-main-image max-h-full max-w-full object-contain"
              :class="{ 'opacity-0': !product.thumbnail_url && !product.hover_image_url }"
            />

            <img
              v-if="product.hover_image_url"
              :src="product.hover_image_url"
              :alt="`${product.name} - DezeStore alternate view`"
              class="product-hover-image max-h-full max-w-full object-contain"
            />

            <img
              v-if="product.country_flag_url"
              :src="product.country_flag_url"
              :alt="product.country_name ? `${product.country_name} flag` : 'Country flag'"
              class="absolute bottom-3 right-3 z-20 h-6 w-9 object-contain sm:bottom-4 sm:right-4 sm:h-7 sm:w-10"
              loading="lazy"
              decoding="async"
            />
          </div>
        </div>

        <div class="bg-white p-3 sm:p-4">
          <h3 class="line-clamp-2 text-[14px] font-medium leading-snug text-neutral-900 sm:text-[16px] xl:text-[17px]">
            {{ product.name }}
          </h3>

          <div class="mt-2 space-y-1 sm:mt-3">
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

          <div class="mt-2 flex flex-col gap-1.5">
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
      </Link>
    </div>

    <div
      v-else
      class="rounded-[24px] border border-dashed border-neutral-300 bg-white px-6 py-12 text-center"
    >
      <h3 class="text-lg font-semibold text-neutral-900">No cosmetics found</h3>
      <p class="mt-2 text-sm text-neutral-500">
        Try another brand to see matching products.
      </p>
    </div>

    <div class="mt-8 flex justify-center">
      <Link
        :href="route('frontend.cosmetic-products.index', {
          cosmetic_brand: selectedBrand || undefined,
        })"
        prefetch="mount"
        class="inline-flex items-center justify-center rounded-full border border-neutral-900 bg-neutral-900 px-7 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-neutral-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-neutral-900 focus-visible:ring-offset-2"
      >
        Explore All
      </Link>
    </div>
  </section>
</template>

<style scoped>
.product-card {
  transition:
    transform 0.4s ease,
    box-shadow 0.4s ease;
}

.product-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 18px 42px rgba(15, 23, 42, 0.1);
}

.product-main-image,
.product-hover-image {
  position: absolute;
  max-width: calc(100% - 1.5rem);
  max-height: calc(100% - 1.5rem);
  object-fit: contain;
  transition:
    opacity 0.4s ease,
    transform 0.4s ease;
}

.product-main-image {
  opacity: 1;
  transform: scale(1);
}

.product-hover-image {
  opacity: 0;
  transform: scale(1.02);
}

.product-card:hover .product-main-image {
  opacity: 0;
  transform: scale(1.01);
}

.product-card:hover .product-hover-image {
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
  .product-main-image,
  .product-hover-image {
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
  .product-card,
  .product-main-image,
  .product-hover-image {
    transition: none !important;
    transform: none !important;
  }
}
</style>
