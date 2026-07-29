<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref } from 'vue'
import { route } from 'ziggy-js'

type ProductOption = { id: number; name: string; label: string }
type Review = Record<string, any>

const props = defineProps<{
  mode: 'create' | 'edit'
  review?: Review | null
  products: ProductOption[]
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.review?.id)
const imagePreview = ref<string[]>(props.review?.image_urls ?? [])
let objectUrls: string[] = []

const form = useForm({
  product_id: props.review?.product_id ?? '',
  rating: props.review?.rating ?? '',
  customer_name: props.review?.customer_name ?? '',
  customer_email: props.review?.customer_email ?? '',
  short_description: props.review?.short_description ?? '',
  long_description: props.review?.long_description ?? '',
  images: [] as File[],
  status: props.review?.status ?? 'active',
})

onBeforeUnmount(() => {
  objectUrls.forEach((url) => URL.revokeObjectURL(url))
})

function onImageChange(event: Event) {
  objectUrls.forEach((url) => URL.revokeObjectURL(url))
  const files = Array.from((event.target as HTMLInputElement).files || [])
  form.images = files
  objectUrls = files.map((file) => URL.createObjectURL(file))
  imagePreview.value = objectUrls.length ? objectUrls : props.review?.image_urls ?? []
}

function submit() {
  if (!isEdit.value) {
    form.post(route('admin.motorcycles.product-reviews.store'), { forceFormData: true, preserveScroll: true })
    return
  }

  form.transform((data) => ({ ...data, _method: 'PUT' })).post(route('admin.motorcycles.product-reviews.update', props.review!.id), {
    forceFormData: true,
    preserveScroll: true,
    onFinish: () => form.transform((data) => data),
  })
}
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Product Review' : 'Create Product Review'" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Product Review' : 'Create Product Review' }}</h1>
          <p class="text-sm text-neutral-500">Attach customer review content to a motorcycle product.</p>
        </div>

        <Link :href="route('admin.motorcycles.product-reviews.index')" class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto">Back</Link>
      </div>

      <form @submit.prevent="submit" class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-neutral-700">Product <span class="text-red-600">*</span></label>
            <select v-model="form.product_id" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
              <option value="">Select product</option>
              <option v-for="product in products" :key="product.id" :value="product.id">{{ product.label }}</option>
            </select>
            <p v-if="form.errors.product_id" class="mt-1 text-sm text-red-600">{{ form.errors.product_id }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Rating</label>
            <select v-model="form.rating" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
              <option value="">No rating</option>
              <option v-for="rating in [1, 2, 3, 4, 5]" :key="rating" :value="rating">{{ rating }} / 5</option>
            </select>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Status</label>
            <select v-model="form.status" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Customer Name</label>
            <input v-model="form.customer_name" type="text" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Customer Email</label>
            <input v-model="form.customer_email" type="email" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
          </div>

          <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-neutral-700">Short Description</label>
            <textarea v-model="form.short_description" rows="2" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
          </div>

          <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-neutral-700">Full Description</label>
            <textarea v-model="form.long_description" rows="5" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
          </div>

          <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-neutral-700">Images</label>
            <div class="mb-3 flex flex-wrap gap-2">
              <img v-for="url in imagePreview" :key="url" :src="url" class="h-16 w-16 rounded-xl border border-neutral-200 object-cover" />
              <div v-if="!imagePreview.length" class="flex h-16 w-16 items-center justify-center rounded-xl border border-neutral-200 bg-neutral-50 text-xs text-neutral-400">None</div>
            </div>
            <input type="file" accept="image/*" multiple @change="onImageChange" class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200" />
          </div>
        </div>

        <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
          <Link :href="route('admin.motorcycles.product-reviews.index')" class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-5 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto">Cancel</Link>
          <button type="submit" :disabled="form.processing" class="inline-flex w-full items-center justify-center rounded-full bg-[#38bdf8] px-6 py-2 text-sm font-medium text-white hover:bg-[#7dd3fc] disabled:opacity-50 sm:w-auto">
            {{ form.processing ? 'Saving...' : 'Save Review' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
