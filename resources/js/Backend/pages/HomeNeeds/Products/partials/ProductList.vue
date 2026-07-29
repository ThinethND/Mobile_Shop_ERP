<template>
  <div class="space-y-4 rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <div class="font-semibold text-neutral-800">All Home Need Products</div>
        <p class="text-sm text-neutral-500">Filter household products by category, brand, warranty, and stock status.</p>
      </div>

      <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 lg:w-auto">
        <div>
          <label class="mb-1 block text-xs font-medium text-neutral-600">Category</label>
          <select v-model="filters.category_id" class="w-full rounded-xl border border-neutral-200 px-3 py-2 text-sm outline-none focus:border-[#38bdf8]">
            <option :value="null">All</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-neutral-600">Brand</label>
          <select v-model="filters.brand_id" class="w-full rounded-xl border border-neutral-200 px-3 py-2 text-sm outline-none focus:border-[#38bdf8]">
            <option :value="null">All</option>
            <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-neutral-600">Warranty</label>
          <select v-model="filters.warranty_option_id" class="w-full rounded-xl border border-neutral-200 px-3 py-2 text-sm outline-none focus:border-[#38bdf8]">
            <option :value="null">All</option>
            <option v-for="warranty in warranties" :key="warranty.id" :value="warranty.id">{{ warranty.name }}</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-neutral-600">Stock Status</label>
          <select v-model="filters.stock_status" class="w-full rounded-xl border border-neutral-200 px-3 py-2 text-sm outline-none focus:border-[#38bdf8]">
            <option value="">All</option>
            <option value="in_stock">In Stock</option>
            <option value="out_of_stock">Out of Stock</option>
            <option value="pre_order">Pre Order</option>
            <option value="discontinued">Discontinued</option>
          </select>
        </div>
      </div>
    </div>

    <div @click="onTableClick">
      <DataTable
        id="homeNeedProductsTable"
        :url="dataUrl"
        :columns="columns"
        :columnDefs="columnDefs"
        :order="[[0, 'desc']]"
        :reloadKey="reloadKey"
      >
        <template #header>
          <tr>
            <th style="width: 60px">#</th>
            <th>Product</th>
            <th>Details</th>
            <th style="width: 150px">Price</th>
            <th style="width: 130px">Stock Status</th>
            <th style="width: 90px">Stock</th>
            <th style="width: 110px">Status</th>
            <th style="width: 180px">Actions</th>
          </tr>
        </template>
      </DataTable>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import DataTable from '@/Backend/components/DataTable.vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

type Option = { id: number; name: string; value: number; label: string }
type Row = { id: number; name: string }

const props = defineProps<{
  categories: Option[]
  brands: Option[]
  warranties: Option[]
}>()

const filters = ref<{
  category_id: number | null
  brand_id: number | null
  warranty_option_id: number | null
  stock_status: string
}>({
  category_id: null,
  brand_id: null,
  warranty_option_id: null,
  stock_status: '',
})

const reloadKey = ref(0)

const dataUrl = computed(() => route('admin.home-needs.products.data', {
  category_id: filters.value.category_id || undefined,
  brand_id: filters.value.brand_id || undefined,
  warranty_option_id: filters.value.warranty_option_id || undefined,
  stock_status: filters.value.stock_status || undefined,
}))

watch(filters, () => {
  reloadKey.value = Date.now()
}, { deep: true })

const categories = computed(() => props.categories || [])
const brands = computed(() => props.brands || [])
const warranties = computed(() => props.warranties || [])

const columns = [
  { data: 'id', name: 'id' },
  { data: 'product_info', name: 'name' },
  { data: 'details', name: 'details', orderable: false, searchable: false },
  { data: 'price_display', name: 'price' },
  { data: 'stock_status', name: 'stock_status' },
  { data: 'stock_display', name: 'stock_quantity' },
  { data: 'status_badge', name: 'status' },
  { data: 'actions', name: 'actions', orderable: false, searchable: false },
]

const columnDefs = [{ targets: [1, 2, 3, 6, 7], render: (data: any) => data }]

function onTableClick(e: MouseEvent) {
  const button = (e.target as HTMLElement).closest('button[data-action]') as HTMLButtonElement | null
  if (!button) return

  e.preventDefault()
  const payload = button.dataset.payload
  if (!payload) return

  const row = JSON.parse(payload) as Row
  if (button.dataset.action === 'edit') {
    router.visit(route('admin.home-needs.products.edit', row.id))
    return
  }

  if (button.dataset.action === 'toggle') {
    router.patch(route('admin.home-needs.products.toggle-status', row.id), {}, {
      preserveScroll: true,
      onSuccess: () => {
        reloadKey.value = Date.now()
      },
    })
    return
  }

  if (button.dataset.action === 'delete' && confirm(`Delete home need product "${row.name}"?`)) {
    router.delete(route('admin.home-needs.products.destroy', row.id), {
      preserveScroll: true,
      onSuccess: () => {
        reloadKey.value = Date.now()
      },
    })
  }
}
</script>
