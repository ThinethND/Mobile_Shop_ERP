<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

type Category = {
  id?: number
  name?: string
  description?: string | null
  status?: 'active' | 'inactive'
}

const props = defineProps<{
  mode: 'create' | 'edit'
  category?: Category | null
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.category?.id)

const form = useForm({
  name: props.category?.name ?? '',
  description: props.category?.description ?? '',
  status: props.category?.status ?? 'active',
})

function submit() {
  if (!isEdit.value) {
    form.post(route('admin.home-needs.categories.store'), { preserveScroll: true })
    return
  }

  form.put(route('admin.home-needs.categories.update', props.category!.id), { preserveScroll: true })
}
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Home Need Category' : 'Create Home Need Category'" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Home Need Category' : 'Create Home Need Category' }}</h1>
          <p class="text-sm text-neutral-500">Use clear category names so product adding stays fast and searchable.</p>
        </div>

        <Link :href="route('admin.home-needs.categories.index')" class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto">
          Back
        </Link>
      </div>

      <form @submit.prevent="submit" class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Category Name <span class="text-red-600">*</span></label>
            <input v-model="form.name" type="text" placeholder="e.g. Kitchen Essentials" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
              <span class="text-sm font-medium text-neutral-700">Active Status</span>
              <input v-model="form.status" true-value="active" false-value="inactive" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
            </label>
          </div>

          <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-neutral-700">Description</label>
            <textarea v-model="form.description" rows="4" placeholder="Optional admin note or product grouping details" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
          </div>
        </div>

        <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
          <Link :href="route('admin.home-needs.categories.index')" class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-5 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto">Cancel</Link>
          <button type="submit" :disabled="form.processing" class="inline-flex w-full items-center justify-center rounded-full bg-[#38bdf8] px-6 py-2 text-sm font-medium text-white hover:bg-[#7dd3fc] disabled:opacity-50 sm:w-auto">
            {{ form.processing ? 'Saving...' : 'Save Category' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
