<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref } from 'vue'
import { route } from 'ziggy-js'

type ProductPayload = {
  id: number
  name: string
}

type ReviewPayload = {
  id: number
  rating?: number | null
  customer_name?: string | null
  customer_email?: string | null
  short_description?: string | null
  long_description?: string | null
  image_urls?: string[]
}

const props = defineProps<{
  mode: 'create' | 'edit'
  product: ProductPayload
  review?: ReviewPayload | null
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.review?.id)

const existingImages = ref<string[]>(props.review?.image_urls ?? [])
const newImagesPreview = ref<string[]>([])
let objectUrls: string[] = []

const form = useForm({
  rating: props.review?.rating ?? '',
  customer_name: props.review?.customer_name ?? '',
  customer_email: props.review?.customer_email ?? '',
  short_description: props.review?.short_description ?? '',
  long_description: props.review?.long_description ?? '',
  images: [] as File[],
})

function revokeObjectUrls() {
  objectUrls.forEach((url) => URL.revokeObjectURL(url))
  objectUrls = []
}

onBeforeUnmount(() => {
  revokeObjectUrls()
})

function onImagesChange(event: Event) {
  const input = event.target as HTMLInputElement
  const files = input.files ? Array.from(input.files) : []

  form.images = files
  revokeObjectUrls()
  objectUrls = files.map((file) => URL.createObjectURL(file))
  newImagesPreview.value = [...objectUrls]
}

function fieldError(key: string) {
  const errors = form.errors as Record<string, string | undefined>
  return errors[key]
}

function submit() {
  form.clearErrors()

  if (!isEdit.value) {
    form.post(route('admin.shoes.product-reviews.reviews.store', props.product.id), {
      forceFormData: true,
      preserveScroll: true,
    })
    return
  }

  form
    .transform((data) => ({
      ...data,
      _method: 'PUT',
    }))
    .post(
      route('admin.shoes.product-reviews.reviews.update', {
        product: props.product.id,
        review: props.review!.id,
      }),
      {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => form.transform((data) => data),
      }
    )
}
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Review' : 'Add Review'" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Review' : 'Add Review' }}</h1>
          <p class="text-sm text-neutral-500">Product: <span class="font-medium text-neutral-700">{{ product.name }}</span></p>
        </div>

        <Link
          :href="route('admin.shoes.product-reviews.reviews.index', product.id)"
          class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto"
        >
          Back
        </Link>
      </div>

      <form @submit.prevent="submit" class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Rating (1–5)</label>
            <select
              v-model="form.rating"
              class="w-full rounded-xl border border-neutral-200 bg-white px-4 py-2 outline-none focus:border-[#38bdf8]"
            >
              <option value="">No rating</option>
              <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
            </select>
            <p v-if="form.errors.rating" class="mt-1 text-sm text-red-600">{{ form.errors.rating }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Customer Name</label>
            <input
              v-model="form.customer_name"
              type="text"
              placeholder="e.g. John Doe"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
            />
            <p v-if="form.errors.customer_name" class="mt-1 text-sm text-red-600">{{ form.errors.customer_name }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Customer Gmail</label>
            <input
              v-model="form.customer_email"
              type="email"
              placeholder="e.g. customer@gmail.com"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
            />
            <p v-if="form.errors.customer_email" class="mt-1 text-sm text-red-600">{{ form.errors.customer_email }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Short Description</label>
            <textarea
              v-model="form.short_description"
              rows="3"
              placeholder="Short review text..."
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
            />
            <p v-if="form.errors.short_description" class="mt-1 text-sm text-red-600">{{ form.errors.short_description }}</p>
          </div>

          <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-neutral-700">Long Description</label>
            <textarea
              v-model="form.long_description"
              rows="5"
              placeholder="Long review text..."
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
            />
            <p v-if="form.errors.long_description" class="mt-1 text-sm text-red-600">{{ form.errors.long_description }}</p>
          </div>

          <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-neutral-700">Review Images</label>

            <input
              type="file"
              accept="image/*"
              multiple
              @change="onImagesChange"
              class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200"
            />

            <p v-if="form.errors.images" class="mt-1 text-sm text-red-600">{{ form.errors.images }}</p>
            <p v-if="fieldError('images.0')" class="mt-1 text-sm text-red-600">{{ fieldError('images.0') }}</p>

            <div v-if="existingImages.length || newImagesPreview.length" class="mt-3 space-y-3">
              <div v-if="existingImages.length">
                <div class="text-xs font-medium text-neutral-600">Existing</div>
                <div class="mt-2 flex flex-wrap gap-2">
                  <img
                    v-for="(url, idx) in existingImages"
                    :key="`existing-${idx}`"
                    :src="url"
                    class="h-16 w-16 rounded-xl border border-neutral-200 object-cover"
                  />
                </div>
              </div>

              <div v-if="newImagesPreview.length">
                <div class="text-xs font-medium text-neutral-600">New Upload</div>
                <div class="mt-2 flex flex-wrap gap-2">
                  <img
                    v-for="(url, idx) in newImagesPreview"
                    :key="`new-${idx}`"
                    :src="url"
                    class="h-16 w-16 rounded-xl border border-neutral-200 object-cover"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
          <Link
            :href="route('admin.shoes.product-reviews.reviews.index', product.id)"
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

