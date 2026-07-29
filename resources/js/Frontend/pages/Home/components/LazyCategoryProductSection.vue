<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch, type ComponentPublicInstance } from 'vue'
import HomeProductCard from './HomeProductCard.vue'

type CategoryItem = {
  id: number | string
  name: string
  count?: number | null
}

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

type SectionPayload = {
  categories?: CategoryItem[]
  products?: HomeProductItem[]
  show_all_url?: string | null
}

const props = withDefaults(defineProps<{
  title: string
  subtitle?: string
  endpoint: string
  showAllHref: string
  tone?: 'motorcycle' | 'electronics' | 'cosmetics' | 'fashion' | 'home'
}>(), {
  subtitle: '',
  tone: 'electronics',
})

const sectionCache = getSectionCache()

const sectionRef = ref<HTMLElement | null>(null)
const listRef = ref<HTMLDivElement | null>(null)
const tabRefs = ref<Record<string, HTMLButtonElement | null>>({})
const categories = ref<CategoryItem[]>([])
const activeCategory = ref<string>('')
const products = ref<HomeProductItem[]>([])
const showAllUrl = ref(props.showAllHref)
const entered = ref(false)
const loading = ref(false)
const error = ref('')

let observer: IntersectionObserver | null = null
let activeScroller: HTMLDivElement | null = null
let requestId = 0

const activeCategoryName = computed(() => {
  if (!activeCategory.value) return 'All'
  return categories.value.find((category) => String(category.id) === activeCategory.value)?.name || 'Selected'
})

const tabs = computed(() => [
  { id: '', label: 'All', count: null },
  ...categories.value.map((category) => ({
    id: String(category.id),
    label: category.name,
    count: category.count,
  })),
])

const indicator = ref({
  left: 0,
  width: 0,
  opacity: 0,
})

function getSectionCache() {
  const key = '__deze_home_category_section_cache__'
  const win = window as any
  win[key] = win[key] || new Map<string, SectionPayload>()
  return win[key] as Map<string, SectionPayload>
}

function cacheKey(category: string) {
  return `${props.endpoint}::${category || 'all'}`
}

function requestUrl(category: string) {
  const url = new URL(props.endpoint, window.location.origin)

  if (category) {
    url.searchParams.set('category', category)
  }

  return url.toString()
}

async function ensureLoaded(category = activeCategory.value) {
  const key = cacheKey(category)
  if (sectionCache.has(key)) {
    requestId += 1
    error.value = ''
    loading.value = false
    applyPayload(sectionCache.get(key) || {})
    return
  }

  const currentRequestId = requestId + 1
  requestId = currentRequestId
  loading.value = true
  error.value = ''
  products.value = []

  try {
    const response = await fetch(requestUrl(category), {
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })

    if (!response.ok) {
      throw new Error('Unable to load this section.')
    }

    const payload = await response.json() as SectionPayload
    sectionCache.set(key, payload)

    if (currentRequestId !== requestId || activeCategory.value !== category) {
      return
    }

    applyPayload(payload)
  } catch (err) {
    if (currentRequestId !== requestId) {
      return
    }

    error.value = err instanceof Error ? err.message : 'Unable to load this section.'
    products.value = []
  } finally {
    if (currentRequestId === requestId) {
      loading.value = false
    }
  }
}

function applyPayload(payload: SectionPayload) {
  if (Array.isArray(payload.categories) && payload.categories.length) {
    categories.value = payload.categories
  }

  products.value = Array.isArray(payload.products) ? payload.products.slice(0, 4) : []
  showAllUrl.value = payload.show_all_url || props.showAllHref
}

function activate(category: string) {
  activeCategory.value = category

  if (!entered.value) {
    entered.value = true
  }

  scrollActiveTabIntoView(category)
  ensureLoaded(category)
}

function retry() {
  sectionCache.delete(cacheKey(activeCategory.value))
  ensureLoaded(activeCategory.value)
}

function tabKey(id: string) {
  return id || '__all__'
}

function setTabRef(id: string, element: Element | ComponentPublicInstance | null) {
  const key = tabKey(id)

  if (element instanceof HTMLButtonElement) {
    tabRefs.value[key] = element
    return
  }

  delete tabRefs.value[key]
}

function updateIndicator() {
  const list = listRef.value
  const tab = tabRefs.value[tabKey(activeCategory.value)]

  if (!list || !tab) {
    indicator.value = {
      ...indicator.value,
      opacity: 0,
    }
    return
  }

  const listRect = list.getBoundingClientRect()
  const tabRect = tab.getBoundingClientRect()

  indicator.value = {
    left: tabRect.left - listRect.left + list.scrollLeft,
    width: tabRect.width,
    opacity: 1,
  }
}

function scrollActiveTabIntoView(id = activeCategory.value) {
  nextTick(() => {
    const tab = tabRefs.value[tabKey(id)]

    tab?.scrollIntoView({
      behavior: 'smooth',
      inline: 'center',
      block: 'nearest',
    })

    updateIndicator()
  })
}

function onCategoryKeydown(event: KeyboardEvent) {
  if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') return
  if (!tabs.value.length) return

  event.preventDefault()

  const activeIndex = Math.max(0, tabs.value.findIndex((tab) => tab.id === activeCategory.value))
  const nextIndex = event.key === 'ArrowRight'
    ? (activeIndex + 1) % tabs.value.length
    : (activeIndex - 1 + tabs.value.length) % tabs.value.length

  activate(tabs.value[nextIndex]?.id ?? '')
}

watch(
  () => [activeCategory.value, tabs.value.length],
  () => nextTick(updateIndicator),
  { flush: 'post' },
)

onMounted(() => {
  nextTick(() => {
    activeScroller = listRef.value
    activeScroller?.addEventListener('scroll', updateIndicator, { passive: true })
    window.addEventListener('resize', updateIndicator)
    updateIndicator()
  })

  if ('IntersectionObserver' in window && sectionRef.value) {
    observer = new IntersectionObserver(
      (entries) => {
        if (entries[0]?.isIntersecting) {
          entered.value = true
          ensureLoaded('')
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
    entered.value = true
    ensureLoaded('')
  }
})

onBeforeUnmount(() => {
  observer?.disconnect()
  activeScroller?.removeEventListener('scroll', updateIndicator)
  window.removeEventListener('resize', updateIndicator)
})
</script>

<template>
  <section ref="sectionRef" class="lazy-category-section" :class="`lazy-category-section--${tone}`">
    <div class="category-shell">
      <header class="category-heading">
        <div class="category-title">
          <span class="category-kicker">{{ activeCategoryName }}</span>
          <h2>{{ title }}</h2>
          <p v-if="subtitle">{{ subtitle }}</p>
        </div>
      </header>

      <div
        class="category-nav"
        role="tablist"
        aria-label="Product categories"
        @keydown="onCategoryKeydown"
      >
        <div class="category-scroller" ref="listRef">
          <span
            class="category-indicator"
            :style="{
              transform: `translateX(${indicator.left}px)`,
              width: `${indicator.width}px`,
              opacity: indicator.opacity,
            }"
            aria-hidden="true"
          />

          <button
            v-for="tab in tabs"
            :key="tabKey(tab.id)"
            :ref="(element) => setTabRef(tab.id, element)"
            type="button"
            role="tab"
            :aria-selected="tab.id === activeCategory"
            :tabindex="tab.id === activeCategory ? 0 : -1"
            class="category-tab"
            :class="{ 'category-tab--active': tab.id === activeCategory }"
            @click="activate(tab.id)"
          >
            <span class="category-tab-label">{{ tab.label }}</span>
            <span v-if="typeof tab.count === 'number'" class="category-tab-count">
              {{ tab.count }}
            </span>
          </button>

          <template v-if="!categories.length && (loading || !entered)">
            <span
              v-for="index in 4"
              :key="`category-tab-skeleton-${index}`"
              class="category-tab-skeleton"
            />
          </template>
        </div>
      </div>
      <div v-if="error" class="category-state category-state--error">
        <span>{{ error }}</span>
        <button type="button" @click="retry">Retry</button>
      </div>

      <div v-else-if="loading || !entered" class="category-grid">
        <HomeProductCard v-for="index in 4" :key="`product-skeleton-${index}`" skeleton />
      </div>

      <div v-else-if="products.length" class="category-grid">
        <HomeProductCard
          v-for="product in products"
          :key="product.id"
          :product="product"
        />
      </div>

      <div v-else class="category-state">
        No products available for this category right now.
      </div>

      <div v-if="showAllUrl && !loading && !error" class="category-footer">
        <Link :href="showAllUrl" class="category-show-all">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M9 6h10M9 12h10M9 18h10M5 6h.01M5 12h.01M5 18h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <span>Show all</span>
        </Link>
      </div>
    </div>
  </section>
</template>

<style scoped>
.lazy-category-section {
  --section-accent: #2f6fe4;
  --section-warm: #ff5b3a;
  --section-title-mid: #1769c2;

  width: 100%;
  padding: 48px 20px;
  color: #0b1a44;
  font-family: 'DezeStoreCustomFont', var(--font-sans, ui-sans-serif, system-ui, sans-serif);
}

.lazy-category-section--motorcycle {
  --section-accent: #e44f35;
  --section-warm: #ff980f;
  --section-title-mid: #bf3d2d;
}

.lazy-category-section--electronics {
  --section-accent: #2f6fe4;
  --section-warm: #ff980f;
  --section-title-mid: #1769c2;
}

.lazy-category-section--cosmetics {
  --section-accent: #d94683;
  --section-warm: #ff8a4c;
  --section-title-mid: #b83274;
}

.lazy-category-section--fashion {
  --section-accent: #7c4dff;
  --section-warm: #ff7a2f;
  --section-title-mid: #5b55c8;
}

.lazy-category-section--home {
  --section-accent: #16876a;
  --section-warm: #ff980f;
  --section-title-mid: #227fbd;
}

.category-shell {
  width: 100%;
  max-width: 1280px;
  margin: 0 auto;
}

.category-heading {
  display: flex;
  align-items: flex-end;
  margin-bottom: 18px;
}

.category-title {
  max-width: 720px;
}

.category-kicker {
  display: inline-block;
  margin-bottom: 8px;
  padding: 5px 10px;
  border-radius: 999px;
  background: linear-gradient(135deg, rgba(255, 91, 58, 0.12), rgba(255, 91, 58, 0.04));
  color: #ff5b3a;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0;
  text-transform: uppercase;
}

.category-heading h2 {
  margin: 0;
  color: #0b1a44;
  font-size: 32px;
  font-weight: 800;
  line-height: 1.1;
  letter-spacing: 0;
}

.category-heading p {
  position: relative;
  max-width: 520px;
  margin: 10px 0 0;
  padding-left: 16px;
  color: #43506e;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.6;
}

.category-heading p::before {
  position: absolute;
  top: 0.38em;
  left: 0;
  width: 4px;
  min-height: 22px;
  height: calc(100% - 0.76em);
  border-radius: 999px;
  background: linear-gradient(180deg, var(--section-accent), var(--section-warm));
  content: '';
}

.category-show-all {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  flex-shrink: 0;
  min-height: 40px;
  padding: 0 14px;
  border-radius: 999px;
  background: #0b1a44;
  color: #fff;
  font-size: 12.5px;
  font-weight: 700;
  letter-spacing: 0;
  text-decoration: none;
  white-space: nowrap;
  box-shadow: 0 8px 20px -10px rgba(11, 26, 68, 0.55);
  transition:
    transform 220ms ease,
    background 220ms ease,
    box-shadow 220ms ease;
}

.category-show-all:hover {
  background: #ff5b3a;
  transform: translateY(-2px);
  box-shadow: 0 14px 26px -10px rgba(255, 91, 58, 0.55);
}

.category-show-all svg {
  width: 15px;
  height: 15px;
  flex: 0 0 auto;
}

.category-nav {
  position: relative;
  margin-bottom: 22px;
  padding: 8px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.7);
  box-shadow:
    inset 0 0 0 1px rgba(11, 26, 68, 0.08),
    0 18px 40px -22px rgba(11, 26, 68, 0.25),
    0 4px 12px -8px rgba(11, 26, 68, 0.15);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
}

.category-scroller {
  position: relative;
  display: flex;
  gap: 6px;
  overflow-x: auto;
  scroll-behavior: smooth;
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.category-scroller::-webkit-scrollbar {
  display: none;
}

.category-indicator {
  position: absolute;
  top: 0;
  left: 0;
  z-index: 0;
  height: 100%;
  border-radius: 999px;
  background: #0b1a44;
  box-shadow: none;
  pointer-events: none;
  transition:
    transform 380ms cubic-bezier(0.2, 0.8, 0.2, 1),
    width 380ms cubic-bezier(0.2, 0.8, 0.2, 1),
    opacity 220ms ease;
}

.category-tab,
.category-tab-skeleton {
  min-height: 42px;
  border-radius: 999px;
}

.category-tab {
  position: relative;
  z-index: 1;
  flex: 0 0 auto;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border: 0;
  background: transparent;
  padding: 11px 18px;
  color: #4b5670;
  font-size: 13.5px;
  font-weight: 700;
  letter-spacing: 0;
  white-space: nowrap;
  cursor: pointer;
  transition:
    color 240ms ease,
    transform 240ms ease;
}

.category-tab:hover {
  color: #0b1a44;
}

.category-tab--active {
  color: #ffffff;
}

.category-tab--active:hover {
  color: #ffffff;
}

.category-tab:focus-visible {
  outline: 2px solid #ff5b3a;
  outline-offset: 3px;
}

.category-tab-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 22px;
  height: 20px;
  padding: 0 7px;
  border-radius: 999px;
  background: rgba(11, 26, 68, 0.08);
  color: #0b1a44;
  font-size: 11px;
  font-weight: 800;
  line-height: 1;
  transition:
    background 240ms ease,
    color 240ms ease;
}

.category-tab--active .category-tab-count {
  background: rgba(255, 255, 255, 0.22);
  color: #fff;
}

.category-tab-skeleton {
  position: relative;
  z-index: 1;
  flex: 0 0 auto;
  width: 104px;
  animation: pulse 1.4s ease-in-out infinite;
  background: #eef2f7;
}

.category-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 18px;
}

.category-footer {
  display: flex;
  justify-content: center;
  margin-top: 22px;
}

.category-state {
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

.category-state--error {
  flex-direction: column;
  gap: 12px;
  border-color: #fecaca;
  background: #fff7f7;
  color: #b91c1c;
}

.category-state--error button {
  border-radius: 999px;
  background: #b91c1c;
  padding: 8px 18px;
  color: #ffffff;
  font-size: 13px;
  font-weight: 600;
}

@keyframes pulse {
  0%,
  100% {
    opacity: 0.55;
  }

  50% {
    opacity: 1;
  }
}

@media (max-width: 1180px) {
  .category-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

@media (max-width: 960px) {
  .category-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 720px) {
  .lazy-category-section {
    padding-inline: 14px;
  }

  .category-heading {
    margin-bottom: 14px;
  }

  .category-heading h2 {
    font-size: 22px;
  }

  .category-heading p {
    max-width: none;
    font-size: 13.5px;
    line-height: 1.55;
  }

  .category-nav {
    padding: 6px;
    border-radius: 18px;
  }

  .category-tab {
    padding: 10px 14px;
    font-size: 13px;
  }

  .category-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 560px) {
  .lazy-category-section {
    padding: 38px 16px;
  }

  .category-footer {
    margin-top: 18px;
  }

  .category-show-all {
    min-height: 38px;
    padding: 0 13px;
    font-size: 12px;
  }

  .category-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .category-indicator,
  .category-show-all,
  .category-show-all svg,
  .category-tab {
    transition: none !important;
  }

  .category-scroller {
    scroll-behavior: auto;
  }
}
</style>
