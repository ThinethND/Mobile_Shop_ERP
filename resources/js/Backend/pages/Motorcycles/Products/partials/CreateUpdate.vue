<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { route } from 'ziggy-js'

type Option = { id: number; name: string; value: number; label: string; bike_brand_id?: number; start_year?: number | null; end_year?: number | null; engine_cc?: number | null }
type Product = Record<string, any>
type ExistingGalleryItem = {
  url: string
  path: string | null
}

const props = defineProps<{
  mode: 'create' | 'edit'
  product: Product
  productTypes: Array<{ value: string; label: string }>
  categories: Option[]
  helmetBrands: Option[]
  bikeBrands: Option[]
  bikeModels: Option[]
  warranties: Option[]
  options: {
    helmet_type: Option[]
    helmet_size: Option[]
    color: Option[]
    accessory_type: Option[]
    part_type: Option[]
  }
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.product?.id)
const specs = props.product?.specifications ?? {}
const mainPreview = ref<string | null>(props.product?.main_image_url ?? null)
const hoverPreview = ref<string | null>(props.product?.hover_image_url ?? null)
const existingGallery = ref<ExistingGalleryItem[]>(
  (props.product?.gallery_urls ?? []).map((url: string, index: number) => ({
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
  product_type: props.product?.product_type ?? 'helmet',
  category_id: props.product?.category_id ?? '',
  helmet_brand_id: props.product?.helmet_brand_id ?? '',
  compatible_helmet_brand_id: props.product?.compatible_helmet_brand_id ?? '',
  compatible_bike_brand_id: props.product?.compatible_bike_brand_id ?? '',
  compatible_bike_model_id: props.product?.compatible_bike_model_id ?? '',
  short_description: props.product?.short_description ?? '',
  full_description: props.product?.full_description ?? '',
  product_video_url: props.product?.product_video_url ?? '',
  main_image: null as File | null,
  hover_image: null as File | null,
  gallery_images: [] as File[],
  gallery_remove_paths: [] as string[],
  clear_gallery: false,
  regular_price: props.product?.regular_price ?? '',
  sale_price: props.product?.sale_price ?? '',
  cost_price: props.product?.cost_price ?? '',
  sku: props.product?.sku ?? '',
  stock_quantity: props.product?.stock_quantity ?? 0,
  low_stock_alert_quantity: props.product?.low_stock_alert_quantity ?? '',
  stock_status: props.product?.stock_status ?? 'in_stock',
  status: props.product?.status ?? 'active',
  featured: !!props.product?.featured,
  today_best_deals: !!props.product?.today_best_deals,
  best_seller: !!props.product?.best_seller,
  warranty_option_id: props.product?.warranty_option_id ?? '',
  warranty_period: props.product?.warranty_period ?? '',
  helmet_type_id: specs.helmet_type_id ?? '',
  helmet_size_id: specs.helmet_size_id ?? '',
  color_id: specs.color_id ?? '',
  shell_material: specs.shell_material ?? '',
  weight: specs.weight ?? '',
  safety_certification: specs.safety_certification ?? '',
  visor_type: specs.visor_type ?? '',
  chin_strap_type: specs.chin_strap_type ?? '',
  ventilation: specs.ventilation ?? '',
  removable_inner_liner: !!specs.removable_inner_liner,
  washable_padding: !!specs.washable_padding,
  bluetooth_compatible: !!specs.bluetooth_compatible,
  sun_visor_available: !!specs.sun_visor_available,
  pinlock_ready: !!specs.pinlock_ready,
  accessory_type_id: specs.accessory_type_id ?? '',
  compatible_helmet_model: specs.compatible_helmet_model ?? '',
  compatible_helmet_size_id: specs.compatible_helmet_size_id ?? '',
  material: specs.material ?? '',
  installation_type: specs.installation_type ?? '',
  compatible_start_year: specs.compatible_start_year ?? '',
  compatible_end_year: specs.compatible_end_year ?? '',
  mounting_position: specs.mounting_position ?? '',
  waterproof: !!specs.waterproof,
  installation_required: !!specs.installation_required,
  universal_fit: !!specs.universal_fit,
  part_type_id: specs.part_type_id ?? '',
  part_number: specs.part_number ?? '',
  oem_number: specs.oem_number ?? '',
  engine_cc: specs.engine_cc ?? '',
  position: specs.position ?? '',
  condition: specs.condition ?? 'brand_new',
  origin_type: specs.origin_type ?? 'aftermarket',
})

const isHelmet = computed(() => form.product_type === 'helmet')
const isHelmetAccessory = computed(() => form.product_type === 'helmet_accessory')
const isBikeAccessory = computed(() => form.product_type === 'bike_accessory')
const isSparePart = computed(() => form.product_type === 'spare_part')
const needsBikeCompatibility = computed(() => isBikeAccessory.value || isSparePart.value)
const filteredBikeModels = computed(() => {
  if (!form.compatible_bike_brand_id) return props.bikeModels
  return props.bikeModels.filter((model) => Number(model.bike_brand_id) === Number(form.compatible_bike_brand_id))
})
const pageTitle = computed(() => {
  const label = props.productTypes.find((type) => type.value === form.product_type)?.label ?? 'Product'
  return `${isEdit.value ? 'Update' : 'Add'} ${label}`
})

watch(() => form.product_type, () => {
  form.clearErrors()
})

watch(() => form.compatible_bike_brand_id, () => {
  if (form.compatible_bike_model_id && !filteredBikeModels.value.some((model) => Number(model.id) === Number(form.compatible_bike_model_id))) {
    form.compatible_bike_model_id = ''
  }
})

function revokePreviews() {
  if (mainObjectUrl) URL.revokeObjectURL(mainObjectUrl)
  if (hoverObjectUrl) URL.revokeObjectURL(hoverObjectUrl)
  galleryObjectUrls.forEach((url) => URL.revokeObjectURL(url))
  mainObjectUrl = null
  hoverObjectUrl = null
  galleryObjectUrls = []
}

onBeforeUnmount(revokePreviews)

function onMainImageChange(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0] || null
  form.main_image = file
  if (mainObjectUrl) URL.revokeObjectURL(mainObjectUrl)
  mainObjectUrl = file ? URL.createObjectURL(file) : null
  mainPreview.value = mainObjectUrl || props.product?.main_image_url || null
}

function onHoverImageChange(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0] || null
  form.hover_image = file
  if (hoverObjectUrl) URL.revokeObjectURL(hoverObjectUrl)
  hoverObjectUrl = file ? URL.createObjectURL(file) : null
  hoverPreview.value = hoverObjectUrl || props.product?.hover_image_url || null
}

function galleryFileKey(file: File) {
  return `${file.name}__${file.size}__${file.lastModified}`
}

function onGalleryChange(event: Event) {
  const input = event.target as HTMLInputElement
  const files = Array.from(input.files || [])
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
  if (!isEdit.value) {
    form.post(route('admin.motorcycles.products.store'), { forceFormData: true, preserveScroll: true })
    return
  }

  form.transform((data) => ({ ...data, _method: 'PUT' })).post(route('admin.motorcycles.products.update', props.product.id), {
    forceFormData: true,
    preserveScroll: true,
    onFinish: () => form.transform((data) => data),
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
          <p class="text-sm text-neutral-500">Only fields for the selected product type are shown.</p>
        </div>

        <Link :href="route('admin.motorcycles.products.index', { type: form.product_type })" class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto">Back</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-lg font-semibold text-neutral-900">Basic Details</h2>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Product Type <span class="text-red-600">*</span></label>
              <select v-model="form.product_type" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option v-for="type in productTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
              </select>
              <p v-if="form.errors.product_type" class="mt-1 text-sm text-red-600">{{ form.errors.product_type }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Category <span class="text-red-600">*</span></label>
              <select v-model="form.category_id" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="">Select category</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
              </select>
              <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id }}</p>
            </div>

            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-neutral-700">Product Name <span class="text-red-600">*</span></label>
              <input v-model="form.name" type="text" placeholder="e.g. Yamaha FZ Brake Pad" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-neutral-700">Short Description</label>
              <textarea v-model="form.short_description" rows="2" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.short_description" class="mt-1 text-sm text-red-600">{{ form.errors.short_description }}</p>
            </div>

            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-neutral-700">Full Description</label>
              <textarea v-model="form.full_description" rows="5" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.full_description" class="mt-1 text-sm text-red-600">{{ form.errors.full_description }}</p>
            </div>
          </div>
        </section>

        <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-lg font-semibold text-neutral-900">Pricing & Stock</h2>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Regular Price <span class="text-red-600">*</span></label>
              <input v-model="form.regular_price" type="number" min="0" step="0.01" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.regular_price" class="mt-1 text-sm text-red-600">{{ form.errors.regular_price }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Sale Price</label>
              <input v-model="form.sale_price" type="number" min="0" step="0.01" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.sale_price" class="mt-1 text-sm text-red-600">{{ form.errors.sale_price }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Cost Price</label>
              <input v-model="form.cost_price" type="number" min="0" step="0.01" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.cost_price" class="mt-1 text-sm text-red-600">{{ form.errors.cost_price }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">SKU</label>
              <input v-model="form.sku" type="text" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.sku" class="mt-1 text-sm text-red-600">{{ form.errors.sku }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Stock Quantity <span class="text-red-600">*</span></label>
              <input v-model="form.stock_quantity" type="number" min="0" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <p v-if="form.errors.stock_quantity" class="mt-1 text-sm text-red-600">{{ form.errors.stock_quantity }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Low Stock Alert Quantity</label>
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
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Product Status</label>
              <select v-model="form.status" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
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
          <h2 class="mb-4 text-lg font-semibold text-neutral-900">Warranty</h2>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
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
          </div>
        </section>

        <section v-if="isHelmet" class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-lg font-semibold text-neutral-900">Helmet Details</h2>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Helmet Brand <span class="text-red-600">*</span></label>
              <select v-model="form.helmet_brand_id" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="">Select brand</option>
                <option v-for="brand in helmetBrands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
              </select>
              <p v-if="form.errors.helmet_brand_id" class="mt-1 text-sm text-red-600">{{ form.errors.helmet_brand_id }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Helmet Type <span class="text-red-600">*</span></label>
              <select v-model="form.helmet_type_id" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="">Select type</option>
                <option v-for="item in options.helmet_type" :key="item.id" :value="item.id">{{ item.name }}</option>
              </select>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Helmet Size</label>
              <select v-model="form.helmet_size_id" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="">Select size</option>
                <option v-for="item in options.helmet_size" :key="item.id" :value="item.id">{{ item.name }}</option>
              </select>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Color</label>
              <select v-model="form.color_id" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="">Select color</option>
                <option v-for="item in options.color" :key="item.id" :value="item.id">{{ item.name }}</option>
              </select>
            </div>

            <input v-model="form.shell_material" placeholder="Shell Material" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            <input v-model="form.weight" placeholder="Weight" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            <input v-model="form.safety_certification" placeholder="Safety Certification" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            <input v-model="form.visor_type" placeholder="Visor Type" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            <input v-model="form.chin_strap_type" placeholder="Chin Strap Type" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            <input v-model="form.ventilation" placeholder="Ventilation" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />

            <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
              <span class="text-sm font-medium text-neutral-700">Removable Inner Liner</span>
              <input v-model="form.removable_inner_liner" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
            </label>

            <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
              <span class="text-sm font-medium text-neutral-700">Washable Padding</span>
              <input v-model="form.washable_padding" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
            </label>

            <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
              <span class="text-sm font-medium text-neutral-700">Bluetooth Compatible</span>
              <input v-model="form.bluetooth_compatible" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
            </label>

            <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
              <span class="text-sm font-medium text-neutral-700">Sun Visor Available</span>
              <input v-model="form.sun_visor_available" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
            </label>

            <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
              <span class="text-sm font-medium text-neutral-700">Pinlock Ready</span>
              <input v-model="form.pinlock_ready" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
            </label>
          </div>
        </section>

        <section v-if="isHelmetAccessory" class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-lg font-semibold text-neutral-900">Helmet Accessory Details</h2>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Accessory Type <span class="text-red-600">*</span></label>
              <select v-model="form.accessory_type_id" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="">Select type</option>
                <option v-for="item in options.accessory_type" :key="item.id" :value="item.id">{{ item.name }}</option>
              </select>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Compatible Helmet Brand <span class="text-red-600">*</span></label>
              <select v-model="form.compatible_helmet_brand_id" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="">Select brand</option>
                <option v-for="brand in helmetBrands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
              </select>
            </div>

            <input v-model="form.compatible_helmet_model" placeholder="Compatible Helmet Model" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            <select v-model="form.compatible_helmet_size_id" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
              <option value="">Compatible Helmet Size</option>
              <option v-for="item in options.helmet_size" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>
            <select v-model="form.color_id" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
              <option value="">Color</option>
              <option v-for="item in options.color" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>
            <input v-model="form.material" placeholder="Material" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            <input v-model="form.installation_type" placeholder="Installation Type" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
          </div>
        </section>

        <section v-if="needsBikeCompatibility" class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-lg font-semibold text-neutral-900">{{ isSparePart ? 'Spare Part Details' : 'Bike Accessory Details' }}</h2>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div v-if="isBikeAccessory">
              <label class="mb-1 block text-sm font-medium text-neutral-700">Accessory Type <span class="text-red-600">*</span></label>
              <select v-model="form.accessory_type_id" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="">Select type</option>
                <option v-for="item in options.accessory_type" :key="item.id" :value="item.id">{{ item.name }}</option>
              </select>
            </div>

            <div v-if="isSparePart">
              <label class="mb-1 block text-sm font-medium text-neutral-700">Part Type <span class="text-red-600">*</span></label>
              <select v-model="form.part_type_id" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="">Select type</option>
                <option v-for="item in options.part_type" :key="item.id" :value="item.id">{{ item.name }}</option>
              </select>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Compatible Bike Brand <span class="text-red-600">*</span></label>
              <select v-model="form.compatible_bike_brand_id" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="">Select bike brand</option>
                <option v-for="brand in bikeBrands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
              </select>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Compatible Bike Model <span class="text-red-600">*</span></label>
              <select v-model="form.compatible_bike_model_id" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="">Select bike model</option>
                <option v-for="model in filteredBikeModels" :key="model.id" :value="model.id">{{ model.name }}</option>
              </select>
            </div>

            <input v-model="form.compatible_start_year" type="number" min="1950" placeholder="Start Year" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            <input v-model="form.compatible_end_year" type="number" min="1950" placeholder="End Year" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            <input v-model="form.material" placeholder="Material" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            <select v-model="form.color_id" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
              <option value="">Color</option>
              <option v-for="item in options.color" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>

            <template v-if="isBikeAccessory">
              <input v-model="form.mounting_position" placeholder="Mounting Position" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3"><span class="text-sm font-medium text-neutral-700">Waterproof</span><input v-model="form.waterproof" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" /></label>
              <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3"><span class="text-sm font-medium text-neutral-700">Installation Required</span><input v-model="form.installation_required" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" /></label>
              <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3"><span class="text-sm font-medium text-neutral-700">Universal Fit</span><input v-model="form.universal_fit" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" /></label>
            </template>

            <template v-if="isSparePart">
              <input v-model="form.part_number" placeholder="Part Number *" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <input v-model="form.oem_number" placeholder="OEM Number" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <input v-model="form.engine_cc" type="number" min="1" placeholder="Engine CC" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <input v-model="form.position" placeholder="Position" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
              <select v-model="form.condition" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="brand_new">Brand New</option>
                <option value="used">Used</option>
                <option value="reconditioned">Reconditioned</option>
              </select>
              <select v-model="form.origin_type" class="rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
                <option value="genuine">Genuine</option>
                <option value="aftermarket">Aftermarket</option>
              </select>
            </template>
          </div>
        </section>

        <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <h2 class="mb-4 text-lg font-semibold text-neutral-900">Media</h2>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Main Image</label>
              <div class="mb-3 flex h-24 w-24 items-center justify-center overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50">
                <img v-if="mainPreview" :src="mainPreview" class="h-full w-full object-cover" />
                <span v-else class="text-xs text-neutral-400">No Image</span>
              </div>
              <input type="file" accept="image/*" @change="onMainImageChange" class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200" />
              <p v-if="form.errors.main_image" class="mt-1 text-sm text-red-600">{{ form.errors.main_image }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Hover Image</label>
              <div class="mb-3 flex h-24 w-24 items-center justify-center overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50">
                <img v-if="hoverPreview" :src="hoverPreview" class="h-full w-full object-cover" />
                <span v-else class="text-xs text-neutral-400">No Image</span>
              </div>
              <input type="file" accept="image/*" @change="onHoverImageChange" class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200" />
              <p v-if="form.errors.hover_image" class="mt-1 text-sm text-red-600">{{ form.errors.hover_image }}</p>
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
                <input id="clearMotorcycleGallery" v-model="form.clear_gallery" type="checkbox" class="h-4 w-4" />
                <label for="clearMotorcycleGallery" class="text-sm text-neutral-700">Update all gallery images (replace existing)</label>
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
          <Link :href="route('admin.motorcycles.products.index', { type: form.product_type })" class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-5 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto">Cancel</Link>
          <button type="submit" :disabled="form.processing" class="inline-flex w-full items-center justify-center rounded-full bg-[#38bdf8] px-6 py-2 text-sm font-medium text-white hover:bg-[#7dd3fc] disabled:opacity-50 sm:w-auto">
            {{ form.processing ? 'Saving...' : 'Save Product' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
