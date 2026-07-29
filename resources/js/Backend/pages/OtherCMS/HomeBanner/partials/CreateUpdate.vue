<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { route } from 'ziggy-js'

type BannerPayload = {
  id: number
  name: string
  description?: string | null
  desktop_image_url?: string | null
  mobile_image_url?: string | null
  video_url?: string | null
}

const props = defineProps<{
  mode: 'create' | 'edit'
  banner?: BannerPayload | null
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.banner?.id)

const desktopPreview = ref<string | null>(props.banner?.desktop_image_url ?? null)
const mobilePreview = ref<string | null>(props.banner?.mobile_image_url ?? null)

const desktopClientError = ref<string | null>(null)
const mobileClientError = ref<string | null>(null)

const desktopObjectUrl = ref<string | null>(null)
const mobileObjectUrl = ref<string | null>(null)

const form = useForm<{
  name: string
  description: string
  desktop_image: File | null
  mobile_image: File | null
}>({
  name: props.banner?.name ?? '',
  description: props.banner?.description ?? '',
  desktop_image: null,
  mobile_image: null,
})

function revokeUrl(url: string | null) {
  if (url) URL.revokeObjectURL(url)
}

function resetObjectUrls() {
  revokeUrl(desktopObjectUrl.value)
  revokeUrl(mobileObjectUrl.value)
  desktopObjectUrl.value = null
  mobileObjectUrl.value = null
}

watch(
  () => props.banner?.id,
  () => {
    resetObjectUrls()

    const b = props.banner
    form.clearErrors()
    form.name = b?.name ?? ''
    form.description = b?.description ?? ''
    form.desktop_image = null
    form.mobile_image = null
    desktopPreview.value = b?.desktop_image_url ?? null
    mobilePreview.value = b?.mobile_image_url ?? null
    desktopClientError.value = null
    mobileClientError.value = null
  }
)

onBeforeUnmount(() => {
  resetObjectUrls()
})

function isAcceptedImage(file: File) {
  const validMime = ['image/jpeg', 'image/png', 'image/webp'].includes(file.type)
  const validName = /\.(jpg|jpeg|png|webp)$/i.test(file.name)
  return validMime || validName
}

function onDesktopImageChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0] || null

  desktopClientError.value = null
  form.desktop_image = null

  revokeUrl(desktopObjectUrl.value)
  desktopObjectUrl.value = null

  if (!file) {
    desktopPreview.value = props.banner?.desktop_image_url ?? null
    return
  }

  if (!isAcceptedImage(file)) {
    desktopClientError.value = 'Only JPG, JPEG, PNG and WEBP images are allowed.'
    input.value = ''
    return
  }

  const maxBytes = 10 * 1024 * 1024
  if (file.size > maxBytes) {
    desktopClientError.value = 'Desktop banner image must be less than 10MB.'
    input.value = ''
    return
  }

  form.desktop_image = file
  desktopObjectUrl.value = URL.createObjectURL(file)
  desktopPreview.value = desktopObjectUrl.value
}

function onMobileImageChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0] || null

  mobileClientError.value = null
  form.mobile_image = null

  revokeUrl(mobileObjectUrl.value)
  mobileObjectUrl.value = null

  if (!file) {
    mobilePreview.value = props.banner?.mobile_image_url ?? null
    return
  }

  if (!isAcceptedImage(file)) {
    mobileClientError.value = 'Only JPG, JPEG, PNG and WEBP images are allowed.'
    input.value = ''
    return
  }

  const maxBytes = 10 * 1024 * 1024
  if (file.size > maxBytes) {
    mobileClientError.value = 'Mobile banner image must be less than 10MB.'
    input.value = ''
    return
  }

  form.mobile_image = file
  mobileObjectUrl.value = URL.createObjectURL(file)
  mobilePreview.value = mobileObjectUrl.value
}

function submit() {
  form.clearErrors()

  if (!isEdit.value) {
    form.post(route('homebanners.store'), {
      forceFormData: true,
      preserveScroll: true,
    })
    return
  }

  form
    .transform((data) => ({ ...data, _method: 'PUT' }))
    .post(route('homebanners.update', props.banner!.id), {
      forceFormData: true,
      preserveScroll: true,
      onFinish: () => form.transform((d) => d),
    })
}
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Home Banner' : 'Create Home Banner'" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Home Banner' : 'Create Home Banner' }}</h1>
          <p class="text-sm text-neutral-500">
            {{ isEdit ? 'Edit home banner desktop and mobile images.' : 'Create a new home banner with separate desktop and mobile images.' }}
          </p>
        </div>

        <Link
          :href="route('homebanners.index')"
          class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto"
        >
          Back
        </Link>
      </div>

      <form @submit.prevent="submit" class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
        <div class="grid grid-cols-1 gap-4">
          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Banner Name</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              placeholder="e.g. New Arrival Banner"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Window Banner Image</label>

              <div class="overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50">
                <img
                  v-if="desktopPreview"
                  :src="desktopPreview"
                  alt="Desktop banner preview"
                  class="h-44 w-full object-cover"
                />
                <div v-else class="flex h-44 w-full items-center justify-center text-xs text-neutral-400">
                  No Desktop Image
                </div>
              </div>

              <div class="mt-3">
                <input
                  type="file"
                  accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                  @change="onDesktopImageChange"
                  class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200"
                />
                <p class="mt-1 text-xs text-neutral-500">Supported: WEBP, PNG, JPG, JPEG — max 10MB.</p>

                <p v-if="desktopClientError" class="mt-1 text-sm text-red-600">{{ desktopClientError }}</p>
                <p v-if="form.errors.desktop_image" class="mt-1 text-sm text-red-600">{{ form.errors.desktop_image }}</p>
              </div>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Mobile Banner Image</label>

              <div class="overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50">
                <img
                  v-if="mobilePreview"
                  :src="mobilePreview"
                  alt="Mobile banner preview"
                  class="h-44 w-full object-cover"
                />
                <div v-else class="flex h-44 w-full items-center justify-center text-xs text-neutral-400">
                  No Mobile Image
                </div>
              </div>

              <div class="mt-3">
                <input
                  type="file"
                  accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                  @change="onMobileImageChange"
                  class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200"
                />
                <p class="mt-1 text-xs text-neutral-500">Supported: WEBP, PNG, JPG, JPEG — max 10MB.</p>

                <p v-if="mobileClientError" class="mt-1 text-sm text-red-600">{{ mobileClientError }}</p>
                <p v-if="form.errors.mobile_image" class="mt-1 text-sm text-red-600">{{ form.errors.mobile_image }}</p>
              </div>
            </div>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Description</label>
            <textarea
              v-model="form.description"
              rows="5"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              placeholder="Short description about the home banner..."
            />
            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
          </div>
        </div>

        <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
          <Link
            :href="route('homebanners.index')"
            class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-5 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto"
          >
            Cancel
          </Link>

          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex w-full items-center justify-center rounded-full bg-[#38bdf8] px-6 py-2 text-sm font-medium text-white hover:bg-[#7dd3fc] disabled:opacity-50 sm:w-auto"
          >
            {{ form.processing ? 'Saving...' : (isEdit ? 'Update' : 'Create') }}
          </button>
        </div>

        <p v-if="isEdit" class="mt-3 text-xs text-neutral-500">
          If you do not upload a new desktop or mobile image, the existing one will remain.
        </p>
      </form>
    </div>
  </AppLayout>
</template>