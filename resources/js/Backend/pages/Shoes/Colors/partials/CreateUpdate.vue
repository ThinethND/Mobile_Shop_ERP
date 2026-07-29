<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

type ColorPayload = {
  id: number
  name: string
  hex_code?: string | null
  status: 'active' | 'inactive'
}

const props = defineProps<{
  mode: 'create' | 'edit'
  color?: ColorPayload | null
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.color?.id)

const form = useForm({
  name: props.color?.name ?? '',
  hex_code: props.color?.hex_code ?? '',
  is_active: (props.color?.status ?? 'active') === 'active',
})

function submit() {
  form.clearErrors()

  const payload = (data: typeof form) => ({
    ...data,
    status: data.is_active ? 'active' : 'inactive',
  })

  if (!isEdit.value) {
    form
      .transform((data) => payload(data as typeof form))
      .post(route('admin.shoes.colors.store'), {
        preserveScroll: true,
        onFinish: () => form.transform((data) => data),
      })

    return
  }

  form
    .transform((data) => ({
      ...payload(data as typeof form),
      _method: 'PUT',
    }))
    .post(route('admin.shoes.colors.update', props.color!.id), {
      preserveScroll: true,
      onFinish: () => form.transform((data) => data),
    })
}
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Shoe Color' : 'Create Shoe Color'" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Shoe Color' : 'Create Shoe Color' }}</h1>
          <p class="text-sm text-neutral-500">
            {{ isEdit ? 'Edit shoe color details.' : 'Create a new shoe color.' }}
          </p>
        </div>

        <Link
          :href="route('admin.shoes.colors.index')"
          class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto"
        >
          Back
        </Link>
      </div>

      <form
        @submit.prevent="submit"
        class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6"
      >
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Name</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="e.g. Black"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Hex Code</label>
            <input
              v-model="form.hex_code"
              type="text"
              placeholder="e.g. #000000"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
            />
            <p v-if="form.errors.hex_code" class="mt-1 text-sm text-red-600">{{ form.errors.hex_code }}</p>
          </div>

          <div class="md:col-span-2">
            <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
              <span class="text-sm font-medium text-neutral-700">Active Status</span>
              <input v-model="form.is_active" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
            </label>
            <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
          </div>
        </div>

        <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
          <Link
            :href="route('admin.shoes.colors.index')"
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