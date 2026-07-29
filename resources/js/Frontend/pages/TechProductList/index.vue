<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { route } from 'ziggy-js'
import AppLayout from '@/Frontend/layouts/AppLayout.vue'
import TechProductFilter from './partials/TechProductFilter.vue'
import TechProductList from './partials/TechProductList.vue'

defineOptions({
  layout: AppLayout,
})

type BrandItem = {
  id: number | string
  name: string
  logo_url?: string | null
}

type CategoryItem = {
  id: number | string
  name: string
  image_url?: string | null
  status?: string | null
  brands?: BrandItem[]
}

type ProductColor = {
  id: number | string
  name: string
  color_code?: string | null
  image_url?: string | null
}

type TechProductCard = {
  id: number | string
  name: string
  category_name?: string | null
  brand_name?: string | null
  thumbnail_url: string | null
  hover_image_url: string | null
  regular_price: number | null
  display_price: number | null
  koko_pay_percentage?: number | null
  koko_installment_price?: number | null
  has_discount: boolean
  discount_label?: string | null
  is_sold_out: boolean
  colors?: ProductColor[]
}

type Filters = {
  search?: string | null
  category?: string | null
  brand?: string | null
  stock?: string | null
  sale?: boolean
  hot_deals?: boolean
  featured?: boolean
  best_seller?: boolean
  top_rated?: boolean
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
  categories: CategoryItem[]
  filters: Filters
}>()

const page = usePage()

const products = ref<TechProductCard[]>([])
const loading = ref(false)
const loadingMore = ref(false)
const loadError = ref('')

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
  stock: props.filters?.stock || '',
  sale: !!props.filters?.sale,
  hot_deals: !!props.filters?.hot_deals,
  featured: !!props.filters?.featured,
  best_seller: !!props.filters?.best_seller,
  top_rated: !!props.filters?.top_rated,
  sort: props.filters?.sort || 'oldest',
  min_price: props.filters?.min_price ?? '',
  max_price: props.filters?.max_price ?? '',
  page: Number(props.filters?.page || 1),
}))

const heading = computed(() => {
  if (currentFilters.value.brand && currentFilters.value.category) {
    return `${currentFilters.value.brand} ${currentFilters.value.category}`
  }

  if (currentFilters.value.brand) {
    return currentFilters.value.brand
  }

  if (currentFilters.value.category) {
    return currentFilters.value.category
  }

  return 'All Tech Products'
})

const breadcrumbItems = computed(() => {
  const items = [
    {
      label: 'Home',
      href: route('frontend.root'),
    },
    {
      label: 'Tech Products',
      href: route('frontend.tech-products.index'),
    },
  ]

  if (currentFilters.value.category) {
    items.push({
      label: currentFilters.value.category,
      href: route('frontend.tech-products.index', {
        category: currentFilters.value.category,
      }),
    })
  }

  if (currentFilters.value.brand) {
    items.push({
      label: currentFilters.value.brand,
      href: route('frontend.tech-products.index', {
        category: currentFilters.value.category || undefined,
        brand: currentFilters.value.brand,
      }),
    })
  }

  return items
})

function cleanParams(params: Record<string, unknown>) {
  return Object.fromEntries(
    Object.entries(params).filter(([, value]) => {
      if (value === null || typeof value === 'undefined') return false
      if (typeof value === 'string' && !value.trim()) return false
      if (typeof value === 'boolean') return value
      return true
    })
  )
}

function buildApiUrl() {
  const params = new URLSearchParams()

  if (currentFilters.value.search) {
    params.set('search', currentFilters.value.search)
  }

  if (currentFilters.value.category) {
    params.set('category', currentFilters.value.category)
  }

  if (currentFilters.value.brand) {
    params.set('brand', currentFilters.value.brand)
  }

  if (currentFilters.value.stock) {
    params.set('stock', currentFilters.value.stock)
  }

  if (currentFilters.value.sale) {
    params.set('sale', '1')
  }

  if (currentFilters.value.hot_deals) {
    params.set('hot_deals', '1')
  }

  if (currentFilters.value.featured) {
    params.set('featured', '1')
  }

  if (currentFilters.value.best_seller) {
    params.set('best_seller', '1')
  }

  if (currentFilters.value.top_rated) {
    params.set('top_rated', '1')
  }

  if (currentFilters.value.sort) {
    params.set('sort', currentFilters.value.sort)
  }

  if (String(currentFilters.value.min_price).trim()) {
    params.set('min_price', String(currentFilters.value.min_price))
  }

  if (String(currentFilters.value.max_price).trim()) {
    params.set('max_price', String(currentFilters.value.max_price))
  }

  params.set('page', String(currentFilters.value.page || 1))

  return `${route('frontend.tech-products.products')}?${params.toString()}`
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
      throw new Error('Failed to load products')
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

    products.value = loadedProducts.slice(0, 6)
    loading.value = false

    const remainingProducts = loadedProducts.slice(6)

    if (remainingProducts.length) {
      loadingMore.value = true

      batchTimer = setTimeout(() => {
        products.value = [...products.value, ...remainingProducts]
        loadingMore.value = false
      }, 320)
    }
  } catch (error) {
    if ((error as Error)?.name === 'AbortError') return

    console.error('Tech products fetch error:', error)
    loading.value = false
    loadingMore.value = false
    loadError.value = 'Unable to load tech products right now.'
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

  const validBrands =
    props.categories.find((category) => category.name === merged.category)?.brands || []

  if (
    merged.brand &&
    merged.category &&
    !validBrands.some((brand) => brand.name === merged.brand)
  ) {
    merged.brand = ''
  }

  router.get(
    route('frontend.tech-products.index'),
    cleanParams({
      search: merged.search,
      category: merged.category,
      brand: merged.brand,
      stock: merged.stock,
      sale: merged.sale ? 1 : undefined,
      hot_deals: merged.hot_deals ? 1 : undefined,
      featured: merged.featured ? 1 : undefined,
      best_seller: merged.best_seller ? 1 : undefined,
      top_rated: merged.top_rated ? 1 : undefined,
      sort: merged.sort,
      min_price: merged.min_price,
      max_price: merged.max_price,
      page: merged.page || 1,
    }),
    {
      preserveState: true,
      preserveScroll: true,
      replace,
    }
  )
}

function applyFilters(nextFilters: Filters) {
  visitWithFilters(
    {
      ...nextFilters,
      page: 1,
    },
    true
  )
}

function resetFilters() {
  router.get(
    route('frontend.tech-products.index'),
    {},
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  )
}

function changePage(pageNumber: number) {
  if (pageNumber === pagination.value.current_page) return

  visitWithFilters({ page: pageNumber }, false)

  requestAnimationFrame(() => {
    document.getElementById('tech-product-list-section')?.scrollIntoView({
      behavior: 'smooth',
      block: 'start',
    })
  })
}

watch(
  () => page.url,
  () => {
    fetchProducts()
  },
  { immediate: true }
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
  <section class="bg-white pb-16 pt-6 sm:pt-8 lg:pt-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-5">
      <nav
        class="mb-5 flex flex-wrap items-center gap-2 text-sm text-neutral-500 sm:mb-7"
        aria-label="Breadcrumb"
      >
        <template v-for="(item, index) in breadcrumbItems" :key="`${item.label}-${index}`">
          <Link
            :href="item.href"
            class="transition-colors hover:text-neutral-900"
          >
            {{ item.label }}
          </Link>

          <span
            v-if="index < breadcrumbItems.length - 1"
            class="text-neutral-300"
          >
            /
          </span>
        </template>
      </nav>

      <div class="mb-6 sm:mb-8">
        <h1 class="text-3xl font-semibold tracking-normal text-neutral-900 sm:text-4xl">
          {{ heading }}
        </h1>
      </div>

      <div
        id="tech-product-list-section"
        class="space-y-6"
      >
        <TechProductFilter
          :filters="currentFilters"
          :categories="categories"
          :loading="loading"
          @apply="applyFilters"
          @reset="resetFilters"
        />

        <TechProductList
          :products="products"
          :loading="loading"
          :loading-more="loadingMore"
          :load-error="loadError"
          :pagination="pagination"
          @retry="fetchProducts"
          @page-change="changePage"
        />
      </div>
    </div>
  </section>
</template>
