<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import HomeProductCard from './HomeProductCard.vue'

type ProductColor = {
  id: number | string
  name: string
  color_code?: string | null
}

type HomeProductItem = {
  id: number | string
  name: string
  category_name?: string | null
  brand_name?: string | null
  short_description?: string | null
  description?: string | null
  thumbnail_url: string | null
  hover_image_url: string | null
  regular_price: number | null
  display_price: number | null
  koko_pay_percentage?: number | null
  koko_installment_price?: number | null
  has_discount: boolean
  discount_label?: string | null
  is_sold_out: boolean
  reviews_count?: number | null
  reviews_avg_rating?: number | null
  colors?: ProductColor[]
  url?: string | null
}

type RailPayload = {
  products?: HomeProductItem[]
  show_all_url?: string | null
}

const props = withDefaults(defineProps<{
  title: string
  subtitle?: string
  eyebrow?: string
  endpoint: string
  maxItems?: number
  desktopVisible?: number
  showAllHref?: string
}>(), {
  subtitle: '',
  eyebrow: '',
  maxItems: 6,
  desktopVisible: 4,
  showAllHref: '',
})

const railCache = getRailCache()

const sectionRef = ref<HTMLElement | null>(null)
const products = ref<HomeProductItem[]>([])
const showAllUrl = ref(props.showAllHref)
const loading = ref(false)
const loaded = ref(false)
const error = ref('')
const slideIndex = ref(0)
const visibleCount = ref(props.desktopVisible)
const hovering = ref(false)

let observer: IntersectionObserver | null = null
let autoplayTimer: number | null = null

const canSlide = computed(() => products.value.length > visibleCount.value)
const maxSlideIndex = computed(() => Math.max(0, products.value.length - visibleCount.value))
const visibleSkeletons = computed(() => Math.max(1, visibleCount.value))
const trackStyle = computed(() => ({
  '--visible-count': String(visibleCount.value),
  transform: `translateX(-${slideIndex.value * (100 / visibleCount.value)}%)`,
}))

function getRailCache() {
  const key = '__deze_home_rail_cache__'
  const win = window as any
  win[key] = win[key] || new Map<string, RailPayload>()
  return win[key] as Map<string, RailPayload>
}

function updateVisibleCount() {
  if (window.innerWidth >= 1180) {
    visibleCount.value = props.desktopVisible
  } else if (window.innerWidth >= 900) {
    visibleCount.value = Math.min(3, props.desktopVisible)
  } else if (window.innerWidth >= 640) {
    visibleCount.value = 2
  } else {
    visibleCount.value = 2
  }

  slideIndex.value = Math.min(slideIndex.value, maxSlideIndex.value)
}

async function loadProducts() {
  if (loaded.value || loading.value) return

  if (railCache.has(props.endpoint)) {
    applyPayload(railCache.get(props.endpoint) || {})
    loaded.value = true
    return
  }

  loading.value = true
  error.value = ''

  try {
    const response = await fetch(props.endpoint, {
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })

    if (!response.ok) {
      throw new Error('Unable to load products.')
    }

    const payload = await response.json() as RailPayload
    railCache.set(props.endpoint, payload)
    applyPayload(payload)
    loaded.value = true
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unable to load products.'
    products.value = []
  } finally {
    loading.value = false
  }
}

function applyPayload(payload: RailPayload) {
  products.value = Array.isArray(payload.products)
    ? payload.products.slice(0, props.maxItems)
    : []
  showAllUrl.value = payload.show_all_url || props.showAllHref || '#'
  slideIndex.value = 0
}

function next() {
  if (!canSlide.value) return
  slideIndex.value = slideIndex.value >= maxSlideIndex.value ? 0 : slideIndex.value + 1
}

function prev() {
  if (!canSlide.value) return
  slideIndex.value = slideIndex.value <= 0 ? maxSlideIndex.value : slideIndex.value - 1
}

function startAutoplay() {
  stopAutoplay()

  if (!canSlide.value || hovering.value) return

  autoplayTimer = window.setInterval(() => {
    next()
  }, 4300)
}

function stopAutoplay() {
  if (autoplayTimer !== null) {
    window.clearInterval(autoplayTimer)
    autoplayTimer = null
  }
}

function retry() {
  loaded.value = false
  railCache.delete(props.endpoint)
  loadProducts()
}

watch([canSlide, visibleCount, products], () => {
  slideIndex.value = Math.min(slideIndex.value, maxSlideIndex.value)
  startAutoplay()
})

watch(hovering, () => {
  if (hovering.value) {
    stopAutoplay()
  } else {
    startAutoplay()
  }
})

onMounted(() => {
  updateVisibleCount()
  window.addEventListener('resize', updateVisibleCount)

  if ('IntersectionObserver' in window && sectionRef.value) {
    observer = new IntersectionObserver(
      (entries) => {
        if (entries[0]?.isIntersecting) {
          loadProducts()
          observer?.disconnect()
          observer = null
        }
      },
      {
        root: null,
        rootMargin: '220px 0px',
        threshold: 0.08,
      },
    )

    observer.observe(sectionRef.value)
  } else {
    loadProducts()
  }
})

onBeforeUnmount(() => {
  observer?.disconnect()
  stopAutoplay()
  window.removeEventListener('resize', updateVisibleCount)
})
</script>

<template>
  <section
    ref="sectionRef"
    class="home-rail-section"
    @mouseenter="hovering = true"
    @mouseleave="hovering = false"
  >
    <div class="home-section-header">
      <div>
        <span v-if="eyebrow" class="home-section-eyebrow">{{ eyebrow }}</span>
        <h2 class="home-section-title">{{ title }}</h2>
        <p v-if="subtitle" class="home-section-subtitle">{{ subtitle }}</p>
      </div>

      <div class="home-section-actions">
        <button
          type="button"
          class="rail-nav-btn"
          :disabled="!canSlide"
          aria-label="Previous products"
          @click="prev"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 6l-6 6 6 6" />
          </svg>
        </button>

        <button
          type="button"
          class="rail-nav-btn"
          :disabled="!canSlide"
          aria-label="Next products"
          @click="next"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6" />
          </svg>
        </button>

        <Link :href="showAllUrl || '#'" class="show-all-link">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M9 6h10M9 12h10M9 18h10M5 6h.01M5 12h.01M5 18h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <span>Show all</span>
        </Link>
      </div>
    </div>

    <div v-if="error" class="home-section-state home-section-state--error">
      <span>{{ error }}</span>
      <button type="button" @click="retry">Retry</button>
    </div>

    <div v-else class="rail-window">
      <div
        v-if="loading || !loaded"
        class="rail-track"
        :style="{ '--visible-count': String(visibleCount) }"
      >
        <div v-for="index in visibleSkeletons" :key="`rail-skeleton-${index}`" class="rail-slide">
          <HomeProductCard skeleton />
        </div>
      </div>

      <div v-else-if="products.length" class="rail-track" :style="trackStyle">
        <div v-for="product in products" :key="product.id" class="rail-slide">
          <HomeProductCard :product="product" />
        </div>
      </div>

      <div v-else class="home-section-state">
        No products available right now.
      </div>
    </div>
  </section>
</template>

<style scoped>
.home-rail-section {
  box-sizing: border-box;
  width: 100%;
  max-width: 1280px;
  margin: 0 auto;
  padding: 48px 20px;
  color: #0b1a44;
  font-family: 'DezeStoreCustomFont', var(--font-sans, sans-serif);
}

#today-best-deals-section.home-rail-section {
  max-width: min(1280px, calc(100% - 32px));
  margin-top: 26px;
  margin-bottom: 18px;
  padding: 42px 22px 36px;
  border: 1px solid #b7dff8;
  border-radius: 34px;
  background: #d8efff;
  box-shadow: none;
}

.home-section-header {
  display: flex;
  align-items: end;
  justify-content: space-between;
  gap: 18px;
  min-height: 78px;
  margin-bottom: 22px;
}

.home-section-eyebrow {
  display: inline-flex;
  margin-bottom: 8px;
  color: #071f4f;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0;
  text-transform: uppercase;
}

.home-section-title {
  margin: 0;
  color: #0f172a;
  font-size: 38px;
  font-weight: 600;
  line-height: 1;
  letter-spacing: 0;
}

#today-best-deals-section .home-section-title {
  background: linear-gradient(90deg, #071f4f 0%, #1769c2 55%, #ff7a2f 100%);
  background-clip: text;
  color: transparent;
  font-size: 42px;
  font-weight: 900;
  line-height: 1.06;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.home-section-subtitle {
  position: relative;
  max-width: 660px;
  margin: 10px 0 0;
  padding-left: 16px;
  color: #365173;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.65;
}

.home-section-subtitle::before {
  position: absolute;
  top: 0.38em;
  left: 0;
  width: 4px;
  min-height: 22px;
  height: calc(100% - 0.76em);
  border-radius: 999px;
  background: linear-gradient(180deg, #2f6fe4, #ff980f);
  content: '';
}

.home-section-actions {
  display: flex;
  flex-shrink: 0;
  align-items: center;
  gap: 8px;
}

.rail-nav-btn,
.show-all-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 40px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 600;
  transition:
    border-color 0.18s ease,
    background-color 0.18s ease,
    color 0.18s ease,
    transform 0.18s ease;
}

.rail-nav-btn {
  width: 40px;
  border: 1px solid #dbe3ef;
  background: #ffffff;
  color: #071f4f;
}

.rail-nav-btn svg {
  width: 18px;
  height: 18px;
}

.rail-nav-btn:hover:not(:disabled) {
  border-color: #071f4f;
  transform: translateY(-1px);
}

.rail-nav-btn:disabled {
  cursor: not-allowed;
  opacity: 0.35;
}

.show-all-link {
  border: 1px solid #071f4f;
  background: #071f4f;
  gap: 7px;
  min-height: 40px;
  padding: 0 14px;
  color: #ffffff;
  text-decoration: none;
}

.show-all-link svg {
  width: 15px;
  height: 15px;
  flex: 0 0 auto;
}

.show-all-link:hover {
  background: #0b2b62;
  transform: translateY(-1px);
}

.rail-window {
  overflow: hidden;
}

.rail-track {
  --visible-count: 4;

  display: flex;
  margin-inline: -9px;
  transition: transform 0.42s ease;
  will-change: transform;
}

.rail-slide {
  flex: 0 0 calc(100% / var(--visible-count));
  min-width: 0;
  padding-inline: 9px;
}

.home-section-state {
  display: flex;
  min-height: 180px;
  align-items: center;
  justify-content: center;
  border: 1px dashed #cbd5e1;
  border-radius: 18px;
  background: #ffffff;
  color: #64748b;
  text-align: center;
}

.home-section-state--error {
  flex-direction: column;
  gap: 12px;
  border-color: #fecaca;
  background: #fff7f7;
  color: #b91c1c;
}

.home-section-state--error button {
  border-radius: 999px;
  background: #b91c1c;
  padding: 8px 18px;
  color: #ffffff;
  font-size: 13px;
  font-weight: 600;
}

@media (max-width: 720px) {
  .home-rail-section {
    padding: 38px 16px;
  }

  #today-best-deals-section.home-rail-section {
    max-width: calc(100% - 20px);
    margin-top: 18px;
    padding: 32px 12px 24px;
    border-radius: 26px;
  }

  .home-section-header {
    align-items: start;
    flex-direction: column;
    min-height: 0;
  }

  .home-section-actions {
    width: 100%;
  }

  .home-section-title {
    font-size: 30px;
    line-height: 1.12;
  }

  #today-best-deals-section .home-section-title {
    font-size: 30px;
    line-height: 1.12;
  }

  .home-section-subtitle {
    max-width: none;
    font-size: 13.5px;
    line-height: 1.55;
  }

  .show-all-link {
    margin-left: auto;
    min-height: 38px;
    padding: 0 13px;
    font-size: 12px;
  }

  .rail-track {
    margin-inline: -5px;
  }

  .rail-slide {
    padding-inline: 5px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .rail-track,
  .rail-nav-btn,
  .show-all-link {
    transition: none !important;
  }
}
</style>
