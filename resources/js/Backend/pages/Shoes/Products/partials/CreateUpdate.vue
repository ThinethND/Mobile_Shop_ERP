<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import MultiSelect from '@/Backend/components/MultiSelect.vue'
import SelectInputComponent from '@/Backend/components/SelectInputComponent.vue'
import axios from 'axios'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { route } from 'ziggy-js'

type Option = {
  id: number
  name: string
}

type ProductPayload = {
  id: number
  name: string
  slug: string
  brand_id: number | null
  
  category_id: number | null
  subcategory_id: number | null
  sku?: string | null
  barcode?: string | null
  model_code?: string | null
  short_description?: string | null
  full_description?: string | null
  status: 'draft' | 'published' | 'out_of_stock' | 'archived'
  featured?: boolean
  new_arrival?: boolean
  best_seller?: boolean
  regular_price?: number | string | null
  sale_price?: number | string | null
  cost_price?: number | string | null
  currency?: string | null
  // tax_class?: string | null
  discount_type?: 'percentage' | 'fixed' | null
  discount_value?: number | string | null
  sale_start_date?: string | null
  sale_end_date?: string | null
  stock_quantity?: number | string | null
  low_stock_alert_quantity?: number | string | null
  stock_status?: 'in_stock' | 'out_of_stock' | 'preorder'
  gender?: 'men' | 'women' | 'kids' | 'unisex' | null
  age_group?: 'adult' | 'teen' | 'kids' | 'baby' | null
  size_type_ids?: number[]
  sizes_by_type?: Array<{ type: string; sizes: string[] }>
  color_ids?: number[]
  material_ids?: number[]
  shoe_weight?: string | null
  product_video_url?: string | null
  thumbnail_url?: string | null
  gallery_urls?: string[]
  gallery_paths?: string[]
  hover_image_url?: string | null
}

const props = defineProps<{
  mode: 'create' | 'edit'
  product?: ProductPayload | null
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.product?.id)

const brandOptions = ref<Option[]>([])

const categoryOptions = ref<Option[]>([])
const subcategoryOptions = ref<Option[]>([])
const sizeTypeOptions = ref<Option[]>([])
const colorOptions = ref<Option[]>([])
const materialOptions = ref<Option[]>([])

const statusOptions = [
  { id: 'draft', name: 'Draft' },
  { id: 'published', name: 'Published' },
  { id: 'out_of_stock', name: 'Out of Stock' },
  { id: 'archived', name: 'Archived' },
]

const currencyOptions = [
  { id: 'LKR', name: 'LKR' },
  { id: 'USD', name: 'USD' },
  { id: 'EUR', name: 'EUR' },
]

// const taxClassOptions = [
//   { id: 'standard', name: 'Standard' },
//   { id: 'reduced', name: 'Reduced' },
//   { id: 'zero', name: 'Zero Rated' },
//   { id: 'exempt', name: 'Exempt' },
// ]

const discountTypeOptions = [
  { id: '', name: 'None' },
  { id: 'percentage', name: 'Percentage' },
  { id: 'fixed', name: 'Fixed Amount' },
]

const stockStatusOptions = [
  { id: 'in_stock', name: 'In Stock' },
  { id: 'out_of_stock', name: 'Out of Stock' },
  { id: 'preorder', name: 'Preorder' },
]

const genderOptions = [
  { id: 'men', name: 'Men' },
  { id: 'women', name: 'Women' },
  { id: 'kids', name: 'Kids' },
  { id: 'unisex', name: 'Unisex' },
]

const ageGroupOptions = [
  { id: 'adult', name: 'Adult' },
  { id: 'teen', name: 'Teen' },
  { id: 'kids', name: 'Kids' },
  { id: 'baby', name: 'Baby' },
]

const thumbnailPreview = ref<string | null>(props.product?.thumbnail_url ?? null)
const hoverPreview = ref<string | null>(props.product?.hover_image_url ?? null)

type ExistingGalleryItem = {
  url: string
  path: string | null
}

const existingGallery = ref<ExistingGalleryItem[]>(
  (props.product?.gallery_urls ?? []).map((url, idx) => ({
    url,
    path: props.product?.gallery_paths?.[idx] ?? null,
  }))
)
const newGalleryPreview = ref<string[]>([])
const sizeInputMap = reactive<Record<string, string>>({})
const attemptedSubmit = ref(false)
const slugTouched = ref(false)
const isGeneratingSku = ref(false)

let thumbnailObjectUrl: string | null = null
let hoverObjectUrl: string | null = null
let galleryObjectUrls: string[] = []

const form = useForm({
  name: props.product?.name ?? '',
  slug: props.product?.slug ?? '',
  brand_id: props.product?.brand_id ?? null as number | null,

  category_id: props.product?.category_id ?? null as number | null,
  subcategory_id: props.product?.subcategory_id ?? null as number | null,
  sku: props.product?.sku ?? '',
  barcode: props.product?.barcode ?? '',
  model_code: props.product?.model_code ?? '',
  short_description: props.product?.short_description ?? '',
  full_description: props.product?.full_description ?? '',
  status: props.product?.status ?? 'draft',
  featured: props.product?.featured ?? false,
  new_arrival: props.product?.new_arrival ?? false,
  best_seller: props.product?.best_seller ?? false,
  regular_price: props.product?.regular_price ?? '',
  sale_price: props.product?.sale_price ?? '',
  cost_price: props.product?.cost_price ?? '',
  currency: props.product?.currency ?? 'LKR',
  // tax_class: props.product?.tax_class ?? 'standard',
  discount_type: props.product?.discount_type ?? '',
  discount_value: props.product?.discount_value ?? '',
  sale_start_date: props.product?.sale_start_date ?? '',
  sale_end_date: props.product?.sale_end_date ?? '',
  stock_quantity: props.product?.stock_quantity ?? '',
  low_stock_alert_quantity: props.product?.low_stock_alert_quantity ?? '',
  stock_status: props.product?.stock_status ?? 'in_stock',
  gender: props.product?.gender ?? 'unisex',
  age_group: props.product?.age_group ?? 'adult',
  size_type_ids: props.product?.size_type_ids ?? [] as number[],
  sizes_by_type: props.product?.sizes_by_type ?? [] as Array<{ type: string; sizes: string[] }>,
  color_ids: props.product?.color_ids ?? [] as number[],
  material_ids: props.product?.material_ids ?? [] as number[],
  shoe_weight: props.product?.shoe_weight ?? '',
  product_video_url: props.product?.product_video_url ?? '',
  thumbnail_image: null as File | null,
  gallery_images: [] as File[],
  gallery_remove_paths: [] as string[],
  clear_gallery: false,
  hover_image: null as File | null,
})

const sizeTypeLabelById = computed<Record<number, string>>(() => {
  return sizeTypeOptions.value.reduce((carry, option) => {
    carry[Number(option.id)] = option.name
    return carry
  }, {} as Record<number, string>)
})

watch(
  () => form.name,
  (value) => {
    if (!slugTouched.value || !form.slug) {
      form.slug = slugify(value)
    }
  }
)

watch(
  () => form.category_id,
  async (categoryId, oldCategoryId) => {
    if (Number(categoryId) !== Number(oldCategoryId)) {
      form.subcategory_id = null
    }

    await fetchSubcategories(categoryId ? Number(categoryId) : null)
  }
)

watch(
  () => form.size_type_ids,
  () => {
    syncSizesByType()
  },
  { deep: true }
)

function slugify(value: string) {
  return value
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
}

function onSlugInput() {
  slugTouched.value = true
}

function generateSkuFallback(name?: string) {
  const safeName = (name || form.name || 'SHOE')
    .toUpperCase()
    .trim()
    .replace(/[^A-Z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')

  const parts = safeName
    .split('-')
    .filter(Boolean)
    .slice(0, 3)
    .map((part) => part.slice(0, 4))

  const base = parts.length ? parts.join('-') : 'SHOE'

  const now = new Date()
  const stamp =
    `${now.getFullYear()}` +
    `${String(now.getMonth() + 1).padStart(2, '0')}` +
    `${String(now.getDate()).padStart(2, '0')}` +
    `${String(now.getHours()).padStart(2, '0')}` +
    `${String(now.getMinutes()).padStart(2, '0')}` +
    `${String(now.getSeconds()).padStart(2, '0')}`

  const rand = Math.random().toString(36).slice(2, 8).toUpperCase()

  return `${base}-${stamp}-${rand}`
}

async function generateSku() {
  form.clearErrors('sku')
  isGeneratingSku.value = true

  try {
    const response = await axios.get(route('admin.shoes.products.generate-sku'), {
      params: {
        name: form.name || 'shoe',
      },
    })

    const sku = response?.data?.sku

    form.sku =
      typeof sku === 'string' && sku.trim()
        ? sku.trim()
        : generateSkuFallback(form.name)
  } catch (error) {
    console.error('Failed to generate SKU:', error)
    form.sku = generateSkuFallback(form.name)
  } finally {
    isGeneratingSku.value = false
  }
}

function revokeThumbnailObjectUrl() {
  if (thumbnailObjectUrl) {
    URL.revokeObjectURL(thumbnailObjectUrl)
    thumbnailObjectUrl = null
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

function onThumbnailChange(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0] || null

  form.thumbnail_image = file
  revokeThumbnailObjectUrl()

  if (file) {
    thumbnailObjectUrl = URL.createObjectURL(file)
    thumbnailPreview.value = thumbnailObjectUrl
    return
  }

  thumbnailPreview.value = props.product?.thumbnail_url ?? null
}

function onHoverChange(event: Event) {
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

function onGalleryChange(event: Event) {
  const input = event.target as HTMLInputElement
  const files = input.files ? Array.from(input.files) : []
  if (!files.length) return

  const existingFiles = form.gallery_images || []
  const existingKeys = new Set(
    existingFiles.map((file) => `${file.name}__${file.size}__${file.lastModified}`),
  )

  const uniqueFiles = files.filter(
    (file) => !existingKeys.has(`${file.name}__${file.size}__${file.lastModified}`),
  )
  if (!uniqueFiles.length) {
    input.value = ''
    return
  }

  form.gallery_images = [...existingFiles, ...uniqueFiles]
  const newUrls = uniqueFiles.map((file) => URL.createObjectURL(file))
  galleryObjectUrls = [...galleryObjectUrls, ...newUrls]
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
  if (url) {
    URL.revokeObjectURL(url)
  }

  form.gallery_images = (form.gallery_images || []).filter((_, i) => i !== index)
  galleryObjectUrls = newGalleryPreview.value.filter((_, i) => i !== index)
  newGalleryPreview.value = [...galleryObjectUrls]
}

function addSize(typeCode: string) {
  const raw = (sizeInputMap[typeCode] ?? '').trim()
  if (!raw) return

  const values = raw
    .split(',')
    .map((value) => value.trim())
    .filter(Boolean)

  const entry = form.sizes_by_type.find((item) => item.type === typeCode)
  if (!entry) return

  const merged = new Set(entry.sizes)
  values.forEach((value) => merged.add(value))
  entry.sizes = Array.from(merged)
  sizeInputMap[typeCode] = ''
}

function removeSize(typeCode: string, value: string) {
  const entry = form.sizes_by_type.find((item) => item.type === typeCode)
  if (!entry) return

  entry.sizes = entry.sizes.filter((size) => size !== value)
}

function syncSizesByType() {
  const selectedTypes = (form.size_type_ids || [])
    .map((id) => sizeTypeLabelById.value[Number(id)])
    .filter(Boolean)

  const currentMap = new Map(
    form.sizes_by_type.map((entry) => [entry.type, [...entry.sizes]])
  )

  form.sizes_by_type = selectedTypes.map((typeCode) => ({
    type: typeCode,
    sizes: currentMap.get(typeCode) ?? [],
  }))

  Object.keys(sizeInputMap).forEach((key) => {
    if (!selectedTypes.includes(key)) {
      delete sizeInputMap[key]
    }
  })
}

function fieldError(key: string) {
  return (form.errors as Record<string, string>)[key] ?? ''
}

function validateSizes() {
  form.clearErrors('size_type_ids', 'sizes_by_type')

  if (!form.size_type_ids.length) {
    form.setError('size_type_ids', 'Select at least one shoe size type.')
    return false
  }

  const invalid = form.sizes_by_type.some((entry) => !entry.sizes.length)
  if (invalid) {
    form.setError('sizes_by_type', 'Each selected size type must have at least one size.')
    return false
  }

  return true
}

async function fetchSubcategories(categoryId: number | null) {
  if (!categoryId) {
    subcategoryOptions.value = []
    form.subcategory_id = null
    return
  }

  try {
    const response = await axios.get(route('admin.shoes.subcategories.options'), {
      params: { category_id: categoryId },
    })

    subcategoryOptions.value = Array.isArray(response.data) ? response.data : []

    const exists = subcategoryOptions.value.some(
      (item) => Number(item.id) === Number(form.subcategory_id)
    )

    if (!exists) {
      form.subcategory_id = null
    }
  } catch (error) {
    subcategoryOptions.value = []
    form.subcategory_id = null
    console.error('Failed to fetch subcategories:', error)
  }
}

async function fetchAllOptions() {
 const [
  brandsResponse,
  categoriesResponse,
  sizeTypesResponse,
  colorsResponse,
  materialsResponse,
] = await Promise.all([
  axios.get(route('admin.shoes.brands.options')),
  axios.get(route('admin.shoes.categories.options')),
  axios.get(route('admin.shoes.size-types.options')),
  axios.get(route('admin.shoes.colors.options')),
  axios.get(route('admin.shoes.materials.options')),
])

brandOptions.value = Array.isArray(brandsResponse.data) ? brandsResponse.data : []
categoryOptions.value = Array.isArray(categoriesResponse.data) ? categoriesResponse.data : []
sizeTypeOptions.value = Array.isArray(sizeTypesResponse.data) ? sizeTypesResponse.data : []
colorOptions.value = Array.isArray(colorsResponse.data) ? colorsResponse.data : []
materialOptions.value = Array.isArray(materialsResponse.data) ? materialsResponse.data : []

  if (form.category_id) {
    await fetchSubcategories(Number(form.category_id))
  }

  syncSizesByType()
}

function submit() {
  attemptedSubmit.value = true
  form.clearErrors()

  if (!validateSizes()) {
    return
  }

  const payloadTransform = (data: any) => ({
    ...data,
    size_type_ids: (data.size_type_ids || []).map((id: any) => Number(id)),
    color_ids: (data.color_ids || []).map((id: any) => Number(id)),
    material_ids: (data.material_ids || []).map((id: any) => Number(id)),
    sizes_by_type: (data.sizes_by_type || []).map((entry: any) => ({
      type: entry.type,
      sizes: (entry.sizes || []).map((size: any) => String(size)),
    })),
    featured: !!data.featured,
    new_arrival: !!data.new_arrival,
    best_seller: !!data.best_seller,
  })

  if (!isEdit.value) {
    form
      .transform(payloadTransform)
      .post(route('admin.shoes.products.store'), {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => form.transform((data) => data),
      })

    return
  }

  form
    .transform((data) => ({
      ...payloadTransform(data),
      _method: 'PUT',
    }))
    .post(route('admin.shoes.products.update', props.product!.id), {
      forceFormData: true,
      preserveScroll: true,
      onFinish: () => form.transform((data) => data),
    })
}

onMounted(() => {
  fetchAllOptions()
})

onBeforeUnmount(() => {
  revokeThumbnailObjectUrl()
  revokeHoverObjectUrl()
  revokeGalleryObjectUrls()
})
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Shoe Product' : 'Create Shoe Product'" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Shoe Product' : 'Create Shoe Product' }}</h1>
          <p class="text-sm text-neutral-500">Create or update a shoe product using the existing admin form pattern.</p>
        </div>

        <Link
          :href="route('admin.shoes.products.index')"
          class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto"
        >
          Back
        </Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-base font-semibold text-neutral-900">Basic Info</h2>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Product Name</label>
              <input
                v-model="form.name"
                type="text"
                placeholder="e.g. Nike Air Zoom Alpha"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Product Slug</label>
              <input
                v-model="form.slug"
                type="text"
                placeholder="nike-air-zoom-alpha"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                @input="onSlugInput"
              />
              <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
            </div>

            <div>
              <SelectInputComponent
                id="shoe_product_brand_id"
                label="Brand"
                :options="brandOptions"
                v-model="form.brand_id"
                :error="form.errors.brand_id"
                :isRequired="true"
                valueKey="id"
                labelKey="name"
                placeholder="Select brand"
              />
            </div>
<!-- 
            <div>
              <SelectInputComponent
                id="shoe_product_type_id"
                label="Product Type"
                :options="typeOptions"
                v-model="form.product_type_id"
                :error="form.errors.product_type_id"
                :isRequired="true"
                valueKey="id"
                labelKey="name"
                placeholder="Select product type"
              />
            </div> -->

            <div>
              <SelectInputComponent
                id="shoe_product_category_id"
                label="Category"
                :options="categoryOptions"
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
                id="shoe_product_subcategory_id"
                label="Subcategory"
                :options="subcategoryOptions"
                v-model="form.subcategory_id"
                :error="form.errors.subcategory_id"
                :isRequired="true"
                valueKey="id"
                labelKey="name"
                placeholder="Select subcategory"
              />
            </div>

           <div>
  <label class="mb-1 block text-sm font-medium text-neutral-700">SKU</label>

  <div class="flex flex-col gap-2 sm:flex-row">
    <input
      v-model="form.sku"
      type="text"
      placeholder="e.g. NZ-ALPHA-001"
      class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
    />

    <button
      type="button"
      :disabled="isGeneratingSku"
      @click="generateSku"
      class="inline-flex shrink-0 items-center justify-center rounded-xl border border-[#38bdf8] px-4 py-2 text-sm font-medium text-[#38bdf8] transition hover:bg-[#38bdf8] hover:text-white disabled:opacity-50"
    >
      {{ isGeneratingSku ? 'Generating...' : 'Generate' }}
    </button>
  </div>

  <p class="mt-1 text-xs text-neutral-500">
    Click Generate to create a unique SKU automatically.
  </p>

  <p v-if="form.errors.sku" class="mt-1 text-sm text-red-600">
    {{ form.errors.sku }}
  </p>
</div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Barcode / UPC / EAN</label>
              <input
                v-model="form.barcode"
                type="text"
                placeholder="e.g. 1234567890123"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              />
              <p v-if="form.errors.barcode" class="mt-1 text-sm text-red-600">{{ form.errors.barcode }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Model / Style Code</label>
              <input
                v-model="form.model_code"
                type="text"
                placeholder="e.g. AAZ-001"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              />
              <p v-if="form.errors.model_code" class="mt-1 text-sm text-red-600">{{ form.errors.model_code }}</p>
            </div>

            <div>
              <SelectInputComponent
                id="shoe_product_status"
                label="Status"
                :options="statusOptions"
                v-model="form.status"
                :error="form.errors.status"
                :isRequired="true"
                valueKey="id"
                labelKey="name"
                placeholder="Select status"
              />
            </div>

            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-neutral-700">Short Description</label>
              <textarea
                v-model="form.short_description"
                rows="3"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              />
              <p v-if="form.errors.short_description" class="mt-1 text-sm text-red-600">{{ form.errors.short_description }}</p>
            </div>

            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-neutral-700">Full Description</label>
              <textarea
                v-model="form.full_description"
                rows="6"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              />
              <p v-if="form.errors.full_description" class="mt-1 text-sm text-red-600">{{ form.errors.full_description }}</p>
            </div>

            <div class="md:col-span-2 grid grid-cols-1 gap-4 md:grid-cols-3">
              <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
                <span class="text-sm font-medium text-neutral-700">Featured</span>
                <input v-model="form.featured" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
              </label>

              <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
                <span class="text-sm font-medium text-neutral-700">New Arrival</span>
                <input v-model="form.new_arrival" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
              </label>

              <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
                <span class="text-sm font-medium text-neutral-700">Best Seller</span>
                <input v-model="form.best_seller" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
              </label>
            </div>
          </div>
        </div>

        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-base font-semibold text-neutral-900">Pricing</h2>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Regular Price</label>
              <input
                v-model="form.regular_price"
                type="number"
                step="0.01"
                min="0"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              />
              <p v-if="form.errors.regular_price" class="mt-1 text-sm text-red-600">{{ form.errors.regular_price }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Sale Price</label>
              <input
                v-model="form.sale_price"
                type="number"
                step="0.01"
                min="0"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              />
              <p v-if="form.errors.sale_price" class="mt-1 text-sm text-red-600">{{ form.errors.sale_price }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Cost Price</label>
              <input
                v-model="form.cost_price"
                type="number"
                step="0.01"
                min="0"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              />
              <p v-if="form.errors.cost_price" class="mt-1 text-sm text-red-600">{{ form.errors.cost_price }}</p>
            </div>

            <div>
              <SelectInputComponent
                id="shoe_product_currency"
                label="Currency"
                :options="currencyOptions"
                v-model="form.currency"
                :error="form.errors.currency"
                :isRequired="true"
                valueKey="id"
                labelKey="name"
                placeholder="Select currency"
              />
            </div>

            <!-- <div>
              <SelectInputComponent
                id="shoe_product_tax_class"
                label="Tax Class"
                :options="taxClassOptions"
                v-model="form.tax_class"
                :error="form.errors.tax_class"
                :isRequired="false"
                valueKey="id"
                labelKey="name"
                placeholder="Select tax class"
              />
            </div> -->

            <div>
              <SelectInputComponent
                id="shoe_product_discount_type"
                label="Discount Type"
                :options="discountTypeOptions"
                v-model="form.discount_type"
                :error="form.errors.discount_type"
                :isRequired="false"
                valueKey="id"
                labelKey="name"
                placeholder="Select discount type"
              />
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Discount Value</label>
              <input
                v-model="form.discount_value"
                type="number"
                step="0.01"
                min="0"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              />
              <p v-if="form.errors.discount_value" class="mt-1 text-sm text-red-600">{{ form.errors.discount_value }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Sale Start Date</label>
              <input
                v-model="form.sale_start_date"
                type="date"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              />
              <p v-if="form.errors.sale_start_date" class="mt-1 text-sm text-red-600">{{ form.errors.sale_start_date }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Sale End Date</label>
              <input
                v-model="form.sale_end_date"
                type="date"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              />
              <p v-if="form.errors.sale_end_date" class="mt-1 text-sm text-red-600">{{ form.errors.sale_end_date }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-base font-semibold text-neutral-900">Inventory</h2>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Stock Quantity</label>
              <input
                v-model="form.stock_quantity"
                type="number"
                step="1"
                min="0"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              />
              <p v-if="form.errors.stock_quantity" class="mt-1 text-sm text-red-600">{{ form.errors.stock_quantity }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Low Stock Alert Quantity</label>
              <input
                v-model="form.low_stock_alert_quantity"
                type="number"
                step="1"
                min="0"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              />
              <p v-if="form.errors.low_stock_alert_quantity" class="mt-1 text-sm text-red-600">{{ form.errors.low_stock_alert_quantity }}</p>
            </div>

            <div>
              <SelectInputComponent
                id="shoe_product_stock_status"
                label="Stock Status"
                :options="stockStatusOptions"
                v-model="form.stock_status"
                :error="form.errors.stock_status"
                :isRequired="true"
                valueKey="id"
                labelKey="name"
                placeholder="Select stock status"
              />
            </div>
          </div>
        </div>

        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-base font-semibold text-neutral-900">Shoe Details</h2>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <SelectInputComponent
                id="shoe_product_gender"
                label="Gender"
                :options="genderOptions"
                v-model="form.gender"
                :error="form.errors.gender"
                :isRequired="false"
                valueKey="id"
                labelKey="name"
                placeholder="Select gender"
              />
            </div>

            <div>
              <SelectInputComponent
                id="shoe_product_age_group"
                label="Age Group"
                :options="ageGroupOptions"
                v-model="form.age_group"
                :error="form.errors.age_group"
                :isRequired="false"
                valueKey="id"
                labelKey="name"
                placeholder="Select age group"
              />
            </div>

            <div class="md:col-span-2">
              <MultiSelect
                id="shoe_product_size_type_ids"
                label="Shoe Size Type"
                placeholder="Select shoe size types"
                :options="sizeTypeOptions"
                v-model="form.size_type_ids"
                valueKey="id"
                labelKey="name"
                :error="form.errors.size_type_ids"
              />
              <p v-if="form.errors.size_type_ids" class="mt-1 text-sm text-red-600">{{ form.errors.size_type_ids }}</p>
            </div>

            <div class="md:col-span-2 space-y-4">
              <div
                v-for="entry in form.sizes_by_type"
                :key="entry.type"
                class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4"
              >
                <div class="mb-3 flex items-center justify-between">
                  <div class="text-sm font-semibold text-neutral-800">{{ entry.type }} Sizes</div>
                  <div class="text-xs text-neutral-500">Store values as strings, decimals allowed.</div>
                </div>

                <div class="mb-3 flex flex-wrap gap-2">
                  <span
                    v-for="size in entry.sizes"
                    :key="`${entry.type}-${size}`"
                    class="inline-flex items-center gap-2 rounded-full border border-neutral-200 bg-white px-3 py-1 text-xs text-neutral-700"
                  >
                    {{ size }}
                    <button
                      type="button"
                      class="text-neutral-500 hover:text-red-600"
                      @click="removeSize(entry.type, size)"
                    >
                      ×
                    </button>
                  </span>

                  <span
                    v-if="!entry.sizes.length"
                    class="text-sm text-neutral-400"
                  >
                    No sizes added yet.
                  </span>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                  <input
                    v-model="sizeInputMap[entry.type]"
                    type="text"
                    :placeholder="`Add ${entry.type} sizes like 8, 8.5, 9`"
                    class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                    @keydown.enter.prevent="addSize(entry.type)"
                  />
                  <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-full border border-[#38bdf8] px-4 py-2 text-sm font-medium text-[#38bdf8] transition hover:bg-[#38bdf8] hover:text-white"
                    @click="addSize(entry.type)"
                  >
                    Add
                  </button>
                </div>

                <p
                  v-if="attemptedSubmit && !entry.sizes.length"
                  class="mt-2 text-sm text-red-600"
                >
                  Add at least one size for {{ entry.type }}.
                </p>
              </div>

              <p v-if="form.errors.sizes_by_type" class="text-sm text-red-600">{{ form.errors.sizes_by_type }}</p>
            </div>

            <div class="md:col-span-2">
              <MultiSelect
                id="shoe_product_color_ids"
                label="Color"
                placeholder="Select colors"
                :options="colorOptions"
                v-model="form.color_ids"
                valueKey="id"
                labelKey="name"
                :error="form.errors.color_ids"
              />
              <p v-if="fieldError('color_ids.0')" class="mt-1 text-sm text-red-600">{{ fieldError('color_ids.0') }}</p>
            </div>

            <div class="md:col-span-2">
              <MultiSelect
                id="shoe_product_material_ids"
                label="Material"
                placeholder="Select materials"
                :options="materialOptions"
                v-model="form.material_ids"
                valueKey="id"
                labelKey="name"
                :error="form.errors.material_ids"
              />
              <p v-if="fieldError('material_ids.0')" class="mt-1 text-sm text-red-600">{{ fieldError('material_ids.0') }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Shoe Weight</label>
              <input
                v-model="form.shoe_weight"
                type="text"
                placeholder="e.g. 420g"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              />
              <p v-if="form.errors.shoe_weight" class="mt-1 text-sm text-red-600">{{ form.errors.shoe_weight }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-base font-semibold text-neutral-900">Media</h2>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Main Image</label>

              <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50">
                  <img v-if="thumbnailPreview" :src="thumbnailPreview" class="h-full w-full object-cover" />
                  <span v-else class="text-xs text-neutral-400">No Image</span>
                </div>

                <div class="w-full flex-1">
                  <input
                    type="file"
                    accept="image/*"
                    @change="onThumbnailChange"
                    class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200"
                  />
                  <p v-if="form.errors.thumbnail_image" class="mt-1 text-sm text-red-600">{{ form.errors.thumbnail_image }}</p>
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
                  <input
                    type="file"
                    accept="image/*"
                    @change="onHoverChange"
                    class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200"
                  />
                  <p v-if="form.errors.hover_image" class="mt-1 text-sm text-red-600">{{ form.errors.hover_image }}</p>
                </div>
              </div>
            </div>

            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-neutral-700">Gallery Images</label>
              <input
                type="file"
                accept="image/*"
                multiple
                @change="onGalleryChange"
                class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200"
              />
              <p v-if="form.errors.gallery_images" class="mt-1 text-sm text-red-600">{{ form.errors.gallery_images }}</p>
              <p v-if="fieldError('gallery_images.0')" class="mt-1 text-sm text-red-600">{{ fieldError('gallery_images.0') }}</p>

              <div v-if="isEdit" class="mt-2 flex items-center gap-2">
                <input id="clearGallery" type="checkbox" v-model="form.clear_gallery" class="h-4 w-4" />
                <label for="clearGallery" class="text-sm text-neutral-700">Update all gallery images (replace existing)</label>
              </div>

              <div v-if="existingGallery.length" class="mt-3">
                <div class="mb-2 text-sm font-medium text-neutral-700">Existing Gallery</div>
                <div class="flex flex-wrap gap-2">
                  <div
                    v-for="(item, index) in existingGallery"
                    :key="item.path || item.url || `existing-gallery-${index}`"
                    class="relative h-16 w-16 overflow-hidden rounded-lg border border-neutral-200 bg-neutral-50"
                  >
                    <img :src="item.url" class="h-full w-full object-cover" />
                    <button
                      v-if="item.path && !form.clear_gallery"
                      type="button"
                      class="absolute right-1 top-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/90 text-neutral-700 shadow hover:bg-white hover:text-red-600"
                      @click="removeExistingGalleryImage(index)"
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

              <div v-if="newGalleryPreview.length" class="mt-3">
                <div class="mb-2 text-sm font-medium text-neutral-700">New Gallery</div>
                <div class="flex flex-wrap gap-2">
                  <div
                    v-for="(url, index) in newGalleryPreview"
                    :key="`new-gallery-${index}`"
                    class="relative h-16 w-16 overflow-hidden rounded-lg border border-neutral-200 bg-neutral-50"
                  >
                    <img :src="url" class="h-full w-full object-cover" />
                    <button
                      type="button"
                      class="absolute right-1 top-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/90 text-neutral-700 shadow hover:bg-white hover:text-red-600"
                      @click="removeNewGalleryImage(index)"
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

            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-neutral-700">Product Video URL</label>
              <input
                v-model="form.product_video_url"
                type="url"
                placeholder="https://example.com/video"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              />
              <p v-if="form.errors.product_video_url" class="mt-1 text-sm text-red-600">{{ form.errors.product_video_url }}</p>
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
          <Link
            :href="route('admin.shoes.products.index')"
            class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-5 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto"
          >
            Cancel
          </Link>

          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex w-full items-center justify-center rounded-full bg-[#38bdf8] px-6 py-2 text-sm font-medium text-white hover:bg-[#7dd3fc] disabled:opacity-50 sm:w-auto"
          >
            {{ form.processing ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
