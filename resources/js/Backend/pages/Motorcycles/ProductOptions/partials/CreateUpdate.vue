<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

type OptionPayload = { id?: number; type?: string; name?: string; status?: 'active' | 'inactive' }
const props = defineProps<{
  mode: 'create' | 'edit'
  option: OptionPayload
  optionTypes: Array<{ value: string; label: string }>
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.option?.id)
const form = useForm({
  type: props.option?.type ?? 'helmet_type',
  name: props.option?.name ?? '',
  status: props.option?.status ?? 'active',
})

function submit() {
  if (!isEdit.value) {
    form.post(route('admin.motorcycles.options.store'), { preserveScroll: true })
    return
  }

  form.put(route('admin.motorcycles.options.update', props.option.id), { preserveScroll: true })
}
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Product Option' : 'Create Product Option'" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Product Option' : 'Create Product Option' }}</h1>
          <p class="text-sm text-neutral-500">Keep option names short and reusable in product forms.</p>
        </div>

        <Link :href="route('admin.motorcycles.options.index', { type: form.type })" class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto">Back</Link>
      </div>

      <form @submit.prevent="submit" class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Option Type <span class="text-red-600">*</span></label>
            <select v-model="form.type" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
              <option v-for="type in optionTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
            </select>
            <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Name <span class="text-red-600">*</span></label>
            <input v-model="form.name" type="text" placeholder="e.g. Full Face" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <div class="md:col-span-2">
            <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
              <span class="text-sm font-medium text-neutral-700">Active Status</span>
              <input v-model="form.status" true-value="active" false-value="inactive" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
            </label>
          </div>
        </div>

        <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
          <Link :href="route('admin.motorcycles.options.index', { type: form.type })" class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-5 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto">Cancel</Link>
          <button type="submit" :disabled="form.processing" class="inline-flex w-full items-center justify-center rounded-full bg-[#38bdf8] px-6 py-2 text-sm font-medium text-white hover:bg-[#7dd3fc] disabled:opacity-50 sm:w-auto">
            {{ form.processing ? 'Saving...' : 'Save Option' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
