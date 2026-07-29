<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref } from 'vue'
import { route } from 'ziggy-js'

type CustomerImage = {
  id: number
  image_url: string | null
  sort_order: number
}

const props = defineProps<{
  images: CustomerImage[]
}>()

const previews = ref<string[]>([])
const objectUrls = ref<string[]>([])
const clientError = ref<string | null>(null)

const form = useForm<{
  customer_images: File[]
}>({
  customer_images: [],
})

const currentImages = computed(() => props.images || [])
const selectedCount = computed(() => form.customer_images.length)

function revokeObjectUrls() {
  objectUrls.value.forEach((url) => URL.revokeObjectURL(url))
  objectUrls.value = []
}

onBeforeUnmount(() => {
  revokeObjectUrls()
})

function isAcceptedImage(file: File) {
  const validMime = ['image/jpeg', 'image/png', 'image/webp'].includes(file.type)
  const validName = /\.(jpg|jpeg|png|webp)$/i.test(file.name)
  return validMime || validName
}

function onImagesChange(event: Event) {
  const input = event.target as HTMLInputElement
  const files = Array.from(input.files || [])

  clientError.value = null
  form.clearErrors()
  form.customer_images = []
  revokeObjectUrls()
  previews.value = []

  if (!files.length) return

  if (files.length > 6) {
    clientError.value = 'You can upload only 6 customer photos.'
    input.value = ''
    return
  }

  const maxBytes = 5 * 1024 * 1024
  const invalidFile = files.find((file) => !isAcceptedImage(file) || file.size > maxBytes)

  if (invalidFile) {
    clientError.value = 'Use JPG, JPEG, PNG or WEBP images under 5MB each.'
    input.value = ''
    return
  }

  form.customer_images = files
  objectUrls.value = files.map((file) => URL.createObjectURL(file))
  previews.value = [...objectUrls.value]
}

function submit() {
  clientError.value = null
  form
    .transform((data) => ({ ...data, _method: 'PUT' }))
    .post(route('customer-gallery.update'), {
      forceFormData: true,
      preserveScroll: true,
      onFinish: () => form.transform((data) => data),
    })
}
</script>

<template>
  <AppLayout>
    <Head title="Our Customers" />

    <div class="space-y-5 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">Our Customers</h1>
          <p class="text-sm text-neutral-500">
            Add up to 6 customer photos for the lazy-loaded home page gallery.
          </p>
        </div>
      </div>

      <form
        class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6"
        @submit.prevent="submit"
      >
        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">
          <div>
            <label class="mb-2 block text-sm font-medium text-neutral-700">Customer Photos</label>
            <input
              type="file"
              accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
              multiple
              class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200"
              @change="onImagesChange"
            />
            <p class="mt-2 text-xs text-neutral-500">
              Select 1 to 6 real customer photos. Saving replaces the current gallery and adds a light centered DezeStore watermark.
            </p>
            <p v-if="clientError" class="mt-2 text-sm text-red-600">{{ clientError }}</p>
            <p v-if="form.errors.customer_images" class="mt-2 text-sm text-red-600">
              {{ form.errors.customer_images }}
            </p>

            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
              <div
                v-for="index in 6"
                :key="`customer-slot-${index}`"
                class="aspect-[4/5] overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50"
              >
                <img
                  v-if="previews[index - 1]"
                  :src="previews[index - 1]"
                  :alt="`Selected customer photo ${index}`"
                  class="h-full w-full object-cover"
                />
                <img
                  v-else-if="currentImages[index - 1]?.image_url"
                  :src="currentImages[index - 1].image_url || ''"
                  :alt="`Current customer photo ${index}`"
                  class="h-full w-full object-cover"
                />
                <div v-else class="flex h-full w-full items-center justify-center text-xs text-neutral-400">
                  Empty slot
                </div>
              </div>
            </div>
          </div>

          <aside class="rounded-xl border border-neutral-200 bg-neutral-50 p-4">
            <div class="text-sm font-semibold text-neutral-900">Gallery Rules</div>
            <div class="mt-4 space-y-3 text-sm text-neutral-600">
              <div class="flex items-center justify-between gap-3">
                <span>Current photos</span>
                <span class="font-semibold text-neutral-900">{{ currentImages.length }}/6</span>
              </div>
              <div class="flex items-center justify-between gap-3">
                <span>Selected photos</span>
                <span class="font-semibold text-neutral-900">{{ selectedCount }}/6</span>
              </div>
              <div class="rounded-lg bg-white p-3 text-xs leading-5 text-neutral-500">
                Upload all photos in the order you want them to appear. Use square or portrait photos for the best home page result.
              </div>
            </div>

            <button
              type="submit"
              :disabled="form.processing || selectedCount < 1"
              class="mt-5 inline-flex w-full items-center justify-center rounded-full bg-[#38bdf8] px-6 py-2 text-sm font-medium text-white hover:bg-[#7dd3fc] disabled:cursor-not-allowed disabled:opacity-50"
            >
              {{ form.processing ? 'Saving...' : 'Save Customer Photos' }}
            </button>
          </aside>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
