<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { route } from 'ziggy-js'
import AppLayout from '@/Frontend/layouts/AppLayout.vue'
import HomeProductCard from '../Home/components/HomeProductCard.vue'
import { Check, ChevronDown, RotateCcw, Search, SlidersHorizontal } from 'lucide-vue-next'

defineOptions({
  layout: AppLayout,
})

type OptionItem = {
  id: number | string
  name: string
  value?: string | null
  logo_url?: string | null
}

type ProductColor = {
  id: number | string
  name: string
  color_code?: string | null
}

type ProductCard = {
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

type Filters = {
  search?: string | null
  category?: string | null
  brand?: string | null
  type?: string | null
  warranty?: string | null
  stock?: string | null
  sale?: boolean
  featured?: boolean
  today_best_deals?: boolean
  best_seller?: boolean
  sort?: string | null
  min_price?: string | number | null
  max_price?: string | number | null
  page?: number
}

type PaginationMeta = {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

const props = defineProps<{
  title: string
  subtitle: string
  eyebrow?: string
  indexUrl: string
  productsUrl: string
  sectionKey: string
  brandLabel: string
  typeLabel?: string
  categories: OptionItem[]
  brands: OptionItem[]
  types: OptionItem[]
  warranties: OptionItem[]
  filters: Filters
}>()

const page = usePage()

const products = ref<ProductCard[]>([])
const loading = ref(false)
const loadingMore = ref(false)
const loadError = ref('')
const filterPanelOpen = ref(false)

const pagination = ref<PaginationMeta>({
  current_page: 1,
  last_page: 1,
  per_page: 12,
  total: 0,
  from: 0,
  to: 0,
})

let batchTimer: ReturnType<typeof setTimeout> | null = null
let activeController: AbortController | null = null

const currentFilters = computed(() => ({
  search: props.filters?.search || '',
  category: props.filters?.category || '',
  brand: props.filters?.brand || '',
  type: props.filters?.type || '',
  warranty: props.filters?.warranty || '',
  stock: props.filters?.stock || '',
  sale: !!props.filters?.sale,
  featured: !!props.filters?.featured,
  today_best_deals: !!props.filters?.today_best_deals,
  best_seller: !!props.filters?.best_seller,
  sort: props.filters?.sort || 'latest',
  min_price: props.filters?.min_price ?? '',
  max_price: props.filters?.max_price ?? '',
  page: Number(props.filters?.page || 1),
}))

const localFilters = ref({ ...currentFilters.value })

const heading = computed(() => {
  if (currentFilters.value.brand && currentFilters.value.category) {
    return `${currentFilters.value.brand} ${currentFilters.value.category}`
  }

  return currentFilters.value.category || currentFilters.value.brand || props.title
})

const breadcrumbItems = computed(() => [
  {
    label: 'Home',
    href: route('frontend.root'),
  },
  {
    label: props.title,
    href: props.indexUrl,
  },
])

const activeFilterCount = computed(() => {
  const filters = localFilters.value
  return [
    filters.search,
    filters.category,
    filters.brand,
    filters.type,
    filters.warranty,
    filters.stock,
    filters.sale,
    filters.featured,
    filters.today_best_deals,
    filters.best_seller,
    filters.min_price,
    filters.max_price,
  ].filter((value) => {
    if (typeof value === 'boolean') return value
    return String(value ?? '').trim().length > 0
  }).length
})

function cleanParams(params: Record<string, unknown>) {
  return Object.fromEntries(
    Object.entries(params).filter(([, value]) => {
      if (value === null || typeof value === 'undefined') return false
      if (typeof value === 'string' && !value.trim()) return false
      if (typeof value === 'boolean') return value
      return true
    }),
  )
}

function buildApiUrl() {
  const params = new URLSearchParams()
  const filters = currentFilters.value

  Object.entries({
    search: filters.search,
    category: filters.category,
    brand: filters.brand,
    type: filters.type,
    warranty: filters.warranty,
    stock: filters.stock,
    sort: filters.sort,
    min_price: filters.min_price,
    max_price: filters.max_price,
    page: filters.page || 1,
  }).forEach(([key, value]) => {
    if (value !== null && typeof value !== 'undefined' && String(value).trim()) {
      params.set(key, String(value))
    }
  })

  if (filters.sale) params.set('sale', '1')
  if (filters.featured) params.set('featured', '1')
  if (filters.today_best_deals) params.set('today_best_deals', '1')
  if (filters.best_seller) params.set('best_seller', '1')

  return `${props.productsUrl}?${params.toString()}`
}

async function fetchProducts() {
  if (batchTimer) {
    clearTimeout(batchTimer)
    batchTimer = null
  }

  if (activeController) {
    activeController.abort()
    activeController = null
  }

  const controller = new AbortController()
  activeController = controller
  loading.value = true
  loadingMore.value = false
  loadError.value = ''
  products.value = []

  try {
    const response = await fetch(buildApiUrl(), {
      method: 'GET',
      signal: controller.signal,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })

    if (!response.ok) {
      throw new Error('Unable to load products.')
    }

    const data = await response.json()

    if (activeController !== controller) return

    const loadedProducts = Array.isArray(data?.products) ? data.products : []

    pagination.value = {
      current_page: Number(data?.pagination?.current_page || 1),
      last_page: Number(data?.pagination?.last_page || 1),
      per_page: Number(data?.pagination?.per_page || 12),
      total: Number(data?.pagination?.total || 0),
      from: Number(data?.pagination?.from || 0),
      to: Number(data?.pagination?.to || 0),
    }

    products.value = loadedProducts.slice(0, 8)
    loading.value = false

    const remainingProducts = loadedProducts.slice(8)

    if (remainingProducts.length) {
      loadingMore.value = true
      batchTimer = setTimeout(() => {
        products.value = [...products.value, ...remainingProducts]
        loadingMore.value = false
      }, 260)
    }
  } catch (error) {
    if ((error as Error)?.name === 'AbortError') return

    loading.value = false
    loadingMore.value = false
    loadError.value = 'Unable to load products right now.'
    products.value = []
    pagination.value = {
      current_page: 1,
      last_page: 1,
      per_page: 12,
      total: 0,
      from: 0,
      to: 0,
    }
  }
}

function visitWithFilters(nextFilters: Partial<Filters>, replace = true) {
  const merged = {
    ...currentFilters.value,
    ...nextFilters,
  }

  router.get(
    props.indexUrl,
    cleanParams({
      search: merged.search,
      category: merged.category,
      brand: merged.brand,
      type: merged.type,
      warranty: merged.warranty,
      stock: merged.stock,
      sale: merged.sale ? 1 : undefined,
      featured: merged.featured ? 1 : undefined,
      today_best_deals: merged.today_best_deals ? 1 : undefined,
      best_seller: merged.best_seller ? 1 : undefined,
      sort: merged.sort,
      min_price: merged.min_price,
      max_price: merged.max_price,
      page: merged.page || 1,
    }),
    {
      preserveState: true,
      preserveScroll: true,
      replace,
    },
  )
}

function applyFilters() {
  visitWithFilters({ ...localFilters.value, page: 1 }, true)
}

function resetFilters() {
  localFilters.value = {
    search: '',
    category: '',
    brand: '',
    type: '',
    warranty: '',
    stock: '',
    sale: false,
    featured: false,
    today_best_deals: false,
    best_seller: false,
    sort: 'latest',
    min_price: '',
    max_price: '',
    page: 1,
  }

  router.get(props.indexUrl, {}, { preserveState: true, preserveScroll: true, replace: true })
}

function changePage(pageNumber: number) {
  if (pageNumber === pagination.value.current_page) return

  visitWithFilters({ page: pageNumber }, false)

  requestAnimationFrame(() => {
    document.getElementById('collection-product-grid')?.scrollIntoView({
      behavior: 'smooth',
      block: 'start',
    })
  })
}

const visiblePages = computed(() => {
  const current = pagination.value.current_page
  const last = pagination.value.last_page
  const start = Math.max(1, current - 2)
  const end = Math.min(last, current + 2)

  return Array.from({ length: end - start + 1 }, (_, index) => start + index)
})

const skeletonCount = computed(() => {
  if (loading.value) return 12
  if (loadingMore.value) return Math.max(0, 12 - products.value.length)
  return 0
})

watch(
  currentFilters,
  (filters) => {
    localFilters.value = { ...filters }
  },
  { immediate: true },
)

watch(
  () => page.url,
  () => {
    fetchProducts()
  },
  { immediate: true },
)

onBeforeUnmount(() => {
  if (batchTimer) {
    clearTimeout(batchTimer)
    batchTimer = null
  }

  if (activeController) {
    activeController.abort()
    activeController = null
  }
})
</script>

<template>
  <section class="collection-page">
    <div class="collection-shell">
      <nav class="collection-breadcrumb" aria-label="Breadcrumb">
        <template v-for="(item, index) in breadcrumbItems" :key="`${item.label}-${index}`">
          <Link :href="item.href" class="collection-breadcrumb__link">
            {{ item.label }}
          </Link>
          <span v-if="index < breadcrumbItems.length - 1" class="collection-breadcrumb__separator">/</span>
        </template>
      </nav>

      <header class="collection-header">
        <span v-if="eyebrow" class="collection-kicker">{{ eyebrow }}</span>
        <h1>{{ heading }}</h1>
        <p>{{ subtitle }}</p>
      </header>

      <form class="collection-filter" @submit.prevent="applyFilters">
        <div class="collection-filter__head">
          <div class="collection-filter__title">
            <span class="collection-filter__icon" aria-hidden="true">
              <SlidersHorizontal :size="18" />
            </span>
            <div>
              <strong>Filters</strong>
              <small>{{ activeFilterCount ? `${activeFilterCount} active` : 'Refine your search' }}</small>
            </div>
          </div>

          <button
            type="button"
            class="collection-filter__toggle"
            :aria-expanded="filterPanelOpen ? 'true' : 'false'"
            @click="filterPanelOpen = !filterPanelOpen"
          >
            <span>{{ filterPanelOpen ? 'Hide' : 'Show' }}</span>
            <ChevronDown :size="18" :class="{ 'collection-filter__toggle-icon--open': filterPanelOpen }" />
          </button>
        </div>

        <div class="collection-filter__content" :class="{ 'collection-filter__content--open': filterPanelOpen }">
          <div class="collection-filter__top">
            <label class="collection-field collection-field--search">
              <span>Search</span>
              <div class="collection-input-wrap">
                <Search :size="17" aria-hidden="true" />
                <input
                  v-model="localFilters.search"
                  type="search"
                  placeholder="Search products, SKU, brand..."
                />
              </div>
            </label>

            <label class="collection-field">
              <span>Sort</span>
              <div class="collection-select-wrap">
                <select v-model="localFilters.sort">
                  <option value="latest">Newest</option>
                  <option value="oldest">Oldest</option>
                  <option value="price_low_high">Price: Low to High</option>
                  <option value="price_high_low">Price: High to Low</option>
                  <option value="name_az">Name: A to Z</option>
                  <option value="name_za">Name: Z to A</option>
                </select>
                <ChevronDown :size="18" aria-hidden="true" />
              </div>
            </label>

            <label class="collection-field">
              <span>Stock</span>
              <div class="collection-select-wrap">
                <select v-model="localFilters.stock">
                  <option value="">All Stock</option>
                  <option value="in_stock">In Stock</option>
                  <option value="out_of_stock">Out of Stock</option>
                </select>
                <ChevronDown :size="18" aria-hidden="true" />
              </div>
            </label>
          </div>

          <div class="collection-filter__grid">
            <label class="collection-field">
              <span>Category</span>
              <div class="collection-select-wrap">
                <select v-model="localFilters.category">
                  <option value="">All Categories</option>
                  <option v-for="category in categories" :key="category.id" :value="category.name">
                    {{ category.name }}
                  </option>
                </select>
                <ChevronDown :size="18" aria-hidden="true" />
              </div>
            </label>

            <label class="collection-field">
              <span>{{ brandLabel }}</span>
              <div class="collection-select-wrap">
                <select v-model="localFilters.brand">
                  <option value="">All {{ brandLabel || 'Brands' }}</option>
                  <option v-for="brand in brands" :key="brand.id" :value="brand.name">
                    {{ brand.name }}
                  </option>
                </select>
                <ChevronDown :size="18" aria-hidden="true" />
              </div>
            </label>

            <label v-if="types.length" class="collection-field">
              <span>{{ typeLabel || 'Type' }}</span>
              <div class="collection-select-wrap">
                <select v-model="localFilters.type">
                  <option value="">All {{ typeLabel || 'Types' }}</option>
                  <option v-for="type in types" :key="type.id" :value="type.value || type.name">
                    {{ type.name }}
                  </option>
                </select>
                <ChevronDown :size="18" aria-hidden="true" />
              </div>
            </label>

            <label v-if="warranties.length" class="collection-field">
              <span>Warranty</span>
              <div class="collection-select-wrap">
                <select v-model="localFilters.warranty">
                  <option value="">All Warranty</option>
                  <option v-for="warranty in warranties" :key="warranty.id" :value="warranty.name">
                    {{ warranty.name }}
                  </option>
                </select>
                <ChevronDown :size="18" aria-hidden="true" />
              </div>
            </label>

            <label class="collection-field">
              <span>Min Price</span>
              <input v-model="localFilters.min_price" min="0" type="number" placeholder="Min" />
            </label>

            <label class="collection-field">
              <span>Max Price</span>
              <input v-model="localFilters.max_price" min="0" type="number" placeholder="Max" />
            </label>
          </div>

          <div class="collection-filter__bottom">
            <label class="collection-toggle">
              <input v-model="localFilters.sale" type="checkbox" />
              <span class="collection-toggle__box"><Check :size="13" /></span>
              <span>Sale</span>
            </label>

            <label class="collection-toggle">
              <input v-model="localFilters.today_best_deals" type="checkbox" />
              <span class="collection-toggle__box"><Check :size="13" /></span>
              <span>Today Best Deals</span>
            </label>

            <label class="collection-toggle">
              <input v-model="localFilters.best_seller" type="checkbox" />
              <span class="collection-toggle__box"><Check :size="13" /></span>
              <span>Best Seller</span>
            </label>

            <label class="collection-toggle">
              <input v-model="localFilters.featured" type="checkbox" />
              <span class="collection-toggle__box"><Check :size="13" /></span>
              <span>Featured</span>
            </label>

            <div class="collection-filter__actions">
              <button type="button" class="collection-reset" @click="resetFilters">
                <RotateCcw :size="16" aria-hidden="true" />
                <span>Reset</span>
              </button>
              <button type="submit" class="collection-apply">
                <span>Apply Filters</span>
              </button>
            </div>
          </div>
        </div>
      </form>

      <div id="collection-product-grid" class="collection-results">
        <div class="collection-results__bar">
          <span v-if="loading">Loading products...</span>
          <span v-else-if="pagination.total">
            Showing {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
          </span>
          <span v-else>Ready to browse</span>
        </div>

        <div v-if="loadError" class="collection-state collection-state--error">
          <span>{{ loadError }}</span>
          <button type="button" @click="fetchProducts">Retry</button>
        </div>

        <template v-else>
          <div v-if="!loading && !products.length" class="collection-state">
            No products found for these filters.
          </div>

          <div v-else class="collection-grid">
            <HomeProductCard
              v-for="product in products"
              :key="product.id"
              :product="product"
            />
            <HomeProductCard
              v-for="index in skeletonCount"
              :key="`collection-skeleton-${index}`"
              skeleton
            />
          </div>

          <div v-if="pagination.last_page > 1" class="collection-pagination">
            <button
              type="button"
              :disabled="pagination.current_page === 1"
              @click="changePage(pagination.current_page - 1)"
            >
              Prev
            </button>

            <button
              v-for="pageNumber in visiblePages"
              :key="pageNumber"
              type="button"
              :class="{ 'collection-pagination__active': pageNumber === pagination.current_page }"
              @click="changePage(pageNumber)"
            >
              {{ pageNumber }}
            </button>

            <button
              type="button"
              :disabled="pagination.current_page === pagination.last_page"
              @click="changePage(pagination.current_page + 1)"
            >
              Next
            </button>
          </div>
        </template>
      </div>
    </div>
  </section>
</template>

<style scoped>
.collection-page {
  min-height: 100vh;
  background: #ffffff;
  padding: 24px 20px 64px;
  font-family: 'DezeStoreCustomFont', var(--font-sans, sans-serif);
}

.collection-shell {
  width: 100%;
  max-width: 1280px;
  margin: 0 auto;
}

.collection-breadcrumb {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  margin-bottom: 22px;
  color: #64748b;
  font-size: 13px;
}

.collection-breadcrumb__link {
  text-decoration: none;
  transition: color 0.18s ease;
}

.collection-breadcrumb__link:hover {
  color: #071f4f;
}

.collection-breadcrumb__separator {
  color: #cbd5e1;
}

.collection-header {
  margin-bottom: 24px;
}

.collection-kicker {
  display: inline-flex;
  margin-bottom: 8px;
  color: #071f4f;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0;
  text-transform: uppercase;
}

.collection-header h1 {
  margin: 0;
  color: #0f172a;
  font-size: 40px;
  font-weight: 700;
  line-height: 1;
  letter-spacing: 0;
}

.collection-header p {
  max-width: 720px;
  margin: 12px 0 0;
  color: #64748b;
  font-size: 15px;
  line-height: 1.65;
}

.collection-filter {
  margin-bottom: 28px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background:
    radial-gradient(circle at top left, rgba(56, 189, 248, 0.11), transparent 32%),
    linear-gradient(135deg, rgba(7, 31, 79, 0.045), transparent 44%),
    #ffffff;
  box-shadow: 0 18px 55px rgba(15, 23, 42, 0.07);
  overflow: hidden;
}

.collection-filter__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  border-bottom: 1px solid rgba(226, 232, 240, 0.85);
  padding: 14px 16px;
}

.collection-filter__title {
  display: flex;
  min-width: 0;
  align-items: center;
  gap: 11px;
}

.collection-filter__icon {
  display: inline-flex;
  width: 38px;
  height: 38px;
  flex: 0 0 38px;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  background: #071f4f;
  color: #ffffff;
}

.collection-filter__title strong,
.collection-filter__title small {
  display: block;
}

.collection-filter__title strong {
  color: #0f172a;
  font-size: 15px;
  font-weight: 800;
  line-height: 1.1;
}

.collection-filter__title small {
  margin-top: 3px;
  color: #64748b;
  font-size: 12px;
  font-weight: 700;
}

.collection-filter__toggle {
  display: none;
  min-height: 38px;
  align-items: center;
  gap: 7px;
  border: 1px solid #dbe3ef;
  border-radius: 8px;
  background: #ffffff;
  padding: 0 12px;
  color: #334155;
  font-size: 12px;
  font-weight: 800;
  transition:
    border-color 0.2s ease,
    color 0.2s ease,
    transform 0.2s ease;
}

.collection-filter__toggle:hover {
  border-color: #071f4f;
  color: #071f4f;
}

.collection-filter__toggle svg {
  transition: transform 0.24s ease;
}

.collection-filter__toggle-icon--open {
  transform: rotate(180deg);
}

.collection-filter__content {
  padding: 16px;
}

.collection-filter__top,
.collection-filter__grid {
  display: grid;
  gap: 12px;
}

.collection-filter__top {
  grid-template-columns: minmax(220px, 1.8fr) repeat(2, minmax(160px, 0.7fr));
}

.collection-filter__grid {
  grid-template-columns: repeat(6, minmax(0, 1fr));
  margin-top: 12px;
}

.collection-field {
  display: grid;
  gap: 7px;
  min-width: 0;
}

.collection-field span {
  color: #334155;
  font-size: 12px;
  font-weight: 700;
}

.collection-field input,
.collection-field select {
  min-width: 0;
  width: 100%;
  min-height: 44px;
  border: 1px solid #dbe3ef;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.92);
  padding: 0 12px;
  color: #0f172a;
  font-size: 13px;
  outline: none;
  transition:
    border-color 0.18s ease,
    box-shadow 0.18s ease;
}

.collection-field select {
  appearance: none;
  padding-right: 36px;
}

.collection-input-wrap,
.collection-select-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.collection-input-wrap > svg,
.collection-select-wrap > svg {
  position: absolute;
  z-index: 2;
  color: #94a3b8;
  pointer-events: none;
}

.collection-input-wrap > svg {
  left: 13px;
}

.collection-select-wrap > svg {
  right: 12px;
  transition: transform 0.2s ease;
}

.collection-input-wrap input {
  padding-left: 38px;
}

.collection-field:focus-within .collection-select-wrap > svg {
  transform: translateY(1px);
  color: #071f4f;
}

.collection-field input:focus,
.collection-field select:focus {
  border-color: #071f4f;
  box-shadow: 0 0 0 3px rgba(7, 31, 79, 0.1);
}

.collection-filter__bottom {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px;
  margin-top: 14px;
}

.collection-toggle {
  position: relative;
  display: inline-flex;
  min-height: 38px;
  align-items: center;
  gap: 9px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #ffffff;
  padding: 0 13px;
  color: #334155;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition:
    border-color 0.2s ease,
    background-color 0.2s ease,
    color 0.2s ease,
    transform 0.2s ease;
}

.collection-toggle input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.collection-toggle:hover {
  border-color: #071f4f;
  transform: translateY(-1px);
}

.collection-toggle__box {
  display: inline-flex;
  width: 17px;
  height: 17px;
  flex: 0 0 17px;
  align-items: center;
  justify-content: center;
  border: 1px solid #cbd5e1;
  border-radius: 5px;
  color: transparent;
  transition:
    border-color 0.2s ease,
    background-color 0.2s ease,
    color 0.2s ease,
    transform 0.2s ease;
}

.collection-toggle input:checked + .collection-toggle__box {
  border-color: #071f4f;
  background: #071f4f;
  color: #ffffff;
  transform: scale(1.04);
}

.collection-toggle:has(input:checked) {
  border-color: rgba(7, 31, 79, 0.36);
  background: rgba(7, 31, 79, 0.06);
  color: #071f4f;
}

.collection-filter__actions {
  display: inline-flex;
  gap: 8px;
  margin-left: auto;
}

.collection-reset,
.collection-apply,
.collection-pagination button,
.collection-state button {
  display: inline-flex;
  min-height: 40px;
  align-items: center;
  justify-content: center;
  gap: 7px;
  border-radius: 8px;
  padding: 0 16px;
  font-size: 13px;
  font-weight: 700;
  transition:
    border-color 0.18s ease,
    background-color 0.18s ease,
    color 0.18s ease,
    transform 0.18s ease;
}

.collection-reset {
  border: 1px solid #dbe3ef;
  background: #ffffff;
  color: #334155;
}

.collection-apply,
.collection-state button {
  border: 1px solid #071f4f;
  background: #071f4f;
  color: #ffffff;
}

.collection-reset:hover,
.collection-apply:hover,
.collection-state button:hover {
  transform: translateY(-1px);
}

.collection-results {
  scroll-margin-top: 130px;
}

.collection-results__bar {
  display: flex;
  min-height: 28px;
  align-items: center;
  justify-content: flex-end;
  margin-bottom: 14px;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.collection-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 18px;
}

.collection-state {
  display: flex;
  min-height: 220px;
  align-items: center;
  justify-content: center;
  border: 1px dashed #cbd5e1;
  border-radius: 8px;
  background: #ffffff;
  color: #64748b;
  text-align: center;
}

.collection-state--error {
  flex-direction: column;
  gap: 12px;
  border-color: #fecaca;
  background: #fff7f7;
  color: #b91c1c;
}

.collection-pagination {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 8px;
  margin-top: 28px;
}

.collection-pagination button {
  min-width: 44px;
  border: 1px solid #dbe3ef;
  background: #ffffff;
  color: #334155;
}

.collection-pagination button:hover:not(:disabled),
.collection-pagination__active {
  border-color: #071f4f !important;
  background: #071f4f !important;
  color: #ffffff !important;
}

.collection-pagination button:disabled {
  cursor: not-allowed;
  opacity: 0.42;
}

@media (max-width: 1180px) {
  .collection-filter__grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .collection-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 820px) {
  .collection-page {
    padding-inline: 16px;
  }

  .collection-filter__toggle {
    display: inline-flex;
  }

  .collection-filter__head {
    padding: 12px;
  }

  .collection-filter__content {
    max-height: 0;
    overflow: hidden;
    padding: 0 12px;
    opacity: 0;
    transform: translateY(-8px);
    transition:
      max-height 0.38s ease,
      padding 0.28s ease,
      opacity 0.24s ease,
      transform 0.28s ease;
  }

  .collection-filter__content--open {
    max-height: 1100px;
    padding: 12px;
    opacity: 1;
    transform: translateY(0);
  }

  .collection-filter__top,
  .collection-filter__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .collection-filter__actions {
    width: 100%;
    margin-left: 0;
  }

  .collection-reset,
  .collection-apply {
    flex: 1;
  }

  .collection-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 560px) {
  .collection-header h1 {
    font-size: 31px;
  }

  .collection-filter {
    margin-inline: -4px;
  }

  .collection-filter__title {
    gap: 9px;
  }

  .collection-filter__icon {
    width: 34px;
    height: 34px;
    flex-basis: 34px;
  }

  .collection-filter__top,
  .collection-filter__grid {
    grid-template-columns: minmax(0, 1fr);
  }

  .collection-filter__bottom {
    align-items: stretch;
  }

  .collection-toggle {
    width: 100%;
  }

  .collection-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .collection-filter__content,
  .collection-field input,
  .collection-field select,
  .collection-toggle,
  .collection-toggle__box,
  .collection-reset,
  .collection-apply {
    transition: none !important;
    transform: none !important;
  }
}
</style>
