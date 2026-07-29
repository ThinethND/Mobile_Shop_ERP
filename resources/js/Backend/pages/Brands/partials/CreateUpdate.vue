<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import MultiSelect from '@/Backend/components/MultiSelect.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref, watch, onBeforeUnmount } from 'vue'
import { route } from 'ziggy-js'

type CategoryOption = { id: number; name: string }

type BrandPayload = {
  id: number
  name: string
  status: 'active' | 'inactive'
  logo_url?: string | null
  category_ids?: number[] | null
}

const props = defineProps<{
  mode: 'create' | 'edit'
  categories: CategoryOption[]
  brand?: BrandPayload | null
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.brand?.id)

const logoPreview = ref<string | null>(props.brand?.logo_url ?? null)
let objectUrl: string | null = null

const form = useForm<{
  name: string
  status: 'active' | 'inactive'
  category_ids: number[]
  logo: File | null
}>({
  name: props.brand?.name ?? '',
  status: props.brand?.status ?? 'active',
  category_ids: props.brand?.category_ids ?? [],
  logo: null,
})

function revokeObjectUrl() {
  if (objectUrl) {
    URL.revokeObjectURL(objectUrl)
    objectUrl = null
  }
}

watch(
  () => props.brand,
  (b) => {
    form.clearErrors()
    form.name = b?.name ?? ''
    form.status = b?.status ?? 'active'
    form.category_ids = b?.category_ids ?? []
    form.logo = null

    revokeObjectUrl()
    logoPreview.value = b?.logo_url ?? null
  },
  { immediate: true, deep: true }
)

onBeforeUnmount(() => {
  revokeObjectUrl()
})

function onLogoChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0] || null

  form.logo = file

  revokeObjectUrl()

  if (file) {
    objectUrl = URL.createObjectURL(file)
    logoPreview.value = objectUrl
  } else {
    logoPreview.value = props.brand?.logo_url ?? null
  }
}

function submit() {
  form.clearErrors()

  if (!isEdit.value) {
    form
      .transform((data) => ({
        ...data,
        category_ids: Array.isArray(data.category_ids) ? data.category_ids : [],
      }))
      .post(route('brands.store'), {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => form.transform((d) => d),
      })

    return
  }

  form
    .transform((data) => ({
      ...data,
      _method: 'PUT',
      category_ids: Array.isArray(data.category_ids) ? data.category_ids : [],
    }))
    .post(route('brands.update', props.brand!.id), {
      forceFormData: true,
      preserveScroll: true,
      onFinish: () => form.transform((d) => d),
    })
}
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Brand' : 'Create Brand'" />

    <div class="p-6 space-y-4">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">
            {{ isEdit ? 'Update Brand' : 'Create Brand' }}
          </h1>
          <p class="text-sm text-neutral-500">
            {{ isEdit ? 'Edit brand details.' : 'Create a new brand.' }}
          </p>
        </div>

        <Link
          :href="route('brands.index')"
          class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-100 transition"
        >
          Back
        </Link>
      </div>

      <form
        @submit.prevent="submit"
        class="rounded-2xl border border-neutral-200 bg-white p-4 sm:p-6 shadow-sm"
      >
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-neutral-700 mb-1">
              Brand Name
            </label>
            <input
              v-model="form.name"
              type="text"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              placeholder="e.g. Apple"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
              {{ form.errors.name }}
            </p>
          </div>

          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-neutral-700 mb-1">
              Status
            </label>
            <select
              v-model="form.status"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
            <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">
              {{ form.errors.status }}
            </p>
          </div>

          <div class="md:col-span-2">
            <MultiSelect
              id="brandCategories"
              label="Brand Categories (Optional)"
              placeholder="Select categories"
              :options="categories"
              v-model="form.category_ids"
              valueKey="id"
              labelKey="name"
              :error="form.errors.category_ids"
            />
            <p class="mt-1 text-xs text-neutral-500">
              You can leave this empty and save the brand without categories.
            </p>
            <p v-if="form.errors.category_ids" class="mt-1 text-sm text-red-600">
              {{ form.errors.category_ids }}
            </p>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-neutral-700 mb-1">
              Brand Logo
            </label>

            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
              <div
                class="h-20 w-20 rounded-xl border border-neutral-200 overflow-hidden bg-neutral-50 flex items-center justify-center"
              >
                <img
                  v-if="logoPreview"
                  :src="logoPreview"
                  class="h-full w-full object-cover"
                />
                <span v-else class="text-xs text-neutral-400">No Logo</span>
              </div>

              <div class="flex-1 w-full">
                <input
                  type="file"
                  accept="image/*"
                  @change="onLogoChange"
                  class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200"
                />
                <p class="text-xs text-neutral-500 mt-1">
                  JPG/PNG/WebP, up to 2MB recommended.
                </p>
                <p v-if="form.errors.logo" class="mt-1 text-sm text-red-600">
                  {{ form.errors.logo }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 flex flex-col sm:flex-row gap-2 sm:justify-end">
          <Link
            :href="route('brands.index')"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-neutral-200 px-5 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-100 transition"
          >
            Cancel
          </Link>

          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-[#38bdf8] px-6 py-2 text-sm font-medium text-white hover:bg-[#7dd3fc] disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : (isEdit ? 'Update' : 'Create') }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>