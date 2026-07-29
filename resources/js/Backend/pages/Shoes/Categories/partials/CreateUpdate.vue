<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref } from 'vue'
import { route } from 'ziggy-js'

type CategoryPayload = {
  id: number
  name: string
  status: 'active' | 'inactive'
  image_url?: string | null
}

const props = defineProps<{
  mode: 'create' | 'edit'
  category?: CategoryPayload | null
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.category?.id)

const imagePreview = ref<string | null>(props.category?.image_url ?? null)
let imageObjectUrl: string | null = null

const form = useForm({
  name: props.category?.name ?? '',
  is_active: (props.category?.status ?? 'active') === 'active',
  image: null as File | null,
})

function revokeImageObjectUrl() {
  if (imageObjectUrl) {
    URL.revokeObjectURL(imageObjectUrl)
    imageObjectUrl = null
  }
}

function onImageChange(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0] || null

  form.image = file
  revokeImageObjectUrl()

  if (file) {
    imageObjectUrl = URL.createObjectURL(file)
    imagePreview.value = imageObjectUrl
    return
  }

  imagePreview.value = props.category?.image_url ?? null
}

function submit() {
  form.clearErrors()

  const payload = (data: any) => ({
    ...data,
    status: data.is_active ? 'active' : 'inactive',
  })

  if (!isEdit.value) {
    form
      .transform((data) => payload(data))
      .post(route('admin.shoes.categories.store'), {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => form.transform((data) => data),
      })

    return
  }

  form
    .transform((data) => ({
      ...payload(data),
      _method: 'PUT',
    }))
    .post(route('admin.shoes.categories.update', props.category!.id), {
      forceFormData: true,
      preserveScroll: true,
      onFinish: () => form.transform((data) => data),
    })
}

onBeforeUnmount(() => {
  revokeImageObjectUrl()
})
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Shoe Category' : 'Create Shoe Category'" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Shoe Category' : 'Create Shoe Category' }}</h1>
          <p class="text-sm text-neutral-500">
            {{ isEdit ? 'Edit shoe category details.' : 'Create a new shoe category.' }}
          </p>
        </div>

        <Link
          :href="route('admin.shoes.categories.index')"
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
            <label class="mb-1 block text-sm font-medium text-neutral-700">Name</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="e.g. Men"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Category Image</label>

            <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
              <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50">
                <img v-if="imagePreview" :src="imagePreview" class="h-full w-full object-cover" />
                <span v-else class="text-xs text-neutral-400">No Image</span>
              </div>

              <div class="w-full flex-1">
                <input
                  type="file"
                  accept="image/*"
                  @change="onImageChange"
                  class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200"
                />
                <p v-if="form.errors.image" class="mt-1 text-sm text-red-600">{{ form.errors.image }}</p>
              </div>
            </div>
          </div>

          <div>
            <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
              <span class="text-sm font-medium text-neutral-700">Active Status</span>
              <input v-model="form.is_active" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
            </label>
            <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
          </div>
        </div>

        <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
          <Link
            :href="route('admin.shoes.categories.index')"
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
