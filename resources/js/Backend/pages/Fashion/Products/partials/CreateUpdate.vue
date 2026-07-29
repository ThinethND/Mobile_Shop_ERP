<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { route } from 'ziggy-js'

type Option = { id: number; name: string; value: number; label: string; category_id?: number | null }
type ProductPayload = {
  id: number
  name: string
  category_id?: number | null
  product_type_id?: number | null
  brand_id?: number | null
  brand_name?: string | null
  sku?: string | null
  target_gender?: string | null
  size_label?: string | null
  color?: string | null
  material?: string | null
  style?: string | null
  fit?: string | null
  lens_type?: string | null
  frame_material?: string | null
  bag_size?: string | null
  closure_type?: string | null
  strap_type?: string | null
  dimensions?: string | null
  care_instructions?: string | null
  warranty_option_id?: number | null
  warranty_period?: string | null
  price?: number | string
  sale_price?: number | string | null
  stock_quantity?: number | string
  low_stock_alert_quantity?: number | string | null
  stock_status?: 'in_stock' | 'out_of_stock' | 'pre_order' | 'discontinued'
  status?: 'active' | 'inactive'
  featured?: boolean
  today_best_deals?: boolean
  best_seller?: boolean
  short_description?: string | null
  full_description?: string | null
  product_video_url?: string | null
  main_image_url?: string | null
  hover_image_url?: string | null
  gallery_urls?: string[]
  gallery_paths?: string[]
}

type ExistingGalleryItem = {
  url: string
  path: string | null
}

const props = defineProps<{
  mode: 'create' | 'edit'
  product?: ProductPayload | null
  categories: Option[]
  brands: Option[]
  productTypes: Option[]
  warranties: Option[]
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.product?.id)
const mainPreview = ref<string | null>(props.product?.main_image_url ?? null)
const hoverPreview = ref<string | null>(props.product?.hover_image_url ?? null)
const existingGallery = ref<ExistingGalleryItem[]>(
  (props.product?.gallery_urls ?? []).map((url, index) => ({
    url,
    path: props.product?.gallery_paths?.[index] ?? null,
  }))
)
const newGalleryPreview = ref<string[]>([])

let mainObjectUrl: string | null = null
let hoverObjectUrl: string | null = null
let galleryObjectUrls: string[] = []

const form = useForm({
  name: props.product?.name ?? '',
  category_id: props.product?.category_id ?? '',
  product_type_id: props.product?.product_type_id ?? '',
  brand_id: props.product?.brand_id ?? '',
  brand_name: props.product?.brand_name ?? '',
  sku: props.product?.sku ?? '',
  target_gender: props.product?.target_gender ?? '',
  size_label: props.product?.size_label ?? '',
  color: props.product?.color ?? '',
  material: props.product?.material ?? '',
  style: props.product?.style ?? '',
  fit: props.product?.fit ?? '',
  lens_type: props.product?.lens_type ?? '',
  frame_material: props.product?.frame_material ?? '',
  bag_size: props.product?.bag_size ?? '',
  closure_type: props.product?.closure_type ?? '',
  strap_type: props.product?.strap_type ?? '',
  dimensions: props.product?.dimensions ?? '',
  care_instructions: props.product?.care_instructions ?? '',
  warranty_option_id: props.product?.warranty_option_id ?? '',
  warranty_period: props.product?.warranty_period ?? '',
  price: props.product?.price ?? '',
  sale_price: props.product?.sale_price ?? '',
  stock_quantity: props.product?.stock_quantity ?? 0,
  low_stock_alert_quantity: props.product?.low_stock_alert_quantity ?? '',
  stock_status: props.product?.stock_status ?? 'in_stock',
  status: props.product?.status ?? 'active',
  featured: props.product?.featured ?? false,
  today_best_deals: props.product?.today_best_deals ?? false,
  best_seller: props.product?.best_seller ?? false,
  short_description: props.product?.short_description ?? '',
  full_description: props.product?.full_description ?? '',
  product_video_url: props.product?.product_video_url ?? '',
  main_image: null as File | null,
  hover_image: null as File | null,
  gallery_images: [] as File[],
  gallery_remove_paths: [] as string[],
  clear_gallery: false,
})

const pageTitle = computed(() => (isEdit.value ? 'Update Fashion Product' : 'Create Fashion Product'))
const filteredProductTypes = computed(() => {
  const categoryId = form.category_id ? Number(form.category_id) : null
  if (!categoryId) return props.productTypes || []

  return (props.productTypes || []).filter((type) => !type.category_id || Number(type.category_id) === categoryId)
})

watch(() => form.category_id, () => {
  const selectedType = form.product_type_id ? Number(form.product_type_id) : null
  if (selectedType && !filteredProductTypes.value.some((type) => type.id === selectedType)) {
    form.product_type_id = ''
  }
})

function revokeMainObjectUrl() {
  if (mainObjectUrl) {
    URL.revokeObjectURL(mainObjectUrl)
    mainObjectUrl = null
  }
}

function revokeHoverObjectUrl() {
  if (hoverObjectUrl) {
    URL.revokeObjectURL(hoverObjectUrl)
    hoverObjectUrl = null
  }
}

function revokeGalleryObjectUrls() {
  galleryObjectUrls.forEach((url) => URL.revokeObjectURL(url))
  galleryObjectUrls = []
}

onBeforeUnmount(() => {
  revokeMainObjectUrl()
  revokeHoverObjectUrl()
  revokeGalleryObjectUrls()
})

function onMainImageChange(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0] || null

  form.main_image = file
  revokeMainObjectUrl()

  if (file) {
    mainObjectUrl = URL.createObjectURL(file)
    mainPreview.value = mainObjectUrl
    return
  }

  mainPreview.value = props.product?.main_image_url ?? null
}

function onHoverImageChange(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0] || null

  form.hover_image = file
  revokeHoverObjectUrl()

  if (file) {
    hoverObjectUrl = URL.createObjectURL(file)
    hoverPreview.value = hoverObjectUrl
    return
  }

  hoverPreview.value = props.product?.hover_image_url ?? null
}

function galleryFileKey(file: File) {
  return `${file.name}__${file.size}__${file.lastModified}`
}

function onGalleryChange(event: Event) {
  const input = event.target as HTMLInputElement
  const files = input.files ? Array.from(input.files) : []
  if (!files.length) return

  const existingKeys = new Set((form.gallery_images || []).map(galleryFileKey))
  const uniqueFiles = files.filter((file) => !existingKeys.has(galleryFileKey(file)))
  if (!uniqueFiles.length) {
    input.value = ''
    return
  }

  form.gallery_images = [...(form.gallery_images || []), ...uniqueFiles]
  const urls = uniqueFiles.map((file) => URL.createObjectURL(file))
  galleryObjectUrls = [...galleryObjectUrls, ...urls]
  newGalleryPreview.value = [...galleryObjectUrls]
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
  const url = newGalleryPreview.value[index]
  if (url) URL.revokeObjectURL(url)

  form.gallery_images = (form.gallery_images || []).filter((_, i) => i !== index)
  galleryObjectUrls = newGalleryPreview.value.filter((_, i) => i !== index)
  newGalleryPreview.value = [...galleryObjectUrls]
}

function submit() {
  form.clearErrors()

  const payloadTransform = (data: any) => ({
    ...data,
    category_id: data.category_id ? Number(data.category_id) : null,
    product_type_id: data.product_type_id ? Number(data.product_type_id) : null,
    brand_id: data.brand_id ? Number(data.brand_id) : null,
    warranty_option_id: data.warranty_option_id ? Number(data.warranty_option_id) : null,
    sale_price: data.sale_price === '' || data.sale_price === null || typeof data.sale_price === 'undefined'
      ? null
      : Number(data.sale_price),
    low_stock_alert_quantity:
      data.low_stock_alert_quantity === '' || data.low_stock_alert_quantity === null || typeof data.low_stock_alert_quantity === 'undefined'
        ? null
        : Number(data.low_stock_alert_quantity),
  })

  if (!isEdit.value) {
    form.transform(payloadTransform).post(route('admin.fashion.products.store'), {
      forceFormData: true,
      preserveScroll: true,
      onFinish: () => form.transform((data: any) => data),
    })
    return
  }

  form
    .transform((data: any) => ({
      ...payloadTransform(data),
      _method: 'PUT',
    }))
    .post(route('admin.fashion.products.update', props.product!.id), {
      forceFormData: true,
      preserveScroll: true,
      onFinish: () => form.transform((data: any) => data),
    })
}
</script>

<template>
  <AppLayout>
    <Head :title="pageTitle" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ pageTitle }}</h1>
          <p class="text-sm text-neutral-500">A flexible form for necklaces, bags, fashion wear, sunglasses, and accessories.</p>
        </div>

        <Link :href="route('admin.fashion.products.index')" class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto">
          Back
        </Link>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-base font-semibold text-neutral-900">Basic Details</h2>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-neutral-700">Product Name <span class="text-red-600">*</span></label>
              <input v-model="form.name" type="text" placeholder="e.g. Gold Plated Necklace, Crossbody Bag, Linen Shirt, UV Sunglasses" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Category <span class="text-red-600">*</span></label>
              <select v-model="form.category_id" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="">Select category</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
              </select>
              <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Product Type <span class="text-red-600">*</span></label>
              <select v-model="form.product_type_id" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="">Select type</option>
                <option v-for="type in filteredProductTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
              </select>
              <p v-if="form.errors.product_type_id" class="mt-1 text-sm text-red-600">{{ form.errors.product_type_id }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Brand</label>
              <select v-model="form.brand_id" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="">Select brand</option>
                <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
              </select>
              <p v-if="form.errors.brand_id" class="mt-1 text-sm text-red-600">{{ form.errors.brand_id }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Supplier / Custom Brand</label>
              <input v-model="form.brand_name" type="text" placeholder="Optional, if not in brand list" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.brand_name" class="mt-1 text-sm text-red-600">{{ form.errors.brand_name }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">SKU</label>
              <input v-model="form.sku" type="text" placeholder="Optional SKU" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.sku" class="mt-1 text-sm text-red-600">{{ form.errors.sku }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Audience</label>
              <select v-model="form.target_gender" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="">Not specified</option>
                <option value="women">Women</option>
                <option value="men">Men</option>
                <option value="unisex">Unisex</option>
                <option value="kids">Kids</option>
              </select>
              <p v-if="form.errors.target_gender" class="mt-1 text-sm text-red-600">{{ form.errors.target_gender }}</p>
            </div>
          </div>
        </section>

        <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-base font-semibold text-neutral-900">Pricing & Stock</h2>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Regular Price <span class="text-red-600">*</span></label>
              <input v-model="form.price" type="number" min="0" step="0.01" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Sale Price</label>
              <input v-model="form.sale_price" type="number" min="0" step="0.01" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.sale_price" class="mt-1 text-sm text-red-600">{{ form.errors.sale_price }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Stock Quantity <span class="text-red-600">*</span></label>
              <input v-model="form.stock_quantity" type="number" min="0" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.stock_quantity" class="mt-1 text-sm text-red-600">{{ form.errors.stock_quantity }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Low Stock Alert</label>
              <input v-model="form.low_stock_alert_quantity" type="number" min="0" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.low_stock_alert_quantity" class="mt-1 text-sm text-red-600">{{ form.errors.low_stock_alert_quantity }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Stock Status <span class="text-red-600">*</span></label>
              <select v-model="form.stock_status" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="in_stock">In Stock</option>
                <option value="out_of_stock">Out of Stock</option>
                <option value="pre_order">Pre Order</option>
                <option value="discontinued">Discontinued</option>
              </select>
              <p v-if="form.errors.stock_status" class="mt-1 text-sm text-red-600">{{ form.errors.stock_status }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Product Status <span class="text-red-600">*</span></label>
              <select v-model="form.status" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
              <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
            </div>

            <div class="md:col-span-3">
              <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
                <span class="text-sm font-medium text-neutral-700">Featured Product</span>
                <input v-model="form.featured" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
              </label>
            </div>

            <div>
              <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
                <span class="text-sm font-medium text-neutral-700">Today Best Deals</span>
                <input v-model="form.today_best_deals" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
              </label>
            </div>

            <div>
              <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
                <span class="text-sm font-medium text-neutral-700">Best Seller</span>
                <input v-model="form.best_seller" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
              </label>
            </div>
          </div>
        </section>

        <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-base font-semibold text-neutral-900">Product Details</h2>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Size / Label</label>
              <input v-model="form.size_label" type="text" placeholder="e.g. One size, M, 18 inch, 12L" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.size_label" class="mt-1 text-sm text-red-600">{{ form.errors.size_label }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Color</label>
              <input v-model="form.color" type="text" placeholder="e.g. Gold, black, tortoise shell" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.color" class="mt-1 text-sm text-red-600">{{ form.errors.color }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Material</label>
              <input v-model="form.material" type="text" placeholder="e.g. Stainless steel, leather, cotton" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.material" class="mt-1 text-sm text-red-600">{{ form.errors.material }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Style</label>
              <input v-model="form.style" type="text" placeholder="e.g. Minimal, party wear, casual, formal" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.style" class="mt-1 text-sm text-red-600">{{ form.errors.style }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Fit</label>
              <input v-model="form.fit" type="text" placeholder="e.g. Regular, slim, oversized" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.fit" class="mt-1 text-sm text-red-600">{{ form.errors.fit }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Lens Type</label>
              <input v-model="form.lens_type" type="text" placeholder="e.g. UV400, polarized, gradient" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.lens_type" class="mt-1 text-sm text-red-600">{{ form.errors.lens_type }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Frame Material</label>
              <input v-model="form.frame_material" type="text" placeholder="e.g. Metal, acetate, plastic" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.frame_material" class="mt-1 text-sm text-red-600">{{ form.errors.frame_material }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Bag Size / Capacity</label>
              <input v-model="form.bag_size" type="text" placeholder="e.g. Mini, medium, 20L, laptop 15 inch" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.bag_size" class="mt-1 text-sm text-red-600">{{ form.errors.bag_size }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Closure Type</label>
              <input v-model="form.closure_type" type="text" placeholder="e.g. Zip, magnetic, clasp, button" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.closure_type" class="mt-1 text-sm text-red-600">{{ form.errors.closure_type }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Strap Type</label>
              <input v-model="form.strap_type" type="text" placeholder="e.g. Chain, adjustable, crossbody" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.strap_type" class="mt-1 text-sm text-red-600">{{ form.errors.strap_type }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Dimensions</label>
              <input v-model="form.dimensions" type="text" placeholder="e.g. 30 x 20 x 10 cm" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.dimensions" class="mt-1 text-sm text-red-600">{{ form.errors.dimensions }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Warranty Type</label>
              <select v-model="form.warranty_option_id" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="">None</option>
                <option v-for="warranty in warranties" :key="warranty.id" :value="warranty.id">{{ warranty.name }}</option>
              </select>
              <p v-if="form.errors.warranty_option_id" class="mt-1 text-sm text-red-600">{{ form.errors.warranty_option_id }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Warranty Period</label>
              <input v-model="form.warranty_period" type="text" placeholder="e.g. 6 months / 1 year" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.warranty_period" class="mt-1 text-sm text-red-600">{{ form.errors.warranty_period }}</p>
            </div>

            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-neutral-700">Care Instructions</label>
              <textarea v-model="form.care_instructions" rows="3" placeholder="Optional care notes for wear, cleaning, storage, or handling" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.care_instructions" class="mt-1 text-sm text-red-600">{{ form.errors.care_instructions }}</p>
            </div>
          </div>
        </section>

        <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-base font-semibold text-neutral-900">Descriptions</h2>
          <div class="grid grid-cols-1 gap-4">
            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Short Description</label>
              <textarea v-model="form.short_description" rows="3" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.short_description" class="mt-1 text-sm text-red-600">{{ form.errors.short_description }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Full Description</label>
              <textarea v-model="form.full_description" rows="6" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.full_description" class="mt-1 text-sm text-red-600">{{ form.errors.full_description }}</p>
            </div>
          </div>
        </section>

        <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-base font-semibold text-neutral-900">Media</h2>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Main Image</label>
              <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50">
                  <img v-if="mainPreview" :src="mainPreview" class="h-full w-full object-cover" />
                  <span v-else class="text-xs text-neutral-400">No Image</span>
                </div>

                <div class="w-full flex-1">
                  <input type="file" accept="image/*" @change="onMainImageChange" class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200" />
                  <p v-if="form.errors.main_image" class="mt-1 text-sm text-red-600">{{ form.errors.main_image }}</p>
                </div>
              </div>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Hover Image</label>
              <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50">
                  <img v-if="hoverPreview" :src="hoverPreview" class="h-full w-full object-cover" />
                  <span v-else class="text-xs text-neutral-400">No Image</span>
                </div>

                <div class="w-full flex-1">
                  <input type="file" accept="image/*" @change="onHoverImageChange" class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200" />
                  <p v-if="form.errors.hover_image" class="mt-1 text-sm text-red-600">{{ form.errors.hover_image }}</p>
                </div>
              </div>
            </div>

            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-neutral-700">Product Video URL</label>
              <input v-model="form.product_video_url" type="url" placeholder="https://www.youtube.com/watch?v=..." class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.product_video_url" class="mt-1 text-sm text-red-600">{{ form.errors.product_video_url }}</p>
            </div>

            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-neutral-700">Gallery Images</label>
              <input type="file" accept="image/*" multiple @change="onGalleryChange" class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200" />
              <p v-if="form.errors.gallery_images" class="mt-1 text-sm text-red-600">{{ form.errors.gallery_images }}</p>

              <div v-if="isEdit" class="mt-2 flex items-center gap-2">
                <input id="clearFashionGallery" v-model="form.clear_gallery" type="checkbox" class="h-4 w-4" />
                <label for="clearFashionGallery" class="text-sm text-neutral-700">Update all gallery images (replace existing)</label>
              </div>
            </div>

            <div class="md:col-span-2 space-y-2">
              <div v-if="existingGallery.length" class="text-sm font-medium text-neutral-700">Existing Gallery</div>
              <div v-if="existingGallery.length" class="flex flex-wrap gap-2">
                <div v-for="(item, index) in existingGallery" :key="item.path || item.url || `existing-${index}`" class="relative h-20 w-20 overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50">
                  <img :src="item.url" class="h-full w-full object-cover" />
                  <button v-if="item.path && !form.clear_gallery" type="button" class="absolute right-1 top-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/90 text-neutral-700 shadow hover:bg-white hover:text-red-600" aria-label="Remove image" title="Remove" @click="removeExistingGalleryImage(index)">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>

              <div v-if="newGalleryPreview.length" class="text-sm font-medium text-neutral-700">New Gallery Selection</div>
              <div v-if="newGalleryPreview.length" class="flex flex-wrap gap-2">
                <div v-for="(url, index) in newGalleryPreview" :key="'new-' + index" class="relative h-20 w-20 overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50">
                  <img :src="url" class="h-full w-full object-cover" />
                  <button type="button" class="absolute right-1 top-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/90 text-neutral-700 shadow hover:bg-white hover:text-red-600" aria-label="Remove image" title="Remove" @click="removeNewGalleryImage(index)">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </section>

        <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
          <Link :href="route('admin.fashion.products.index')" class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-5 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto">Cancel</Link>
          <button type="submit" :disabled="form.processing" class="inline-flex w-full items-center justify-center rounded-full bg-[#38bdf8] px-6 py-2 text-sm font-medium text-white hover:bg-[#7dd3fc] disabled:opacity-50 sm:w-auto">
            {{ form.processing ? 'Saving...' : 'Save Product' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
