<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import { route } from 'ziggy-js'

type ColorPayload = {
  id: number
  name: string
  status: 'active' | 'inactive'
  color_code?: string | null
  image_url?: string | null
}

const props = defineProps<{
  mode: 'create' | 'edit'
  color?: ColorPayload | null
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.color?.id)

const form = useForm<{
  name: string
  status: 'active' | 'inactive'
  color_code: string
}>({
  name: props.color?.name ?? '',
  status: props.color?.status ?? 'active',
  color_code: props.color?.color_code ?? '#000000',
})

watch(
  () => props.color,
  (c) => {
    form.clearErrors()
    form.name = c?.name ?? ''
    form.status = c?.status ?? 'active'
    form.color_code = c?.color_code ?? '#000000'
  },
  { immediate: true, deep: true }
)

const previewColor = computed(() => {
  const value = String(form.color_code || '').trim()
  const isValid = /^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/.test(value)
  return isValid ? value : '#d4d4d8'
})

function normalizeColorCode(value: string) {
  const trimmed = String(value || '').trim()
  if (!trimmed) return '#000000'

  const withHash = trimmed.startsWith('#') ? trimmed : `#${trimmed}`
  return withHash.toUpperCase()
}

function onColorCodeInput() {
  form.color_code = normalizeColorCode(form.color_code)
}

function submit() {
  form.clearErrors()
  form.color_code = normalizeColorCode(form.color_code)

  if (!isEdit.value) {
    form.post(route('colors.store'), {
      preserveScroll: true,
    })
    return
  }

  form
    .transform((data) => ({ ...data, _method: 'PUT' }))
    .post(route('colors.update', props.color!.id), {
      preserveScroll: true,
      onFinish: () => form.transform((d) => d),
    })
}
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Color' : 'Create Color'" />

    <div class="p-6 space-y-4">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Color' : 'Create Color' }}</h1>
          <p class="text-sm text-neutral-500">
            {{ isEdit ? 'Edit color details.' : 'Create a new color.' }}
          </p>
        </div>

        <Link
          :href="route('colors.index')"
          class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-100 transition"
        >
          Back
        </Link>
      </div>

      <form @submit.prevent="submit" class="rounded-2xl border border-neutral-200 bg-white p-4 sm:p-6 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-neutral-700 mb-1">Color Name</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              placeholder="e.g. Midnight Black"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-neutral-700 mb-1">Status</label>
            <select
              v-model="form.status"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
            <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-neutral-700 mb-1">Color Code</label>

            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
              <div
                class="h-20 w-20 rounded-full border border-neutral-300 shrink-0"
                :style="{ backgroundColor: previewColor }"
              />

              <div class="flex-1 w-full">
                <input
                  v-model="form.color_code"
                  type="text"
                  @blur="onColorCodeInput"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                  placeholder="#000000"
                />

                <p class="text-xs text-neutral-500 mt-2">
                  Enter hex color code like #000000, #FFFFFF, #EF4444
                </p>
                <p v-if="form.errors.color_code" class="mt-1 text-sm text-red-600">{{ form.errors.color_code }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 flex flex-col sm:flex-row gap-2 sm:justify-end">
          <Link
            :href="route('colors.index')"
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