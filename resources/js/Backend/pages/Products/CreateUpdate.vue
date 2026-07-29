<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { route } from 'ziggy-js'

import MultiSelect from '@/Backend/components/MultiSelect.vue'
import SelectInputComponent from '@/Backend/components/SelectInputComponent.vue'

type Opt = { id: number; name: string }
type BrandOpt = { id: number; name: string; category_ids?: number[] }
type WarrantyOpt = { id: number; name: string }
type ColorOpt = { id: number; name: string; color_code?: string | null; image_url?: string | null }
type StorageOpt = { id: number; label: string }
type RamOpt = { id: number; label: string }

type ProductPayload = {
  id: number
  category_id: number
  brand_id: number
  sku?: string | null
  model: string
  device_status: 'used' | 'brandnew'
  price_lkr: number

  discount_type?: 'percent' | 'price' | null
  discount_value?: number | null
  in_stock?: boolean
  stock_count?: number | null
  low_stock_alert_quantity?: number | null

  status?: 'active' | 'inactive'
  is_featured?: boolean
  is_best_seller?: boolean
  is_top_rated?: boolean
  is_pre_order?: boolean
  is_deal_of_the_day?: boolean
  is_coming_soon?: boolean

  warranty_option_id?: number | null
  warranty_period?: string | null

  os?: string | null
  storage_option_ids?: number[]
  ram_option_ids?: number[]

  storage_option_id?: number | null
  ram_option_id?: number | null

  display_size?: string | null
  display_type?: string | null
  resolution?: string | null
  rear_camera?: string | null
  front_camera?: string | null
  connectivity?: string | null
  battery_mah?: number | null

  short_description?: string | null
  long_description?: string | null

  color_ids?: number[]

  product_video_url?: string | null

  main_image_url?: string | null
  hover_image_url?: string | null
  gallery_urls?: string[]
  gallery_paths?: string[]
  variants?: ProductVariantPayload[]
}

type ProductVariantPayload = {
  id?: number
  storage_option_id: number
  color_option_id: number
  price_lkr: number | string
  stock_count?: number | string | null
  sku?: string | null
  status?: 'active' | 'inactive'
}

type ExistingGalleryItem = {
  url: string
  path: string | null
}

const page = usePage()

const categories = computed<Opt[]>(() => (page.props.categories as any) || [])
const brandsAll = computed<BrandOpt[]>(() => (page.props.brands as any) || [])
const warrantyOptions = computed<WarrantyOpt[]>(() => (page.props.warranties as any) || [])
const colors = computed<ColorOpt[]>(() => (page.props.colors as any) || [])
const storages = computed<StorageOpt[]>(() => (page.props.storages as any) || [])
const rams = computed<RamOpt[]>(() => (page.props.rams as any) || [])

const props = defineProps<{
  mode: 'create' | 'edit'
  product?: ProductPayload | null
}>()

const storageLabelMap = computed(() => {
  return new Map(storages.value.map(s => [Number(s.id), s.label]))
})

const colorMetaMap = computed(() => {
  return new Map(colors.value.map(c => [Number(c.id), c]))
})

const colorLabelMap = computed(() => {
  return new Map(colors.value.map(c => [Number(c.id), c.name]))
})

function variantKey(storageId: number, colorId: number) {
  return `${storageId}-${colorId}`
}

function storageLabel(id: number) {
  return storageLabelMap.value.get(Number(id)) || `Storage #${id}`
}

function colorLabel(id: number) {
  return colorLabelMap.value.get(Number(id)) || `Color #${id}`
}

function colorSwatchStyle(id: number) {
  const color = colorMetaMap.value.get(Number(id))
  return {
    backgroundColor: color?.color_code || '#d4d4d8',
  }
}

const isEdit = computed(() => props.mode === 'edit' && !!props.product?.id)

const mainPreview = ref<string | null>(props.product?.main_image_url ?? null)
const hoverPreview = ref<string | null>(props.product?.hover_image_url ?? null)
const existingGallery = ref<ExistingGalleryItem[]>(
  (props.product?.gallery_urls ?? []).map((url, idx) => ({
    url,
    path: props.product?.gallery_paths?.[idx] ?? null,
  }))
)
const galleryNewPreview = ref<string[]>([])

let galleryObjectUrls: string[] = []

function revokeGalleryObjectUrls() {
  for (const url of galleryObjectUrls) {
    URL.revokeObjectURL(url)
  }
  galleryObjectUrls = []
}

onBeforeUnmount(() => {
  revokeGalleryObjectUrls()
})

const initialStorageIds =
  props.product?.storage_option_ids?.length
    ? props.product.storage_option_ids
    : props.product?.storage_option_id
      ? [Number(props.product.storage_option_id)]
      : []

const initialRamIds =
  props.product?.ram_option_ids?.length
    ? props.product.ram_option_ids
    : props.product?.ram_option_id
      ? [Number(props.product.ram_option_id)]
      : []

const form = useForm({
  category_id: props.product?.category_id ?? (categories.value[0]?.id ?? null),
  brand_id: props.product?.brand_id ?? null,
  sku: props.product?.sku ?? '',
  model: props.product?.model ?? '',
  device_status: props.product?.device_status ?? 'brandnew',
  price_lkr: props.product?.price_lkr ?? '',

  discount_type: props.product?.discount_type ?? '',
  discount_value: props.product?.discount_value ?? null,
  in_stock: props.product?.in_stock ?? true,
  stock_count: props.product?.stock_count ?? null,
  low_stock_alert_quantity: props.product?.low_stock_alert_quantity ?? null,

  status: props.product?.status ?? 'active',
  is_featured: props.product?.is_featured ?? false,
  is_best_seller: props.product?.is_best_seller ?? false,
  is_top_rated: props.product?.is_top_rated ?? false,
  is_pre_order: props.product?.is_pre_order ?? false,
  is_deal_of_the_day: props.product?.is_deal_of_the_day ?? false,
  is_coming_soon: props.product?.is_coming_soon ?? false,

  warranty_option_id: props.product?.warranty_option_id ?? null,
  warranty_period: props.product?.warranty_period ?? '',

  os: props.product?.os ?? '',
  storage_option_ids: initialStorageIds as number[],
  ram_option_ids: initialRamIds as number[],
  display_size: props.product?.display_size ?? '',
  display_type: props.product?.display_type ?? '',
  resolution: props.product?.resolution ?? '',
  rear_camera: props.product?.rear_camera ?? '',
  front_camera: props.product?.front_camera ?? '',
  connectivity: props.product?.connectivity ?? '',
  battery_mah: props.product?.battery_mah ?? '',

  short_description: props.product?.short_description ?? '',
  long_description: props.product?.long_description ?? '',

  color_ids: props.product?.color_ids ?? [],
  variants: (props.product?.variants || []).map(v => ({
    id: v.id,
    storage_option_id: Number(v.storage_option_id),
    color_option_id: Number(v.color_option_id),
    price_lkr: v.price_lkr ?? props.product?.price_lkr ?? '',
    stock_count: v.stock_count ?? null,
    sku: v.sku ?? '',
    status: v.status ?? 'active',
  })) as ProductVariantPayload[],

  product_video_url: props.product?.product_video_url ?? '',

  main_image: null as File | null,
  hover_image: null as File | null,
  gallery_images: [] as File[],
  gallery_remove_paths: [] as string[],
  clear_gallery: false,
})

const brandsFiltered = computed(() => {
  if (!form.category_id) return brandsAll.value
  return brandsAll.value.filter(b => (b.category_ids || []).includes(Number(form.category_id)))
})

const skuManuallyEdited = ref(!!props.product?.sku)

function normalizeSku(value: string | number | null | undefined) {
  return String(value ?? '')
    .trim()
    .toUpperCase()
    .replace(/[^A-Z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .replace(/-{2,}/g, '-')
}

function generateSku() {
  const brandName = brandsAll.value.find(b => Number(b.id) === Number(form.brand_id))?.name || 'PRODUCT'
  const categoryName = categories.value.find(c => Number(c.id) === Number(form.category_id))?.name || ''
  const modelName = String(form.model || '').trim()

  const left = normalizeSku(`${brandName}-${modelName || categoryName || 'ITEM'}`)
  const uniquePart = new Date().toISOString().replace(/\D/g, '').slice(2, 14)

  form.sku = `${left || 'PRODUCT-ITEM'}-${uniquePart}`
}

function onSkuInput() {
  skuManuallyEdited.value = true
  form.sku = normalizeSku(form.sku)
}

watch(
  () => [form.category_id, form.brand_id, form.model],
  () => {
    const b = brandsAll.value.find(x => x.id === Number(form.brand_id))
    if (form.brand_id && b && !(b.category_ids || []).includes(Number(form.category_id))) {
      form.brand_id = null
    }

    if (!isEdit.value && !skuManuallyEdited.value) {
      generateSku()
    }
  },
  { immediate: true }
)

watch(
  () => form.in_stock,
  (val) => {
    if (!val) form.stock_count = null
  }
)

watch(
  () => form.discount_type,
  (val) => {
    if (!val) {
      form.discount_value = null
    }
  }
)

function syncVariantRows() {
  const selectedStorageIds = (form.storage_option_ids || []).map(Number).filter(Boolean)
  const selectedColorIds = (form.color_ids || []).map(Number).filter(Boolean)

  if (!selectedStorageIds.length || !selectedColorIds.length) {
    form.variants = []
    return
  }

  const existingMap = new Map(
    (form.variants || []).map((v: any) => [
      variantKey(Number(v.storage_option_id), Number(v.color_option_id)),
      v,
    ])
  )

  const nextRows: ProductVariantPayload[] = []

  for (const storageId of selectedStorageIds) {
    for (const colorId of selectedColorIds) {
      const key = variantKey(storageId, colorId)
      const existing = existingMap.get(key)

      nextRows.push({
        id: existing?.id,
        storage_option_id: storageId,
        color_option_id: colorId,
        price_lkr: existing?.price_lkr ?? form.price_lkr ?? '',
        stock_count: existing?.stock_count ?? null,
        sku: existing?.sku ?? '',
        status: existing?.status ?? 'active',
      })
    }
  }

  form.variants = nextRows
}

watch(
  () => [
    [...(form.storage_option_ids || [])].map(Number).sort((a, b) => a - b).join(','),
    [...(form.color_ids || [])].map(Number).sort((a, b) => a - b).join(','),
  ],
  syncVariantRows,
  { immediate: true }
)

function onMainImageChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0] || null
  form.main_image = file
  if (file) mainPreview.value = URL.createObjectURL(file)
}

function onHoverImageChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0] || null
  form.hover_image = file
  if (file) hoverPreview.value = URL.createObjectURL(file)
}

function galleryFileKey(file: File) {
  return `${file.name}__${file.size}__${file.lastModified}`
}

function onGalleryChange(e: Event) {
  const input = e.target as HTMLInputElement
  const files = input.files ? Array.from(input.files) : []
  if (!files.length) return

  const existingFiles = form.gallery_images || []
  const existingKeys = new Set(existingFiles.map(galleryFileKey))

  const uniqueFiles = files.filter((file) => !existingKeys.has(galleryFileKey(file)))
  if (!uniqueFiles.length) {
    input.value = ''
    return
  }

  form.gallery_images = [...existingFiles, ...uniqueFiles]

  const newUrls = uniqueFiles.map((file) => URL.createObjectURL(file))
  galleryObjectUrls = [...galleryObjectUrls, ...newUrls]
  galleryNewPreview.value = [...galleryObjectUrls]
  input.value = ''
}

function removeExistingGalleryImage(index: number) {
  const item = existingGallery.value[index]
  if (!item) return

  existingGallery.value = existingGallery.value.filter((_, i) => i !== index)

  if (item.path && !form.gallery_remove_paths.includes(item.path)) {
    form.gallery_remove_paths.push(item.path)
  }
}

function removeNewGalleryImage(index: number) {
  const url = galleryNewPreview.value[index]
  if (url) {
    URL.revokeObjectURL(url)
  }

  form.gallery_images = (form.gallery_images || []).filter((_, i) => i !== index)
  galleryObjectUrls = galleryNewPreview.value.filter((_, i) => i !== index)
  galleryNewPreview.value = [...galleryObjectUrls]
}

function submit() {
  form.clearErrors()

  const payloadTransform = (data: any) => ({
    ...data,
    sku: normalizeSku(data.sku),
    discount_type: data.discount_type ? data.discount_type : null,
    discount_value: data.discount_type
      ? (data.discount_value === '' || data.discount_value === null || typeof data.discount_value === 'undefined'
          ? null
          : Number(data.discount_value))
      : null,
    color_ids: (data.color_ids || []).map((id: any) => Number(id)),
    storage_option_ids: (data.storage_option_ids || []).map((id: any) => Number(id)),
    ram_option_ids: (data.ram_option_ids || []).map((id: any) => Number(id)),
    variants: JSON.stringify(
      (data.variants || []).map((v: any) => ({
        storage_option_id: Number(v.storage_option_id),
        color_option_id: Number(v.color_option_id),
        price_lkr: Number(v.price_lkr ?? 0),
        stock_count:
          v.stock_count === '' || v.stock_count === null || typeof v.stock_count === 'undefined'
            ? null
            : Number(v.stock_count),
        sku: v.sku ? String(v.sku).trim() : null,
        status: v.status || 'active',
      }))
    ),
  })

  if (!isEdit.value) {
    form
      .transform(payloadTransform)
      .post(route('products.store'), {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => form.transform((d: any) => d),
      })

    return
  }

  form
    .transform((data: any) => ({
      ...payloadTransform(data),
      _method: 'PUT',
    }))
    .post(route('products.update', props.product!.id), {
      forceFormData: true,
      preserveScroll: true,
      onFinish: () => form.transform((d: any) => d),
    })
}

const deviceStatusOptions = [
  { id: 'brandnew', name: 'Brand New' },
  { id: 'used', name: 'Used' },
]

const inStockOptions = [
  { id: true, name: 'Yes' },
  { id: false, name: 'No' },
]

const statusOptions = [
  { id: 'active', name: 'Active' },
  { id: 'inactive', name: 'Inactive' },
]
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Electronics Product' : 'Create Electronics Product'" />

    <div class="p-6 space-y-4">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Electronics Product' : 'Create Electronics Product' }}</h1>
          <p class="text-sm text-neutral-500">Use this for phones, smart watches, AirPods, analog watches, and electronics accessories.</p>
        </div>

        <Link
          :href="route('products.index')"
          class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-100 transition"
        >
          Back
        </Link>
      </div>

      <form @submit.prevent="submit" class="rounded-2xl border border-neutral-200 bg-white p-4 sm:p-6 shadow-sm space-y-6">

        <div>
          <h2 class="text-base font-semibold text-neutral-900 mb-3">Required</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
              <SelectInputComponent
                id="category_id"
                label="Category"
                :options="categories"
                v-model="form.category_id"
                :error="form.errors.category_id"
                :isRequired="true"
                valueKey="id"
                labelKey="name"
                placeholder="Select category"
              />
            </div>

            <div>
              <SelectInputComponent
                id="brand_id"
                label="Brand (filtered by category)"
                :options="brandsFiltered"
                v-model="form.brand_id"
                :error="form.errors.brand_id"
                :isRequired="true"
                valueKey="id"
                labelKey="name"
                placeholder="Select brand"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Product Name / Model</label>
              <input
                v-model="form.model"
                type="text"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                placeholder="e.g. iPhone 13 Pro, Apple Watch, AirPods Pro"
              />
              <p v-if="form.errors.model" class="mt-1 text-sm text-red-600">{{ form.errors.model }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">SKU</label>
              <div class="flex gap-2">
                <input
                  :value="form.sku"
                  @input="onSkuInput"
                  type="text"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                  placeholder="Auto generated SKU"
                />
                <button
                  type="button"
                  @click="generateSku"
                  class="shrink-0 rounded-xl border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-100"
                >
                  Generate
                </button>
              </div>
              <p class="mt-1 text-xs text-neutral-500">A unique SKU will also be enforced by the backend.</p>
              <p v-if="form.errors.sku" class="mt-1 text-sm text-red-600">{{ form.errors.sku }}</p>
            </div>

            <div>
              <SelectInputComponent
                id="device_status"
                label="Product Condition"
                :options="deviceStatusOptions"
                v-model="form.device_status"
                :error="form.errors.device_status"
                :isRequired="true"
                valueKey="id"
                labelKey="name"
                placeholder="Select status"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Price (LKR)</label>
              <input
                v-model="form.price_lkr"
                type="number"
                step="0.01"
                min="0"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                placeholder="e.g. 250000"
              />
              <p v-if="form.errors.price_lkr" class="mt-1 text-sm text-red-600">{{ form.errors.price_lkr }}</p>
            </div>

          </div>
        </div>

        <div>
          <h2 class="text-base font-semibold text-neutral-900 mb-3">Stock and Status</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Discount Type</label>
              <select
                v-model="form.discount_type"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              >
                <option value="">None</option>
                <option value="percent">%</option>
                <option value="price">Price</option>
              </select>
              <p v-if="form.errors.discount_type" class="mt-1 text-sm text-red-600">{{ form.errors.discount_type }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Discount Value</label>
              <input
                v-model="form.discount_value"
                type="number"
                step="0.01"
                min="0"
                :disabled="!form.discount_type"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8] disabled:bg-neutral-100"
                placeholder="e.g. 10 or 5000"
              />
              <p v-if="form.errors.discount_value" class="mt-1 text-sm text-red-600">{{ form.errors.discount_value }}</p>
            </div>

            <div>
              <SelectInputComponent
                id="in_stock"
                label="In Stock"
                :options="inStockOptions"
                v-model="form.in_stock"
                :error="form.errors.in_stock"
                :isRequired="false"
                valueKey="id"
                labelKey="name"
                placeholder="Select"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Stock Count</label>
              <input
                v-model="form.stock_count"
                type="number"
                step="1"
                min="0"
                :disabled="!form.in_stock"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8] disabled:bg-neutral-100"
                placeholder="e.g. 10"
              />
              <p v-if="form.errors.stock_count" class="mt-1 text-sm text-red-600">{{ form.errors.stock_count }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Low Stock Alert Quantity</label>
              <input
                v-model="form.low_stock_alert_quantity"
                type="number"
                step="1"
                min="0"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                placeholder="e.g. 5"
              />
              <p v-if="form.errors.low_stock_alert_quantity" class="mt-1 text-sm text-red-600">{{ form.errors.low_stock_alert_quantity }}</p>
            </div>

            <div>
              <SelectInputComponent
                id="status"
                label="Status"
                :options="statusOptions"
                v-model="form.status"
                :error="form.errors.status"
                :isRequired="false"
                valueKey="id"
                labelKey="name"
                placeholder="Select status"
              />
            </div>
          </div>
        </div>

        <div>
          <h2 class="text-base font-semibold text-neutral-900 mb-3">Product Labels / Marketing Flags</h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <label class="flex items-center gap-3 rounded-xl border border-neutral-200 px-4 py-3">
              <input type="checkbox" v-model="form.is_featured" class="h-4 w-4" />
              <span class="text-sm font-medium text-neutral-700">Featured</span>
            </label>

            <label class="flex items-center gap-3 rounded-xl border border-neutral-200 px-4 py-3">
              <input type="checkbox" v-model="form.is_best_seller" class="h-4 w-4" />
              <span class="text-sm font-medium text-neutral-700">Best Seller</span>
            </label>

            <label class="flex items-center gap-3 rounded-xl border border-neutral-200 px-4 py-3">
              <input type="checkbox" v-model="form.is_top_rated" class="h-4 w-4" />
              <span class="text-sm font-medium text-neutral-700">Top Rated</span>
            </label>

            <label class="flex items-center gap-3 rounded-xl border border-neutral-200 px-4 py-3">
              <input type="checkbox" v-model="form.is_pre_order" class="h-4 w-4" />
              <span class="text-sm font-medium text-neutral-700">Pre Order</span>
            </label>

            <label class="flex items-center gap-3 rounded-xl border border-neutral-200 px-4 py-3">
              <input type="checkbox" v-model="form.is_deal_of_the_day" class="h-4 w-4" />
              <span class="text-sm font-medium text-neutral-700">Today Best Deals</span>
            </label>

            <label class="flex items-center gap-3 rounded-xl border border-neutral-200 px-4 py-3">
              <input type="checkbox" v-model="form.is_coming_soon" class="h-4 w-4" />
              <span class="text-sm font-medium text-neutral-700">Coming Soon</span>
            </label>
          </div>
        </div>

        <div>
          <h2 class="text-base font-semibold text-neutral-900 mb-3">Warranty</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <SelectInputComponent
                id="warranty_option_id"
                label="Warranty Type"
                :options="[{ id: null, name: 'None' }, ...warrantyOptions]"
                v-model="form.warranty_option_id"
                :error="form.errors.warranty_option_id"
                :isRequired="false"
                valueKey="id"
                labelKey="name"
                placeholder="None"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Warranty Period</label>
              <input
                v-model="form.warranty_period"
                type="text"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                placeholder="e.g. 12 months / 1 year"
              />
              <p v-if="form.errors.warranty_period" class="mt-1 text-sm text-red-600">{{ form.errors.warranty_period }}</p>
            </div>
          </div>
        </div>

        <div>
          <h2 class="text-base font-semibold text-neutral-900 mb-3">Optional Specifications</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">OS</label>
              <input v-model="form.os" type="text" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Connectivity</label>
              <input v-model="form.connectivity" type="text" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" placeholder="e.g. 5G, WiFi 6" />
            </div>

            <div>
              <MultiSelect
                id="storage_option_ids"
                label="Storage (multi)"
                placeholder="Pick storage options"
                :options="storages"
                v-model="form.storage_option_ids"
                valueKey="id"
                labelKey="label"
                :required="false"
                :error="form.errors.storage_option_ids"
              />
              <p v-if="form.errors['storage_option_ids.0']" class="mt-1 text-sm text-red-600">{{ form.errors['storage_option_ids.0'] }}</p>
            </div>

            <div>
              <MultiSelect
                id="ram_option_ids"
                label="RAM (multi)"
                placeholder="Pick ram options"
                :options="rams"
                v-model="form.ram_option_ids"
                valueKey="id"
                labelKey="label"
                :required="false"
                :error="form.errors.ram_option_ids"
              />
              <p v-if="form.errors['ram_option_ids.0']" class="mt-1 text-sm text-red-600">{{ form.errors['ram_option_ids.0'] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Display Size</label>
              <input v-model="form.display_size" type="text" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" placeholder="e.g. 6.1 inch" />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Display Type</label>
              <input v-model="form.display_type" type="text" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" placeholder="e.g. OLED" />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Resolution</label>
              <input v-model="form.resolution" type="text" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" placeholder="e.g. 1170x2532" />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Battery (mAh)</label>
              <input v-model="form.battery_mah" type="number" min="0" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" placeholder="e.g. 5000" />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Rear Camera</label>
              <input v-model="form.rear_camera" type="text" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" placeholder="e.g. 48MP + 12MP" />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Front Camera</label>
              <input v-model="form.front_camera" type="text" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" placeholder="e.g. 12MP" />
            </div>

            <div class="md:col-span-2">
              <MultiSelect
                id="color_ids"
                label="Colors (multi select)"
                placeholder="Pick colors"
                :options="colors"
                v-model="form.color_ids"
                valueKey="id"
                labelKey="name"
                imageKey="image_url"
                :required="false"
                :error="form.errors.color_ids"
              />
            </div>

            <div class="md:col-span-2 rounded-2xl border border-neutral-200 p-4">
              <div class="mb-3">
                <h3 class="text-sm font-semibold text-neutral-900">Variant Prices</h3>
                <p class="text-sm text-neutral-500">
                  Select at least one storage and one color. Then set a different price for each combination.
                </p>
              </div>

              <div v-if="!form.storage_option_ids.length || !form.color_ids.length" class="rounded-xl bg-neutral-50 px-4 py-3 text-sm text-neutral-500">
                Choose at least 1 storage and 1 color to manage variant prices.
              </div>

              <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-sm">
                  <thead class="bg-neutral-50">
                    <tr>
                      <th class="px-3 py-2 text-left font-medium text-neutral-600">Storage</th>
                      <th class="px-3 py-2 text-left font-medium text-neutral-600">Color</th>
                      <th class="px-3 py-2 text-left font-medium text-neutral-600">Price (LKR)</th>
                      <th class="px-3 py-2 text-left font-medium text-neutral-600">Stock</th>
                      <th class="px-3 py-2 text-left font-medium text-neutral-600">SKU</th>
                      <th class="px-3 py-2 text-left font-medium text-neutral-600">Status</th>
                    </tr>
                  </thead>

                  <tbody class="divide-y divide-neutral-100">
                    <tr
                      v-for="(variant, index) in form.variants"
                      :key="`${variant.storage_option_id}-${variant.color_option_id}`"
                    >
                      <td class="px-3 py-3 text-neutral-800">
                        {{ storageLabel(variant.storage_option_id) }}
                      </td>

                      <td class="px-3 py-3 text-neutral-800">
                        <div class="flex items-center gap-2">
                          <span
                            class="h-5 w-5 rounded-full border border-neutral-300 shrink-0"
                            :style="colorSwatchStyle(variant.color_option_id)"
                          />
                          <span>{{ colorLabel(variant.color_option_id) }}</span>
                        </div>
                      </td>

                      <td class="px-3 py-3">
                        <input
                          v-model="variant.price_lkr"
                          type="number"
                          min="0"
                          step="0.01"
                          class="w-full rounded-xl border border-neutral-200 px-3 py-2 outline-none focus:border-[#38bdf8]"
                          placeholder="Variant price"
                        />
                        <p v-if="form.errors[`variants.${index}.price_lkr`]" class="mt-1 text-xs text-red-600">
                          {{ form.errors[`variants.${index}.price_lkr`] }}
                        </p>
                      </td>

                      <td class="px-3 py-3">
                        <input
                          v-model="variant.stock_count"
                          type="number"
                          min="0"
                          step="1"
                          class="w-full rounded-xl border border-neutral-200 px-3 py-2 outline-none focus:border-[#38bdf8]"
                          placeholder="Stock"
                        />
                      </td>

                      <td class="px-3 py-3">
                        <input
                          v-model="variant.sku"
                          type="text"
                          class="w-full rounded-xl border border-neutral-200 px-3 py-2 outline-none focus:border-[#38bdf8]"
                          placeholder="Optional variant SKU"
                        />
                      </td>

                      <td class="px-3 py-3">
                        <select
                          v-model="variant.status"
                          class="w-full rounded-xl border border-neutral-200 px-3 py-2 outline-none focus:border-[#38bdf8]"
                        >
                          <option value="active">Active</option>
                          <option value="inactive">Inactive</option>
                        </select>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <p v-if="form.errors.variants" class="mt-2 text-sm text-red-600">
                {{ form.errors.variants }}
              </p>
            </div>
          </div>
        </div>

        <div>
          <h2 class="text-base font-semibold text-neutral-900 mb-3">Descriptions</h2>
          <div class="grid grid-cols-1 gap-4">
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Short Description</label>
              <textarea
                v-model="form.short_description"
                rows="3"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Long Description</label>
              <textarea
                v-model="form.long_description"
                rows="6"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              ></textarea>
            </div>
          </div>
        </div>

        <div>
          <h2 class="text-base font-semibold text-neutral-900 mb-3">Media</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Main Image</label>
              <div class="flex items-center gap-4">
                <div class="h-20 w-20 rounded-xl border border-neutral-200 overflow-hidden bg-neutral-50 flex items-center justify-center">
                  <img v-if="mainPreview" :src="mainPreview" class="h-full w-full object-cover" />
                  <span v-else class="text-xs text-neutral-400">No Image</span>
                </div>

                <div class="flex-1">
                  <input
                    type="file"
                    accept="image/*"
                    @change="onMainImageChange"
                    class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200"
                  />
                  <p v-if="form.errors.main_image" class="mt-1 text-sm text-red-600">{{ form.errors.main_image }}</p>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Hover Image</label>
              <div class="flex items-center gap-4">
                <div class="h-20 w-20 rounded-xl border border-neutral-200 overflow-hidden bg-neutral-50 flex items-center justify-center">
                  <img v-if="hoverPreview" :src="hoverPreview" class="h-full w-full object-cover" />
                  <span v-else class="text-xs text-neutral-400">No Image</span>
                </div>

                <div class="flex-1">
                  <input
                    type="file"
                    accept="image/*"
                    @change="onHoverImageChange"
                    class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200"
                  />
                  <p v-if="form.errors.hover_image" class="mt-1 text-sm text-red-600">{{ form.errors.hover_image }}</p>
                </div>
              </div>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-neutral-700 mb-1">Product Video URL</label>
              <input
                v-model="form.product_video_url"
                type="url"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                placeholder="https://www.youtube.com/watch?v=..."
              />
              <p v-if="form.errors.product_video_url" class="mt-1 text-sm text-red-600">{{ form.errors.product_video_url }}</p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-neutral-700 mb-1">Gallery Images</label>
              <input
                type="file"
                accept="image/*"
                multiple
                @change="onGalleryChange"
                class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200"
              />
              <p v-if="form.errors.gallery_images" class="mt-1 text-sm text-red-600">{{ form.errors.gallery_images }}</p>

              <div v-if="isEdit" class="mt-2 flex items-center gap-2">
                <input id="clearGallery" type="checkbox" v-model="form.clear_gallery" class="h-4 w-4" />
                <label for="clearGallery" class="text-sm text-neutral-700">Update all gallery images (replace existing)</label>
              </div>
            </div>

            <div class="md:col-span-2 space-y-2">
              <div v-if="existingGallery.length" class="text-sm font-medium text-neutral-700">Existing Gallery</div>
              <div class="flex flex-wrap gap-2">
                <div
                  v-for="(item, i) in existingGallery"
                  :key="item.path || item.url || `old-${i}`"
                  class="relative h-16 w-16 overflow-hidden rounded-lg border border-neutral-200 bg-neutral-50"
                >
                  <img :src="item.url" class="h-full w-full object-cover" />
                  <button
                    v-if="item.path && !form.clear_gallery"
                    type="button"
                    class="absolute right-1 top-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/90 text-neutral-700 shadow hover:bg-white hover:text-red-600"
                    @click="removeExistingGalleryImage(i)"
                    aria-label="Remove image"
                    title="Remove"
                  >
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>

              <div v-if="galleryNewPreview.length" class="text-sm font-medium text-neutral-700 mt-2">New Gallery (to upload)</div>
              <div class="flex flex-wrap gap-2">
                <div
                  v-for="(u, i) in galleryNewPreview"
                  :key="'new-' + i"
                  class="relative h-16 w-16 overflow-hidden rounded-lg border border-neutral-200 bg-neutral-50"
                >
                  <img :src="u" class="h-full w-full object-cover" />
                  <button
                    type="button"
                    class="absolute right-1 top-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/90 text-neutral-700 shadow hover:bg-white hover:text-red-600"
                    @click="removeNewGalleryImage(i)"
                    aria-label="Remove image"
                    title="Remove"
                  >
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-2 sm:justify-end pt-2">
          <Link
            :href="route('products.index')"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-neutral-200 px-5 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-100 transition"
          >
            Cancel
          </Link>

          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-[#38bdf8] px-6 py-2 text-sm font-medium text-white hover:bg-[#7dd3fc] disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : (isEdit ? 'Update' : 'Create') }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
