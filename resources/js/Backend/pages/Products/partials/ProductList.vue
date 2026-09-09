<template>
  <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm space-y-4">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <div class="font-semibold text-neutral-800">All Electronics & Gadget Products</div>
        <p class="text-sm text-neutral-500">Filter and manage phones, watches, AirPods, analog watches, and related products.</p>
      </div>

      <!-- Filters -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 w-full lg:w-auto">
        <div>
          <label class="block text-xs font-medium text-neutral-600 mb-1">Category</label>
          <select
            v-model="filters.category_id"
            class="w-full rounded-xl border border-neutral-200 px-3 py-2 text-sm outline-none focus:border-[#38bdf8]"
          >
            <option :value="null">All</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-medium text-neutral-600 mb-1">Brand</label>
          <select
            v-model="filters.brand_id"
            class="w-full rounded-xl border border-neutral-200 px-3 py-2 text-sm outline-none focus:border-[#38bdf8]"
          >
            <option :value="null">All</option>
            <option v-for="b in brandsFiltered" :key="b.id" :value="b.id">{{ b.name }}</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-medium text-neutral-600 mb-1">Warranty Type</label>
          <select
            v-model="filters.warranty_option_id"
            class="w-full rounded-xl border border-neutral-200 px-3 py-2 text-sm outline-none focus:border-[#38bdf8]"
          >
            <option :value="null">All</option>
            <option v-for="w in warrantyOptions" :key="w.id" :value="w.id">{{ w.name }}</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-medium text-neutral-600 mb-1">Colors</label>
<MultiSelect
  id="filter_color_ids"
  label=""
  placeholder="All colors"
  :options="colorOptions"
  v-model="filters.color_ids"
  valueKey="id"
  labelKey="name"
  imageKey="image_url"
/>

        </div>
      </div>
    </div>

    <div @click="onTableClick">
      <DataTable
        id="productsTable"
        :url="dataUrl"
        :columns="columns"
        :columnDefs="columnDefs"
        :order="[[0, 'desc']]"
        :reloadKey="reloadKey"
      >
        <template #header>
          <tr>
            <th style="width: 60px;">#</th>
            <th>Product / Model</th>
            <th>Category</th>
            <th>Brand</th>
            <th style="width: 120px;">Price (LKR)</th>
            <th style="width: 120px;">Stock</th>
            <th style="width: 120px;">Status</th>
            <th style="width: 220px;">Actions</th>
          </tr>
        </template>
      </DataTable>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch, defineComponent } from 'vue'
import DataTable from '@/Backend/components/Datatable.vue'
import MultiSelect from '@/Backend/components/MultiSelect.vue'

import { router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

type Opt = { id: number; name: string }
type BrandOpt = { id: number; name: string; category_ids?: number[] }
type ColorOpt = { id: number; name: string; image_url?: string | null }
type WarrantyOpt = { id: number; name: string }

const page = usePage()

const categories = computed<Opt[]>(() => (page.props.categories as any) || [])
const brands = computed<BrandOpt[]>(() => (page.props.brands as any) || [])
const colorOptions = computed<ColorOpt[]>(() => (page.props.colors as any) || [])
const warrantyOptions = computed<WarrantyOpt[]>(() => (page.props.warranties as any) || [])

const filters = ref<{
  category_id: number | null
  brand_id: number | null
  warranty_option_id: number | null
  color_ids: number[]
}>({
  category_id: null,
  brand_id: null,
  warranty_option_id: null,
  color_ids: [],
})

const brandsFiltered = computed(() => {
  if (!filters.value.category_id) return brands.value
  return brands.value.filter(b => (b.category_ids || []).includes(filters.value.category_id!))
})

watch(
  () => filters.value.category_id,
  () => {
    // if current brand doesn't belong to selected category -> reset brand filter
    if (filters.value.brand_id) {
      const b = brands.value.find(x => x.id === filters.value.brand_id)
      if (b && !(b.category_ids || []).includes(filters.value.category_id!)) {
        filters.value.brand_id = null
      }
    }
  }
)

const dataUrl = computed(() =>
  route('products.data', {
    category_id: filters.value.category_id || undefined,
    brand_id: filters.value.brand_id || undefined,
    warranty_option_id: filters.value.warranty_option_id || undefined,
    color_ids: filters.value.color_ids.length ? filters.value.color_ids.join(',') : undefined,
  })
)

const reloadKey = ref<number>(0)

watch(filters, () => {
  reloadKey.value = Date.now()
}, { deep: true })

const columns = [
  { data: 'id', name: 'id' },
  { data: 'model', name: 'model' },
  { data: 'category_name', name: 'category_id' },
  { data: 'brand_name', name: 'brand_id' },
  { data: 'price_lkr', name: 'price_lkr' },
  { data: 'stock_badge', name: 'in_stock', orderable: false, searchable: false },
  { data: 'status_badge', name: 'status', orderable: false, searchable: false },
  { data: 'actions', name: 'actions', orderable: false, searchable: false },
]

const columnDefs = [{ targets: [5, 6, 7], render: (d: any) => d }]

function onTableClick(e: MouseEvent) {
  const target = e.target as HTMLElement
  const btn = target.closest('button[data-action]') as HTMLButtonElement | null
  if (!btn) return

  e.preventDefault()
  e.stopPropagation()

  const action = btn.dataset.action
  const id = btn.dataset.id
  const name = btn.dataset.name
  if (!action || !id) return

  if (action === 'edit') {
    router.visit(route('products.edit', Number(id)))
    return
  }

  if (action === 'toggle') {
    router.patch(route('products.toggle-status', Number(id)), {}, {
      preserveScroll: true,
      onSuccess: () => (reloadKey.value = Date.now()),
    })
    return
  }

  if (action === 'delete') {
    const ok = confirm(`Delete product "${name || ''}"? This cannot be undone.`)
    if (!ok) return

    router.delete(route('products.destroy', Number(id)), {
      preserveScroll: true,
      onSuccess: () => (reloadKey.value = Date.now()),
    })
  }
}

/** Simple color multi select (image circles + name) */
// const ColorMultiSelect = defineComponent({
//   props: {
//     options: { type: Array as any, required: true },
//     modelValue: { type: Array as any, required: true },
//     placeholder: { type: String, default: 'Select colors' },
//   },
//   emits: ['update:modelValue'],
//   setup(props, { emit }) {
//     const open = ref(false)

//     const selected = computed<number[]>({
//       get: () => props.modelValue,
//       set: (v) => emit('update:modelValue', v),
//     })

//     function toggle(id: number) {
//       const s = new Set(selected.value)
//       if (s.has(id)) s.delete(id)
//       else s.add(id)
//       selected.value = Array.from(s)
//     }

//     const selectedLabels = computed(() => {
//       const map = new Map((props.options as any[]).map(o => [o.id, o]))
//       return selected.value.map(id => map.get(id)).filter(Boolean)
//     })

//     return { open, selected, toggle, selectedLabels }
//   },
//   template: `
//     <div class="relative">
//       <button type="button"
//         class="w-full rounded-xl border border-neutral-200 px-3 py-2 text-sm text-left outline-none hover:bg-neutral-50"
//         @click="open = !open">
//         <span v-if="selected.length === 0" class="text-neutral-400">{{ placeholder }}</span>
//         <span v-else class="text-neutral-700">{{ selected.length }} selected</span>
//       </button>

//       <div v-if="open" class="absolute z-50 mt-2 w-full rounded-xl border border-neutral-200 bg-white shadow-lg p-2 max-h-64 overflow-auto">
//         <div v-for="c in options" :key="c.id"
//           class="flex items-center gap-2 px-2 py-2 rounded-lg hover:bg-neutral-50 cursor-pointer"
//           @click="toggle(c.id)">
//           <div class="h-5 w-5 rounded-full overflow-hidden border border-neutral-200 bg-neutral-50 flex items-center justify-center">
//             <img v-if="c.image_url" :src="c.image_url" class="h-full w-full object-cover" />
//           </div>
//           <div class="text-sm text-neutral-700">{{ c.name }}</div>
//           <div class="ml-auto text-xs text-neutral-500" v-if="selected.includes(c.id)">✓</div>
//         </div>
//       </div>
//     </div>
//   `,
// })
</script>
