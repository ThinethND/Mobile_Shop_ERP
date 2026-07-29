<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import { route } from 'ziggy-js'

type StoragePayload = {
  id: number
  value: number
  unit: 'GB' | 'TB'
  status: 'active' | 'inactive'
}

const props = defineProps<{
  mode: 'create' | 'edit'
  storage?: StoragePayload | null
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.storage?.id)

const form = useForm<{
  value: number | null
  unit: 'GB' | 'TB'
  status: 'active' | 'inactive'
}>({
  value: props.storage?.value ?? null,
  unit: props.storage?.unit ?? 'GB',
  status: props.storage?.status ?? 'active',
})

watch(
  () => props.storage?.id,
  () => {
    form.clearErrors()
    form.value = props.storage?.value ?? null
    form.unit = props.storage?.unit ?? 'GB'
    form.status = props.storage?.status ?? 'active'
  }
)

function submit() {
  form.clearErrors()

  if (!isEdit.value) {
    form.post(route('storage.store'), {
      preserveScroll: true,
    })
    return
  }

  form.put(route('storage.update', props.storage!.id), {
    preserveScroll: true,
  })
}
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Storage' : 'Create Storage'" />

    <div class="p-6 space-y-4">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Storage' : 'Create Storage' }}</h1>
          <p class="text-sm text-neutral-500">
            {{ isEdit ? 'Edit storage details.' : 'Create a new storage option.' }}
          </p>
        </div>

        <Link
          :href="route('storage.index')"
          class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-100 transition"
        >
          Back
        </Link>
      </div>

      <form
        @submit.prevent="submit"
        class="rounded-2xl border border-neutral-200 bg-white p-4 sm:p-6 shadow-sm"
      >
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <!-- Value -->
          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-neutral-700 mb-1">Storage Value</label>
            <input
              v-model.number="form.value"
              type="number"
              min="1"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
              placeholder="e.g. 128"
            />
            <p v-if="form.errors.value" class="mt-1 text-sm text-red-600">{{ form.errors.value }}</p>
          </div>

          <!-- Unit -->
          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-neutral-700 mb-1">Unit</label>
            <select
              v-model="form.unit"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
            >
              <option value="GB">GB</option>
              <option value="TB">TB</option>
            </select>
            <p v-if="form.errors.unit" class="mt-1 text-sm text-red-600">{{ form.errors.unit }}</p>
          </div>

          <!-- Status -->
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
        </div>

        <div class="mt-6 flex flex-col sm:flex-row gap-2 sm:justify-end">
          <Link
            :href="route('storage.index')"
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
