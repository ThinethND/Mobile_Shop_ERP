<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { useWishlist } from '@/Frontend/pages/shop/composables/useWishlist'
import type { AddToCartPayload } from '@/Frontend/pages/shop/composables/useCart'

type CategoryItem = {
  id: number | string
  name: string
  image_url?: string | null
  status?: string | null
}

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
  short_description?: string | null
  description?: string | null
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

const props = defineProps<{
  products?: ProductItem[]
  categories?: CategoryItem[]
  activeCategory?: string | null
}>()

const sectionRef = ref<HTMLElement | null>(null)
const loadingCategories = ref(true)
const loadingProducts = ref(false)
const categoriesLoaded = ref<CategoryItem[]>([])
const productsLoaded = ref<ProductItem[]>([])
const selectedCategory = ref<string | null>(props.activeCategory ?? null)
const hasLoadedProductsOnce = ref(false)
const loadError = ref(false)
const categoryLoadError = ref(false)

const imageLoadTotal = ref(0)
const imageLoadDone = ref(0)

let sectionObserver: IntersectionObserver | null = null
let delayedLoadTimer: number | null = null

const navCategories = computed(() => categoriesLoaded.value)
const visibleProducts = computed(() => productsLoaded.value.slice(0, 8))
const showSkeletons = computed(() => loadingProducts.value)
const { addItem: addWishlistItem, hasItem: hasWishlistItem } = useWishlist()

function normalizeName(value: string | null | undefined) {
  return String(value ?? '').trim().toLowerCase()
}

function isActiveNav(name?: string | null) {
  return normalizeName(selectedCategory.value) === normalizeName(name)
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

function clampRating(value: number | null | undefined) {
  const rating = Number(value ?? 0)

  if (!Number.isFinite(rating)) {
    return 0
  }

  return Math.max(0, Math.min(5, rating))
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
  const rating = clampRating(value).toFixed(1)
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

function buildWishlistPayload(product: ProductItem): AddToCartPayload {
  const selectedColor = product.colors?.[0] ?? null

  return {
    id: product.id,
    productType: 'electronics',
    productId: product.id,
    variantId: `electronics-${product.id}`,
    quantity: 1,
    colorId: selectedColor?.id ?? null,
    colorName: selectedColor?.name ?? null,
    storageId: null,
    storageLabel: null,
    sizeId: null,
    sizeLabel: null,
    variantLabel: null,
    sku: null,
    price: Number(product.display_price ?? product.regular_price ?? 0),
    oldPrice: showRegularPrice(product) ? product.regular_price : null,
    stockCount: 99,
    name: product.name,
    image: product.thumbnail_url || product.hover_image_url || null,
    url: productUrl(product),
  }
}

function addProductToWishlist(product: ProductItem) {
  addWishlistItem(buildWishlistPayload(product))
  router.visit(route('frontend.wishlist.index'))
}

function isWishlisted(product: ProductItem) {
  return hasWishlistItem(buildWishlistPayload(product))
}

function productDescription(product: ProductItem) {
  const directDescription = String(product.short_description || product.description || '').trim()

  if (directDescription) {
    return directDescription.replace(/<[^>]*>/g, '').slice(0, 110)
  }

  const parts = [product.brand_name, product.category_name]
    .map((item) => String(item || '').trim())
    .filter(Boolean)

  if (parts.length) {
    return parts.join(', ')
  }

  return 'Premium selection with a clean modern finish.'
}

function discountPill(product: ProductItem) {
  if (!product.has_discount) return ''

  const label = String(product.discount_label || '')
    .replace(/^sale\s*/i, '')
    .replace(/^discount\s*/i, '')
    .replace(/^save\s*/i, '')
    .trim()

  if (label) return label

  const regular = Number(product.regular_price ?? 0)
  const display = Number(product.display_price ?? 0)

  if (regular > 0 && display > 0 && display < regular) {
    const percentage = Math.round(((regular - display) / regular) * 100)
    return `-${percentage}%`
  }

  return ''
}

function showRegularPrice(product: ProductItem) {
  return product.has_discount
    && product.regular_price !== null
    && product.display_price !== null
    && Number(product.regular_price) > Number(product.display_price)
}

function updateUrl(category?: string | null) {
  if (typeof window === 'undefined') return

  const url = new URL(window.location.href)

  if (category && String(category).trim() !== '') {
    url.searchParams.set('category', category)
  } else {
    url.searchParams.delete('category')
  }

  url.hash = 'products-section'
  window.history.replaceState({}, '', url.toString())
}

function scrollToSection() {
  sectionRef.value?.scrollIntoView({
    behavior: 'smooth',
    block: 'start',
  })
}

function preloadImages() {
  const urls = visibleProducts.value
    .flatMap((product) => [product.thumbnail_url, product.hover_image_url])
    .filter((url): url is string => !!url)

  imageLoadTotal.value = urls.length
  imageLoadDone.value = 0

  if (!urls.length) {
    loadingProducts.value = false
    return
  }

  urls.forEach((url) => {
    const img = new Image()

    const markDone = () => {
      imageLoadDone.value += 1
      if (imageLoadDone.value >= imageLoadTotal.value) {
        loadingProducts.value = false
      }
    }

    img.onload = markDone
    img.onerror = markDone
    img.src = url
  })
}

async function fetchCategories() {
  loadingCategories.value = true
  categoryLoadError.value = false

  try {
    const response = await fetch('/home/categories', {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })

    if (!response.ok) {
      throw new Error('Failed to fetch categories')
    }

    const data = await response.json()
    categoriesLoaded.value = Array.isArray(data?.categories) ? data.categories : []
  } catch (error) {
    console.error('Product categories fetch error:', error)
    categoryLoadError.value = true
    categoriesLoaded.value = []
  } finally {
    loadingCategories.value = false
  }
}

async function fetchProducts(category?: string | null) {
  loadingProducts.value = true
  loadError.value = false

  try {
    const url = new URL('/home/products', window.location.origin)

    if (category && String(category).trim() !== '') {
      url.searchParams.set('category', category)
    }

    const response = await fetch(url.toString(), {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })

    if (!response.ok) {
      throw new Error('Failed to fetch products')
    }

    const data = await response.json()
    productsLoaded.value = Array.isArray(data?.products) ? data.products : []
    hasLoadedProductsOnce.value = true
    preloadImages()
  } catch (error) {
    console.error('Products fetch error:', error)
    loadError.value = true
    productsLoaded.value = []
    loadingProducts.value = false
  }
}

async function ensureProductsLoaded() {
  if (hasLoadedProductsOnce.value || loadingProducts.value) return
  await fetchProducts(selectedCategory.value)
}

async function onCategoryClick(category?: string | null) {
  selectedCategory.value = category ?? null
  updateUrl(selectedCategory.value)
  scrollToSection()
  await nextTick()
  await fetchProducts(selectedCategory.value)
}

onMounted(async () => {
  if ((props.categories ?? []).length > 0) {
    categoriesLoaded.value = props.categories ?? []
    loadingCategories.value = false
  } else {
    fetchCategories()
  }

  if ((props.products ?? []).length > 0) {
    productsLoaded.value = props.products ?? []
    hasLoadedProductsOnce.value = true
    preloadImages()
  }

  delayedLoadTimer = window.setTimeout(() => {
    ensureProductsLoaded()
  }, 1400)

  if ('IntersectionObserver' in window && sectionRef.value) {
    sectionObserver = new IntersectionObserver(
      (entries) => {
        const entry = entries[0]
        if (entry?.isIntersecting) {
          ensureProductsLoaded()
        }
      },
      {
        root: null,
        rootMargin: '200px 0px',
        threshold: 0.1,
      }
    )

    sectionObserver.observe(sectionRef.value)
  }

  if (window.location.hash === '#products-section') {
    setTimeout(() => {
      scrollToSection()
      ensureProductsLoaded()
    }, 150)
  }
})

onBeforeUnmount(() => {
  if (sectionObserver) {
    sectionObserver.disconnect()
    sectionObserver = null
  }

  if (delayedLoadTimer) {
    window.clearTimeout(delayedLoadTimer)
    delayedLoadTimer = null
  }
})
</script>

<template>
  <section
    id="products-section"
    ref="sectionRef"
    class="collection-section mx-auto max-w-7xl px-3 py-8 sm:px-6 sm:py-12 lg:px-8"
  >
    <div class="mb-5 flex flex-col gap-4 sm:mb-7">
      <div>
        <h2 class="collection-heading-title text-2xl font-semibold tracking-normal text-gray-900 sm:text-4xl">
          Our Collection
        </h2>
        <p class="collection-heading-subtitle mt-1 text-sm text-neutral-500 sm:text-base">
          Carefully selected devices and accessories with standout style, sharp value, and a premium finish.
        </p>
      </div>

      <div class="overflow-x-auto pb-1 no-scrollbar-mobile">
        <div class="inline-flex min-w-full gap-2 sm:gap-3">
          <button
            type="button"
            class="whitespace-nowrap rounded-full border px-4 py-2 text-sm font-semibold transition"
            :class="!selectedCategory
              ? 'border-black bg-black text-white'
              : 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100'"
            @click="onCategoryClick(null)"
          >
            All
          </button>

          <template v-if="!loadingCategories && navCategories.length">
            <button
              v-for="category in navCategories"
              :key="category.id"
              type="button"
              class="whitespace-nowrap rounded-full border px-4 py-2 text-sm font-semibold transition"
              :class="isActiveNav(category.name)
                ? 'border-black bg-black text-white'
                : 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100'"
              @click="onCategoryClick(category.name)"
            >
              {{ category.name }}
            </button>
          </template>

          <template v-else-if="loadingCategories">
            <span
              v-for="index in 4"
              :key="`cat-pill-${index}`"
              class="inline-block h-[40px] w-[100px] animate-pulse rounded-full border border-neutral-200 bg-neutral-100"
            />
          </template>
        </div>
      </div>
    </div>

    <div
      v-if="showSkeletons"
      class="products-grid"
    >
      <div
        v-for="index in 8"
        :key="`product-skeleton-${index}`"
        class="product-card product-card--skeleton"
      >
        <div class="product-card__media animate-pulse">
          <div class="skeleton-image" />
        </div>
        <div class="product-card__body">
          <div class="h-4 w-4/5 animate-pulse rounded bg-slate-200" />
          <div class="mt-2 h-3 w-full animate-pulse rounded bg-slate-100" />
          <div class="mt-1 h-3 w-3/4 animate-pulse rounded bg-slate-100" />
          <div class="mt-4 h-4 w-1/2 animate-pulse rounded bg-slate-200" />
          <div class="mt-4 flex items-end justify-between">
            <div class="h-7 w-24 animate-pulse rounded bg-slate-200" />
            <div class="h-14 w-14 animate-pulse rounded-2xl bg-slate-200" />
          </div>
        </div>
      </div>
    </div>

    <div
      v-else-if="loadError"
      class="rounded-[24px] border border-red-200 bg-red-50 px-6 py-12 text-center"
    >
      <h3 class="text-lg font-semibold text-red-700">Failed to load products</h3>
      <p class="mt-2 text-sm text-red-500">
        Please try again.
      </p>
    </div>

    <div
      v-else-if="visibleProducts.length"
      class="products-grid"
    >
      <article
        v-for="product in visibleProducts"
        :key="product.id"
        class="product-card group"
      >
        <Link
          :href="productUrl(product)"
          class="product-card__link"
          :aria-label="`View ${product.name}`"
        >
          <div class="product-card__media">
            <div class="product-card__actions">
              <span
                role="button"
                tabindex="0"
                class="action-bubble action-bubble--heart"
                :class="{ 'action-bubble--heart-active': isWishlisted(product) }"
                :aria-label="isWishlisted(product) ? `${product.name} is already in wishlist` : `Add ${product.name} to wishlist`"
                @click.prevent.stop="addProductToWishlist(product)"
                @keydown.enter.prevent.stop="addProductToWishlist(product)"
                @keydown.space.prevent.stop="addProductToWishlist(product)"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 000-7.78z" />
                </svg>
              </span>
            </div>

            <span
              v-if="product.is_sold_out"
              class="sold-out-badge"
            >
              Sold Out
            </span>

            <img
              :src="product.thumbnail_url || product.hover_image_url || ''"
              :alt="`${product.name} - DezeStore`"
              class="product-main-image"
              :class="{ 'opacity-0': !product.thumbnail_url && !product.hover_image_url }"
              loading="lazy"
              decoding="async"
            />

            <img
              v-if="product.hover_image_url"
              :src="product.hover_image_url"
              :alt="`${product.name} - DezeStore alternate view`"
              class="product-hover-image"
              loading="lazy"
              decoding="async"
            />

          </div>

          <div class="product-card__body">
            <h3 class="product-title">
              {{ product.name }}
            </h3>

            <p class="product-description">
              {{ productDescription(product) }}
            </p>

            <div
              class="rating-row"
              :aria-label="ratingAriaLabel(product.reviews_avg_rating, product.reviews_count)"
              role="img"
            >
              <div class="product-rating-stars">
                <span
                  v-for="starNumber in 5"
                  :key="`rating-star-${product.id}-${starNumber}`"
                  class="product-rating-star"
                  aria-hidden="true"
                >
                  <svg
                    viewBox="0 0 24 24"
                    class="product-rating-star-base"
                  >
                    <path d="M12 2.25l2.917 5.91 6.523.948-4.72 4.6 1.114 6.497L12 17.118 6.166 20.205l1.114-6.497-4.72-4.6 6.523-.948L12 2.25z" />
                  </svg>

                  <span
                    class="product-rating-star-fill"
                    :style="starFillStyle(product.reviews_avg_rating || 5, starNumber)"
                  >
                    <svg
                      viewBox="0 0 24 24"
                      class="product-rating-star-top"
                    >
                      <path d="M12 2.25l2.917 5.91 6.523.948-4.72 4.6 1.114 6.497L12 17.118 6.166 20.205l1.114-6.497-4.72-4.6 6.523-.948L12 2.25z" />
                    </svg>
                  </span>
                </span>
              </div>

              <span class="comment-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v11H8l-4 4V5z" />
                </svg>
              </span>

              <span class="review-count">
                {{ product.reviews_count ?? 0 }}
              </span>
            </div>

            <div class="price-meta-row">
              <span
                v-if="showRegularPrice(product)"
                class="regular-price"
              >
                {{ formatPrice(product.regular_price) }}
              </span>

              <span
                v-if="discountPill(product)"
                class="discount-pill"
              >
                {{ discountPill(product) }}
              </span>
            </div>
          </div>
        </Link>

        <div class="product-card__footer">
          <div class="final-price">
            {{ formatPrice(product.display_price) }}
          </div>

          <Link
            :href="productUrl(product)"
            class="cart-button"
            :class="product.is_sold_out ? 'cart-button--disabled' : ''"
            :aria-label="product.is_sold_out ? `${product.name} is sold out` : `View ${product.name}`"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 5h2l2.2 9.4a1.2 1.2 0 001.17.93h8.72a1.2 1.2 0 001.16-.9L20.5 8H7" />
              <path stroke-linecap="round" d="M9.5 19h.01M17.5 19h.01" />
            </svg>
          </Link>
        </div>

        <div
          v-if="product.colors && product.colors.length"
          class="color-strip"
        >
          <span
            v-for="color in product.colors.slice(0, 5)"
            :key="color.id"
            class="color-dot"
            :style="colorSwatchStyle(color)"
            :title="color.name"
          />
        </div>
      </article>
    </div>

    <div
      v-else
      class="rounded-[24px] border border-dashed border-neutral-300 bg-white px-6 py-12 text-center"
    >
      <h3 class="text-lg font-semibold text-neutral-900">No products found</h3>
      <p class="mt-2 text-sm text-neutral-500">
        Try another category to see matching products.
      </p>
    </div>

    <div class="mt-8 flex justify-center">
      <Link
        :href="route('frontend.tech-products.index', {
          category: selectedCategory || undefined,
        })"
        prefetch="mount"
        class="inline-flex items-center justify-center rounded-full border border-neutral-900 bg-neutral-900 px-7 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-neutral-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-neutral-900 focus-visible:ring-offset-2"
      >
        Explore All
      </Link>
    </div>
  </section>
</template>

<style scoped>
.collection-section {
  --card-blue: #2f6fe4;
  --card-orange: #ff980f;
  --card-ink: #151821;
  --card-muted: #535866;
  --card-soft-blue: #eaf3ff;
}

.collection-heading-title {
  display: inline-block;
  color: #111827;
  font-weight: 600;
  line-height: 1.1;
}

.collection-heading-subtitle {
  position: relative;
  max-width: 660px;
  margin-top: 10px;
  padding-left: 16px;
  color: #365173;
  font-weight: 700;
  line-height: 1.65;
}

.collection-heading-subtitle::before {
  position: absolute;
  top: 0.38em;
  left: 0;
  width: 4px;
  min-height: 22px;
  height: calc(100% - 0.76em);
  border-radius: 999px;
  background: linear-gradient(180deg, var(--card-blue), var(--card-orange));
  content: '';
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(255px, 1fr));
  gap: 18px;
  align-items: stretch;
}

.product-card {
  position: relative;
  display: flex;
  min-height: 463px;
  overflow: hidden;
  border-radius: 18px;
  background: #ffffff;
  box-shadow: none;
  isolation: isolate;
  transition:
    transform 0.28s ease,
    box-shadow 0.28s ease;
  flex-direction: column;
}

.product-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 18px 44px rgba(30, 76, 143, 0.14);
}

.product-card__link {
  display: block;
  min-width: 0;
  color: inherit;
  text-decoration: none;
  outline: none;
}

.product-card__link:focus-visible {
  border-radius: 18px;
  box-shadow: 0 0 0 3px rgba(47, 111, 228, 0.28);
}

.product-card__media {
  position: relative;
  height: 255px;
  overflow: hidden;
  background: #ffffff;
}

.product-card__actions {
  position: absolute;
  top: 14px;
  right: 13px;
  z-index: 5;
  display: flex;
  flex-direction: column;
  gap: 13px;
}

.action-bubble {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 41px;
  height: 41px;
  border-radius: 999px;
  border: 0;
  cursor: pointer;
}

.action-bubble svg {
  width: 21px;
  height: 21px;
}

.action-bubble--heart {
  background: #fff0db;
  color: #ff980f;
}

.action-bubble--heart-active {
  background: #ef5a4f;
  color: #ffffff;
}

.sold-out-badge {
  position: absolute;
  top: 15px;
  left: 15px;
  z-index: 5;
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  background: rgba(21, 24, 33, 0.9);
  padding: 6px 10px;
  color: #ffffff;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.02em;
}

.product-main-image,
.product-hover-image {
  position: absolute;
  top: 27px;
  left: 50%;
  width: calc(100% - 42px);
  height: 205px;
  object-fit: contain;
  transform: translateX(-50%) scale(1);
  transform-origin: center;
  transition:
    opacity 0.34s ease,
    transform 0.34s ease;
}

.product-main-image {
  opacity: 1;
}

.product-hover-image {
  opacity: 0;
  transform: translateX(-50%) scale(1.025);
}

.product-card:hover .product-main-image {
  opacity: 0;
  transform: translateX(-50%) scale(1.02);
}

.product-card:hover .product-hover-image {
  opacity: 1;
  transform: translateX(-50%) scale(1);
}

.product-card__body {
  padding: 13px 16px 0;
}

.product-title {
  display: -webkit-box;
  min-height: 39px;
  margin: 0;
  overflow: hidden;
  color: var(--card-ink);
  font-size: 16px;
  font-weight: 500;
  letter-spacing: 0;
  line-height: 1.18;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.product-description {
  display: -webkit-box;
  min-height: 33px;
  margin: 7px 0 0;
  overflow: hidden;
  color: #242833;
  font-size: 13px;
  line-height: 1.22;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.rating-row {
  display: flex;
  align-items: center;
  gap: 5px;
  margin-top: 12px;
  min-height: 18px;
}

.product-rating-stars {
  display: inline-flex;
  align-items: center;
  gap: 1px;
  flex-shrink: 0;
  line-height: 0;
}

.product-rating-star {
  position: relative;
  display: inline-flex;
  width: 16px;
  height: 16px;
  flex: 0 0 16px;
}

.product-rating-star-base,
.product-rating-star-top {
  display: block;
  width: 100%;
  height: 100%;
}

.product-rating-star-base {
  color: #d3d7df;
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
  color: var(--card-orange);
  fill: currentColor;
}

.comment-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 17px;
  height: 17px;
  margin-left: 4px;
  color: #74809a;
}

.comment-icon svg {
  width: 16px;
  height: 16px;
}

.review-count {
  color: #68728b;
  font-size: 12px;
  line-height: 1;
}

.price-meta-row {
  display: flex;
  align-items: center;
  gap: 8px;
  min-height: 22px;
  margin-top: 11px;
}

.regular-price {
  color: #7d8493;
  font-size: 15px;
  line-height: 1;
  text-decoration: line-through;
  text-decoration-thickness: 1.4px;
}

.discount-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 20px;
  border-radius: 4px;
  background: #eaf2ff;
  padding: 2px 7px;
  color: #1e4692;
  font-size: 12px;
  font-weight: 500;
  line-height: 1;
}

.product-card__footer {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 10px;
  margin-top: auto;
  padding: 0 16px 16px;
}

.final-price {
  min-width: 0;
  color: var(--card-ink);
  font-size: 25px;
  font-weight: 800;
  letter-spacing: 0;
  line-height: 1;
  word-break: break-word;
}

.cart-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  flex: 0 0 56px;
  border-radius: 11px;
  background: var(--card-blue);
  color: #ffffff;
  text-decoration: none;
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    background-color 0.22s ease;
}

.cart-button:hover {
  transform: translateY(-2px);
  background: #275fd0;
  box-shadow: 0 12px 24px rgba(47, 111, 228, 0.28);
}

.cart-button svg {
  width: 27px;
  height: 27px;
}

.cart-button--disabled {
  opacity: 0.55;
  pointer-events: none;
}

.color-strip {
  position: absolute;
  left: 16px;
  bottom: 59px;
  z-index: 8;
  display: flex;
  gap: 5px;
  opacity: 0;
  transform: translateY(6px);
  transition:
    opacity 0.22s ease,
    transform 0.22s ease;
}

.product-card:hover .color-strip {
  opacity: 1;
  transform: translateY(0);
}

.color-dot {
  display: inline-flex;
  width: 13px;
  height: 13px;
  border: 1px solid rgba(15, 23, 42, 0.18);
  border-radius: 999px;
  box-shadow: 0 1px 3px rgba(15, 23, 42, 0.12);
}

.product-card--skeleton {
  border: none;
}

.skeleton-image {
  position: absolute;
  top: 27px;
  left: 50%;
  width: calc(100% - 70px);
  height: 200px;
  border-radius: 999px;
  background: #eef2f7;
  transform: translateX(-50%);
}

.no-scrollbar-mobile {
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.no-scrollbar-mobile::-webkit-scrollbar {
  display: none;
}

@media (max-width: 640px) {
  .products-grid {
    grid-template-columns: minmax(0, 1fr);
    gap: 16px;
  }

  .product-card {
    width: min(100%, 280px);
    margin: 0 auto;
  }
}

@media (min-width: 641px) and (max-width: 900px) {
  .products-grid {
    grid-template-columns: repeat(2, minmax(255px, 1fr));
  }
}

@media (prefers-reduced-motion: reduce) {
  .product-card,
  .product-main-image,
  .product-hover-image,
  .cart-button,
  .color-strip {
    transition: none !important;
    transform: none !important;
  }
}
</style>
