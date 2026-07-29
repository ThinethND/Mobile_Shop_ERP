import { computed, ref } from 'vue'
import type { AddToCartPayload } from './useCart'

export type WishlistItem = {
  key: string
  id: number | string | null
  productType: string
  productId: number | string
  variantId: number | string
  name: string
  sku: string | null
  image: string | null
  url: string | null
  quantity: number
  price: number
  oldPrice: number | null
  colorId: number | string | null
  colorName: string | null
  storageId: number | string | null
  storageLabel: string | null
  sizeId: number | string | null
  sizeLabel: string | null
  variantLabel: string | null
  stockCount: number
}

const items = ref<WishlistItem[]>([])

function clampQuantity(value: number, stockCount: number) {
  const safeValue = Number.isFinite(value) ? Math.max(1, Math.floor(value)) : 1
  const max = Math.max(1, stockCount || 1)
  return Math.min(safeValue, max)
}

function normalizeProductType(value: unknown, productId: unknown = null): string {
  const explicitType = String(value ?? '').trim().toLowerCase()
  const rawId = String(productId ?? '').trim().toLowerCase()

  if (explicitType) {
    if (['tech', 'electronics', 'electronic'].includes(explicitType)) return 'electronics'
    if (['cosmetic', 'cosmetics'].includes(explicitType)) return 'cosmetics'
    if (['motorcycle', 'motorcycles', 'motorcycle-products'].includes(explicitType)) return 'motorcycle'
    if (['fashion', 'fashion-accessories'].includes(explicitType)) return 'fashion'
    if (['home-need', 'home-needs', 'home_needs'].includes(explicitType)) return 'home-needs'

    return explicitType
  }

  if (rawId.startsWith('motorcycle-')) return 'motorcycle'
  if (rawId.startsWith('cosmetic-') || rawId.startsWith('cosmetics-')) return 'cosmetics'
  if (rawId.startsWith('fashion-')) return 'fashion'
  if (rawId.startsWith('home-need-') || rawId.startsWith('home-needs-')) return 'home-needs'

  return 'electronics'
}

function normalizeProductId(value: unknown): number | string | null {
  if (value === null || typeof value === 'undefined') return null

  const raw = String(value).trim()
  if (!raw) return null

  const withoutPrefix = raw
    .replace(/^motorcycle-/i, '')
    .replace(/^cosmetics?-/i, '')
    .replace(/^fashion-/i, '')
    .replace(/^home-needs?/i, '')
    .replace(/^tech-/i, '')
    .replace(/^electronics-/i, '')

  return withoutPrefix || raw
}

function toWishlistItem(payload: AddToCartPayload): WishlistItem | null {
  const productId = normalizeProductId(payload?.productId ?? payload?.id)

  if (!productId) {
    return null
  }

  const productType = normalizeProductType(payload.productType, payload.productId ?? payload.id)
  const variantId = payload.variantId ?? `${productType}-${productId}`
  const stockCount = Math.max(1, Number(payload.stockCount ?? 1))

  return {
    key: `${productType}-${productId}-${variantId}`,
    id: normalizeProductId(payload.id ?? payload.productId),
    productType,
    productId,
    variantId,
    name: String(payload.name ?? 'Product'),
    sku: payload.sku ?? null,
    image: payload.image ?? null,
    url: payload.url ?? null,
    quantity: clampQuantity(Number(payload.quantity ?? 1), stockCount),
    price: Number(payload.price ?? 0),
    oldPrice: payload.oldPrice !== null && typeof payload.oldPrice !== 'undefined'
      ? Number(payload.oldPrice)
      : null,
    colorId: payload.colorId ?? null,
    colorName: payload.colorName ?? null,
    storageId: payload.storageId ?? null,
    storageLabel: payload.storageLabel ?? null,
    sizeId: payload.sizeId ?? null,
    sizeLabel: payload.sizeLabel ?? null,
    variantLabel: payload.variantLabel ?? null,
    stockCount,
  }
}

function addItem(payload: AddToCartPayload) {
  const nextItem = toWishlistItem(payload)
  if (!nextItem) return null

  const existing = items.value.find((item) => item.key === nextItem.key)
  if (existing) {
    return existing
  }

  items.value.unshift(nextItem)
  return nextItem
}

function removeItem(key: string) {
  items.value = items.value.filter((item) => item.key !== key)
}

function clearWishlist() {
  items.value = []
}

function hasItem(payload: AddToCartPayload) {
  const nextItem = toWishlistItem(payload)
  if (!nextItem) return false

  return items.value.some((item) => item.key === nextItem.key)
}

export function useWishlist() {
  const totalItems = computed(() => items.value.length)

  return {
    items,
    totalItems,
    addItem,
    removeItem,
    clearWishlist,
    hasItem,
  }
}
