<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import SelectInputComponent from '@/Backend/components/SelectInputComponent.vue'
import axios from 'axios'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import { route } from 'ziggy-js'

type ProductTypePayload = {
  id: number
  cosmetic_category_id: number
  name: string
}

type Option = {
  id: number
  name: string
}

const props = defineProps<{
  mode: 'create' | 'edit'
  productType?: ProductTypePayload | null
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.productType?.id)
const categories = ref<Option[]>([])

const form = useForm({
  cosmetic_category_id: props.productType?.cosmetic_category_id ?? null as number | null,
  name: props.productType?.name ?? '',
})

async function fetchCategories() {
  const response = await axios.get(route('admin.cosmetics.categories.options'))
  categories.value = Array.isArray(response.data) ? response.data : []
}

function submit() {
  form.clearErrors()

  if (!isEdit.value) {
    form.post(route('admin.cosmetics.product-types.store'), {
      preserveScroll: true,
    })

    return
  }

  form
    .transform((data) => ({
      ...data,
      _method: 'PUT',
    }))
    .post(route('admin.cosmetics.product-types.update', props.productType!.id), {
      preserveScroll: true,
      onFinish: () => form.transform((data) => data),
    })
}

onMounted(() => {
  fetchCategories()
})
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Product Type' : 'Create Product Type'" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Product Type' : 'Create Product Type' }}</h1>
          <p class="text-sm text-neutral-500">
            {{ isEdit ? 'Edit product type details.' : 'Create a new product type.' }}
          </p>
        </div>

        <Link
          :href="route('admin.cosmetics.product-types.index')"
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
            <SelectInputComponent
              id="cosmetic_product_type_category_id"
              label="Cosmetic Category"
              :options="categories"
              v-model="form.cosmetic_category_id"
              :error="form.errors.cosmetic_category_id"
              :isRequired="true"
              valueKey="id"
              labelKey="name"
              placeholder="Select category"
            />
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Product Type Name</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="e.g. Face Wash"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>
        </div>

        <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
          <Link
            :href="route('admin.cosmetics.product-types.index')"
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

