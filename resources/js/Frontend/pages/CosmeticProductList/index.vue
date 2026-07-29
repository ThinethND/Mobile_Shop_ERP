<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { route } from 'ziggy-js'
import AppLayout from '@/Frontend/layouts/AppLayout.vue'
import CosmeticFeaturedProducts from './partials/CosmeticFeaturedProducts.vue'
import CosmeticProductFilter from './partials/CosmeticProductFilter.vue'
import CosmeticProductList from './partials/CosmeticProductList.vue'

defineOptions({
  layout: AppLayout,
})

type CosmeticProductCard = {
  id: number | string
  name: string
  slug?: string | null
  brand_name?: string | null
  category_name?: string | null
  country_name?: string | null
  country_code?: string | null
  country_flag_url?: string | null
  thumbnail_url: string | null
  hover_image_url: string | null
  currency?: string | null
  regular_price: number | null
  sale_price: number | null
  display_price: number | null
  has_discount: boolean
  discount_label?: string | null
  is_sold_out: boolean
  url?: string | null
}

type Filters = {
  search?: string | null
  cosmetic_category?: string | null
  cosmetic_brand?: string | null
  cosmetic_country?: string | null
  stock?: string | null
  sale?: boolean
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

type BrandItem = {
  id: number | string
  name: string
  logo_url?: string | null
}

type CategoryItem = {
  id: number | string
  name: string
  slug?: string | null
  brands?: BrandItem[]
}

type CountryItem = {
  id: number | string
  name: string
  code?: string | null
  flag_image_url?: string | null
}

const props = defineProps<{
  categories: CategoryItem[]
  countries: CountryItem[]
  filters: Filters
}>()

const page = usePage()

const products = ref<CosmeticProductCard[]>([])
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
  cosmetic_category: props.filters?.cosmetic_category || '',
  cosmetic_brand: props.filters?.cosmetic_brand || '',
  cosmetic_country: props.filters?.cosmetic_country || '',
  stock: props.filters?.stock || '',
  sale: !!props.filters?.sale,
  sort: props.filters?.sort || 'oldest',
  min_price: props.filters?.min_price ?? '',
  max_price: props.filters?.max_price ?? '',
  page: Number(props.filters?.page || 1),
}))

const breadcrumbItems = computed(() => {
  const items = [
    {
      label: 'Home',
      href: route('frontend.root'),
    },
    {
      label: 'Cosmetic Products',
      href: route('frontend.cosmetic-products.index'),
    },
  ]

  if (currentFilters.value.cosmetic_category) {
    items.push({
      label: currentFilters.value.cosmetic_category,
      href: route('frontend.cosmetic-products.index', {
        cosmetic_category: currentFilters.value.cosmetic_category,
      }),
    })
  }

  if (currentFilters.value.cosmetic_brand) {
    items.push({
      label: currentFilters.value.cosmetic_brand,
      href: route('frontend.cosmetic-products.index', {
        cosmetic_category: currentFilters.value.cosmetic_category || undefined,
        cosmetic_brand: currentFilters.value.cosmetic_brand,
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
  ) as Record<string, any>
}

function buildApiUrl() {
  const params = new URLSearchParams()

  if (currentFilters.value.search) {
    params.set('search', currentFilters.value.search)
  }

  if (currentFilters.value.cosmetic_category) {
    params.set('cosmetic_category', currentFilters.value.cosmetic_category)
  }

  if (currentFilters.value.cosmetic_brand) {
    params.set('cosmetic_brand', currentFilters.value.cosmetic_brand)
  }

  if (currentFilters.value.cosmetic_country) {
    params.set('cosmetic_country', currentFilters.value.cosmetic_country)
  }

  if (currentFilters.value.stock) {
    params.set('stock', currentFilters.value.stock)
  }

  if (currentFilters.value.sale) {
    params.set('sale', '1')
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

  return `${route('frontend.cosmetic-products.products')}?${params.toString()}`
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

    console.error('Cosmetic products fetch error:', error)
    loading.value = false
    loadingMore.value = false
    loadError.value = 'Unable to load cosmetic products right now.'
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
  const merged = { ...currentFilters.value, ...nextFilters }

  const validBrands =
    props.categories.find((category) => category.name === merged.cosmetic_category)?.brands || []

  if (
    merged.cosmetic_brand &&
    merged.cosmetic_category &&
    !validBrands.some((brand) => brand.name === merged.cosmetic_brand)
  ) {
    merged.cosmetic_brand = ''
  }

  router.get(
    route('frontend.cosmetic-products.index'),
    cleanParams({
      search: merged.search,
      cosmetic_category: merged.cosmetic_category,
      cosmetic_brand: merged.cosmetic_brand,
      cosmetic_country: merged.cosmetic_country,
      stock: merged.stock,
      sale: merged.sale ? 1 : undefined,
      sort: merged.sort,
      min_price: merged.min_price,
      max_price: merged.max_price,
      page: merged.page || 1,
    }),
    { preserveState: true, preserveScroll: true, replace }
  )
}

function applyFilters(nextFilters: Filters) {
  visitWithFilters({ ...nextFilters, page: 1 }, true)
}

function resetFilters() {
  router.get(route('frontend.cosmetic-products.index'), {}, { preserveState: true, preserveScroll: true, replace: true })
}

function changePage(pageNumber: number) {
  if (pageNumber === pagination.value.current_page) return

  visitWithFilters({ page: pageNumber }, false)

  requestAnimationFrame(() => {
    document.getElementById('cosmetic-product-list-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  })
}

watch(() => page.url, () => { fetchProducts() }, { immediate: true })

onBeforeUnmount(() => {
  if (batchTimer) { clearTimeout(batchTimer); batchTimer = null }
  if (activeController) { activeController.abort(); activeController = null }
})

</script>

<template>
  <section class="bg-white pb-16 pt-6 sm:pt-8 lg:pt-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-5">
      <nav class="mb-5 flex flex-wrap items-center gap-2 text-sm text-neutral-500 sm:mb-7" aria-label="Breadcrumb">
        <template v-for="(item, index) in breadcrumbItems" :key="`${item.label}-${index}`">
          <Link :href="item.href" class="transition-colors hover:text-neutral-900">{{ item.label }}</Link>
          <span v-if="index < breadcrumbItems.length - 1" class="text-neutral-300">/</span>
        </template>
      </nav>

      <CosmeticFeaturedProducts />

      <div id="cosmetic-product-list-section" class="mt-10 sm:mt-12">
        <div class="mb-6 sm:mb-8">
          <h1 class="text-3xl font-semibold tracking-normal text-neutral-900 sm:text-4xl">
            All Cosmetic Products
          </h1>
          <p class="mt-1 text-sm text-neutral-500 sm:text-base">
            Everything in Beauty, All in One Place
          </p>
        </div>

        <div class="space-y-6">
          <CosmeticProductFilter
            :filters="currentFilters"
            :categories="props.categories"
            :countries="props.countries"
            :loading="loading"
            @apply="applyFilters"
            @reset="resetFilters"
          />

          <CosmeticProductList
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
    </div>
  </section>
</template>
