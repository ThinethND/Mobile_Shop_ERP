<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import MultiSelect from '@/Backend/components/MultiSelect.vue'
import SelectInputComponent from '@/Backend/components/SelectInputComponent.vue'
import axios from 'axios'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { route } from 'ziggy-js'

type Option = {
  id: number
  name: string
}

type ProductPayload = {
  id: number
  name: string
  brand_id: number | null
  category_id: number | null
  product_type_id: number | null
  country_of_origin_id: number | null
  size_volume_ids?: number[]
  batch_number?: string | null
  manufacture_date?: string | null
  expiry_date?: string | null
  price: number | string
  stock?: number | string | null
  discount_type?: 'percentage' | 'fixed' | null
  discount_value?: number | string | null
  is_featured?: boolean
  hot_deals?: boolean
  best_selling?: boolean
  status?: 'active' | 'inactive'
  short_description?: string | null
  long_description?: string | null
  product_video_url?: string | null
  main_image_url?: string | null
  hover_image_url?: string | null
  gallery_urls?: string[]
  gallery_paths?: string[]
  variants?: VariantPayload[]
}

type VariantPayload = {
  id?: number
  cosmetic_size_volume_id: number
  price: number | string
  stock_count?: number | string | null
  sku?: string | null
  status?: 'active' | 'inactive'
}

const props = defineProps<{
  mode: 'create' | 'edit'
  product?: ProductPayload | null
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.product?.id)

const brandOptions = ref<Option[]>([])
const categoryOptions = ref<Option[]>([])
const productTypeOptions = ref<Option[]>([])
const countryOptions = ref<Option[]>([])
const sizeVolumeOptions = ref<Option[]>([])

const mainPreview = ref<string | null>(props.product?.main_image_url ?? null)
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

let mainObjectUrl: string | null = null
let hoverObjectUrl: string | null = null
let galleryObjectUrls: string[] = []

const discountTypeOptions = [
  { id: '', name: 'None' },
  { id: 'percentage', name: 'Percentage' },
  { id: 'fixed', name: 'Fixed Amount' },
]

const statusOptions = [
  { id: 'active', name: 'Active' },
  { id: 'inactive', name: 'Inactive' },
]

const form = useForm({
  name: props.product?.name ?? '',
  brand_id: props.product?.brand_id ?? null as number | null,
  category_id: props.product?.category_id ?? null as number | null,
  product_type_id: props.product?.product_type_id ?? null as number | null,
  country_of_origin_id: props.product?.country_of_origin_id ?? null as number | null,

  size_volume_ids: props.product?.size_volume_ids ?? [] as number[],
  variants: (props.product?.variants || []).map(v => ({
    id: v.id,
    cosmetic_size_volume_id: Number(v.cosmetic_size_volume_id),
    price: v.price ?? props.product?.price ?? '',
    stock_count: v.stock_count ?? null,
    sku: v.sku ?? '',
    status: v.status ?? 'active',
  })) as VariantPayload[],

  batch_number: props.product?.batch_number ?? '',
  manufacture_date: props.product?.manufacture_date ?? '',
  expiry_date: props.product?.expiry_date ?? '',

  price: props.product?.price ?? '',
  stock: props.product?.stock ?? '',

  discount_type: props.product?.discount_type ?? '',
  discount_value: props.product?.discount_value ?? '',

  is_featured: props.product?.is_featured ?? false,
  hot_deals: props.product?.hot_deals ?? false,
  best_selling: props.product?.best_selling ?? false,
  status: props.product?.status ?? 'active',

  short_description: props.product?.short_description ?? '',
  long_description: props.product?.long_description ?? '',
  product_video_url: props.product?.product_video_url ?? '',

  main_image: null as File | null,
  hover_image: null as File | null,
  gallery_images: [] as File[],
  gallery_remove_paths: [] as string[],
  clear_gallery: false,
})

const sizeLabelMap = computed(() => {
  return new Map(sizeVolumeOptions.value.map(s => [Number(s.id), s.name]))
})

function sizeLabel(id: number) {
  return sizeLabelMap.value.get(Number(id)) || `Size #${id}`
}

async function fetchBrands() {
  const response = await axios.get(route('admin.cosmetics.brands.options'))
  brandOptions.value = Array.isArray(response.data) ? response.data : []
}

async function fetchCategories() {
  const response = await axios.get(route('admin.cosmetics.categories.options'))
  categoryOptions.value = Array.isArray(response.data) ? response.data : []
}

async function fetchCountries() {
  const response = await axios.get(route('admin.cosmetics.countries-origin.options'))
  countryOptions.value = Array.isArray(response.data) ? response.data : []
}

async function fetchSizes() {
  const response = await axios.get(route('admin.cosmetics.sizes-volume.options'))
  sizeVolumeOptions.value = Array.isArray(response.data) ? response.data : []
}

async function fetchProductTypes(categoryId: number | null) {
  if (!categoryId) {
    productTypeOptions.value = []
    return
  }

  const response = await axios.get(route('admin.cosmetics.product-types.options'), {
    params: { category_id: categoryId },
  })

  productTypeOptions.value = Array.isArray(response.data) ? response.data : []
}

watch(
  () => form.category_id,
  async (categoryId, oldCategoryId) => {
    if (Number(categoryId) !== Number(oldCategoryId)) {
      form.product_type_id = null
    }

    await fetchProductTypes(categoryId ? Number(categoryId) : null)
  }
)

watch(
  () => form.discount_type,
  (value) => {
    if (!value) {
      form.discount_value = ''
    }
  }
)

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
  for (const url of galleryObjectUrls) {
    URL.revokeObjectURL(url)
  }
  galleryObjectUrls = []
}

onBeforeUnmount(() => {
  revokeMainObjectUrl()
  revokeHoverObjectUrl()
  revokeGalleryObjectUrls()
})

function onMainImageChange(e: Event) {
  const input = e.target as HTMLInputElement
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

function onHoverImageChange(e: Event) {
  const input = e.target as HTMLInputElement
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

function onGalleryChange(e: Event) {
  const input = e.target as HTMLInputElement
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

function syncVariantRows() {
  const selectedSizeIds = (form.size_volume_ids || []).map(Number).filter(Boolean)

  if (!selectedSizeIds.length) {
    form.variants = []
    return
  }

  const existingMap = new Map(
    (form.variants || []).map((v: any) => [Number(v.cosmetic_size_volume_id), v])
  )

  const nextRows: VariantPayload[] = []

  for (const sizeId of selectedSizeIds) {
    const existing = existingMap.get(sizeId)

    nextRows.push({
      id: existing?.id,
      cosmetic_size_volume_id: sizeId,
      price: existing?.price ?? form.price ?? '',
      stock_count: existing?.stock_count ?? null,
      sku: existing?.sku ?? '',
      status: existing?.status ?? 'active',
    })
  }

  form.variants = nextRows
}

watch(
  () => [...(form.size_volume_ids || [])].map(Number).sort((a, b) => a - b).join(','),
  syncVariantRows,
  { immediate: true }
)

function submit() {
  form.clearErrors()

  const payloadTransform = (data: any) => ({
    ...data,
    discount_type: data.discount_type ? data.discount_type : null,
    discount_value: data.discount_type
      ? (data.discount_value === '' || data.discount_value === null || typeof data.discount_value === 'undefined'
          ? null
          : Number(data.discount_value))
      : null,
    size_volume_ids: (data.size_volume_ids || []).map((id: any) => Number(id)),
    variants: JSON.stringify(
      (data.variants || []).map((v: any) => ({
        cosmetic_size_volume_id: Number(v.cosmetic_size_volume_id),
        price: Number(v.price ?? 0),
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
      .post(route('admin.cosmetics.products.store'), {
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
    .post(route('admin.cosmetics.products.update', props.product!.id), {
      forceFormData: true,
      preserveScroll: true,
      onFinish: () => form.transform((d: any) => d),
    })
}

onMounted(async () => {
  await Promise.all([
    fetchBrands(),
    fetchCategories(),
    fetchCountries(),
    fetchSizes(),
  ])

  await fetchProductTypes(form.category_id ? Number(form.category_id) : null)
})
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Cosmetic Product' : 'Create Cosmetic Product'" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Cosmetic Product' : 'Create Cosmetic Product' }}</h1>
          <p class="text-sm text-neutral-500">
            {{ isEdit ? 'Edit cosmetic product details.' : 'Create a new cosmetic product.' }}
          </p>
        </div>

        <Link
          :href="route('admin.cosmetics.products.index')"
          class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto"
        >
          Back
        </Link>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-base font-semibold text-neutral-900">Basic Details</h2>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-neutral-700">Product Name</label>
              <input
                v-model="form.name"
                type="text"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                placeholder="e.g. Vitamin C Serum"
              />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div>
              <SelectInputComponent
                id="cosmetic_product_brand_id"
                label="Cosmetic Brand"
                :options="brandOptions"
                v-model="form.brand_id"
                :error="form.errors.brand_id"
                :isRequired="true"
                valueKey="id"
                labelKey="name"
                placeholder="Select brand"
              />
            </div>

            <div>
              <SelectInputComponent
                id="cosmetic_product_country_of_origin_id"
                label="Country of Origin"
                :options="countryOptions"
                v-model="form.country_of_origin_id"
                :error="form.errors.country_of_origin_id"
                :isRequired="true"
                valueKey="id"
                labelKey="name"
                placeholder="Select country"
              />
            </div>

            <div>
              <SelectInputComponent
                id="cosmetic_product_category_id"
                label="Cosmetic Category"
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
                id="cosmetic_product_product_type_id"
                label="Product Type"
                :options="productTypeOptions"
                v-model="form.product_type_id"
                :error="form.errors.product_type_id"
                :isRequired="true"
                valueKey="id"
                labelKey="name"
                placeholder="Select product type"
              />
            </div>
          </div>
        </div>

        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-base font-semibold text-neutral-900">Sizes / Volume & Variant Pricing</h2>

          <div class="grid grid-cols-1 gap-4">
            <div>
              <MultiSelect
                id="cosmetic_product_sizes"
                label="Sizes / Volume"
                placeholder="Select sizes"
                :options="sizeVolumeOptions"
                v-model="form.size_volume_ids"
                :error="form.errors.size_volume_ids"
                valueKey="id"
                labelKey="name"
              />
            </div>

            <div v-if="form.variants.length" class="overflow-hidden rounded-xl border border-neutral-200">
              <div class="overflow-auto">
                <table class="w-full text-sm text-neutral-700">
                  <thead class="bg-neutral-50 text-neutral-600">
                    <tr>
                      <th class="px-3 py-3 text-left">Size / Volume</th>
                      <th class="px-3 py-3 text-left" style="width: 150px">Price</th>
                      <th class="px-3 py-3 text-left" style="width: 140px">Stock</th>
                      <th class="px-3 py-3 text-left" style="width: 220px">SKU</th>
                      <th class="px-3 py-3 text-left" style="width: 140px">Status</th>
                    </tr>
                  </thead>

                  <tbody>
                    <tr v-for="(variant, index) in form.variants" :key="variant.cosmetic_size_volume_id" class="border-t">
                      <td class="px-3 py-3">
                        <div class="font-medium text-neutral-800">
                          {{ sizeLabel(variant.cosmetic_size_volume_id) }}
                        </div>
                      </td>

                      <td class="px-3 py-3">
                        <input
                          v-model="variant.price"
                          type="number"
                          step="0.01"
                          min="0"
                          class="w-full rounded-xl border border-neutral-200 px-3 py-2 outline-none focus:border-[#38bdf8]"
                        />
                        <p v-if="form.errors[`variants.${index}.price`]" class="mt-1 text-xs text-red-600">
                          {{ form.errors[`variants.${index}.price`] }}
                        </p>
                      </td>

                      <td class="px-3 py-3">
                        <input
                          v-model="variant.stock_count"
                          type="number"
                          step="1"
                          min="0"
                          class="w-full rounded-xl border border-neutral-200 px-3 py-2 outline-none focus:border-[#38bdf8]"
                        />
                        <p v-if="form.errors[`variants.${index}.stock_count`]" class="mt-1 text-xs text-red-600">
                          {{ form.errors[`variants.${index}.stock_count`] }}
                        </p>
                      </td>

                      <td class="px-3 py-3">
                        <input
                          v-model="variant.sku"
                          type="text"
                          class="w-full rounded-xl border border-neutral-200 px-3 py-2 outline-none focus:border-[#38bdf8]"
                          placeholder="Optional"
                        />
                        <p v-if="form.errors[`variants.${index}.sku`]" class="mt-1 text-xs text-red-600">
                          {{ form.errors[`variants.${index}.sku`] }}
                        </p>
                      </td>

                      <td class="px-3 py-3">
                        <select
                          v-model="variant.status"
                          class="w-full rounded-xl border border-neutral-200 px-3 py-2 outline-none focus:border-[#38bdf8]"
                        >
                          <option value="active">Active</option>
                          <option value="inactive">Inactive</option>
                        </select>
                        <p v-if="form.errors[`variants.${index}.status`]" class="mt-1 text-xs text-red-600">
                          {{ form.errors[`variants.${index}.status`] }}
                        </p>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <p v-if="form.errors.variants" class="text-sm text-red-600">{{ form.errors.variants }}</p>
          </div>
        </div>

        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-base font-semibold text-neutral-900">Batch & Expiry</h2>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Batch Number</label>
              <input
                v-model="form.batch_number"
                type="text"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                placeholder="e.g. BATCH-001"
              />
              <p v-if="form.errors.batch_number" class="mt-1 text-sm text-red-600">{{ form.errors.batch_number }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Manufacture Date</label>
              <input
                v-model="form.manufacture_date"
                type="date"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              />
              <p v-if="form.errors.manufacture_date" class="mt-1 text-sm text-red-600">{{ form.errors.manufacture_date }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Expiry Date</label>
              <input
                v-model="form.expiry_date"
                type="date"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              />
              <p v-if="form.errors.expiry_date" class="mt-1 text-sm text-red-600">{{ form.errors.expiry_date }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-base font-semibold text-neutral-900">Pricing, Inventory & Flags</h2>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Price</label>
              <input
                v-model="form.price"
                type="number"
                step="0.01"
                min="0"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              />
              <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Stock</label>
              <input
                v-model="form.stock"
                type="number"
                step="1"
                min="0"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              />
              <p v-if="form.errors.stock" class="mt-1 text-sm text-red-600">{{ form.errors.stock }}</p>
            </div>

            <div>
              <SelectInputComponent
                id="cosmetic_product_status"
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

            <div>
              <SelectInputComponent
                id="cosmetic_product_discount_type"
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

            <div class="md:col-span-3 grid grid-cols-1 gap-4 md:grid-cols-3">
              <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
                <span class="text-sm font-medium text-neutral-700">Featured</span>
                <input v-model="form.is_featured" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
              </label>

              <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
                <span class="text-sm font-medium text-neutral-700">Today Best Deals</span>
                <input v-model="form.hot_deals" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
              </label>

              <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
                <span class="text-sm font-medium text-neutral-700">Best Seller</span>
                <input v-model="form.best_selling" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
              </label>
            </div>
          </div>
        </div>

        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-base font-semibold text-neutral-900">Descriptions</h2>

          <div class="grid grid-cols-1 gap-4">
            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Short Description</label>
              <textarea
                v-model="form.short_description"
                rows="3"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              ></textarea>
              <p v-if="form.errors.short_description" class="mt-1 text-sm text-red-600">
                {{ form.errors.short_description }}
              </p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Long Description</label>
              <textarea
                v-model="form.long_description"
                rows="6"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              ></textarea>
              <p v-if="form.errors.long_description" class="mt-1 text-sm text-red-600">
                {{ form.errors.long_description }}
              </p>
            </div>
          </div>
        </div>

        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-base font-semibold text-neutral-900">Media</h2>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Main Image</label>
              <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                <div class="h-20 w-20 rounded-xl border border-neutral-200 overflow-hidden bg-neutral-50 flex items-center justify-center">
                  <img v-if="mainPreview" :src="mainPreview" class="h-full w-full object-cover" />
                  <span v-else class="text-xs text-neutral-400">No Image</span>
                </div>

                <div class="w-full flex-1">
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
                    @change="onHoverImageChange"
                    class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200"
                  />
                  <p v-if="form.errors.hover_image" class="mt-1 text-sm text-red-600">{{ form.errors.hover_image }}</p>
                </div>
              </div>
            </div>

            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-neutral-700">Product Video URL</label>
              <input
                v-model="form.product_video_url"
                type="url"
                placeholder="https://www.youtube.com/watch?v=..."
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
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
              <div v-if="existingGallery.length" class="flex flex-wrap gap-2">
                <div
                  v-for="(item, idx) in existingGallery"
                  :key="item.path || item.url || `existing-${idx}`"
                  class="relative h-20 w-20 overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50"
                >
                  <img :src="item.url" class="h-full w-full object-cover" />
                  <button
                    v-if="item.path && !form.clear_gallery"
                    type="button"
                    class="absolute right-1 top-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/90 text-neutral-700 shadow hover:bg-white hover:text-red-600"
                    @click="removeExistingGalleryImage(idx)"
                    aria-label="Remove image"
                    title="Remove"
                  >
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>

              <div v-if="newGalleryPreview.length" class="text-sm font-medium text-neutral-700">New Gallery Selection</div>
              <div v-if="newGalleryPreview.length" class="flex flex-wrap gap-2">
                <div
                  v-for="(url, idx) in newGalleryPreview"
                  :key="'new-' + idx"
                  class="relative h-20 w-20 overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50"
                >
                  <img :src="url" class="h-full w-full object-cover" />
                  <button
                    type="button"
                    class="absolute right-1 top-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/90 text-neutral-700 shadow hover:bg-white hover:text-red-600"
                    @click="removeNewGalleryImage(idx)"
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

        <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
          <Link
            :href="route('admin.cosmetics.products.index')"
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
