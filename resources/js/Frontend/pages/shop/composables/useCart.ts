
import { computed, ref } from 'vue'

export type CartItem = {
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

export type AddToCartPayload = {
  id?: number | string | null
  productType?: string | null
  productId: number | string
  variantId?: number | string | null
  name?: string | null
  sku?: string | null
  image?: string | null
  url?: string | null
  quantity?: number
  price?: number
  oldPrice?: number | null
  colorId?: number | string | null
  colorName?: string | null
  storageId?: number | string | null
  storageLabel?: string | null
  sizeId?: number | string | null
  sizeLabel?: string | null
  variantLabel?: string | null
  stockCount?: number | null
}

const STORAGE_KEY = 'dezestore.tech.cart.v1'

const items = ref<CartItem[]>([])
const isCartOpen = ref(false)
const hydrated = ref(false)

let booted = false
let listenersBound = false

function clampQuantity(value: number, stockCount: number) {
  const safeValue = Number.isFinite(value) ? Math.max(1, Math.floor(value)) : 1
  const max = Math.max(1, stockCount || 1)
  return Math.min(safeValue, max)
}

function persist() {
  if (typeof window === 'undefined') return

  try {
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(items.value))
  } catch (error) {
    console.error('Unable to persist cart:', error)
  }
}

function hydrate() {
  if (typeof window === 'undefined' || hydrated.value) return

  try {
    const raw = window.localStorage.getItem(STORAGE_KEY)
    if (raw) {
      const parsed = JSON.parse(raw)
      if (Array.isArray(parsed)) {
        items.value = parsed
          .map((item) => toCartItem(item))
          .filter((item): item is CartItem => item !== null)
      }
    }
  } catch (error) {
    console.error('Unable to hydrate cart:', error)
  } finally {
    hydrated.value = true
  }
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
    .replace(/^home-needs?-/i, '')
    .replace(/^tech-/i, '')
    .replace(/^electronics-/i, '')

  return withoutPrefix || raw
}

function toCartItem(payload: AddToCartPayload): CartItem | null {
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

function addItem(payload: AddToCartPayload, openDrawer = true) {
  const nextItem = toCartItem(payload)
  if (!nextItem) return

  const existingIndex = items.value.findIndex((item) => item.key === nextItem.key)

  if (existingIndex >= 0) {
    const existing = items.value[existingIndex]
    const mergedQty = clampQuantity(existing.quantity + nextItem.quantity, existing.stockCount || nextItem.stockCount)

    items.value.splice(existingIndex, 1, {
      ...existing,
      ...nextItem,
      quantity: mergedQty,
    })
  } else {
    items.value.unshift(nextItem)
  }

  persist()

  if (openDrawer) {
    isCartOpen.value = true
  }
}

function removeItem(key: string) {
  items.value = items.value.filter((item) => item.key !== key)
  persist()
}

function updateQuantity(key: string, quantity: number) {
  items.value = items.value.map((item) => {
    if (item.key !== key) return item

    return {
      ...item,
      quantity: clampQuantity(quantity, item.stockCount),
    }
  })

  persist()
}

function incrementQuantity(key: string) {
  const item = items.value.find((entry) => entry.key === key)
  if (!item) return
  updateQuantity(key, item.quantity + 1)
}

function decrementQuantity(key: string) {
  const item = items.value.find((entry) => entry.key === key)
  if (!item) return
  updateQuantity(key, Math.max(1, item.quantity - 1))
}

function clearCart() {
  items.value = []
  persist()
}

function openCart() {
  isCartOpen.value = true
}

function closeCart() {
  isCartOpen.value = false
}

function bindWindowListeners() {
  if (typeof window === 'undefined' || listenersBound) return

  listenersBound = true

  window.addEventListener('tech-product:add-to-cart', ((event: Event) => {
    const detail = (event as CustomEvent<AddToCartPayload>).detail
    if (!detail) return
    addItem(detail, true)
  }) as EventListener)

  window.addEventListener('tech-cart:open', (() => {
    openCart()
  }) as EventListener)

  window.addEventListener('tech-cart:close', (() => {
    closeCart()
  }) as EventListener)
}

function boot() {
  if (booted) return
  booted = true

  hydrate()
  bindWindowListeners()
}

export function useCart() {
  if (typeof window !== 'undefined') {
    boot()
  }

  const totalItems = computed(() => items.value.reduce((sum, item) => sum + Number(item.quantity || 0), 0))
  const subtotal = computed(() => items.value.reduce((sum, item) => sum + (Number(item.price || 0) * Number(item.quantity || 0)), 0))

  return {
    items,
    isCartOpen,
    totalItems,
    subtotal,
    addItem,
    removeItem,
    updateQuantity,
    incrementQuantity,
    decrementQuantity,
    clearCart,
    openCart,
    closeCart,
  }
}
