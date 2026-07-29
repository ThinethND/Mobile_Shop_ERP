<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref } from 'vue'
import { route } from 'ziggy-js'

type Brand = {
  id?: number
  name?: string
  description?: string | null
  status?: 'active' | 'inactive'
  logo_url?: string | null
}

const props = defineProps<{
  mode: 'create' | 'edit'
  brand?: Brand | null
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.brand?.id)
const logoPreview = ref<string | null>(props.brand?.logo_url ?? null)
let logoObjectUrl: string | null = null

const form = useForm({
  name: props.brand?.name ?? '',
  description: props.brand?.description ?? '',
  status: props.brand?.status ?? 'active',
  logo: null as File | null,
})

onBeforeUnmount(() => {
  if (logoObjectUrl) {
    URL.revokeObjectURL(logoObjectUrl)
  }
})

function onLogoChange(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0] || null
  form.logo = file

  if (logoObjectUrl) {
    URL.revokeObjectURL(logoObjectUrl)
    logoObjectUrl = null
  }

  if (file) {
    logoObjectUrl = URL.createObjectURL(file)
    logoPreview.value = logoObjectUrl
    return
  }

  logoPreview.value = props.brand?.logo_url ?? null
}

function submit() {
  if (!isEdit.value) {
    form.post(route('admin.fashion.brands.store'), {
      forceFormData: true,
      preserveScroll: true,
    })
    return
  }

  form
    .transform((data) => ({ ...data, _method: 'PUT' }))
    .post(route('admin.fashion.brands.update', props.brand!.id), {
      forceFormData: true,
      preserveScroll: true,
      onFinish: () => form.transform((data) => data),
    })
}
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Fashion Brand' : 'Create Fashion Brand'" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Fashion Brand' : 'Create Fashion Brand' }}</h1>
          <p class="text-sm text-neutral-500">Add fashion labels, suppliers, or house brands for product selection.</p>
        </div>

        <Link :href="route('admin.fashion.brands.index')" class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto">
          Back
        </Link>
      </div>

      <form @submit.prevent="submit" class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Brand Name <span class="text-red-600">*</span></label>
            <input v-model="form.name" type="text" placeholder="e.g. Luxe Lane" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
              <span class="text-sm font-medium text-neutral-700">Active Status</span>
              <input v-model="form.status" true-value="active" false-value="inactive" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
            </label>
            <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
          </div>

          <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-neutral-700">Brand Logo</label>
            <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
              <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50">
                <img v-if="logoPreview" :src="logoPreview" class="h-full w-full object-cover" />
                <span v-else class="text-xs text-neutral-400">No Logo</span>
              </div>

              <div class="w-full flex-1">
                <input type="file" accept="image/*" @change="onLogoChange" class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200" />
                <p v-if="form.errors.logo" class="mt-1 text-sm text-red-600">{{ form.errors.logo }}</p>
              </div>
            </div>
          </div>

          <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-neutral-700">Description</label>
            <textarea v-model="form.description" rows="4" placeholder="Optional brand note" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
          </div>
        </div>

        <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
          <Link :href="route('admin.fashion.brands.index')" class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-5 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto">Cancel</Link>
          <button type="submit" :disabled="form.processing" class="inline-flex w-full items-center justify-center rounded-full bg-[#38bdf8] px-6 py-2 text-sm font-medium text-white hover:bg-[#7dd3fc] disabled:opacity-50 sm:w-auto">
            {{ form.processing ? 'Saving...' : 'Save Brand' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
