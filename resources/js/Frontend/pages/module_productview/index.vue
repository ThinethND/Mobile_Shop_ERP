<!--module_productview/index.vue-->

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { route } from 'ziggy-js'
import AppLayout from '@/Frontend/layouts/AppLayout.vue'
import KokoPayLine from '@/Frontend/components/KokoPayLine.vue'
import ProductReviewsPanel from '@/Frontend/components/ProductReviewsPanel.vue'
import HomeProductCard from '@/Frontend/pages/Home/components/HomeProductCard.vue'
import { useCart } from '@/Frontend/pages/shop/composables/useCart'
import { kokoInstallmentAmount } from '@/Frontend/utils/kokoPay'

defineOptions({
  layout: AppLayout,
})

type BreadcrumbItem = {
  label: string
  href: string | null
}

type ShellData = {
  name?: string | null
  breadcrumb?: BreadcrumbItem[]
}

type GalleryItem = {
  id: string | number
  src: string
}

type SpecRow = {
  label: string
  value: string
}

type BrandPayload = {
  id?: number | string | null
  name?: string | null
  logo_url?: string | null
}

type ProductPayload = {
  id: number | string
  product_type: string
  name: string
  slug?: string | null
  sku?: string | null
  short_description?: string | null
  long_description?: string | null
  brand?: BrandPayload | null
  category?: {
    id?: number | string | null
    name?: string | null
  } | null
  product_type_label?: string | null
  breadcrumb?: BreadcrumbItem[]
  main_image?: string | null
  gallery?: GalleryItem[]
  base_price?: number | null
  old_price?: number | null
  current_price?: number | null
  has_discount?: boolean
  discount_label?: string | null
  koko_pay_percentage?: number | null
  koko_installment_price?: number | null
  stock_count?: number | null
  in_stock?: boolean
  warranty_label?: string | null
  color?: string | null
  size_label?: string | null
  unit_label?: string | null
  variant_label?: string | null
  specifications?: SpecRow[]
  reviews_count?: number | null
  reviews_avg_rating?: number | null
}

type ProductCard = {
  id: number | string
  product_type?: string | null
  name: string
  slug?: string | null
  category_name?: string | null
  brand_name?: string | null
  short_description?: string | null
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
  colors?: []
  url?: string | null
}

type TabKey = 'description' | 'specifications' | 'delivery' | 'reviews'

const props = defineProps<{
  sectionKey: 'motorcycle' | 'fashion' | 'home-needs'
  productKey: number | string
  dataUrl: string
  shell?: ShellData | null
}>()

const { addItem } = useCart()

const loading = ref(true)
const error = ref<string | null>(null)
const product = ref<ProductPayload | null>(null)
const relatedProducts = ref<ProductCard[]>([])
const activeImage = ref<string | null>(null)
const quantity = ref(1)
const activeTab = ref<TabKey>('description')
const flashMessage = ref('')

const desktopTabSentinel = ref<HTMLElement | null>(null)
const mobileTabSentinel = ref<HTMLElement | null>(null)
const hasUserScrolled = ref(false)
const autoOpenedReviews = ref(false)
const desktopSentinelVisible = ref(false)
const mobileSentinelVisible = ref(false)

let abortController: AbortController | null = null
let flashTimer: ReturnType<typeof setTimeout> | null = null
let tabObserver: IntersectionObserver | null = null

const sectionFallbacks: Record<string, { label: string, href: string }> = {
  motorcycle: { label: 'Motorcycle Products', href: '/motorcycle-products' },
  fashion: { label: 'Fashion & Accessories', href: '/fashion' },
  'home-needs': { label: 'Home Needs', href: '/home-needs' },
}

const deliveryParagraphs = [
  'We partner with dependable courier providers to ensure each parcel reaches you securely and within the usual delivery window.',
  'Dispatches are handled from Monday through Saturday. Deliveries are not scheduled on Sundays or mercantile holidays, and shipping charges are applied separately.',
  'Orders confirmed during weekends will be processed from Monday. We always try to respect special delivery notes where possible, although that may slightly extend the standard delivery timeline.',
  'While we make every effort to send and deliver orders on time, occasional delays can happen due to circumstances outside normal operations. When that occurs, we will move your order forward as quickly as possible.',
]

const sentinelInView = computed(() => {
  return desktopSentinelVisible.value || mobileSentinelVisible.value
})

const breadcrumbItems = computed(() => {
  return product.value?.breadcrumb || props.shell?.breadcrumb || [
    { label: 'Home', href: '/' },
    { label: sectionFallbacks[props.sectionKey].label, href: sectionFallbacks[props.sectionKey].href },
    { label: props.shell?.name || 'Product', href: null },
  ]
})

const thumbnailImages = computed(() => {
  const gallery = product.value?.gallery ?? []
  const mainImage = product.value?.main_image

  if (mainImage && !gallery.some((item) => item.src === mainImage)) {
    return [{ id: 'main-image', src: mainImage }, ...gallery].slice(0, 6)
  }

  return gallery.slice(0, 6)
})

const displayImage = computed(() => {
  return activeImage.value
    || product.value?.main_image
    || thumbnailImages.value[0]?.src
    || ''
})

const currentPrice = computed(() => {
  return Number(product.value?.current_price ?? product.value?.base_price ?? 0)
})

const currentKokoInstallmentPrice = computed(() => {
  const calculated = kokoInstallmentAmount(currentPrice.value, product.value?.koko_pay_percentage ?? 0)
  return calculated || Number(product.value?.koko_installment_price ?? 0)
})

const currentOldPrice = computed(() => product.value?.old_price ?? null)
const currentDiscountLabel = computed(() => product.value?.discount_label ?? null)

const hasDiscount = computed(() => {
  return !!currentOldPrice.value && Number(currentOldPrice.value) > Number(currentPrice.value)
})

const stockCount = computed(() => Number(product.value?.stock_count ?? 0))

const isInStock = computed(() => {
  return !!product.value?.in_stock && stockCount.value > 0
})

const availabilityText = computed(() => (isInStock.value ? 'In Stock' : 'Out of Stock'))

const relatedSectionLabel = computed(() => {
  return product.value?.category?.name || sectionFallbacks[props.sectionKey].label
})

const relatedProductCards = computed(() => relatedProducts.value.map((item) => ({
  ...item,
  hover_image_url: item.hover_image_url || item.thumbnail_url,
  product_type: item.product_type || props.sectionKey,
  url: item.url || relatedProductFallbackUrl(item),
})))

const canIncreaseQty = computed(() => {
  return isInStock.value && quantity.value < Math.max(1, stockCount.value)
})

const selectedOptionLabel = computed(() => {
  return [
    product.value?.variant_label,
    product.value?.size_label,
    product.value?.unit_label,
    product.value?.color,
  ]
    .filter((item, index, array) => {
      const value = String(item || '').trim()
      return value !== '' && array.findIndex((other) => String(other || '').trim() === value) === index
    })
    .join(' / ')
})

const priceTransitionKey = computed(() => {
  return [
    product.value?.id ?? 'default',
    currentPrice.value ?? '',
    currentOldPrice.value ?? '',
    currentDiscountLabel.value ?? '',
  ].join('-')
})

const currentProductUrl = computed(() => {
  if (typeof window !== 'undefined') {
    return window.location.pathname
  }

  return null
})

const reviewsFetchUrl = computed(() => {
  const current = product.value
  if (!current || props.sectionKey !== 'motorcycle') return ''

  return `/motorcycle-products/${current.slug || current.id}/reviews`
})

const kokoWhatsAppUrl = computed(() => {
  const productName = product.value?.name || 'this product'
  const pageUrl = typeof window !== 'undefined' ? window.location.href : ''
  const message = [
    `Hi DezeStore, I want to enable Koko Pay for ${productName}.`,
    `Price: ${formatPrice(currentPrice.value)}.`,
    pageUrl,
  ].filter(Boolean).join(' ')

  return `https://wa.me/94772030597?text=${encodeURIComponent(message)}`
})

function formatPrice(value: number | null | undefined) {
  if (value === null || typeof value === 'undefined' || Number.isNaN(Number(value))) {
    return 'Rs 0.00'
  }

  return `Rs ${Number(value).toLocaleString('en-LK', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}

function relatedProductFallbackUrl(item: ProductCard) {
  const key = item.slug || item.id

  if (props.sectionKey === 'motorcycle') {
    return route('frontend.motorcycle-products.show', { product: key })
  }

  if (props.sectionKey === 'fashion') {
    return route('frontend.fashion.show', { product: key })
  }

  return route('frontend.home-needs.show', { product: key })
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

function selectImage(src: string) {
  activeImage.value = src
}

function setTab(tab: TabKey) {
  activeTab.value = tab
}

function maybeAutoOpenReviews() {
  if (autoOpenedReviews.value) return
  if (activeTab.value === 'reviews') return
  if (!hasUserScrolled.value) return
  if (!sentinelInView.value) return

  autoOpenedReviews.value = true
  setTab('reviews')
}

function handleScroll() {
  if (hasUserScrolled.value) return
  hasUserScrolled.value = true
  maybeAutoOpenReviews()
}

function ensureObserver() {
  if (typeof window === 'undefined') return
  if (tabObserver || !('IntersectionObserver' in window)) return

  tabObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (desktopTabSentinel.value && entry.target === desktopTabSentinel.value) {
          desktopSentinelVisible.value = entry.isIntersecting
        }

        if (mobileTabSentinel.value && entry.target === mobileTabSentinel.value) {
          mobileSentinelVisible.value = entry.isIntersecting
        }
      })

      maybeAutoOpenReviews()
    },
    {
      root: null,
      rootMargin: '0px 0px -35% 0px',
      threshold: 0,
    },
  )

  if (desktopTabSentinel.value) {
    tabObserver.observe(desktopTabSentinel.value)
  }

  if (mobileTabSentinel.value) {
    tabObserver.observe(mobileTabSentinel.value)
  }
}

watch(desktopTabSentinel, (el, prev) => {
  if (prev && tabObserver) {
    tabObserver.unobserve(prev)
  }

  desktopSentinelVisible.value = false

  if (el) {
    ensureObserver()
    tabObserver?.observe(el)
  }
})

watch(mobileTabSentinel, (el, prev) => {
  if (prev && tabObserver) {
    tabObserver.unobserve(prev)
  }

  mobileSentinelVisible.value = false

  if (el) {
    ensureObserver()
    tabObserver?.observe(el)
  }
})

onMounted(() => {
  if (typeof window === 'undefined') return

  window.addEventListener('scroll', handleScroll, { passive: true })
  ensureObserver()
})

function decreaseQuantity() {
  quantity.value = Math.max(1, quantity.value - 1)
}

function increaseQuantity() {
  if (!canIncreaseQty.value) return
  quantity.value += 1
}

function showMessage(message: string) {
  flashMessage.value = message

  if (flashTimer) {
    clearTimeout(flashTimer)
  }

  flashTimer = setTimeout(() => {
    flashMessage.value = ''
    flashTimer = null
  }, 2200)
}

function cartPayload() {
  const current = product.value
  if (!current) return null

  return {
    id: current.id,
    productType: current.product_type || props.sectionKey,
    productId: current.id,
    variantId: `${current.product_type || props.sectionKey}-${current.id}`,
    quantity: quantity.value,
    sku: current.sku ?? null,
    colorName: current.color ?? null,
    sizeLabel: current.size_label ?? null,
    storageLabel: null,
    variantLabel: selectedOptionLabel.value || current.variant_label || current.unit_label || null,
    price: currentPrice.value,
    oldPrice: currentOldPrice.value,
    stockCount: Math.max(1, stockCount.value),
    name: current.name,
    image: displayImage.value || current.main_image || null,
    url: currentProductUrl.value,
  }
}

function addToCart() {
  try {
    if (!product.value || !isInStock.value) return

    const payload = cartPayload()
    if (!payload) return

    addItem(payload, true)
    showMessage('Product added to cart.')
  } catch (cartError) {
    console.error('Error while adding product to cart:', cartError)
  }
}

function buyNow() {
  try {
    if (!product.value || !isInStock.value) return

    const payload = cartPayload()
    if (!payload) return

    addItem(payload, false)
    showMessage('Ready for checkout.')
    router.visit(route('frontend.checkout.index'))
  } catch (cartError) {
    console.error('Error while processing buy now:', cartError)
  }
}

async function fetchProduct() {
  loading.value = true
  error.value = null
  product.value = null
  relatedProducts.value = []
  activeImage.value = null
  quantity.value = 1
  activeTab.value = 'description'
  hasUserScrolled.value = false
  autoOpenedReviews.value = false

  try {
    abortController?.abort()
    abortController = new AbortController()

    const response = await fetch(props.dataUrl, {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      signal: abortController.signal,
    })

    if (!response.ok) {
      throw new Error('Unable to load product details.')
    }

    const data = await response.json()
    product.value = data?.product ?? null
    relatedProducts.value = Array.isArray(data?.related_products) ? data.related_products : []
    activeImage.value = product.value?.main_image || product.value?.gallery?.[0]?.src || null
  } catch (fetchError) {
    if ((fetchError as Error)?.name === 'AbortError') return
    error.value = fetchError instanceof Error ? fetchError.message : 'Unable to load product details.'
  } finally {
    loading.value = false
  }
}

watch(
  () => props.dataUrl,
  () => fetchProduct(),
  { immediate: true },
)

onBeforeUnmount(() => {
  abortController?.abort()

  if (flashTimer) {
    clearTimeout(flashTimer)
  }

  if (typeof window !== 'undefined') {
    window.removeEventListener('scroll', handleScroll)
  }

  tabObserver?.disconnect()
  tabObserver = null
})
</script>

<template>
  <div class="product-detail-page mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <nav aria-label="Breadcrumb" class="mb-6 page-enter">
      <ol class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
        <li
          v-for="(item, index) in breadcrumbItems"
          :key="`${item.label}-${index}`"
          class="flex items-center gap-2"
        >
          <template v-if="item.href">
            <Link :href="item.href" class="transition hover:text-slate-900">
              {{ item.label }}
            </Link>
          </template>
          <template v-else>
            <span class="font-medium text-slate-900">{{ item.label }}</span>
          </template>
          <span v-if="index < breadcrumbItems.length - 1" class="text-slate-300">/</span>
        </li>
      </ol>
    </nav>

    <Transition name="fade-slide" mode="out-in">
      <div
        v-if="flashMessage"
        key="flash-message"
        class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
      >
        {{ flashMessage }}
      </div>
    </Transition>

    <div
      v-if="error && !loading"
      class="py-14 text-center page-enter"
    >
      <h2 class="text-xl font-semibold text-red-700">Failed to load product details</h2>
      <p class="mt-2 text-sm text-red-500">{{ error }}</p>
      <button
        type="button"
        class="mt-5 border-b border-slate-900 pb-1 text-sm font-semibold text-slate-900 transition hover:opacity-70"
        @click="fetchProduct"
      >
        Retry
      </button>
    </div>

    <template v-else>
      <div class="product-detail-layout grid gap-10 lg:grid-cols-[minmax(0,1.02fr)_minmax(360px,0.98fr)] lg:gap-12">
        <section class="product-gallery-column page-enter page-enter-delay-1">
          <template v-if="loading || !product">
            <div class="product-gallery-grid grid gap-4 md:grid-cols-[78px_minmax(0,1fr)]">
              <div class="hidden gap-3 md:flex md:flex-col">
                <div
                  v-for="index in 4"
                  :key="`thumb-skeleton-${index}`"
                  class="h-20 animate-pulse rounded-2xl bg-slate-100"
                />
              </div>

              <div class="min-h-[360px] rounded-[28px] border border-slate-200 bg-white p-6 sm:min-h-[460px]">
                <div class="h-full w-full animate-pulse rounded-[24px] bg-slate-100" />
              </div>
            </div>

            <div class="mt-10 hidden border-b border-slate-200 lg:block" />
            <div class="hidden pt-6 lg:block">
              <div class="space-y-3">
                <div class="h-6 w-40 animate-pulse rounded bg-slate-200" />
                <div class="h-5 w-full animate-pulse rounded bg-slate-100" />
                <div class="h-5 w-11/12 animate-pulse rounded bg-slate-100" />
                <div class="h-5 w-10/12 animate-pulse rounded bg-slate-100" />
              </div>
            </div>
          </template>

          <template v-else>
            <div class="grid gap-4 md:grid-cols-[78px_minmax(0,1fr)]">
              <div
                v-if="thumbnailImages.length"
                class="order-2 flex gap-3 overflow-x-auto hide-scrollbar py-2 md:order-1 md:flex-col md:overflow-visible md:py-0"
              >
                <button
                  v-for="image in thumbnailImages"
                  :key="image.id"
                  type="button"
                  class="thumb-button h-20 min-w-20 overflow-hidden rounded-2xl border bg-white sm:h-[88px] sm:min-w-[88px]"
                  :class="displayImage === image.src
                    ? 'border-slate-900 shadow-sm'
                    : 'border-slate-200 hover:border-slate-400'"
                  @click="selectImage(image.src)"
                >
                  <img
                    :src="image.src"
                    :alt="`${product.name} - DezeStore`"
                    class="thumb-image h-full w-full object-contain p-2"
                  />
                </button>
              </div>

              <div
                class="product-image-stage order-1 flex min-h-[360px] items-center justify-center overflow-hidden rounded-[28px] border border-slate-200 bg-white p-5 sm:min-h-[460px] sm:p-8 md:order-2"
              >
                <Transition name="image-swap" mode="out-in">
                  <img
                    v-if="displayImage"
                    :key="displayImage"
                    :src="displayImage"
                    :alt="`${product.name} - DezeStore`"
                    class="max-h-[440px] w-full object-contain"
                  />
                </Transition>
              </div>
            </div>

            <div class="product-info-tabs mt-10 hidden border-b border-slate-200 lg:block">
              <div class="flex items-end gap-8 overflow-x-auto hide-scrollbar">
                <button
                  type="button"
                  class="relative shrink-0 pb-4 text-sm transition sm:text-base"
                  :class="activeTab === 'description'
                    ? 'font-semibold text-slate-950'
                    : 'font-medium text-slate-500 hover:text-slate-900'"
                  @click="setTab('description')"
                >
                  Description
                  <span
                    v-if="activeTab === 'description'"
                    class="absolute inset-x-0 bottom-[-1px] h-[2px] bg-slate-950"
                  />
                </button>

                <button
                  type="button"
                  class="relative shrink-0 pb-4 text-sm transition sm:text-base"
                  :class="activeTab === 'specifications'
                    ? 'font-semibold text-slate-950'
                    : 'font-medium text-slate-500 hover:text-slate-900'"
                  @click="setTab('specifications')"
                >
                  <span class="md:hidden">Specs</span>
                  <span class="hidden md:inline">Specifications</span>
                  <span
                    v-if="activeTab === 'specifications'"
                    class="absolute inset-x-0 bottom-[-1px] h-[2px] bg-slate-950"
                  />
                </button>

                <button
                  type="button"
                  class="relative shrink-0 pb-4 text-sm transition sm:text-base"
                  :class="activeTab === 'delivery'
                    ? 'font-semibold text-slate-950'
                    : 'font-medium text-slate-500 hover:text-slate-900'"
                  @click="setTab('delivery')"
                >
                  <span class="md:hidden">Delivery</span>
                  <span class="hidden md:inline">Delivery Information</span>
                  <span
                    v-if="activeTab === 'delivery'"
                    class="absolute inset-x-0 bottom-[-1px] h-[2px] bg-slate-950"
                  />
                </button>

                <button
                  type="button"
                  class="relative shrink-0 pb-4 text-sm transition sm:text-base"
                  :class="activeTab === 'reviews'
                    ? 'font-semibold text-slate-950'
                    : 'font-medium text-slate-500 hover:text-slate-900'"
                  @click="setTab('reviews')"
                >
                  <span class="md:hidden">Reviews</span>
                  <span class="hidden md:inline">Reviews ({{ product.reviews_count ?? 0 }})</span>
                  <span
                    v-if="activeTab === 'reviews'"
                    class="absolute inset-x-0 bottom-[-1px] h-[2px] bg-slate-950"
                  />
                </button>
              </div>
            </div>

            <div class="hidden pt-6 lg:block">
              <Transition name="tab-fade-up" mode="out-in">
                <div
                  v-if="activeTab === 'description'"
                  key="description-desktop"
                  class="prose prose-slate max-w-none text-sm leading-8 sm:text-[15px]"
                  v-html="product.long_description || product.short_description || '<p>No description available.</p>'"
                />

                <div
                  v-else-if="activeTab === 'specifications'"
                  key="specifications-desktop"
                  class="space-y-3"
                >
                  <div
                    v-if="!product.specifications?.length"
                    class="text-sm text-slate-500"
                  >
                    No specifications available.
                  </div>

                  <div
                    v-if="product.specifications?.length"
                    class="hidden md:block"
                  >
                    <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-white">
                      <table class="w-full border-collapse table-fixed">
                        <tbody>
                          <tr
                            v-for="(spec, index) in product.specifications"
                            :key="`${spec.label}-${index}`"
                            class="border-b border-slate-200 last:border-b-0"
                          >
                            <td class="w-[34%] px-6 py-4 align-top text-sm font-semibold text-slate-700 xl:w-[30%]">
                              <div class="break-words">
                                {{ spec.label }}
                              </div>
                            </td>
                            <td class="px-6 py-4 align-top text-sm text-slate-600">
                              <div class="break-words">
                                {{ spec.value }}
                              </div>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

                <div
                  v-else-if="activeTab === 'delivery'"
                  key="delivery-desktop"
                  class="space-y-4 text-sm leading-7 text-slate-600 sm:text-[15px]"
                >
                  <p v-for="(paragraph, index) in deliveryParagraphs" :key="`desktop-delivery-${index}`">
                    {{ paragraph }}
                  </p>
                </div>

                <div
                  v-else
                  key="reviews-desktop"
                >
                  <ProductReviewsPanel
                    v-if="reviewsFetchUrl"
                    :fetchUrl="reviewsFetchUrl"
                    :active="activeTab === 'reviews'"
                    :initialCount="product.reviews_count ?? 0"
                    :initialAvg="product.reviews_avg_rating ?? 0"
                    :productName="product.name"
                    :productImage="product.main_image || product.gallery?.[0]?.src || null"
                  />

                  <div v-else class="rounded-[24px] border border-slate-200 bg-white p-6 text-sm leading-7 text-slate-600">
                    Reviews are not enabled for this category yet.
                  </div>
                </div>
              </Transition>

              <div ref="desktopTabSentinel" class="h-px w-full" />
            </div>
          </template>
        </section>

        <section class="product-summary-card pt-1 page-enter page-enter-delay-2">
          <template v-if="loading || !product">
            <div class="space-y-5">
              <div class="h-4 w-24 animate-pulse rounded bg-slate-100" />
              <div class="h-10 w-4/5 animate-pulse rounded bg-slate-200" />
              <div class="h-5 w-full animate-pulse rounded bg-slate-100" />
              <div class="h-5 w-5/6 animate-pulse rounded bg-slate-100" />
              <div class="h-10 w-52 animate-pulse rounded bg-slate-200" />
              <div class="h-12 w-20 animate-pulse rounded bg-slate-100" />
              <div class="space-y-3 pt-2">
                <div class="h-5 w-64 animate-pulse rounded bg-slate-100" />
                <div class="h-5 w-52 animate-pulse rounded bg-slate-100" />
                <div class="h-5 w-48 animate-pulse rounded bg-slate-100" />
              </div>
              <div class="flex gap-3 pt-2">
                <div class="h-8 w-8 animate-pulse rounded-full bg-slate-100" />
                <div class="h-8 w-8 animate-pulse rounded-full bg-slate-100" />
                <div class="h-8 w-8 animate-pulse rounded-full bg-slate-100" />
                <div class="h-8 w-8 animate-pulse rounded-full bg-slate-100" />
              </div>
            </div>
          </template>

          <template v-else>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
              {{ product.category?.name || sectionFallbacks[sectionKey].label }}
            </p>

            <h1 class="mt-3 text-3xl font-semibold tracking-[-0.03em] text-slate-950 sm:text-[2.25rem]">
              {{ product.name }}
            </h1>

            <p
              v-if="product.short_description"
              class="mt-4 text-sm leading-7 text-slate-600 sm:text-[15px]"
            >
              {{ product.short_description }}
            </p>

            <div class="mt-6">
              <Transition name="price-fade-up" mode="out-in">
                <div :key="priceTransitionKey">
                  <div class="flex flex-wrap items-center gap-3">
                    <span
                      v-if="hasDiscount && currentDiscountLabel"
                      class="inline-flex items-center text-xs font-semibold uppercase tracking-[0.12em] text-[#ef5a4f]"
                    >
                      {{ currentDiscountLabel }}
                    </span>
                  </div>

                  <div class="mt-2 flex flex-wrap items-end gap-3">
                    <span
                      v-if="hasDiscount && currentOldPrice"
                      class="text-lg font-medium text-slate-400 line-through"
                    >
                      {{ formatPrice(currentOldPrice) }}
                    </span>

                    <span class="text-3xl font-bold text-slate-950 sm:text-4xl">
                      {{ formatPrice(currentPrice) }}
                    </span>
                  </div>

                  <div class="mt-2 space-y-2">
                    <KokoPayLine
                      :amount="currentKokoInstallmentPrice"
                      detail
                    />

                    <a
                      v-if="currentKokoInstallmentPrice"
                      :href="kokoWhatsAppUrl"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="koko-whatsapp-link"
                    >
                      <span class="koko-whatsapp-link__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                          <path d="M12.04 2.5A9.43 9.43 0 002.6 11.93c0 1.66.44 3.28 1.28 4.7L2.5 21.5l4.99-1.31a9.42 9.42 0 004.55 1.16h.01a9.43 9.43 0 000-18.85zm5.56 13.31c-.24.68-1.22 1.24-1.98 1.4-.53.11-1.23.2-3.57-.77-2.99-1.24-4.92-4.29-5.07-4.49-.15-.2-1.21-1.61-1.21-3.07s.77-2.18 1.04-2.48c.25-.27.56-.34.75-.34h.54c.17.01.4-.06.63.48.24.58.82 2 .89 2.14.07.15.12.32.02.52-.09.2-.15.32-.29.49-.15.17-.31.38-.44.51-.15.15-.3.31-.13.6.17.29.75 1.24 1.61 2 .11.1.22.2.34.29.96.76 1.76.99 2.05 1.14.29.15.46.12.63-.07.17-.2.72-.84.91-1.13.19-.29.39-.24.66-.15.27.1 1.72.81 2.01.96.29.15.49.22.56.34.07.13.07.74-.17 1.42z" />
                        </svg>
                      </span>
                      <span>Click here to contact us to enable Koko via WhatsApp</span>
                    </a>
                  </div>
                </div>
              </Transition>

              <div v-if="clampRating(product.reviews_avg_rating) > 0" class="mt-3">
                <div
                  class="detail-rating"
                  :aria-label="ratingAriaLabel(product.reviews_avg_rating, product.reviews_count)"
                  role="img"
                >
                  <div class="detail-rating-stars">
                    <span
                      v-for="starNumber in 5"
                      :key="`module-detail-rating-star-${product.id}-${starNumber}`"
                      class="detail-rating-star"
                      aria-hidden="true"
                    >
                      <svg viewBox="0 0 24 24" class="detail-rating-star-base">
                        <path
                          d="M12 2.25l2.917 5.91 6.523.948-4.72 4.6 1.114 6.497L12 17.118 6.166 20.205l1.114-6.497-4.72-4.6 6.523-.948L12 2.25z"
                        />
                      </svg>

                      <span
                        class="detail-rating-star-fill"
                        :style="starFillStyle(product.reviews_avg_rating, starNumber)"
                      >
                        <svg viewBox="0 0 24 24" class="detail-rating-star-top">
                          <path
                            d="M12 2.25l2.917 5.91 6.523.948-4.72 4.6 1.114 6.497L12 17.118 6.166 20.205l1.114-6.497-4.72-4.6 6.523-.948L12 2.25z"
                          />
                        </svg>
                      </span>
                    </span>
                  </div>

                  <span class="detail-rating-value">
                    {{ formatRating(product.reviews_avg_rating) }}
                    <span class="ml-1 text-xs font-medium text-slate-400">
                      ({{ product.reviews_count ?? 0 }})
                    </span>
                  </span>
                </div>
              </div>

              <div v-if="product.brand?.logo_url" class="mt-4">
                <img
                  :src="product.brand.logo_url"
                  :alt="product.brand?.name || 'Brand logo'"
                  class="h-10 w-auto object-contain"
                />
              </div>
            </div>

            <ul class="mt-8 space-y-3 text-sm text-slate-700">
              <li class="flex items-start gap-3">
                <span class="mt-[7px] h-1.5 w-1.5 rounded-full bg-slate-900" />
                <span>
                  <span class="font-semibold text-slate-900">Availability:</span>
                  {{ availabilityText }}
                </span>
              </li>

              <li v-if="product.warranty_label" class="flex items-start gap-3">
                <span class="mt-[7px] h-1.5 w-1.5 rounded-full bg-slate-900" />
                <span>
                  <span class="font-semibold text-slate-900">Warranty:</span>
                  {{ product.warranty_label }}
                </span>
              </li>

              <li v-if="product.sku" class="flex items-start gap-3">
                <span class="mt-[7px] h-1.5 w-1.5 rounded-full bg-slate-900" />
                <span>
                  <span class="font-semibold text-slate-900">SKU:</span>
                  {{ product.sku }}
                </span>
              </li>

              <li v-if="selectedOptionLabel" class="flex items-start gap-3">
                <span class="mt-[7px] h-1.5 w-1.5 rounded-full bg-slate-900" />
                <span>
                  <span class="font-semibold text-slate-900">Selected Option:</span>
                  {{ selectedOptionLabel }}
                </span>
              </li>
            </ul>

            <div class="mt-8 flex flex-wrap items-center gap-6 border-b border-t border-slate-200 py-5">
              <div class="flex items-center gap-4">
                <button
                  type="button"
                  class="text-2xl leading-none text-slate-700 transition hover:text-slate-950"
                  @click="decreaseQuantity"
                >
                  −
                </button>

                <span class="min-w-[28px] text-center text-lg font-semibold text-slate-950">
                  {{ quantity }}
                </span>

                <button
                  type="button"
                  class="text-2xl leading-none text-slate-700 transition hover:text-slate-950 disabled:cursor-not-allowed disabled:text-slate-300"
                  :disabled="!canIncreaseQty"
                  @click="increaseQuantity"
                >
                  +
                </button>
              </div>

              <p class="text-sm text-slate-500">
                {{ stockCount > 0 ? `${stockCount} available` : 'Currently unavailable' }}
              </p>
            </div>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
              <button
                type="button"
                class="detail-action detail-action--secondary inline-flex min-h-[52px] items-center justify-center border border-slate-900 px-6 text-sm font-semibold text-slate-950 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-300"
                :disabled="!isInStock"
                @click="addToCart"
              >
                Add to Cart
              </button>

              <button
                type="button"
                class="detail-action detail-action--primary inline-flex min-h-[52px] items-center justify-center bg-slate-950 px-6 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300"
                :disabled="!isInStock"
                @click="buyNow"
              >
                Pay Now
              </button>
            </div>

            <div class="mt-10 lg:hidden">
              <div class="border-b border-slate-200">
                <div class="flex items-end gap-5 overflow-x-auto hide-scrollbar no-scrollbar-mobile md:gap-8">
                  <button
                    type="button"
                    class="relative shrink-0 pb-4 text-sm transition sm:text-base"
                    :class="activeTab === 'description'
                      ? 'font-semibold text-slate-950'
                      : 'font-medium text-slate-500 hover:text-slate-900'"
                    @click="setTab('description')"
                  >
                    Description
                    <span
                      v-if="activeTab === 'description'"
                      class="absolute inset-x-0 bottom-[-1px] h-[2px] bg-slate-950"
                    />
                  </button>

                  <button
                    type="button"
                    class="relative shrink-0 pb-4 text-sm transition sm:text-base"
                    :class="activeTab === 'specifications'
                      ? 'font-semibold text-slate-950'
                      : 'font-medium text-slate-500 hover:text-slate-900'"
                    @click="setTab('specifications')"
                  >
                    <span class="md:hidden">Specs</span>
                    <span class="hidden md:inline">Specifications</span>
                    <span
                      v-if="activeTab === 'specifications'"
                      class="absolute inset-x-0 bottom-[-1px] h-[2px] bg-slate-950"
                    />
                  </button>
                  <button
                    type="button"
                    class="relative shrink-0 pb-4 text-sm transition sm:text-base"
                    :class="activeTab === 'delivery'
                      ? 'font-semibold text-slate-950'
                      : 'font-medium text-slate-500 hover:text-slate-900'"
                    @click="setTab('delivery')"
                  >
                    <span class="md:hidden">Delivery</span>
                    <span class="hidden md:inline">Delivery Information</span>
                    <span
                      v-if="activeTab === 'delivery'"
                      class="absolute inset-x-0 bottom-[-1px] h-[2px] bg-slate-950"
                    />
                  </button>

                  <button
                    type="button"
                    class="relative shrink-0 pb-4 text-sm transition sm:text-base"
                    :class="activeTab === 'reviews'
                      ? 'font-semibold text-slate-950'
                      : 'font-medium text-slate-500 hover:text-slate-900'"
                    @click="setTab('reviews')"
                  >
                    <span class="md:hidden">Reviews</span>
                    <span class="hidden md:inline">Reviews ({{ product.reviews_count ?? 0 }})</span>
                    <span
                      v-if="activeTab === 'reviews'"
                      class="absolute inset-x-0 bottom-[-1px] h-[2px] bg-slate-950"
                    />
                  </button>
                </div>
              </div>

              <div class="pt-6">
                <Transition name="tab-fade-up" mode="out-in">
                  <div
                    v-if="activeTab === 'description'"
                    key="description-mobile"
                    class="prose prose-slate max-w-none text-sm leading-8 sm:text-[15px]"
                    v-html="product.long_description || product.short_description || '<p>No description available.</p>'"
                  />

                  <div
                    v-else-if="activeTab === 'specifications'"
                    key="specifications-mobile"
                    class="space-y-3"
                  >
                    <div
                      v-if="!product.specifications?.length"
                      class="text-sm text-slate-500"
                    >
                      No specifications available.
                    </div>

                    <div
                      v-else
                      class="space-y-3"
                    >
                      <div
                        v-for="(spec, index) in product.specifications"
                        :key="`${spec.label}-${index}`"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-4"
                      >
                        <div class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                          {{ spec.label }}
                        </div>
                        <div class="mt-2 break-words text-sm leading-6 text-slate-700">
                          {{ spec.value }}
                        </div>
                      </div>
                    </div>
                  </div>

                  <div
                    v-else-if="activeTab === 'delivery'"
                    key="delivery-mobile"
                    class="space-y-4 text-sm leading-7 text-slate-600 sm:text-[15px]"
                  >
                    <p v-for="(paragraph, index) in deliveryParagraphs" :key="`mobile-delivery-${index}`">
                      {{ paragraph }}
                    </p>
                  </div>

                  <div
                    v-else
                    key="reviews-mobile"
                  >
                    <ProductReviewsPanel
                      v-if="reviewsFetchUrl"
                      :fetchUrl="reviewsFetchUrl"
                      :active="activeTab === 'reviews'"
                      :initialCount="product.reviews_count ?? 0"
                      :initialAvg="product.reviews_avg_rating ?? 0"
                      :productName="product.name"
                      :productImage="product.main_image || product.gallery?.[0]?.src || null"
                    />

                    <div v-else class="rounded-[24px] border border-slate-200 bg-white p-6 text-sm leading-7 text-slate-600">
                      Reviews are not enabled for this category yet.
                    </div>
                  </div>
                </Transition>

                <div ref="mobileTabSentinel" class="h-px w-full" />
              </div>
            </div>
          </template>
        </section>
      </div>
    </template>

    <section
      v-if="loading || relatedProductCards.length"
      class="mx-auto max-w-7xl px-4 pt-14 pb-16 sm:px-6 sm:pt-16 sm:pb-18 lg:px-8 lg:pt-20 lg:pb-24"
    >
      <div class="mb-6 sm:mb-8">
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-neutral-500">
          More from {{ relatedSectionLabel }}
        </p>

        <h2 class="mt-2 text-2xl font-semibold tracking-[-0.02em] text-neutral-950 sm:text-3xl">
          You may also like
        </h2>
      </div>

      <div class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-4 lg:gap-5">
        <template v-if="loading">
          <HomeProductCard
            v-for="index in 4"
            :key="`module-related-skeleton-${index}`"
            skeleton
          />
        </template>

        <template v-else>
          <HomeProductCard
            v-for="item in relatedProductCards"
            :key="`${item.product_type}-${item.id}`"
            :product="item"
          />
        </template>
      </div>
    </section>
  </div>
</template>
<style scoped>
.product-detail-page {
  --detail-page-bg: #ffffff;
  --detail-ink: #071f4f;

  position: relative;
}

.product-detail-page::before {
  position: fixed;
  inset: 0;
  z-index: -1;
  background: var(--detail-page-bg);
  content: '';
  pointer-events: none;
}

.product-detail-layout {
  border: 0;
  border-radius: 0;
  background: transparent;
  padding: 0;
  box-shadow: none;
}

.product-gallery-grid {
  align-items: stretch;
}

.product-image-stage {
  border: 0 !important;
  border-radius: 0 !important;
  background: var(--detail-page-bg) !important;
  box-shadow: none !important;
}

.product-summary-card {
  border: 0;
  border-radius: 0;
  background: transparent;
  padding: clamp(8px, 1.5vw, 18px) 0;
}

.product-summary-card h1 {
  color: var(--detail-ink);
  font-weight: 800;
  line-height: 1.08;
}

.product-info-tabs {
  border: 0;
  border-bottom: 1px solid #dbe3ef;
  border-radius: 0;
  background: transparent;
  padding: 0;
}

.product-info-tabs button {
  min-height: auto;
  border-radius: 0;
  padding: 0 0 14px;
}

.product-info-tabs button span.absolute {
  display: block;
  background: var(--detail-ink);
}

.product-info-tabs button.font-semibold {
  background: transparent;
  color: var(--detail-ink);
  box-shadow: none;
}

.detail-action {
  border-radius: 8px;
  transition:
    transform 0.18s ease,
    background-color 0.18s ease,
    border-color 0.18s ease,
    color 0.18s ease;
}

.detail-action:hover:not(:disabled) {
  transform: translateY(-1px);
}

.detail-action--primary {
  border-color: var(--detail-ink) !important;
  background: var(--detail-ink) !important;
  color: #ffffff !important;
}

.detail-action--primary:hover:not(:disabled) {
  background: #0b2b62 !important;
}

.detail-action--secondary {
  border-color: var(--detail-ink) !important;
  background: transparent !important;
  color: var(--detail-ink) !important;
}

.detail-action--secondary:hover:not(:disabled) {
  background: rgba(7, 31, 79, 0.05) !important;
}

.prose :deep(p) {
  margin-top: 0;
  margin-bottom: 1rem;
}

.prose :deep(ul),
.prose :deep(ol) {
  margin-top: 0.75rem;
  margin-bottom: 1rem;
}

.prose :deep(img) {
  max-width: 100%;
  height: auto;
}

.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.hide-scrollbar::-webkit-scrollbar {
  display: none;
  width: 0;
  height: 0;
}

.page-enter {
  animation: pageFadeUp 0.55s ease both;
}

.page-enter-delay-1 {
  animation-delay: 0.04s;
}

.page-enter-delay-2 {
  animation-delay: 0.1s;
}

.thumb-button {
  transition:
    transform 0.22s ease,
    border-color 0.22s ease,
    box-shadow 0.22s ease,
    background-color 0.22s ease;
}

.thumb-button:hover {
  transform: translateY(-2px);
}

.thumb-image {
  transition:
    transform 0.28s ease,
    opacity 0.28s ease;
}

.thumb-button:hover .thumb-image {
  transform: scale(1.04);
}

.color-swatch {
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    ring-color 0.22s ease;
}

.color-swatch:hover {
  transform: translateY(-2px) scale(1.04);
}

.detail-rating {
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  min-height: 22px;
}

.detail-rating-stars {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  flex-shrink: 0;
  line-height: 0;
}

.detail-rating-star {
  position: relative;
  display: inline-flex;
  width: 18px;
  height: 18px;
  flex: 0 0 18px;
}

.detail-rating-star-base,
.detail-rating-star-top {
  display: block;
  width: 100%;
  height: 100%;
}

.detail-rating-star-base {
  color: #d1d5db;
  fill: currentColor;
}

.detail-rating-star-fill {
  position: absolute;
  inset: 0;
  display: block;
  width: 100%;
  height: 100%;
}

.detail-rating-star-top {
  color: #f2a536;
  fill: currentColor;
}

.detail-rating-value {
  font-size: 15px;
  line-height: 1;
  font-weight: 700;
  color: #111827;
  letter-spacing: -0.02em;
  font-variant-numeric: tabular-nums;
}

.koko-whatsapp-link {
  display: inline-flex;
  max-width: 100%;
  align-items: center;
  gap: 8px;
  border-radius: 999px;
  border: 1px solid #bbf7d0;
  background: #f0fdf4;
  padding: 8px 12px;
  color: #15803d;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0;
  line-height: 1.35;
  text-decoration: none;
  transition:
    border-color 0.2s ease,
    background-color 0.2s ease,
    color 0.2s ease;
}

.koko-whatsapp-link:hover {
  border-color: #86efac;
  background: #dcfce7;
  color: #166534;
}

.koko-whatsapp-link__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  flex: 0 0 18px;
}

.koko-whatsapp-link__icon svg {
  width: 18px;
  height: 18px;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition:
    opacity 0.22s ease,
    transform 0.22s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

.tab-fade-up-enter-active,
.tab-fade-up-leave-active {
  transition:
    opacity 0.28s ease,
    transform 0.28s ease;
}

.tab-fade-up-enter-from,
.tab-fade-up-leave-to {
  opacity: 0;
  transform: translateY(18px);
}

.image-swap-enter-active,
.image-swap-leave-active {
  transition:
    opacity 0.3s ease,
    transform 0.3s ease,
    filter 0.3s ease;
}

.image-swap-enter-from,
.image-swap-leave-to {
  opacity: 0;
  transform: translateY(18px) scale(0.985);
  filter: blur(2px);
}

.price-fade-up-enter-active,
.price-fade-up-leave-active {
  transition:
    opacity 0.26s ease,
    transform 0.26s ease,
    filter 0.26s ease;
}

.price-fade-up-enter-from,
.price-fade-up-leave-to {
  opacity: 0;
  transform: translateY(14px);
  filter: blur(1px);
}

@keyframes pageFadeUp {
  from {
    opacity: 0;
    transform: translateY(22px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (min-width: 640px) {
  .detail-rating-star {
    width: 20px;
    height: 20px;
    flex-basis: 20px;
  }

  .detail-rating-stars {
    gap: 4px;
  }

  .detail-rating-value {
    font-size: 16px;
  }
}

@media (max-width: 640px) {
  .product-detail-layout {
    margin-inline: 0;
  }

  .product-summary-card {
    padding: 18px 0;
  }

  .product-image-stage {
    border-radius: 0 !important;
  }

  .koko-whatsapp-link {
    align-items: flex-start;
    border-radius: 14px;
    padding: 8px 10px;
    font-size: 11px;
  }
}

@media (prefers-reduced-motion: reduce) {
  * {
    scroll-behavior: auto !important;
    transition: none !important;
    animation: none !important;
  }
}
</style>
