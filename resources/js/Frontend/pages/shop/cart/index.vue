<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { route } from 'ziggy-js'
import AppLayout from '@/Frontend/layouts/AppLayout.vue'
import { useCart } from '../composables/useCart'
import StarRating from '@/Frontend/components/StarRating.vue'
import KokoPayLine from '@/Frontend/components/KokoPayLine.vue'
import { resolveKokoInstallmentPrice } from '@/Frontend/utils/kokoPay'

defineOptions({
  layout: AppLayout,
})

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
  reviews_count?: number | null
  reviews_avg_rating?: number | null
  colors?: ProductColor[]
  url?: string | null
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

function normalizeName(value: string | null | undefined) {
  return String(value ?? '').trim().toLowerCase()
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

function productUrl(product: TechProductCard) {
  return product.url || `/tech-products/${product.id}`
}

function kokoInstallmentPrice(product: TechProductCard) {
  return resolveKokoInstallmentPrice(product)
}

const skeletonCount = computed(() => 4)

const {
  items,
  subtotal,
  totalItems,
  incrementQuantity,
  decrementQuantity,
  removeItem,
} = useCart()

const estimatedDelivery = computed(() => {
  const today = new Date()
  const end = new Date(today)
  end.setDate(today.getDate() + 3)

  return end.toLocaleDateString('en-LK', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
})

const relatedProducts = ref<TechProductCard[]>([])
const loadingRelated = ref(false)
const relatedError = ref<string | null>(null)
let abortController: AbortController | null = null

const productIdsSignature = computed(() => {
  return items.value
    .map((item: any) => String(item.productId ?? ''))
    .filter(Boolean)
    .sort()
    .join(',')
})

const relatedCategoryName = computed(() => {
  return relatedProducts.value[0]?.category_name || null
})

async function fetchRelatedProducts() {
  const productIds = items.value
    .map((item: any) => Number(item.productId))
    .filter((id: number) => Number.isFinite(id) && id > 0)

  if (!productIds.length) {
    relatedProducts.value = []
    relatedError.value = null
    return
  }

  loadingRelated.value = true
  relatedError.value = null

  try {
    abortController?.abort()
    abortController = new AbortController()

    const params = new URLSearchParams()
    productIds.forEach((id) => {
      params.append('product_ids[]', String(id))
    })

    const response = await fetch(
      `${route('frontend.tech-products.cart-related')}?${params.toString()}`,
      {
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        signal: abortController.signal,
      },
    )

    if (!response.ok) {
      throw new Error('Failed to load related products.')
    }

    const payload = await response.json()
    relatedProducts.value = Array.isArray(payload?.products) ? payload.products.slice(0, 4) : []
  } catch (error: any) {
    if (error?.name !== 'AbortError') {
      relatedProducts.value = []
      relatedError.value = 'Failed to load related products.'
    }
  } finally {
    loadingRelated.value = false
  }
}

watch(productIdsSignature, () => {
  fetchRelatedProducts()
}, { immediate: true })

onBeforeUnmount(() => {
  abortController?.abort()
})
</script>

<template>
  <div class="min-h-screen bg-[#f8f8fa] text-slate-950">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
      <nav aria-label="Breadcrumb" class="mb-6">
  <ol class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
    <li class="flex items-center gap-2">
      <Link :href="route('frontend.root')" class="transition hover:text-slate-900">
        Home
      </Link>
      <span class="text-slate-300">/</span>
    </li>
    <li>
      <span class="font-medium text-slate-900">Cart</span>
    </li>
  </ol>
</nav>
      <div class="flex flex-col gap-3 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Cart Page</p>
          <h1 class="mt-2 text-3xl font-semibold tracking-[-0.03em] text-slate-950 sm:text-[2.35rem]">
            Your Shopping Cart
          </h1>
          <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600 sm:text-[15px]">
            Review your selected products, adjust quantities, and continue to checkout when you are ready.
          </p>
        </div>

        <p class="text-sm font-medium text-slate-500">
          {{ totalItems }} item{{ totalItems === 1 ? '' : 's' }}
        </p>
      </div>

      <div
        v-if="!items.length"
        class="mt-8 rounded-[32px] border border-dashed border-slate-200 bg-white px-6 py-14 text-center shadow-sm"
      >
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-50">
          <svg class="h-8 w-8 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.4 10.2a1 1 0 00.98.8H18.8a1 1 0 00.97-.76L21 7H7" />
            <circle cx="10" cy="20" r="1.5" />
            <circle cx="18" cy="20" r="1.5" />
          </svg>
        </div>

        <h2 class="mt-5 text-2xl font-semibold text-slate-950">Your cart is empty</h2>
        <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-slate-500 sm:text-[15px]">
          Browse your product collection and add the items you want. They will appear here automatically.
        </p>

        <Link
          :href="route('frontend.tech-products.index')"
          class="mt-7 inline-flex min-h-[52px] items-center justify-center rounded-full bg-slate-950 px-7 text-sm font-semibold text-white transition hover:bg-slate-800"
        >
          Continue Shopping
        </Link>
      </div>

      <template v-else>
        <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px]">
          <section class="min-w-0">
            <div class="divide-y divide-slate-200">
              <article
                v-for="item in items"
                :key="item.key"
                class="grid grid-cols-[72px_minmax(0,1fr)] gap-4 py-5 first:pt-0 last:pb-0 sm:grid-cols-[88px_minmax(0,1fr)_auto] sm:gap-6"
              >
               <div class="flex h-[72px] w-[72px] items-center justify-center overflow-hidden rounded-[18px] sm:h-[88px] sm:w-[88px] sm:rounded-[22px]">
                  <img
                    v-if="item.image"
                    :src="item.image"
                    :alt="item.name"
                    class="max-h-full max-w-full object-contain"
                  />
                  <div
                    v-else
                    class="flex h-full w-full items-center justify-center text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400"
                  >
                    No Image
                  </div>
                </div>

                <div class="min-w-0">
                  <div class="flex items-start justify-between gap-4 sm:hidden">
                    <div class="min-w-0">
                      <Link
                        v-if="item.url"
                        :href="item.url"
                        class="line-clamp-2 text-[15px] font-semibold leading-7 text-slate-900 transition hover:text-slate-700"
                      >
                        {{ item.name }}
                      </Link>

                      <p
                        v-else
                        class="line-clamp-2 text-[15px] font-semibold leading-7 text-slate-900"
                      >
                        {{ item.name }}
                      </p>
                    </div>

                    <div class="shrink-0 text-right">
                      <p
                        v-if="item.oldPrice && item.oldPrice > item.price"
                        class="text-xs text-slate-400 line-through"
                      >
                        {{ formatPrice(item.oldPrice) }}
                      </p>
                      <p class="text-[15px] font-medium text-slate-800">
                        {{ formatPrice(item.price) }}
                      </p>
                    </div>
                  </div>

                  <div class="hidden sm:block">
                    <Link
                      v-if="item.url"
                      :href="item.url"
                      class="line-clamp-2 text-[15px] font-semibold leading-7 text-slate-900 transition hover:text-slate-700"
                    >
                      {{ item.name }}
                    </Link>

                    <p
                      v-else
                      class="line-clamp-2 text-[15px] font-semibold leading-7 text-slate-900"
                    >
                      {{ item.name }}
                    </p>
                  </div>

                  <p
                    v-if="item.colorName || item.storageLabel"
                    class="mt-1 text-sm text-slate-500"
                  >
                    <span v-if="item.colorName">{{ item.colorName }}</span>
                    <span v-if="item.colorName && item.storageLabel"> • </span>
                    <span v-if="item.storageLabel">{{ item.storageLabel }}</span>
                  </p>

                  <div class="mt-3 flex items-center gap-4">
                    <div class="inline-flex h-9 items-center overflow-hidden rounded-md border border-slate-300">
                      <button
                        type="button"
                        class="flex h-full w-10 items-center justify-center text-lg text-slate-500 transition hover:bg-slate-50 hover:text-slate-900"
                        @click="decrementQuantity(item.key)"
                      >
                        −
                      </button>

                      <span class="flex h-full min-w-[34px] items-center justify-center px-2 text-sm font-medium text-slate-800">
                        {{ item.quantity }}
                      </span>

                      <button
                        type="button"
                        class="flex h-full w-10 items-center justify-center text-lg text-slate-500 transition hover:bg-slate-50 hover:text-slate-900"
                        @click="incrementQuantity(item.key)"
                      >
                        +
                      </button>
                    </div>

                    <button
                      type="button"
                      class="inline-flex h-8 w-8 items-center justify-center text-[#ef5a4f] transition hover:opacity-70"
                      @click="removeItem(item.key)"
                      aria-label="Remove item"
                    >
                      <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path
                          fill-rule="evenodd"
                          d="M6 8a.75.75 0 011.5 0v6A.75.75 0 016 14V8zm6.75-.75A.75.75 0 0012 8v6a.75.75 0 001.5 0V8a.75.75 0 00-.75-.75z"
                          clip-rule="evenodd"
                        />
                        <path
                          fill-rule="evenodd"
                          d="M14.5 4.75V4a1 1 0 00-1-1h-7a1 1 0 00-1 1v.75H3.75a.75.75 0 000 1.5h.598l.585 8.768A2 2 0 006.93 17.9h6.14a2 2 0 001.997-1.882l.585-8.768h.598a.75.75 0 000-1.5H14.5zm-7.5-.75h6v.75h-6V4zm-.563 2.25h7.126l-.55 8.256a.5.5 0 01-.499.469H6.986a.5.5 0 01-.499-.469l-.55-8.256z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </button>
                  </div>
                </div>

                <div class="hidden shrink-0 text-right sm:block">
                  <p
                    v-if="item.oldPrice && item.oldPrice > item.price"
                    class="text-xs text-slate-400 line-through"
                  >
                    {{ formatPrice(item.oldPrice) }}
                  </p>
                  <p class="text-[16px] font-medium text-slate-800">
                    {{ formatPrice(item.price) }}
                  </p>
                </div>
              </article>
            </div>
          </section>

          <aside class="h-fit rounded-[30px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-xl font-semibold text-slate-950">Order Summary</h2>

            <div class="mt-6 space-y-4 border-b border-slate-200 pb-5">
              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500">Items</span>
                <span class="font-semibold text-slate-950">{{ totalItems }}</span>
              </div>

              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500">Estimated delivery</span>
                <span class="font-semibold text-slate-950">{{ estimatedDelivery }}</span>
              </div>

              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500">Shipping</span>
                <span class="font-semibold text-emerald-600">Calculated at checkout</span>
              </div>
            </div>

            <div class="mt-5 flex items-center justify-between">
              <span class="text-base font-medium text-slate-600">Subtotal</span>
              <span class="text-2xl font-semibold text-slate-950">{{ formatPrice(subtotal) }}</span>
            </div>

            <Link
              :href="route('frontend.checkout.index')"
              class="mt-6 inline-flex min-h-[54px] w-full items-center justify-center rounded-full bg-slate-950 px-6 text-sm font-semibold text-white transition hover:bg-slate-800"
            >
              Pay Now
            </Link>

            <Link
              :href="route('frontend.tech-products.index')"
              class="mt-3 inline-flex min-h-[52px] w-full items-center justify-center rounded-full border border-slate-900 px-6 text-sm font-semibold text-slate-950 transition hover:bg-slate-50"
            >
              Continue Shopping
            </Link>
          </aside>
        </div>

        <section v-if="loadingRelated || relatedProducts.length || relatedError" class="pt-8">
          <div class="mb-6 pt-8 sm:mb-8 sm:pt-10 lg:pt-10">
            <p
              v-if="relatedCategoryName"
              class="text-[11px] font-semibold uppercase tracking-[0.18em] text-neutral-500"
            >
              More from {{ relatedCategoryName }}
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-[-0.02em] text-neutral-950 sm:text-3xl">
              You may also like
            </h2>
          </div>

          <div
            v-if="relatedError"
            class="rounded-[28px] border border-red-200 bg-red-50 px-6 py-12 text-center"
          >
            <h3 class="text-lg font-semibold text-red-700">
              Failed to load products
            </h3>
            <p class="mt-2 text-sm text-red-500">
              Please try again.
            </p>

            <button
              type="button"
              class="mt-5 inline-flex items-center justify-center rounded-2xl bg-neutral-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-neutral-800"
              @click="fetchRelatedProducts"
            >
              Retry
            </button>
          </div>

          <template v-else>
            <div
              v-if="!loadingRelated && !relatedProducts.length"
              class="rounded-[28px] border border-neutral-200 bg-white px-6 py-14 text-center shadow-sm"
            >
              <h3 class="text-xl font-semibold text-neutral-900">
                No related products found
              </h3>
              <p class="mt-2 text-sm text-neutral-500">
                More products from this category will appear here.
              </p>
            </div>

            <div
              v-else
              class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-4 xl:grid-cols-4 lg:gap-5"
            >
              <Link
                v-for="product in relatedProducts"
                :key="product.id"
                :href="productUrl(product)"
                class="product-card group block overflow-hidden rounded-[20px] border border-neutral-200 bg-white shadow-sm outline-none transition focus-visible:ring-2 focus-visible:ring-neutral-900 sm:rounded-[24px]"
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
                      {{ product.category_name || 'Category' }}
                    </div>
                    <div class="text-[9px] text-neutral-500 sm:text-[11px]">
                      tech collection
                    </div>
                  </div>

                  <div class="relative flex h-[180px] items-center justify-center px-3 pb-3 pt-10 sm:h-[240px] sm:px-4 sm:pb-4 sm:pt-12 xl:h-[260px]">
                    <img
                      :src="product.thumbnail_url || product.hover_image_url || ''"
                      :alt="product.name"
                      class="product-main-image max-h-full max-w-full object-contain"
                      :class="{ 'opacity-0': !product.thumbnail_url && !product.hover_image_url }"
                    />

                    <img
                      v-if="product.hover_image_url"
                      :src="product.hover_image_url"
                      :alt="`${product.name} hover`"
                      class="product-hover-image max-h-full max-w-full object-contain"
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

                    <KokoPayLine
                      :amount="kokoInstallmentPrice(product)"
                      compact
                    />
                  </div>

                  <div class="mt-2 flex flex-col gap-1">
                    <StarRating
                      :rating="product.reviews_avg_rating ?? 0"
                      :count="product.reviews_count ?? 0"
                      :showCount="true"
                      size="xs"
                    />
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

                  <div
                    v-if="product.colors && product.colors.length"
                    class="mt-3 flex items-center gap-2"
                  >
                    <span
                      v-for="color in product.colors"
                      :key="color.id"
                      class="h-6 w-6 rounded-full border border-neutral-300 shadow-sm"
                      :style="colorSwatchStyle(color)"
                      :title="color.name"
                    />
                  </div>
                </div>
              </Link>

              <div
                v-for="index in loadingRelated ? skeletonCount : 0"
                :key="`related-tech-skeleton-${index}`"
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
                  <div class="mt-2 flex gap-2">
                    <div class="h-6 w-6 animate-pulse rounded-full bg-neutral-200" />
                    <div class="h-6 w-6 animate-pulse rounded-full bg-neutral-200" />
                    <div class="h-6 w-6 animate-pulse rounded-full bg-neutral-200" />
                  </div>
                </div>
              </div>
            </div>
          </template>
        </section>
      </template>
    </main>
  </div>
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

@media (min-width: 640px) {
  .product-main-image,
  .product-hover-image {
    max-width: calc(100% - 2rem);
    max-height: calc(100% - 2rem);
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
