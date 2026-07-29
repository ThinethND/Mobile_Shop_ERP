<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import KokoPayLine from '@/Frontend/components/KokoPayLine.vue'
import { useCart, type AddToCartPayload } from '@/Frontend/pages/shop/composables/useCart'
import { useWishlist } from '@/Frontend/pages/shop/composables/useWishlist'
import { resolveKokoInstallmentPrice } from '@/Frontend/utils/kokoPay'

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
  sku?: string | null
  product_type?: string | null
}

const props = withDefaults(defineProps<{
  product?: HomeProductItem | null
  skeleton?: boolean
}>(), {
  product: null,
  skeleton: false,
})

const { addItem } = useCart()
const { addItem: addWishlistItem, hasItem: hasWishlistItem } = useWishlist()

function formatPrice(value: number | null | undefined) {
  if (value === null || typeof value === 'undefined' || Number.isNaN(Number(value))) {
    return 'Rs 0.00'
  }

  return `Rs ${Number(value).toLocaleString('en-LK', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}

function normalizeName(value: string | null | undefined) {
  return String(value ?? '').trim().toLowerCase()
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

function productUrl(product: HomeProductItem) {
  return product.url || '#'
}

function productIdentity(product: HomeProductItem) {
  const rawId = String(product.id ?? '').trim()
  const explicitType = String(product.product_type ?? '').trim().toLowerCase()
  const typeFromId = rawId.startsWith('motorcycle-')
    ? 'motorcycle'
    : rawId.startsWith('cosmetic-') || rawId.startsWith('cosmetics-')
      ? 'cosmetics'
      : rawId.startsWith('fashion-')
        ? 'fashion'
        : rawId.startsWith('home-need-') || rawId.startsWith('home-needs-')
          ? 'home-needs'
          : 'electronics'

  const productType = explicitType || typeFromId
  const productId = rawId
    .replace(/^motorcycle-/i, '')
    .replace(/^cosmetics?-/i, '')
    .replace(/^fashion-/i, '')
    .replace(/^home-needs?-/i, '')
    .replace(/^tech-/i, '')
    .replace(/^electronics-/i, '')

  return {
    productType,
    productId: productId || rawId,
  }
}

function addProductToCart(product: HomeProductItem) {
  if (product.is_sold_out) return

  addItem(buildCartPayload(product), false)

  router.visit(route('frontend.cart.index'))
}

function buildCartPayload(product: HomeProductItem): AddToCartPayload {
  const identity = productIdentity(product)
  const selectedColor = product.colors?.[0] ?? null
  const price = Number(product.display_price ?? product.regular_price ?? 0)

  return {
    id: identity.productId,
    productType: identity.productType,
    productId: identity.productId,
    variantId: `${identity.productType}-${identity.productId}`,
    quantity: 1,
    colorId: selectedColor?.id ?? null,
    colorName: selectedColor?.name ?? null,
    storageId: null,
    storageLabel: null,
    sku: product.sku ?? null,
    price,
    oldPrice: showRegularPrice(product) ? product.regular_price : null,
    stockCount: 99,
    name: product.name,
    image: product.thumbnail_url || product.hover_image_url || null,
    url: productUrl(product),
  }
}

function addProductToWishlist(product: HomeProductItem) {
  addWishlistItem(buildCartPayload(product))
  router.visit(route('frontend.wishlist.index'))
}

function isWishlisted(product: HomeProductItem) {
  return hasWishlistItem(buildCartPayload(product))
}

function productBrand(product: HomeProductItem) {
  const parts = [product.brand_name, product.category_name]
    .map((item) => String(item || '').trim())
    .filter(Boolean)

  if (parts.length) {
    return parts[0]
  }

  return 'DezeStore'
}

function discountPill(product: HomeProductItem) {
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

function showRegularPrice(product: HomeProductItem) {
  return product.has_discount
    && product.regular_price !== null
    && product.display_price !== null
    && Number(product.regular_price) > Number(product.display_price)
}

function kokoInstallmentPrice(product: HomeProductItem) {
  return resolveKokoInstallmentPrice(product)
}
</script>

<template>
  <article v-if="skeleton || !product" class="product-card product-card--skeleton">
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
  </article>

  <article v-else class="product-card group">
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

        <span v-if="product.is_sold_out" class="sold-out-badge">
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
        <p class="product-brand">
          {{ productBrand(product) }}
        </p>

        <h3 class="product-title">
          {{ product.name }}
        </h3>

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
              <svg viewBox="0 0 24 24" class="product-rating-star-base">
                <path d="M12 2.25l2.917 5.91 6.523.948-4.72 4.6 1.114 6.497L12 17.118 6.166 20.205l1.114-6.497-4.72-4.6 6.523-.948L12 2.25z" />
              </svg>

              <span
                class="product-rating-star-fill"
                :style="starFillStyle(product.reviews_avg_rating || 5, starNumber)"
              >
                <svg viewBox="0 0 24 24" class="product-rating-star-top">
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
          <span v-if="showRegularPrice(product)" class="regular-price">
            {{ formatPrice(product.regular_price) }}
          </span>

          <span v-if="discountPill(product)" class="discount-pill">
            {{ discountPill(product) }}
          </span>
        </div>
      </div>
    </Link>

    <div class="product-card__footer">
      <div class="price-stack">
        <div class="final-price">
          {{ formatPrice(product.display_price) }}
        </div>

        <KokoPayLine
          :amount="kokoInstallmentPrice(product)"
          compact
        />
      </div>

      <button
        type="button"
        class="cart-button"
        :class="product.is_sold_out ? 'cart-button--disabled' : ''"
        :disabled="product.is_sold_out"
        :aria-label="product.is_sold_out ? `${product.name} is sold out` : `Add ${product.name} to cart`"
        @click="addProductToCart(product)"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 5h2l2.2 9.4a1.2 1.2 0 001.17.93h8.72a1.2 1.2 0 001.16-.9L20.5 8H7" />
          <path stroke-linecap="round" d="M9.5 19h.01M17.5 19h.01" />
        </svg>
      </button>
    </div>

    <div v-if="product.colors && product.colors.length" class="color-strip">
      <span
        v-for="color in product.colors.slice(0, 5)"
        :key="color.id"
        class="color-dot"
        :style="colorSwatchStyle(color)"
        :title="color.name"
      />
    </div>
  </article>
</template>

<style scoped>
.product-card {
  --card-blue: #2f6fe4;
  --card-orange: #ff980f;
  --card-ink: #151821;
  --card-muted: #535866;
  --card-soft-blue: #eaf3ff;

  position: relative;
  display: flex;
  width: 100%;
  min-height: 410px;
  overflow: hidden;
  border: 1px solid #e3e8f0;
  border-radius: 16px;
  background: #ffffff;
  box-shadow: none;
  isolation: isolate;
  transition: border-color 0.2s ease;
  flex-direction: column;
}

.product-card:hover {
  border-color: #cfd8e6;
  box-shadow: none;
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
  height: 214px;
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
  letter-spacing: 0;
}

.product-main-image,
.product-hover-image {
  position: absolute;
  top: 22px;
  left: 50%;
  width: calc(100% - 42px);
  height: 172px;
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
  padding: 13px 15px 0;
}

.product-brand {
  display: block;
  min-height: 14px;
  margin: 0 0 5px;
  overflow: hidden;
  color: #7a8294;
  font-size: 11px;
  font-weight: 800;
  line-height: 1.15;
  letter-spacing: 0;
  text-transform: uppercase;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.product-title {
  display: -webkit-box;
  min-height: 42px;
  margin: 0;
  overflow: hidden;
  color: var(--card-ink);
  font-size: 17px;
  font-weight: 700;
  letter-spacing: 0;
  line-height: 1.23;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.rating-row {
  display: flex;
  align-items: center;
  gap: 5px;
  margin-top: 10px;
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
  width: 14px;
  height: 14px;
  flex: 0 0 14px;
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

.price-meta-row:empty {
  display: none;
}

.regular-price {
  color: #7d8493;
  font-size: 13px;
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
  padding: 0 15px 15px;
}

.price-stack {
  display: flex;
  min-width: 0;
  flex-direction: column;
  gap: 5px;
}

.final-price {
  min-width: 0;
  color: var(--card-ink);
  font-size: 19px;
  font-weight: 800;
  letter-spacing: 0;
  line-height: 1;
  word-break: break-word;
}

.cart-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  flex: 0 0 40px;
  border: 0;
  border-radius: 10px;
  background: var(--card-blue);
  color: #ffffff;
  cursor: pointer;
  text-decoration: none;
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease,
    background-color 0.22s ease;
}

.cart-button:hover {
  background: #275fd0;
  box-shadow: none;
}

.cart-button svg {
  width: 20px;
  height: 20px;
}

.cart-button--disabled {
  cursor: not-allowed;
  opacity: 0.55;
  pointer-events: none;
}

.color-strip {
  position: absolute;
  left: 16px;
  bottom: 55px;
  z-index: 8;
  display: flex;
  gap: 5px;
  opacity: 1;
  transform: none;
}

.product-card:hover .color-strip {
  opacity: 1;
  transform: none;
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
  border: 1px solid #e3e8f0;
}

.skeleton-image {
  position: absolute;
  top: 22px;
  left: 50%;
  width: calc(100% - 70px);
  height: 170px;
  border-radius: 999px;
  background: #eef2f7;
  transform: translateX(-50%);
}

@media (max-width: 640px) {
  .product-card {
    min-height: 300px;
    border-radius: 14px;
  }

  .product-card__media {
    height: 146px;
  }

  .product-card__actions {
    top: 8px;
    right: 8px;
  }

  .action-bubble {
    width: 30px;
    height: 30px;
  }

  .action-bubble svg {
    width: 15px;
    height: 15px;
  }

  .sold-out-badge {
    top: 8px;
    left: 8px;
    padding: 4px 7px;
    font-size: 9px;
  }

  .product-main-image,
  .product-hover-image {
    top: 12px;
    width: calc(100% - 18px);
    height: 124px;
  }

  .product-card__body {
    padding: 8px 10px 0;
  }

  .product-brand {
    min-height: 12px;
    margin-bottom: 4px;
    font-size: 9.5px;
    line-height: 1.15;
  }

  .product-title {
    min-height: 34px;
    font-size: 13.25px;
    line-height: 1.26;
  }

  .rating-row {
    gap: 3px;
    min-height: 15px;
    margin-top: 7px;
  }

  .product-rating-star {
    width: 11px;
    height: 11px;
    flex-basis: 11px;
  }

  .comment-icon {
    width: 13px;
    height: 13px;
    margin-left: 2px;
  }

  .comment-icon svg {
    width: 12px;
    height: 12px;
  }

  .review-count {
    font-size: 10px;
  }

  .price-meta-row {
    gap: 5px;
    min-height: 14px;
    margin-top: 4px;
  }

  .regular-price,
  .discount-pill {
    font-size: 10px;
  }

  .discount-pill {
    min-height: 16px;
    padding: 1px 5px;
  }

  .product-card__footer {
    gap: 6px;
    margin-top: 6px;
    padding: 0 10px 8px;
  }

  .price-stack {
    gap: 4px;
  }

  .final-price {
    font-size: 13.5px;
    line-height: 1.12;
  }

  .cart-button {
    width: 29px;
    height: 29px;
    flex-basis: 29px;
    border-radius: 8px;
  }

  .cart-button svg {
    width: 15px;
    height: 15px;
  }

  .color-strip {
    left: 10px;
    bottom: 42px;
    gap: 4px;
  }

  .color-dot {
    width: 10px;
    height: 10px;
  }

  .skeleton-image {
    top: 12px;
    width: calc(100% - 34px);
    height: 124px;
  }
}

@media (max-width: 380px) {
  .product-card {
    min-height: 292px;
  }

  .product-card__media {
    height: 136px;
  }

  .product-main-image,
  .product-hover-image,
  .skeleton-image {
    height: 112px;
  }

  .product-title {
    font-size: 12.5px;
  }

  .final-price {
    font-size: 12.5px;
  }

  .cart-button {
    width: 29px;
    height: 29px;
    flex-basis: 29px;
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
