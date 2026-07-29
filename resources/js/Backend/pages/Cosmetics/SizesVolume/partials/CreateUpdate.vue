<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

type SizeVolumePayload = {
  id: number
  size: string | number
  unit: string
}

const props = defineProps<{
  mode: 'create' | 'edit'
  sizeVolume?: SizeVolumePayload | null
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.sizeVolume?.id)

const unitOptions = ['ml', 'g', 'kg', 'l', 'oz']

const form = useForm({
  size: props.sizeVolume?.size ?? '',
  unit: props.sizeVolume?.unit ?? '',
})

function submit() {
  form.clearErrors()

  if (!isEdit.value) {
    form.post(route('admin.cosmetics.sizes-volume.store'), {
      preserveScroll: true,
    })

    return
  }

  form
    .transform((data) => ({
      ...data,
      _method: 'PUT',
    }))
    .post(route('admin.cosmetics.sizes-volume.update', props.sizeVolume!.id), {
      preserveScroll: true,
      onFinish: () => form.transform((data) => data),
    })
}
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Size / Volume' : 'Create Size / Volume'" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Size / Volume' : 'Create Size / Volume' }}</h1>
          <p class="text-sm text-neutral-500">
            {{ isEdit ? 'Edit size / volume details.' : 'Create a new size / volume option.' }}
          </p>
        </div>

        <Link
          :href="route('admin.cosmetics.sizes-volume.index')"
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
            <label class="mb-1 block text-sm font-medium text-neutral-700">Size</label>
            <input
              v-model="form.size"
              type="number"
              step="0.01"
              min="0"
              placeholder="e.g. 125"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
            />
            <p v-if="form.errors.size" class="mt-1 text-sm text-red-600">{{ form.errors.size }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Unit</label>
            <select
              v-model="form.unit"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
            >
              <option value="" disabled>Select unit</option>
              <option v-for="u in unitOptions" :key="u" :value="u">{{ u }}</option>
            </select>
            <p v-if="form.errors.unit" class="mt-1 text-sm text-red-600">{{ form.errors.unit }}</p>
          </div>
        </div>

        <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
          <Link
            :href="route('admin.cosmetics.sizes-volume.index')"
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

