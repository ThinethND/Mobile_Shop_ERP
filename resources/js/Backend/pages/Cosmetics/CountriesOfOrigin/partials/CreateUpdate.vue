<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref } from 'vue'
import { route } from 'ziggy-js'

type CountryPayload = {
  id: number
  name: string
  code?: string | null
  flag_image_url?: string | null
}

const props = defineProps<{
  mode: 'create' | 'edit'
  country?: CountryPayload | null
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.country?.id)

const previewUrl = ref<string | null>(props.country?.flag_image_url ?? null)
let objectUrl: string | null = null

const form = useForm({
  name: props.country?.name ?? '',
  code: props.country?.code ?? '',
  flag_image: null as File | null,
})

function revokeObjectUrl() {
  if (objectUrl) {
    URL.revokeObjectURL(objectUrl)
    objectUrl = null
  }
}

onBeforeUnmount(() => {
  revokeObjectUrl()
})

function onFileChange(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0] || null

  form.flag_image = file
  revokeObjectUrl()

  if (file) {
    objectUrl = URL.createObjectURL(file)
    previewUrl.value = objectUrl
    return
  }

  previewUrl.value = props.country?.flag_image_url ?? null
}

function submit() {
  form.clearErrors()

  if (!isEdit.value) {
    form.post(route('admin.cosmetics.countries-origin.store'), {
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
    .post(route('admin.cosmetics.countries-origin.update', props.country!.id), {
      forceFormData: true,
      preserveScroll: true,
      onFinish: () => form.transform((data) => data),
    })
}
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Country of Origin' : 'Create Country of Origin'" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Country of Origin' : 'Create Country of Origin' }}</h1>
          <p class="text-sm text-neutral-500">
            {{ isEdit ? 'Edit country details.' : 'Create a new country of origin entry.' }}
          </p>
        </div>

        <Link
          :href="route('admin.cosmetics.countries-origin.index')"
          class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto"
        >
          Back
        </Link>
      </div>

      <form
        @submit.prevent="submit"
        class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6"
      >
        <div class="grid grid-cols-1 gap-4">
          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Country Name</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="e.g. United States"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Code</label>
            <input
              v-model="form.code"
              type="text"
              placeholder="e.g. US"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 uppercase outline-none focus:border-[#38bdf8]"
            />
            <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Flag Image</label>

            <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
              <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50">
                <img v-if="previewUrl" :src="previewUrl" class="h-full w-full object-cover" />
                <span v-else class="text-xs text-neutral-400">No Flag</span>
              </div>

              <div class="w-full flex-1">
                <input
                  type="file"
                  accept="image/*"
                  @change="onFileChange"
                  class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200"
                />
                <p v-if="form.errors.flag_image" class="mt-1 text-sm text-red-600">{{ form.errors.flag_image }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
          <Link
            :href="route('admin.cosmetics.countries-origin.index')"
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
