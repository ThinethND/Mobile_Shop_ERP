<template>
  <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm">
    <div class="mb-4 flex flex-wrap gap-2">
      <button
        v-for="tab in tabs"
        :key="tab.value || 'all'"
        type="button"
        class="rounded-full border px-4 py-2 text-sm font-medium transition"
        :class="currentType === tab.value ? 'border-[#38bdf8] bg-[#38bdf8] text-white' : 'border-neutral-200 text-neutral-700 hover:bg-neutral-100'"
        @click="switchType(tab.value)"
      >
        {{ tab.label }}
      </button>
    </div>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="font-semibold text-neutral-800">{{ currentTitle }}</div>

      <button class="inline-flex items-center rounded-full border border-[#38bdf8] px-4 py-2 text-sm font-medium text-[#38bdf8] transition hover:bg-[#38bdf8] hover:text-white" type="button" @click="openCreate">
        + {{ addButtonLabel }}
      </button>
    </div>

    <div @click="onTableClick">
      <DataTable :key="tableId" :id="tableId" :url="dataUrl" :columns="columns" :columnDefs="columnDefs" :order="[[0, 'desc']]" :reloadKey="reloadKey">
        <template #header>
          <tr>
            <th style="width: 60px">#</th>
            <th>Product</th>
            <th style="width: 150px">Product Type</th>
            <th>Details</th>
            <th style="width: 130px">Stock Status</th>
            <th style="width: 130px">Price</th>
            <th style="width: 90px">Stock</th>
            <th style="width: 110px">Status</th>
            <th style="width: 240px">Actions</th>
          </tr>
        </template>
      </DataTable>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import DataTable from '@/Backend/components/DataTable.vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

type Row = { id: number; name: string; status: string }

const props = defineProps<{
  activeType?: string | null
  productTypes: Array<{ value: string; label: string }>
}>()

const currentType = ref<string | null>(props.activeType ?? null)
const reloadKey = ref(0)
const tabs = computed(() => [{ value: null, label: 'All' }, ...props.productTypes])
const activeTypeLabel = computed(() => props.productTypes.find((type) => type.value === currentType.value)?.label ?? 'All')
const currentTitle = computed(() => (currentType.value ? `${activeTypeLabel.value} Products` : 'All Motorcycle Products'))
const addButtonLabel = computed(() => {
  if (currentType.value === 'helmet') return 'Add Helmet'
  if (currentType.value === 'helmet_accessory') return 'Add Helmet Accessory'
  if (currentType.value === 'bike_accessory') return 'Add Bike Accessory'
  if (currentType.value === 'spare_part') return 'Add Spare Part'
  return 'Add Product'
})
const tableId = computed(() => `motorcycleProductsTable-${currentType.value || 'all'}`)
const dataUrl = computed(() => currentType.value
  ? route('admin.motorcycles.products.data', { type: currentType.value })
  : route('admin.motorcycles.products.data'))

const columns = [
  { data: 'id', name: 'id' },
  { data: 'product_info', name: 'name' },
  { data: 'type', name: 'product_type' },
  { data: 'details', name: 'details', orderable: false, searchable: false },
  { data: 'stock_status', name: 'stock_status' },
  { data: 'price_display', name: 'regular_price' },
  { data: 'stock_display', name: 'stock_quantity' },
  { data: 'status_badge', name: 'status' },
  { data: 'actions', name: 'actions', orderable: false, searchable: false },
]

const columnDefs = [{ targets: [1, 3, 7, 8], render: (data: any) => data }]

function switchType(type: string | null) {
  currentType.value = type
  reloadKey.value = Date.now()
  router.visit(type ? route('admin.motorcycles.products.index', { type }) : route('admin.motorcycles.products.index'), {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  })
}

function openCreate() {
  const type = currentType.value || 'helmet'
  router.visit(route('admin.motorcycles.products.create', { type }))
}

function onTableClick(e: MouseEvent) {
  const button = (e.target as HTMLElement).closest('button[data-action]') as HTMLButtonElement | null
  if (!button || !button.dataset.payload) return

  const row = JSON.parse(button.dataset.payload) as Row
  if (button.dataset.action === 'edit') {
    router.visit(route('admin.motorcycles.products.edit', row.id))
    return
  }

  if (button.dataset.action === 'toggle') {
    router.patch(route('admin.motorcycles.products.toggle-status', row.id), {}, {
      preserveScroll: true,
      onSuccess: () => {
        reloadKey.value = Date.now()
      },
    })
    return
  }

  if (button.dataset.action === 'delete' && confirm(`Delete motorcycle product "${row.name}"?`)) {
    router.delete(route('admin.motorcycles.products.destroy', row.id), {
      preserveScroll: true,
      onSuccess: () => {
        reloadKey.value = Date.now()
      },
    })
  }
}
</script>
