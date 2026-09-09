<template>
  <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm">
    <div class="mb-4 flex flex-wrap gap-2">
      <button
        v-for="type in optionTypes"
        :key="type.value"
        type="button"
        class="rounded-full border px-4 py-2 text-sm font-medium transition"
        :class="currentType === type.value ? 'border-[#38bdf8] bg-[#38bdf8] text-white' : 'border-neutral-200 text-neutral-700 hover:bg-neutral-100'"
        @click="switchType(type.value)"
      >
        {{ type.label }}
      </button>
    </div>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="font-semibold text-neutral-800">{{ activeLabel }}</div>

      <button class="inline-flex items-center rounded-full border border-[#38bdf8] px-4 py-2 text-sm font-medium text-[#38bdf8] transition hover:bg-[#38bdf8] hover:text-white" type="button" @click="openCreate">
        + Add {{ singularLabel }}
      </button>
    </div>

    <div @click="onTableClick">
      <DataTable :key="tableId" :id="tableId" :url="dataUrl" :columns="columns" :columnDefs="columnDefs" :order="[[0, 'desc']]" :reloadKey="reloadKey">
        <template #header>
          <tr>
            <th style="width: 60px">#</th>
            <th>Name</th>
            <th style="width: 120px">Status</th>
            <th style="width: 180px">Actions</th>
          </tr>
        </template>
      </DataTable>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import DataTable from '@/Backend/components/Datatable.vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

type Row = { id: number; name: string }

const props = defineProps<{
  activeType: string
  optionTypes: Array<{ value: string; label: string }>
}>()

const currentType = ref(props.activeType)
const reloadKey = ref(0)
const activeLabel = computed(() => props.optionTypes.find((type) => type.value === currentType.value)?.label ?? 'Options')
const singularLabel = computed(() => activeLabel.value.replace(/s$/, ''))
const tableId = computed(() => `motorcycleOptionsTable-${currentType.value}`)
const dataUrl = computed(() => route('admin.motorcycles.options.data', { type: currentType.value }))

const columns = [
  { data: 'id', name: 'id' },
  { data: 'name', name: 'name' },
  { data: 'status_badge', name: 'status' },
  { data: 'actions', name: 'actions', orderable: false, searchable: false },
]
const columnDefs = [{ targets: [2, 3], render: (data: any) => data }]

function switchType(type: string) {
  currentType.value = type
  reloadKey.value = Date.now()
  router.visit(route('admin.motorcycles.options.index', { type }), {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  })
}

function openCreate() {
  router.visit(route('admin.motorcycles.options.create', { type: currentType.value }))
}

function onTableClick(e: MouseEvent) {
  const button = (e.target as HTMLElement).closest('button[data-action]') as HTMLButtonElement | null
  if (!button || !button.dataset.payload) return

  const row = JSON.parse(button.dataset.payload) as Row
  if (button.dataset.action === 'edit') {
    router.visit(route('admin.motorcycles.options.edit', row.id))
    return
  }

  if (button.dataset.action === 'delete' && confirm(`Delete option "${row.name}"?`)) {
    router.delete(route('admin.motorcycles.options.destroy', row.id), {
      preserveScroll: true,
      onSuccess: () => {
        reloadKey.value = Date.now()
      },
    })
  }
}
</script>
