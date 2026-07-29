<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

type BikeBrand = { id: number; name: string }
type BikeModel = {
  id?: number
  bike_brand_id?: number | null
  bike_brand_name?: string | null
  name?: string
  start_year?: number | null
  end_year?: number | null
  engine_cc?: number | null
  status?: 'active' | 'inactive'
}

const props = defineProps<{ mode: 'create' | 'edit'; bikeModel?: BikeModel | null; bikeBrands: BikeBrand[] }>()
const isEdit = computed(() => props.mode === 'edit' && !!props.bikeModel?.id)

const form = useForm({
  bike_brand_id: props.bikeModel?.bike_brand_id ?? '',
  bike_brand_name: props.bikeModel?.bike_brand_name ?? '',
  name: props.bikeModel?.name ?? '',
  start_year: props.bikeModel?.start_year ?? '',
  end_year: props.bikeModel?.end_year ?? '',
  engine_cc: props.bikeModel?.engine_cc ?? '',
  status: props.bikeModel?.status ?? 'active',
})

function submit() {
  if (!isEdit.value) {
    form.post(route('admin.motorcycles.bike-models.store'), { preserveScroll: true })
    return
  }

  form.put(route('admin.motorcycles.bike-models.update', props.bikeModel!.id), { preserveScroll: true })
}
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Bike Brand & Model' : 'Create Bike Brand & Model'" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Bike Brand & Model' : 'Create Bike Brand & Model' }}</h1>
          <p class="text-sm text-neutral-500">These entries are for product fitment only, not product brands.</p>
        </div>

        <Link :href="route('admin.motorcycles.bike-models.index')" class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto">Back</Link>
      </div>

      <form @submit.prevent="submit" class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Bike Brand <span class="text-red-600">*</span></label>
            <select v-model="form.bike_brand_id" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]">
              <option value="">Add new brand below</option>
              <option v-for="brand in bikeBrands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
            </select>
            <p class="mt-1 text-xs text-neutral-500">Choose an existing brand, or leave empty and type a new brand name.</p>
            <p v-if="form.errors.bike_brand_id" class="mt-1 text-sm text-red-600">{{ form.errors.bike_brand_id }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">New Bike Brand</label>
            <input v-model="form.bike_brand_name" :disabled="!!form.bike_brand_id" type="text" placeholder="e.g. Yamaha" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8] disabled:bg-neutral-100" />
            <p v-if="form.errors.bike_brand_name" class="mt-1 text-sm text-red-600">{{ form.errors.bike_brand_name }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Bike Model <span class="text-red-600">*</span></label>
            <input v-model="form.name" type="text" placeholder="e.g. FZ" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Engine CC</label>
            <input v-model="form.engine_cc" type="number" min="1" placeholder="e.g. 150" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            <p v-if="form.errors.engine_cc" class="mt-1 text-sm text-red-600">{{ form.errors.engine_cc }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Start Year</label>
            <input v-model="form.start_year" type="number" min="1950" placeholder="2018" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            <p v-if="form.errors.start_year" class="mt-1 text-sm text-red-600">{{ form.errors.start_year }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">End Year</label>
            <input v-model="form.end_year" type="number" min="1950" placeholder="2024" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]" />
            <p v-if="form.errors.end_year" class="mt-1 text-sm text-red-600">{{ form.errors.end_year }}</p>
          </div>

          <div class="md:col-span-2">
            <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
              <span class="text-sm font-medium text-neutral-700">Active Status</span>
              <input v-model="form.status" true-value="active" false-value="inactive" type="checkbox" class="h-4 w-4 accent-[#38bdf8]" />
            </label>
          </div>
        </div>

        <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
          <Link :href="route('admin.motorcycles.bike-models.index')" class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-5 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto">Cancel</Link>
          <button type="submit" :disabled="form.processing" class="inline-flex w-full items-center justify-center rounded-full bg-[#38bdf8] px-6 py-2 text-sm font-medium text-white hover:bg-[#7dd3fc] disabled:opacity-50 sm:w-auto">
            {{ form.processing ? 'Saving...' : 'Save Bike Compatibility' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
