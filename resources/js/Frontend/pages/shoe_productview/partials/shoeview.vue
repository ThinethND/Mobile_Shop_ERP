<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { route } from 'ziggy-js'
import ProductReviewsPanel from '@/Frontend/components/ProductReviewsPanel.vue'

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

type ShoeSize = {
  id: number | string
  label: string
}

type ShoeVariant = {
  id: string | number
  size_id?: number | string | null
  size_label?: string | null
  sku?: string | null
  price_lkr: number
  old_price_lkr?: number | null
  final_price_lkr?: number | null
  stock_count?: number | null
  in_stock?: boolean
  status?: string | null
  discount_label?: string | null
}

type ProductPayload = {
  id: number | string
  name: string
  slug?: string | null
  sku?: string | null
  short_description?: string | null
  long_description?: string | null
  brand?: {
    id?: number | string | null
    name?: string | null
    logo_url?: string | null
  } | null
  category?: {
    id?: number | string | null
    name?: string | null
  } | null
  subcategory?: {
    id?: number | string | null
    name?: string | null
  } | null
  breadcrumb?: BreadcrumbItem[]
  main_image?: string | null
  gallery: GalleryItem[]
  sizes: ShoeSize[]
  variants: ShoeVariant[]
  size_chart_image?: string | null
  base_price?: number | null
  old_price?: number | null
  current_price?: number | null
  has_discount?: boolean
  discount_label?: string | null
  default_size_id?: number | string | null
  stock_count?: number | null
  in_stock?: boolean
  reviews_count?: number | null
  reviews_avg_rating?: number | null
}

type TabKey = 'description' | 'size-chart' | 'delivery' | 'reviews'

const props = defineProps<{
  loading: boolean
  error: string | null
  product: ProductPayload | null
  shell?: ShellData | null
}>()

const emit = defineEmits<{
  (e: 'retry'): void
}>()

const selectedSizeId = ref<number | string | null>(null)
const activeImage = ref<string | null>(null)
const quantity = ref(1)
const activeTab = ref<TabKey>('description')
const flashMessage = ref('')

const reviewsFetchUrl = computed(() => {
  const key = props.product?.slug || props.product?.id
  if (!key) return ''
  return `/shoe-products/${key}/reviews`
})

const breadcrumbItems = computed(() => {
  return props.product?.breadcrumb || props.shell?.breadcrumb || [
    { label: 'Home', href: '/' },
    { label: 'Shoe Products', href: '/shoe-products' },
    { label: props.shell?.name || 'Product', href: null },
  ]
})

const shoeSizes = computed(() => props.product?.sizes ?? [])
const productVariants = computed(() => props.product?.variants ?? [])

const thumbnailImages = computed(() => {
  const gallery = props.product?.gallery ?? []
  const mainImage = props.product?.main_image

  if (mainImage && !gallery.some((item) => item.src === mainImage)) {
    return [{ id: 'main-image', src: mainImage }, ...gallery].slice(0, 6)
  }

  return gallery.slice(0, 6)
})

const currentVariant = computed<ShoeVariant | null>(() => {
  const exactInStock = productVariants.value.find((variant) => {
    return variant.size_id === selectedSizeId.value
      && variant.status !== 'inactive'
      && variant.in_stock
  })
  if (exactInStock) return exactInStock

  const exactActive = productVariants.value.find((variant) => {
    return variant.size_id === selectedSizeId.value
      && variant.status !== 'inactive'
  })
  if (exactActive) return exactActive

  return productVariants.value.find((variant) => variant.in_stock)
    || productVariants.value.find((variant) => variant.status !== 'inactive')
    || productVariants.value[0]
    || null
})

const currentPrice = computed(() => {
  return currentVariant.value?.final_price_lkr
    ?? props.product?.current_price
    ?? props.product?.base_price
    ?? 0
})

const currentOldPrice = computed(() => {
  return currentVariant.value?.old_price_lkr ?? props.product?.old_price ?? null
})

const currentDiscountLabel = computed(() => {
  return currentVariant.value?.discount_label ?? props.product?.discount_label ?? null
})

const hasDiscount = computed(() => {
  return !!currentOldPrice.value && Number(currentOldPrice.value) > Number(currentPrice.value)
})

const stockCount = computed(() => {
  return Number(currentVariant.value?.stock_count ?? props.product?.stock_count ?? 0)
})

const isInStock = computed(() => {
  if (currentVariant.value) {
    return !!currentVariant.value.in_stock && stockCount.value > 0
  }

  return !!props.product?.in_stock && Number(props.product?.stock_count ?? 0) > 0
})

const availabilityText = computed(() => (isInStock.value ? 'In Stock' : 'Out of Stock'))

const selectedSizeLabel = computed(() => {
  const matched = shoeSizes.value.find((item) => item.id === selectedSizeId.value)
  return matched?.label || currentVariant.value?.size_label || 'N/A'
})

const canIncreaseQty = computed(() => {
  return isInStock.value && quantity.value < Math.max(1, stockCount.value)
})

const displayImage = computed(() => {
  return activeImage.value
    || props.product?.main_image
    || props.product?.gallery?.[0]?.src
    || ''
})

const sizeChartImage = computed(() => {
  return props.product?.size_chart_image || '/assets/images/shoechart.webp'
})

const currentProductUrl = computed(() => {
  if (typeof window !== 'undefined') {
    return window.location.pathname
  }

  const key = props.product?.slug || props.product?.id
  return props.product && key
    ? route('frontend.shoe-products.show', { product: key })
    : null
})

const deliveryParagraphs = [
  'We partner with dependable courier providers to ensure each parcel reaches you securely and within the usual delivery window.',
  'Dispatches are handled from Monday through Saturday. Deliveries are not scheduled on Sundays or mercantile holidays, and shipping charges are applied separately.',
  'Orders confirmed during weekends will be processed from Monday. We always try to respect special delivery notes where possible, although that may slightly extend the standard delivery timeline.',
  'While we make every effort to send and deliver orders on time, occasional delays can happen due to circumstances outside normal operations. When that occurs, we will move your order forward as quickly as possible.',
]

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

function showMessage(message: string) {
  flashMessage.value = message
  window.setTimeout(() => {
    if (flashMessage.value === message) {
      flashMessage.value = ''
    }
  }, 2200)
}

function ensureSelections() {
  if (!props.product) return

  const defaultVariant = currentVariant.value

  selectedSizeId.value = props.product.default_size_id
    ?? defaultVariant?.size_id
    ?? shoeSizes.value[0]?.id
    ?? null

  activeImage.value = props.product.main_image || thumbnailImages.value[0]?.src || null
  quantity.value = 1
}

function selectSize(sizeId: number | string) {
  selectedSizeId.value = sizeId
}

function selectImage(src: string) {
  activeImage.value = src
}

function setTab(tab: TabKey) {
  activeTab.value = tab
}

function decreaseQuantity() {
  quantity.value = Math.max(1, quantity.value - 1)
}

function increaseQuantity() {
  if (!canIncreaseQty.value) return
  quantity.value += 1
}

function cartPayload() {
  if (!props.product || !currentVariant.value) return null

  return {
    productId: props.product.id,
    variantId: currentVariant.value.id,
    quantity: quantity.value,
    colorId: null,
    colorName: null,
    storageId: selectedSizeId.value,
    storageLabel: selectedSizeLabel.value !== 'N/A' ? selectedSizeLabel.value : null,
    price: currentPrice.value,
    oldPrice: currentOldPrice.value,
    stockCount: stockCount.value,
    name: props.product.name,
    image: displayImage.value || props.product.main_image || null,
    url: currentProductUrl.value,
  }
}

function addToCart() {
  try {
    if (!props.product || !currentVariant.value || !isInStock.value) return

    const payload = cartPayload()
    if (!payload) return

    window.dispatchEvent(new CustomEvent('tech-product:add-to-cart', {
      detail: payload,
    }))

    showMessage('Product added to cart.')
  } catch (error) {
    console.error('Error while adding product to cart:', error)
  }
}

function buyNow() {
  try {
    if (!props.product || !currentVariant.value || !isInStock.value) return

    const payload = cartPayload()
    if (!payload) return

    window.dispatchEvent(new CustomEvent('tech-product:add-to-cart', {
      detail: payload,
    }))

    showMessage('Ready for checkout.')
    router.visit(route('frontend.checkout.index'))
  } catch (error) {
    console.error('Error while processing buy now:', error)
  }
}

watch(() => props.product, () => {
  selectedSizeId.value = null
  activeImage.value = null
  ensureSelections()
}, { immediate: true })

watch(currentVariant, () => {
  quantity.value = Math.min(quantity.value, Math.max(1, stockCount.value))
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
        class="mb-4 border-b border-emerald-200 pb-3 text-sm font-medium text-emerald-700"
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
        @click="emit('retry')"
      >
        Retry
      </button>
    </div>

    <template v-else>
      <div class="product-detail-layout grid gap-10 lg:grid-cols-[minmax(0,1.02fr)_minmax(360px,0.98fr)] lg:gap-12">
        <section class="product-gallery-column page-enter page-enter-delay-1">
          <template v-if="loading || !product">
            <div class="grid gap-4 md:grid-cols-[78px_minmax(0,1fr)]">
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

            <div class="mt-10 border-b border-slate-200" />
            <div class="pt-6">
              <div class="space-y-3">
                <div class="h-6 w-40 animate-pulse rounded bg-slate-200" />
                <div class="h-5 w-full animate-pulse rounded bg-slate-100" />
                <div class="h-5 w-11/12 animate-pulse rounded bg-slate-100" />
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
              <div class="flex items-end gap-8">
                <button
                  type="button"
                  class="relative pb-4 text-sm transition sm:text-base"
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
                  class="relative pb-4 text-sm transition sm:text-base"
                  :class="activeTab === 'size-chart'
                    ? 'font-semibold text-slate-950'
                    : 'font-medium text-slate-500 hover:text-slate-900'"
                  @click="setTab('size-chart')"
                >
                  <span class="md:hidden">Size</span>
                  <span class="hidden md:inline">Size Chart</span>
                  <span
                    v-if="activeTab === 'size-chart'"
                    class="absolute inset-x-0 bottom-[-1px] h-[2px] bg-slate-950"
                  />
                </button>

                <button
                  type="button"
                  class="relative pb-4 text-sm transition sm:text-base"
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
                  class="relative pb-4 text-sm transition sm:text-base"
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
                  key="description"
                  class="prose prose-slate max-w-none text-sm leading-8 sm:text-[15px]"
                  v-html="product.long_description || product.short_description || '<p>No description available.</p>'"
                />

                <div
                  v-else-if="activeTab === 'size-chart'"
                  key="size-chart"
                  class="overflow-hidden rounded-[24px] border border-slate-200 bg-white p-4"
                >
                  <img
                    :src="sizeChartImage"
                    alt="Size chart"
                    class="w-full object-contain"
                  />
                </div>

                <div
                  v-else-if="activeTab === 'delivery'"
                  key="delivery"
                  class="space-y-4 text-sm leading-7 text-slate-600 sm:text-[15px]"
                >
                  <p v-for="(paragraph, index) in deliveryParagraphs" :key="index">
                    {{ paragraph }}
                  </p>
                </div>

                <div
                  v-else
                  key="reviews-desktop"
                >
                  <ProductReviewsPanel
                    :fetchUrl="reviewsFetchUrl"
                    :active="activeTab === 'reviews'"
                    :initialCount="product.reviews_count ?? 0"
                    :initialAvg="product.reviews_avg_rating ?? 0"
                    :productName="product.name"
                    :productImage="product.main_image || product.gallery?.[0]?.src || null"
                  />
                </div>
              </Transition>
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
            </div>
          </template>

          <template v-else>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
              {{ product.category?.name || 'Shoe Product' }}
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

              <div v-if="clampRating(product.reviews_avg_rating) > 0" class="mt-3">
                <div
                  class="detail-rating"
                  :aria-label="ratingAriaLabel(product.reviews_avg_rating, product.reviews_count)"
                  role="img"
                >
                  <div class="detail-rating-stars">
                    <span
                      v-for="starNumber in 5"
                      :key="`shoe-detail-rating-star-${product.id}-${starNumber}`"
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
                <span><span class="font-semibold text-slate-900">Availability:</span> {{ availabilityText }}</span>
              </li>

              <li v-if="product.category?.name" class="flex items-start gap-3">
                <span class="mt-[7px] h-1.5 w-1.5 rounded-full bg-slate-900" />
                <span><span class="font-semibold text-slate-900">Category:</span> {{ product.category.name }}</span>
              </li>

              <li v-if="product.subcategory?.name" class="flex items-start gap-3">
                <span class="mt-[7px] h-1.5 w-1.5 rounded-full bg-slate-900" />
                <span><span class="font-semibold text-slate-900">Sub Category:</span> {{ product.subcategory.name }}</span>
              </li>
            </ul>

            <div v-if="shoeSizes.length" class="mt-8">
              <p class="text-sm font-semibold text-slate-900">Available Sizes</p>

              <div class="mt-3 flex flex-wrap items-center gap-4">
                <button
                  v-for="size in shoeSizes"
                  :key="size.id"
                  type="button"
                  class="border-b pb-1 text-sm transition"
                  :class="selectedSizeId === size.id
                    ? 'border-slate-900 font-semibold text-slate-950'
                    : 'border-transparent font-medium text-slate-500 hover:border-slate-400 hover:text-slate-900'"
                  @click="selectSize(size.id)"
                >
                  {{ size.label }}
                </button>
              </div>
            </div>

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
                Buy Now
              </button>
            </div>

            <div class="mt-6 text-sm text-slate-500">
              <p><span class="font-semibold text-slate-800">Selected Size:</span> {{ selectedSizeLabel }}</p>
              <p class="mt-1"><span class="font-semibold text-slate-800">SKU:</span> {{ currentVariant?.sku || product.sku || 'N/A' }}</p>
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
                    :class="activeTab === 'size-chart'
                      ? 'font-semibold text-slate-950'
                      : 'font-medium text-slate-500 hover:text-slate-900'"
                    @click="setTab('size-chart')"
                  >
                    <span class="md:hidden">Size</span>
                    <span class="hidden md:inline">Size Chart</span>
                    <span
                      v-if="activeTab === 'size-chart'"
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
                    v-else-if="activeTab === 'size-chart'"
                    key="size-chart-mobile"
                    class="overflow-hidden rounded-[24px] border border-slate-200 bg-white p-4"
                  >
                    <img
                      :src="sizeChartImage"
                      alt="Size chart"
                      class="w-full object-contain"
                    />
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
                      :fetchUrl="reviewsFetchUrl"
                      :active="activeTab === 'reviews'"
                      :initialCount="product.reviews_count ?? 0"
                      :initialAvg="product.reviews_avg_rating ?? 0"
                      :productName="product.name"
                      :productImage="product.main_image || product.gallery?.[0]?.src || null"
                    />
                  </div>
                </Transition>
              </div>
            </div>
          </template>
        </section>
      </div>
    </template>
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

.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.hide-scrollbar::-webkit-scrollbar {
  display: none;
  width: 0;
  height: 0;
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
}

@media (prefers-reduced-motion: reduce) {
  * {
    scroll-behavior: auto !important;
    transition: none !important;
    animation: none !important;
  }
}
</style>
