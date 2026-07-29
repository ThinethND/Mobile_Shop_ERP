<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { route } from 'ziggy-js'
import ProductReviewsPanel from '@/Frontend/components/ProductReviewsPanel.vue'
import StarRating from '@/Frontend/components/StarRating.vue'

type BreadcrumbItem = { label: string; href: string | null }
type ShellData = { name?: string | null; breadcrumb?: BreadcrumbItem[] }
type GalleryItem = { id: string | number; src: string }
type CosmeticVolume = { id: number | string; label: string }

type CosmeticVariant = {
  id: string | number
  volume_id?: number | string | null
  volume_label?: string | null
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
  batch_number?: string | null
  manufacture_date?: string | null
  expiry_date?: string | null
  short_description?: string | null
  long_description?: string | null
  brand?: { id?: number | string | null; name?: string | null; logo_url?: string | null } | null
  category?: { id?: number | string | null; name?: string | null } | null
  product_type?: { id?: number | string | null; name?: string | null } | null
  country?: { id?: number | string | null; name?: string | null; code?: string | null; flag_image_url?: string | null } | null
  breadcrumb?: BreadcrumbItem[]
  main_image?: string | null
  gallery: GalleryItem[]
  volumes: CosmeticVolume[]
  variants: CosmeticVariant[]
  base_price?: number | null
  old_price?: number | null
  current_price?: number | null
  discount_label?: string | null
  default_volume_id?: number | string | null
  stock_count?: number | null
  in_stock?: boolean
  reviews_count?: number | null
  reviews_avg_rating?: number | null
}

type TabKey = 'description' | 'details' | 'delivery' | 'reviews'

const props = defineProps<{
  loading: boolean
  error: string | null
  product: ProductPayload | null
  shell?: ShellData | null
}>()

const emit = defineEmits<{ (e: 'retry'): void }>()

const selectedVolumeId = ref<number | string | null>(null)
const activeImage = ref<string | null>(null)
const quantity = ref(1)
const activeTab = ref<TabKey>('description')
const flashMessage = ref('')

const reviewsFetchUrl = computed(() => {
  const key = props.product?.slug || props.product?.id
  return key ? `/cosmetics/${key}/reviews` : ''
})

const breadcrumbItems = computed(() => {
  return props.product?.breadcrumb || props.shell?.breadcrumb || [
    { label: 'Home', href: '/' },
    { label: 'Cosmetic Products', href: '/cosmetics' },
    { label: props.shell?.name || 'Product', href: null },
  ]
})

const volumes = computed(() => props.product?.volumes ?? [])
const variants = computed(() => props.product?.variants ?? [])

const thumbnailImages = computed(() => {
  const gallery = props.product?.gallery ?? []
  const mainImage = props.product?.main_image

  if (mainImage && !gallery.some((item) => item.src === mainImage)) {
    return [{ id: 'main-image', src: mainImage }, ...gallery].slice(0, 6)
  }

  return gallery.slice(0, 6)
})

const currentVariant = computed<CosmeticVariant | null>(() => {
  const exactInStock = variants.value.find((variant) => {
    return variant.volume_id === selectedVolumeId.value
      && variant.status !== 'inactive'
      && variant.in_stock
  })
  if (exactInStock) return exactInStock

  const exactActive = variants.value.find((variant) => {
    return variant.volume_id === selectedVolumeId.value
      && variant.status !== 'inactive'
  })
  if (exactActive) return exactActive

  return variants.value.find((variant) => variant.in_stock)
    || variants.value.find((variant) => variant.status !== 'inactive')
    || variants.value[0]
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

const priceTransitionKey = computed(() => {
  return [
    currentVariant.value?.id ?? 'default',
    currentPrice.value ?? '',
    currentOldPrice.value ?? '',
    currentDiscountLabel.value ?? '',
  ].join('-')
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

const availabilityText = computed(() => (isInStock.value ? 'In stock' : 'Out of stock'))

const selectedVolumeLabel = computed(() => {
  const matched = volumes.value.find((item) => item.id === selectedVolumeId.value)
  return matched?.label || currentVariant.value?.volume_label || 'N/A'
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

const currentProductUrl = computed(() => {
  if (typeof window !== 'undefined') return window.location.pathname
  const key = props.product?.slug || props.product?.id
  return props.product && key
    ? route('frontend.cosmetic-products.show', { product: key })
    : null
})

const detailRows = computed(() => {
  const product = props.product
  if (!product) return []

  const rows = [
    { label: 'Brand', value: product.brand?.name || null },
    { label: 'Category', value: product.category?.name || null },
    { label: 'Product Type', value: product.product_type?.name || null },
    { label: 'Country of Origin', value: product.country?.name || null },
    { label: 'Selected Volume', value: selectedVolumeLabel.value !== 'N/A' ? selectedVolumeLabel.value : null },
    { label: 'Batch Number', value: product.batch_number || null },
    { label: 'Manufacture Date', value: product.manufacture_date || null },
    { label: 'Expiry Date', value: product.expiry_date || null },
    { label: 'SKU', value: currentVariant.value?.sku || product.sku || null },
  ]

  return rows.filter((row) => !!row.value)
})

const deliveryParagraphs = [
  'We partner with dependable courier providers to ensure each parcel reaches you securely and within the usual delivery window.',
  'Dispatches are handled from Monday through Saturday. Deliveries are not scheduled on Sundays or mercantile holidays, and shipping charges are applied separately.',
  'Orders confirmed during weekends will be processed from Monday. We always try to respect special delivery notes where possible, although that may slightly extend the standard delivery timeline.',
  'While we make every effort to send and deliver orders on time, occasional delays can happen due to circumstances outside normal operations. When that occurs, we will move your order forward as quickly as possible.',
]

function formatPrice(value: number | null | undefined) {
  if (value === null || typeof value === 'undefined' || Number.isNaN(Number(value))) return 'Rs 0.00'
  return `Rs ${Number(value).toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

function showMessage(message: string) {
  flashMessage.value = message
  window.setTimeout(() => {
    if (flashMessage.value === message) flashMessage.value = ''
  }, 2200)
}

function ensureSelections() {
  if (!props.product) return

  const defaultVariant = currentVariant.value
  selectedVolumeId.value = props.product.default_volume_id
    ?? defaultVariant?.volume_id
    ?? volumes.value[0]?.id
    ?? null

  activeImage.value = props.product.main_image || thumbnailImages.value[0]?.src || null
  quantity.value = 1
}

function selectVolume(volumeId: number | string) {
  selectedVolumeId.value = volumeId
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
    id: props.product.id,
    productType: 'cosmetics',
    productId: props.product.id,
    variantId: currentVariant.value.id,
    quantity: quantity.value,
    sku: currentVariant.value.sku ?? props.product.sku ?? props.product.batch_number ?? null,
    colorId: null,
    colorName: null,
    storageId: selectedVolumeId.value,
    storageLabel: selectedVolumeLabel.value !== 'N/A' ? selectedVolumeLabel.value : null,
    variantLabel: selectedVolumeLabel.value !== 'N/A' ? selectedVolumeLabel.value : null,
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

    window.dispatchEvent(new CustomEvent('tech-product:add-to-cart', { detail: payload }))
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

    window.dispatchEvent(new CustomEvent('tech-product:add-to-cart', { detail: payload }))
    showMessage('Ready for checkout.')
    router.visit(route('frontend.checkout.index'))
  } catch (error) {
    console.error('Error while processing buy now:', error)
  }
}

watch(() => props.product, () => {
  selectedVolumeId.value = null
  activeImage.value = null
  ensureSelections()
}, { immediate: true })

watch(currentVariant, () => {
  quantity.value = Math.min(quantity.value, Math.max(1, stockCount.value))
})
</script>

<template>
  <div class="product-detail-page mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <nav aria-label="Breadcrumb" class="mb-6">
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

    <div v-if="error && !loading" class="py-14 text-center">
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

    <div v-else class="product-detail-layout grid gap-10 lg:grid-cols-[minmax(0,1.02fr)_minmax(360px,0.98fr)] lg:gap-12">
      <section class="product-gallery-column">
        <template v-if="loading || !product">
          <div class="min-h-[420px] rounded-[28px] border border-slate-200 bg-white p-6">
            <div class="h-full w-full animate-pulse rounded-[24px] bg-slate-100" />
          </div>
        </template>

        <template v-else>
          <div class="grid gap-4 md:grid-cols-[78px_minmax(0,1fr)]">
            <div
              v-if="thumbnailImages.length"
              class="order-2 flex gap-3 overflow-x-auto py-2 md:order-1 md:flex-col md:overflow-visible md:py-0"
            >
              <button
                v-for="image in thumbnailImages"
                :key="image.id"
                type="button"
                class="h-20 min-w-20 overflow-hidden rounded-2xl border bg-white sm:h-[88px] sm:min-w-[88px]"
                :class="displayImage === image.src
                  ? 'border-slate-900 shadow-sm'
                  : 'border-slate-200 hover:border-slate-400'"
                @click="activeImage = image.src"
              >
                <img :src="image.src" :alt="`${product.name} - DezeStore`" class="h-full w-full object-contain p-2" />
              </button>
            </div>

            <div class="product-image-stage relative order-1 flex min-h-[360px] items-center justify-center overflow-hidden rounded-[28px] border border-slate-200 bg-white p-5 sm:min-h-[460px] sm:p-8 md:order-2">
              <img
                v-if="product.country?.flag_image_url"
                :src="product.country.flag_image_url"
                :alt="product.country?.name ? `${product.country.name} flag` : 'Country flag'"
                class="absolute right-4 top-4 z-20 h-6 w-9 object-contain sm:h-7 sm:w-10"
                loading="lazy"
                decoding="async"
              />
              <img v-if="displayImage" :src="displayImage" :alt="`${product.name} - DezeStore`" class="max-h-[440px] w-full object-contain" />
            </div>
          </div>

          <div class="product-info-tabs mt-10 hidden border-b border-slate-200 lg:block">
            <div class="flex items-end gap-8">
              <button
                type="button"
                class="relative pb-4 text-sm transition sm:text-base"
                :class="activeTab === 'description' ? 'font-semibold text-slate-950' : 'font-medium text-slate-500 hover:text-slate-900'"
                @click="activeTab = 'description'"
              >
                Description
                <span v-if="activeTab === 'description'" class="absolute inset-x-0 bottom-[-1px] h-[2px] bg-slate-950" />
              </button>
              <button
                type="button"
                class="relative pb-4 text-sm transition sm:text-base"
                :class="activeTab === 'details' ? 'font-semibold text-slate-950' : 'font-medium text-slate-500 hover:text-slate-900'"
                @click="activeTab = 'details'"
              >
                Details
                <span v-if="activeTab === 'details'" class="absolute inset-x-0 bottom-[-1px] h-[2px] bg-slate-950" />
              </button>
              <button
                type="button"
                class="relative pb-4 text-sm transition sm:text-base"
                :class="activeTab === 'delivery' ? 'font-semibold text-slate-950' : 'font-medium text-slate-500 hover:text-slate-900'"
                @click="activeTab = 'delivery'"
              >
                Delivery
                <span v-if="activeTab === 'delivery'" class="absolute inset-x-0 bottom-[-1px] h-[2px] bg-slate-950" />
              </button>
              <button
                type="button"
                class="relative pb-4 text-sm transition sm:text-base"
                :class="activeTab === 'reviews' ? 'font-semibold text-slate-950' : 'font-medium text-slate-500 hover:text-slate-900'"
                @click="activeTab = 'reviews'"
              >
                Reviews ({{ product.reviews_count ?? 0 }})
                <span v-if="activeTab === 'reviews'" class="absolute inset-x-0 bottom-[-1px] h-[2px] bg-slate-950" />
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
                v-else-if="activeTab === 'details'"
                key="details-desktop"
                class="overflow-hidden rounded-[24px] border border-slate-200 bg-white"
              >
                <div class="divide-y divide-slate-200">
                  <div
                    v-for="row in detailRows"
                    :key="row.label"
                    class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-4"
                  >
                    <div class="text-sm font-semibold text-slate-900">{{ row.label }}</div>
                    <div class="text-sm text-slate-600">{{ row.value }}</div>
                  </div>
                </div>
              </div>

              <div
                v-else-if="activeTab === 'delivery'"
                key="delivery-desktop"
                class="space-y-4 text-sm leading-7 text-slate-600 sm:text-[15px]"
              >
                <p v-for="(paragraph, index) in deliveryParagraphs" :key="index">{{ paragraph }}</p>
              </div>

              <div v-else key="reviews-desktop">
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

      <aside class="product-summary-card pt-1">
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
            {{ product.category?.name || product.product_type?.name || 'Cosmetics' }}
          </p>

          <h1 class="mt-3 text-3xl font-semibold tracking-[-0.03em] text-slate-950 sm:text-[2.25rem]">
            {{ product.name }}
          </h1>

          <p v-if="product.short_description" class="mt-4 text-sm leading-7 text-slate-600 sm:text-[15px]">
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
                  <span v-if="hasDiscount && currentOldPrice" class="text-lg font-medium text-slate-400 line-through">
                    {{ formatPrice(currentOldPrice) }}
                  </span>
                  <span class="text-3xl font-bold text-slate-950 sm:text-4xl">
                    {{ formatPrice(currentPrice) }}
                  </span>
                </div>
              </div>
            </Transition>

            <div v-if="product.reviews_avg_rating" class="mt-3">
              <StarRating :rating="product.reviews_avg_rating" :count="product.reviews_count ?? 0" :show-count="true" />
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-4">
              <img
                v-if="product.brand?.logo_url"
                :src="product.brand.logo_url"
                :alt="product.brand?.name || 'Brand logo'"
                class="h-10 w-auto object-contain"
              />

              <div
                v-if="product.country?.flag_image_url || product.country?.name"
                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700"
              >
                <img
                  v-if="product.country?.flag_image_url"
                  :src="product.country.flag_image_url"
                  :alt="product.country?.name ? `${product.country.name} flag` : 'Country flag'"
                  class="h-4 w-6 rounded-sm object-contain"
                  loading="lazy"
                  decoding="async"
                />
                <span>{{ product.country?.name || 'Country' }}</span>
              </div>
            </div>
          </div>

          <ul class="mt-8 space-y-3 text-sm text-slate-700">
            <li class="flex items-start gap-3">
              <span class="mt-[7px] h-1.5 w-1.5 rounded-full bg-slate-900" />
              <span><span class="font-semibold text-slate-900">Availability:</span> {{ availabilityText }}</span>
            </li>
            <li v-if="product.batch_number" class="flex items-start gap-3">
              <span class="mt-[7px] h-1.5 w-1.5 rounded-full bg-slate-900" />
              <span><span class="font-semibold text-slate-900">Batch:</span> {{ product.batch_number }}</span>
            </li>
            <li v-if="product.expiry_date" class="flex items-start gap-3">
              <span class="mt-[7px] h-1.5 w-1.5 rounded-full bg-slate-900" />
              <span><span class="font-semibold text-slate-900">Expiry:</span> {{ product.expiry_date }}</span>
            </li>
          </ul>

          <div v-if="volumes.length" class="mt-8">
            <p class="text-sm font-semibold text-slate-900">Available Volumes</p>
            <div class="mt-3 flex flex-wrap items-center gap-4">
              <button
                v-for="volume in volumes"
                :key="volume.id"
                type="button"
                class="border-b pb-1 text-sm transition"
                :class="selectedVolumeId === volume.id
                  ? 'border-slate-900 font-semibold text-slate-950'
                  : 'border-transparent font-medium text-slate-500 hover:border-slate-400 hover:text-slate-900'"
                @click="selectVolume(volume.id)"
              >
                {{ volume.label }}
              </button>
            </div>
          </div>

          <div class="mt-8 flex flex-wrap items-center gap-6 border-b border-t border-slate-200 py-5">
            <div class="flex items-center gap-4">
              <button type="button" class="text-2xl leading-none text-slate-700 transition hover:text-slate-950" @click="decreaseQuantity">-</button>
              <span class="min-w-[28px] text-center text-lg font-semibold text-slate-950">{{ quantity }}</span>
              <button
                type="button"
                class="text-2xl leading-none text-slate-700 transition hover:text-slate-950 disabled:cursor-not-allowed disabled:text-slate-300"
                :disabled="!canIncreaseQty"
                @click="increaseQuantity"
              >
                +
              </button>
            </div>
            <p class="text-sm text-slate-500">{{ stockCount > 0 ? `${stockCount} available` : 'Currently unavailable' }}</p>
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

          <div class="mt-6 text-sm text-slate-500">
            <p><span class="font-semibold text-slate-800">Selected Volume:</span> {{ selectedVolumeLabel }}</p>
            <p class="mt-1"><span class="font-semibold text-slate-800">SKU:</span> {{ currentVariant?.sku || product.sku || 'N/A' }}</p>
          </div>

            <div class="mt-10 lg:hidden">
              <div class="border-b border-slate-200">
                <div class="flex items-end gap-8 overflow-x-auto no-scrollbar-mobile">
                  <button
                    type="button"
                    class="relative shrink-0 pb-4 text-sm transition sm:text-base"
                    :class="activeTab === 'description' ? 'font-semibold text-slate-950' : 'font-medium text-slate-500 hover:text-slate-900'"
                  @click="activeTab = 'description'"
                >
                  Description
                  <span v-if="activeTab === 'description'" class="absolute inset-x-0 bottom-[-1px] h-[2px] bg-slate-950" />
                </button>
                <button
                  type="button"
                  class="relative shrink-0 pb-4 text-sm transition sm:text-base"
                  :class="activeTab === 'details' ? 'font-semibold text-slate-950' : 'font-medium text-slate-500 hover:text-slate-900'"
                  @click="activeTab = 'details'"
                >
                  Details
                  <span v-if="activeTab === 'details'" class="absolute inset-x-0 bottom-[-1px] h-[2px] bg-slate-950" />
                </button>
                <button
                  type="button"
                  class="relative shrink-0 pb-4 text-sm transition sm:text-base"
                  :class="activeTab === 'delivery' ? 'font-semibold text-slate-950' : 'font-medium text-slate-500 hover:text-slate-900'"
                  @click="activeTab = 'delivery'"
                >
                  Delivery
                  <span v-if="activeTab === 'delivery'" class="absolute inset-x-0 bottom-[-1px] h-[2px] bg-slate-950" />
                </button>
                <button
                  type="button"
                  class="relative shrink-0 pb-4 text-sm transition sm:text-base"
                  :class="activeTab === 'reviews' ? 'font-semibold text-slate-950' : 'font-medium text-slate-500 hover:text-slate-900'"
                  @click="activeTab = 'reviews'"
                >
                  Reviews ({{ product.reviews_count ?? 0 }})
                  <span v-if="activeTab === 'reviews'" class="absolute inset-x-0 bottom-[-1px] h-[2px] bg-slate-950" />
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
                  v-else-if="activeTab === 'details'"
                  key="details-mobile"
                  class="overflow-hidden rounded-[24px] border border-slate-200 bg-white"
                >
                  <div class="divide-y divide-slate-200">
                    <div
                      v-for="row in detailRows"
                      :key="`mobile-${row.label}`"
                      class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-4"
                    >
                      <div class="text-sm font-semibold text-slate-900">{{ row.label }}</div>
                      <div class="text-sm text-slate-600">{{ row.value }}</div>
                    </div>
                  </div>
                </div>
                <div
                  v-else-if="activeTab === 'delivery'"
                  key="delivery-mobile"
                  class="space-y-4 text-sm leading-7 text-slate-600 sm:text-[15px]"
                >
                  <p v-for="(paragraph, index) in deliveryParagraphs" :key="`mobile-delivery-${index}`">{{ paragraph }}</p>
                </div>
                <div v-else key="reviews-mobile">
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
      </aside>
    </div>
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

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.25s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-8px);
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
</style>
