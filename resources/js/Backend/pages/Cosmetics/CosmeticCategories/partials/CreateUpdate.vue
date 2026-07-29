<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import axios from 'axios'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { route } from 'ziggy-js'

type CategoryPayload = {
  id: number
  name: string
  slug: string
}

const props = defineProps<{
  mode: 'create' | 'edit'
  category?: CategoryPayload | null
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.category?.id)

const slugTouched = ref(false)
const isGeneratingSlug = ref(false)

const form = useForm({
  name: props.category?.name ?? '',
  slug: props.category?.slug ?? '',
})

function slugify(value: string) {
  return value
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
}

watch(
  () => form.name,
  (value) => {
    if (!slugTouched.value || !form.slug) {
      form.slug = slugify(value)
    }
  }
)

function onSlugInput() {
  slugTouched.value = true
}

async function generateSlug() {
  if (!form.name) return

  isGeneratingSlug.value = true

  try {
    const response = await axios.get(route('admin.cosmetics.categories.generate-slug'), {
      params: {
        name: form.name,
        ignore_id: isEdit.value ? props.category!.id : undefined,
      },
    })

    form.slug = response.data?.slug ?? form.slug
    slugTouched.value = true
  } finally {
    isGeneratingSlug.value = false
  }
}

function submit() {
  form.clearErrors()

  if (!isEdit.value) {
    form.post(route('admin.cosmetics.categories.store'), {
      preserveScroll: true,
    })

    return
  }

  form
    .transform((data) => ({
      ...data,
      _method: 'PUT',
    }))
    .post(route('admin.cosmetics.categories.update', props.category!.id), {
      preserveScroll: true,
      onFinish: () => form.transform((data) => data),
    })
}
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Cosmetic Category' : 'Create Cosmetic Category'" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Cosmetic Category' : 'Create Cosmetic Category' }}</h1>
          <p class="text-sm text-neutral-500">
            {{ isEdit ? 'Edit cosmetic category details.' : 'Create a new cosmetic category.' }}
          </p>
        </div>

        <Link
          :href="route('admin.cosmetics.categories.index')"
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
            <label class="mb-1 block text-sm font-medium text-neutral-700">Category Name</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="e.g. Skincare"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Slug</label>
            <div class="flex gap-2">
              <input
                v-model="form.slug"
                type="text"
                placeholder="e.g. skincare"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                @input="onSlugInput"
              />
              <button
                type="button"
                @click="generateSlug"
                :disabled="isGeneratingSlug"
                class="shrink-0 rounded-xl border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-100 disabled:opacity-50"
              >
                {{ isGeneratingSlug ? 'Generating...' : 'Generate' }}
              </button>
            </div>
            <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
          </div>
        </div>

        <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
          <Link
            :href="route('admin.cosmetics.categories.index')"
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

