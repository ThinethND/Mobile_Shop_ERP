<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { Link } from '@inertiajs/vue3'

type ProductColor = {
  id: number | string
  name: string
  color_code?: string | null
  image_url?: string | null
}

type ProductItem = {
  id: number | string
  name: string
  category_name?: string | null
  brand_name?: string | null
  country_name?: string | null
  country_flag_url?: string | null
  thumbnail_url: string | null
  hover_image_url: string | null
  regular_price: number | null
  display_price: number | null
  has_discount: boolean
  discount_label?: string | null
  is_sold_out: boolean
  reviews_count?: number | null
  reviews_avg_rating?: number | null
  colors?: ProductColor[]
  url?: string | null
}

const props = withDefaults(
  defineProps<{
    endpoint: string
    title?: string
    subtitle?: string
    activeCategory?: string | null
    activeBrand?: string | null
    search?: string | null
    exploreHref?: string
    labelMode?: 'category' | 'brand'
    cardTagline?: string
    theme?: 'blue' | 'rose'
    containerClass?: string
  }>(),
  {
    title: 'Trending Now',
    subtitle:
      'Discover our featured technology picks, selected for standout design, reliable performance, and everyday value.',
    activeCategory: null,
    activeBrand: null,
    search: null,
    exploreHref: '/tech-products?featured=1',
    labelMode: 'category',
    cardTagline: 'featured pick',
    theme: 'blue',
    containerClass: 'mx-auto max-w-7xl px-3 sm:px-6 lg:px-8',
  },
)

const sectionRef = ref<HTMLElement | null>(null)
const scrollerRef = ref<HTMLElement | null>(null)

const products = ref<ProductItem[]>([])
const isLoading = ref(false)
const hasLoaded = ref(false)
const fetchError = ref('')
const isHovered = ref(false)

const skeletons = [1, 2, 3]

const CARD_GAP = 14
const AUTO_SLIDE_DELAY = 2600

let observer: IntersectionObserver | null = null
let autoSlideTimer: number | null = null
let snapTimer: number | null = null
let resizeTimer: number | null = null

const hasProducts = computed(() => products.value.length > 0)
const hasMultipleProducts = computed(() => products.value.length > 1)

const repeatedProducts = computed(() => {
  if (products.value.length <= 1) return products.value
  return [...products.value, ...products.value, ...products.value]
})

const endpointUrl = computed(() => {
  const url = new URL(props.endpoint, window.location.origin)

  if (props.activeCategory) url.searchParams.set('category', props.activeCategory)
  if (props.activeBrand) url.searchParams.set('brand', props.activeBrand)
  if (props.search) url.searchParams.set('search', props.search)

  return url.toString()
})

const themeVars = computed<Record<string, string>>(() => {
  if (props.theme !== 'rose') return {}

  return {
    '--featured-accent': '#e11d48',
    '--featured-accent-deep': '#9f1239',
    '--featured-banner-start': 'rgba(159, 18, 57, 0.95)',
    '--featured-banner-end': 'rgba(136, 19, 55, 0.96)',
    '--featured-banner-highlight': 'rgba(251, 113, 133, 0.22)',
    '--featured-banner-shadow': 'rgba(159, 18, 57, 0.2)',
    '--featured-banner-overlay-weak': 'rgba(45, 5, 20, 0.1)',
    '--featured-banner-overlay-strong': 'rgba(45, 5, 20, 0.36)',
    '--featured-button-start': '#fda4af',
    '--featured-button-end': '#fb7185',
    '--featured-button-text': '#881337',
    '--featured-button-shadow': 'rgba(251, 113, 133, 0.32)',
    '--featured-glow-one': 'rgba(251, 113, 133, 0.18)',
  }
})

function goToFeaturedList() {
  if (typeof window === 'undefined') return

  const href = String(props.exploreHref ?? '').trim()
  if (!href) return

  if (href.startsWith('#')) {
    document.querySelector(href)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    return
  }

  window.location.href = href
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

function normalizeName(value: string | null | undefined) {
  return String(value ?? '').trim().toLowerCase()
}

function normalizedDiscount(label?: string | null) {
  if (!label) return null

  return label
    .replace(/^sale\s*/i, '')
    .replace(/^rs\s*/i, 'Rs ')
    .replace(/^lkr\s*/i, 'Rs ')
    .trim()
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

const colorFallbackMap: Record<string, string> = {
  black: '#111111',
  white: '#ffffff',
  red: '#b91c1c',
  blue: '#1d4ed8',
  green: '#15803d',
  yellow: '#eab308',
  gold: '#c9a227',
  silver: '#c0c0c0',
  gray: '#9ca3af',
  grey: '#9ca3af',
  pink: '#ec4899',
  purple: '#7c3aed',
  orange: '#ea580c',
  brown: '#7c4a2d',
  beige: '#d6c2a1',
  cream: '#f3eadb',
  graphite: '#4b5563',
  midnight: '#1f2937',
  starlight: '#f5deb3',
}

function colorSwatchStyle(color: ProductColor) {
  if (color.color_code && /^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/.test(color.color_code)) {
    return {
      backgroundColor: color.color_code,
    }
  }

  const key = normalizeName(color.name)
  return {
    backgroundColor: colorFallbackMap[key] || '#d4d4d8',
  }
}

function productUrl(product: ProductItem) {
  return product.url || `/tech-products/${product.id}`
}

function topLabel(product: ProductItem) {
  if (props.labelMode === 'brand') {
    return product.brand_name || product.category_name || 'Brand'
  }

  return product.category_name || product.brand_name || 'Category'
}

function buildSixProducts(items: ProductItem[]) {
  const source = items.slice(0, 6)

  if (!source.length) return []

  const padded = [...source]

  let index = 0
  while (padded.length < 6) {
    padded.push(source[index % source.length])
    index += 1
  }

  return padded
}

function getSlides() {
  const el = scrollerRef.value
  if (!el) return []
  return Array.from(el.querySelectorAll('.featured-slide')) as HTMLElement[]
}

function getStepSize() {
  const slides = getSlides()

  if (slides.length >= 2) {
    return slides[1].offsetLeft - slides[0].offsetLeft
  }

  if (slides.length === 1) {
    return slides[0].offsetWidth + CARD_GAP
  }

  return 260
}

function getSegmentWidth() {
  if (!products.value.length) return 0
  return getStepSize() * products.value.length
}

function resetLoopPosition() {
  const el = scrollerRef.value
  if (!el || !hasMultipleProducts.value) return

  el.scrollLeft = getSegmentWidth()
}

function normalizeLoopPosition() {
  const el = scrollerRef.value
  if (!el || !hasMultipleProducts.value) return

  const step = getStepSize()
  const segment = getSegmentWidth()

  if (!segment || !step) return

  if (el.scrollLeft < segment - step * 0.5) {
    el.scrollLeft += segment
  } else if (el.scrollLeft >= segment * 2 - step * 0.5) {
    el.scrollLeft -= segment
  }
}

function snapToNearest(smooth = true) {
  const el = scrollerRef.value
  if (!el) return

  const step = getStepSize()
  if (!step) return

  const target = Math.round(el.scrollLeft / step) * step

  el.scrollTo({
    left: target,
    behavior: smooth ? 'smooth' : 'auto',
  })

  window.setTimeout(() => {
    normalizeLoopPosition()
  }, smooth ? 380 : 0)
}

function scrollCards(direction: 'left' | 'right', smooth = true) {
  const el = scrollerRef.value
  if (!el || !hasMultipleProducts.value) return

  const step = getStepSize()

  el.scrollBy({
    left: direction === 'right' ? step : -step,
    behavior: smooth ? 'smooth' : 'auto',
  })

  window.setTimeout(() => {
    normalizeLoopPosition()
    snapToNearest(false)
  }, smooth ? 420 : 0)
}

function handleTrackScroll() {
  if (snapTimer !== null) {
    window.clearTimeout(snapTimer)
    snapTimer = null
  }

  snapTimer = window.setTimeout(() => {
    normalizeLoopPosition()
    snapToNearest(true)
  }, 120)
}

function stopAutoSlide() {
  if (autoSlideTimer !== null) {
    window.clearInterval(autoSlideTimer)
    autoSlideTimer = null
  }
}

function startAutoSlide() {
  stopAutoSlide()

  if (typeof window === 'undefined') return
  if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) return
  if (!hasMultipleProducts.value) return

  autoSlideTimer = window.setInterval(() => {
    if (document.hidden || isHovered.value) return
    scrollCards('right')
  }, AUTO_SLIDE_DELAY)
}

async function loadFeaturedProducts() {
  if (isLoading.value || hasLoaded.value) return

  isLoading.value = true
  fetchError.value = ''

  try {
    const response = await fetch(endpointUrl.value, {
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
    })

    if (!response.ok) {
      throw new Error('Unable to load featured products.')
    }

    const payload = await response.json()
    const loadedProducts = Array.isArray(payload.products) ? buildSixProducts(payload.products) : []

    products.value = loadedProducts
    hasLoaded.value = true

    await nextTick()

    if (hasMultipleProducts.value) {
      resetLoopPosition()
      snapToNearest(false)
      startAutoSlide()
    }
  } catch (error) {
    fetchError.value =
      error instanceof Error ? error.message : 'Something went wrong while loading featured products.'
  } finally {
    isLoading.value = false
  }
}

function initObserver() {
  if (!sectionRef.value) return

  if (!('IntersectionObserver' in window)) {
    loadFeaturedProducts()
    return
  }

  observer = new IntersectionObserver(
    (entries) => {
      const entry = entries[0]
      if (!entry?.isIntersecting) return

      loadFeaturedProducts()
      observer?.disconnect()
      observer = null
    },
    {
      root: null,
      threshold: 0.12,
      rootMargin: '180px 0px',
    },
  )

  observer.observe(sectionRef.value)
}

function handleResize() {
  if (resizeTimer !== null) {
    window.clearTimeout(resizeTimer)
    resizeTimer = null
  }

  resizeTimer = window.setTimeout(() => {
    if (!hasLoaded.value) return

    nextTick(() => {
      if (hasMultipleProducts.value) {
        resetLoopPosition()
        snapToNearest(false)
      }
    })
  }, 120)
}

function handleVisibilityChange() {
  if (document.hidden) {
    stopAutoSlide()
    return
  }

  if (hasLoaded.value && hasMultipleProducts.value) {
    startAutoSlide()
  }
}

onMounted(() => {
  initObserver()
  window.addEventListener('resize', handleResize)
  document.addEventListener('visibilitychange', handleVisibilityChange)
})

onBeforeUnmount(() => {
  observer?.disconnect()
  observer = null
  stopAutoSlide()

  if (snapTimer !== null) {
    window.clearTimeout(snapTimer)
    snapTimer = null
  }

  if (resizeTimer !== null) {
    window.clearTimeout(resizeTimer)
    resizeTimer = null
  }

  window.removeEventListener('resize', handleResize)
  document.removeEventListener('visibilitychange', handleVisibilityChange)
})
</script>
<!--kushan-->

<template>
  <section
    ref="sectionRef"
    class="featured-products-section"
    :style="themeVars"
  >
    <div :class="props.containerClass">
      <div class="featured-shell">
        <div class="featured-banner">
          <div class="featured-banner__content">
            <span class="featured-banner__eyebrow">LIMITED PICKS</span>

            <h2 class="featured-banner__title">
              {{ title }}
            </h2>

            <p class="featured-banner__text">
              {{ subtitle }}
            </p>

            <button
              type="button"
              class="featured-banner__button"
              @click="goToFeaturedList"
            >
              Explore Now
            </button>
          </div>

          <div class="featured-banner__glow featured-banner__glow--one" />
          <div class="featured-banner__glow featured-banner__glow--two" />
        </div>

        <div
          class="featured-products-panel"
          @mouseenter="isHovered = true"
          @mouseleave="isHovered = false"
        >
          <div class="featured-products-panel__header">
            <div>
              <!-- <h3 class="featured-products-panel__title">Featured Collection</h3> -->
              <p class="featured-products-panel__subtitle">
                <!-- 3 cards visible, 6 total cards, smooth infinite one-by-one carousel. -->
              </p>
            </div>

            <div class="featured-products-panel__nav">
              <button
                type="button"
                class="featured-nav-btn"
                :disabled="!hasMultipleProducts"
                aria-label="Previous featured products"
                @click="scrollCards('left')"
              >
                <svg viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M15.25 19.25L8 12l7.25-7.25" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" />
                </svg>
              </button>

              <button
                type="button"
                class="featured-nav-btn"
                :disabled="!hasMultipleProducts"
                aria-label="Next featured products"
                @click="scrollCards('right')"
              >
                <svg viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M8.75 19.25L16 12 8.75 4.75" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" />
                </svg>
              </button>
            </div>
          </div>

          <div
            ref="scrollerRef"
            class="featured-products-track"
            @scroll="handleTrackScroll"
          >
            <template v-if="isLoading">
              <div
                v-for="item in skeletons"
                :key="item"
                class="featured-slide overflow-hidden rounded-[20px] border border-neutral-200 bg-white sm:rounded-[24px]"
              >
                <div class="relative h-[160px] animate-pulse bg-white sm:h-[190px] xl:h-[210px]">
                  <div class="absolute left-3 top-3 h-5 w-16 rounded bg-neutral-200 sm:left-4 sm:top-4 sm:h-6 sm:w-20" />
                </div>

                <div class="space-y-2 p-3 sm:space-y-3 sm:p-4">
                  <div class="h-4 w-3/4 animate-pulse rounded bg-neutral-200 sm:h-5" />
                  <div class="h-4 w-full animate-pulse rounded bg-neutral-100" />
                  <div class="h-4 w-2/3 animate-pulse rounded bg-neutral-200" />
                  <div class="mt-2 flex gap-2">
                    <div class="h-6 w-6 animate-pulse rounded-full bg-neutral-200" />
                    <div class="h-6 w-6 animate-pulse rounded-full bg-neutral-200" />
                    <div class="h-6 w-6 animate-pulse rounded-full bg-neutral-200" />
                  </div>
                </div>
              </div>
            </template>

            <template v-else-if="hasProducts">
              <Link
                v-for="(product, index) in repeatedProducts"
                :key="`${product.id}-${index}`"
                :href="productUrl(product)"
                class="featured-slide product-card group block overflow-hidden rounded-[20px] border border-neutral-200 bg-white outline-none focus-visible:ring-2 focus-visible:ring-neutral-900 sm:rounded-[24px]"
              >
                <div class="relative overflow-hidden bg-white">
                  <div class="absolute left-3 top-3 z-20 flex flex-col gap-1.5 sm:left-4 sm:top-4 sm:gap-2">
                    <span
                      v-if="product.has_discount && product.discount_label"
                      class="inline-flex w-fit items-center rounded-md bg-[#ef5a4f] px-2.5 py-1 text-[10px] font-semibold text-white sm:px-3 sm:text-xs"
                    >
                      {{ normalizedDiscount(product.discount_label) }}
                    </span>
                  </div>

                  <div class="absolute right-3 top-3 z-20 text-right sm:right-4 sm:top-4">
                    <div class="text-[9px] font-semibold uppercase tracking-[0.14em] text-neutral-700 sm:text-xs sm:tracking-[0.16em]">
                      {{ topLabel(product) }}
                    </div>
                    <div class="text-[9px] text-neutral-500 sm:text-[11px]">
                      {{ cardTagline }}
                    </div>
                  </div>

                  <div class="relative flex h-[160px] items-center justify-center px-3 pb-3 pt-10 sm:h-[190px] sm:px-4 sm:pb-4 sm:pt-12 xl:h-[210px]">
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

                      <span class="featured-price-sale font-bold">
                        {{ formatPrice(product.display_price) }}
                      </span>
                    </p>

                    <p
                      v-else
                      class="featured-price-normal"
                    >
                      {{ formatPrice(product.display_price) }}
                    </p>
                  </div>

                  <div
                    v-if="clampRating(product.reviews_avg_rating) > 0"
                    class="mt-2 flex flex-col gap-1.5"
                  >
                    <div
                      class="product-rating"
                      :aria-label="ratingAriaLabel(product.reviews_avg_rating, product.reviews_count)"
                      role="img"
                    >
                      <div class="product-rating-stars">
                        <span
                          v-for="starNumber in 5"
                          :key="`rating-star-${product.id}-${index}-${starNumber}`"
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

                  <div
                    v-if="product.colors && product.colors.length"
                    class="mt-3 flex items-center gap-2"
                  >
                    <span
                      v-for="color in product.colors"
                      :key="color.id"
                      class="h-6 w-6 rounded-full border border-neutral-300"
                      :style="colorSwatchStyle(color)"
                      :title="color.name"
                    />
                  </div>
                </div>
              </Link>
            </template>

            <div
              v-else-if="fetchError"
              class="featured-products-state"
            >
              {{ fetchError }}
            </div>

            <div
              v-else-if="hasLoaded && !hasProducts"
              class="featured-products-state"
            >
              No featured products available right now.
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.featured-products-section {
  width: 100%;
  padding: 28px 0 8px;
}

.featured-shell {
  display: grid;
  grid-template-columns: 250px minmax(0, 1fr);
  gap: 16px;
  align-items: stretch;
}

.featured-banner {
  position: relative;
  overflow: hidden;
  border-radius: 24px;
  min-height: 300px;
  padding: 24px 22px;
  background:
    radial-gradient(circle at top right, var(--featured-banner-highlight, rgba(255, 213, 79, 0.18)), transparent 36%),
    linear-gradient(180deg, var(--featured-banner-start, rgba(8, 27, 104, 0.95)) 0%, var(--featured-banner-end, rgba(6, 20, 79, 0.96)) 100%);
  color: #fff;
  box-shadow: 0 18px 40px var(--featured-banner-shadow, rgba(8, 27, 104, 0.18));
}

.featured-banner::before {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.08);
  opacity: 0.16;
  mix-blend-mode: screen;
  pointer-events: none;
}

.featured-banner::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, var(--featured-banner-overlay-weak, rgba(5, 11, 45, 0.08)) 0%, var(--featured-banner-overlay-strong, rgba(5, 11, 45, 0.34)) 100%);
  pointer-events: none;
}

.featured-banner__content {
  position: relative;
  z-index: 2;
  display: flex;
  min-height: 100%;
  flex-direction: column;
  justify-content: center;
}

.featured-banner__eyebrow {
  display: inline-flex;
  width: fit-content;
  margin-bottom: 12px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.14);
  padding: 7px 13px;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.12em;
}

.featured-banner__title {
  margin: 0;
  font-size: clamp(24px, 3vw, 36px);
  line-height: 1.08;
  font-weight: 900;
}

.featured-banner__text {
  margin: 14px 0 0;
  color: rgba(255, 255, 255, 0.88);
  font-size: 13px;
  line-height: 1.7;
}

.featured-banner__button {
  margin-top: 22px;
  width: fit-content;
  border: none;
  border-radius: 999px;
  padding: 13px 20px;
  background: linear-gradient(135deg, var(--featured-button-start, #ffd54f) 0%, var(--featured-button-end, #f4c400) 100%);
  color: var(--featured-button-text, #081b68);
  font-weight: 800;
  font-size: 13px;
  letter-spacing: 0.03em;
  cursor: pointer;
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease,
    opacity 0.2s ease;
}

.featured-banner__button:hover {
  transform: translateY(-2px);
  box-shadow: 0 14px 26px var(--featured-button-shadow, rgba(244, 196, 0, 0.24));
}

.featured-banner__glow {
  position: absolute;
  border-radius: 999px;
  filter: blur(14px);
  pointer-events: none;
}

.featured-banner__glow--one {
  width: 160px;
  height: 160px;
  right: -36px;
  top: -28px;
  background: var(--featured-glow-one, rgba(255, 212, 59, 0.14));
}

.featured-banner__glow--two {
  width: 120px;
  height: 120px;
  left: -18px;
  bottom: -26px;
  background: rgba(255, 255, 255, 0.08);
}

.featured-products-panel {
  border-radius: 24px;
  padding: 16px;
  overflow: hidden;
}

.featured-products-panel__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 14px;
}

.featured-products-panel__title {
  margin: 0;
  color: #111827;
  font-size: 18px;
  font-weight: 800;
}

.featured-products-panel__subtitle {
  margin: 4px 0 0;
  color: #64748b;
  font-size: 12px;
}

.featured-products-panel__nav {
  display: flex;
  gap: 8px;
}

.featured-nav-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border: 1px solid #e5e7eb;
  border-radius: 999px;
  background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
  color: #0f172a;
  cursor: pointer;
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease,
    border-color 0.2s ease,
    color 0.2s ease;
}

.featured-nav-btn svg {
  width: 18px;
  height: 18px;
}

.featured-nav-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  border-color: var(--featured-accent-deep, #081b68);
  color: var(--featured-accent-deep, #081b68);
  box-shadow: 0 12px 22px rgba(15, 23, 42, 0.1);
}

.featured-nav-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
  box-shadow: none;
}

.featured-products-track {
  display: flex;
  gap: 14px;
  overflow-x: auto;
  overflow-y: hidden;
  scroll-behavior: smooth;
  scrollbar-width: none;
  -ms-overflow-style: none;
  scroll-snap-type: x mandatory;
  scroll-padding-left: 0;
  overscroll-behavior-x: contain;
  -webkit-overflow-scrolling: touch;
  padding-top: 10px;
  padding-bottom: 10px;
}

.featured-products-track::-webkit-scrollbar {
  display: none;
}

.featured-slide {
  flex: 0 0 calc((100% - 28px) / 3);
  min-width: calc((100% - 28px) / 3);
  scroll-snap-align: start;
  scroll-snap-stop: always;
}

.featured-products-state {
  width: 100%;
  min-height: 180px;
  display: grid;
  place-items: center;
  text-align: center;
  color: #64748b;
  font-size: 15px;
  font-weight: 600;
  padding: 20px;
}

.featured-price-sale {
  color: var(--featured-accent, #2563eb);
  font-size: 15px;
}

.featured-price-normal {
  font-size: 16px;
  font-weight: 700;
  color: var(--featured-accent, #2563eb);
}

.product-card {
  box-shadow: none;
  transition: transform 0.4s ease;
}

.product-card:hover {
  transform: translateY(-6px);
  box-shadow: none;
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

@media (max-width: 1199px) {
  .featured-shell {
    grid-template-columns: 1fr;
  }

  .featured-banner {
    min-height: 240px;
  }
}

@media (max-width: 991px) {
  .featured-slide {
    flex: 0 0 calc((100% - 14px) / 2);
    min-width: calc((100% - 14px) / 2);
  }
}

@media (max-width: 640px) {
  .featured-products-section {
    padding-top: 24px;
  }

  .featured-products-panel {
    padding: 14px;
    border-radius: 22px;
  }

  .featured-products-panel__header {
    align-items: flex-start;
    flex-direction: column;
  }

  .featured-products-track {
    padding-top: 8px;
    padding-bottom: 8px;
  }

  .featured-slide {
    flex: 0 0 100%;
    min-width: 100%;
    max-width: 100%;
  }

  .featured-banner {
    min-height: 220px;
    padding: 22px 18px;
  }

  .featured-banner__title {
    font-size: 26px;
  }
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

  .featured-price-sale {
    font-size: 16px;
  }

  .featured-price-normal {
    font-size: 17px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .product-card,
  .product-main-image,
  .product-hover-image,
  .featured-banner__button,
  .featured-nav-btn {
    transition: none !important;
    transform: none !important;
  }

  .featured-products-track {
    scroll-behavior: auto;
  }
}
</style>
