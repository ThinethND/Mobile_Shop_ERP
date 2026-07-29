<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'
import AppLayout from '@/Frontend/layouts/AppLayout.vue'
import RelatedProducts from './partials/relatedproducts.vue'
import CosmeticView from './partials/cosmeticview.vue'

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
  short_description?: string | null
  long_description?: string | null
  brand?: { id?: number | string | null; name?: string | null; logo_url?: string | null } | null
  category?: { id?: number | string | null; name?: string | null } | null
  breadcrumb?: BreadcrumbItem[]
  main_image?: string | null
  gallery: GalleryItem[]
  volumes: { id: number | string; label: string }[]
  variants: CosmeticVariant[]
  base_price?: number | null
  old_price?: number | null
  current_price?: number | null
  has_discount?: boolean
  discount_label?: string | null
  default_volume_id?: number | string | null
  stock_count?: number | null
  in_stock?: boolean
}

type ProductCard = {
  id: number | string
  name: string
  slug?: string | null
  brand_name?: string | null
  thumbnail_url: string | null
  hover_image_url: string | null
  regular_price: number | null
  sale_price: number | null
  display_price: number | null
  has_discount: boolean
  discount_label?: string | null
  is_sold_out: boolean
  reviews_count?: number | null
  reviews_avg_rating?: number | null
}

const props = defineProps<{
  productKey: string | number
  shell?: ShellData | null
}>()

const loading = ref(true)
const error = ref<string | null>(null)
const product = ref<ProductPayload | null>(null)
const relatedProducts = ref<ProductCard[]>([])

let abortController: AbortController | null = null

async function fetchProduct() {
  loading.value = true
  error.value = null

  try {
    abortController?.abort()
    abortController = new AbortController()

    const response = await fetch(`/cosmetics/${props.productKey}/data`, {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      signal: abortController.signal,
    })

    if (!response.ok) {
      throw new Error('Failed to load cosmetic product details.')
    }

    const data = await response.json()
    product.value = data?.product ?? null
    relatedProducts.value = data?.related_products ?? []
  } catch (err: unknown) {
    if ((err as Error)?.name === 'AbortError') return
    console.error('Cosmetic product view fetch error:', err)
    error.value = 'Failed to load cosmetic product details. Please try again.'
    product.value = null
    relatedProducts.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchProduct()
})

onBeforeUnmount(() => {
  abortController?.abort()
})
</script>

<template>
  <div class="min-h-screen bg-white">
    <CosmeticView
      :loading="loading"
      :error="error"
      :product="product"
      :shell="shell || null"
      @retry="fetchProduct"
    />

    <RelatedProducts
      v-if="loading || product"
      :products="relatedProducts"
      :loading="loading"
      :load-error="null"
      :category-name="product?.category?.name || null"
      @retry="fetchProduct"
    />
  </div>
</template>
