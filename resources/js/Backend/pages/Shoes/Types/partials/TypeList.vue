<template>
  <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="font-semibold text-neutral-800">All Shoe Types</div>

      <button
        class="inline-flex items-center rounded-full border border-[#38bdf8] px-4 py-2 text-sm font-medium text-[#38bdf8] transition hover:bg-[#38bdf8] hover:text-white"
        @click="openCreate"
        type="button"
      >
        + Add Shoe Type
      </button>
    </div>

    <div @click="onTableClick">
      <DataTable
        id="shoeTypesTable"
        :url="dataUrl"
        :columns="columns"
        :columnDefs="columnDefs"
        :order="[[0, 'desc']]"
        :reloadKey="reloadKey"
      >
        <template #header>
          <tr>
            <th style="width: 60px">#</th>
            <th>Name</th>
            <th style="width: 120px">Status</th>
            <th style="width: 220px">Actions</th>
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

type TypeRow = {
  id: number
  name: string
  status: 'active' | 'inactive'
}

const dataUrl = computed(() => route('admin.shoes.types.data'))
const reloadKey = ref<number>(0)

const columns = [
  { data: 'id', name: 'id' },
  { data: 'name', name: 'name' },
  { data: 'status_badge', name: 'status', orderable: true, searchable: true },
  { data: 'actions', name: 'actions', orderable: false, searchable: false },
]

const columnDefs = [
  { targets: [2, 3], render: (data: any) => data },
]

function openCreate() {
  router.visit(route('admin.shoes.types.create'))
}

function openEdit(row: TypeRow) {
  router.visit(route('admin.shoes.types.edit', row.id))
}

function onTableClick(e: MouseEvent) {
  const target = e.target as HTMLElement
  const btn = target.closest('button[data-action]') as HTMLButtonElement | null
  if (!btn) return

  e.preventDefault()
  e.stopPropagation()

  const action = btn.dataset.action
  const payload = btn.dataset.payload
  if (!action || !payload) return

  let row: TypeRow | null = null

  try {
    row = JSON.parse(payload)
  } catch {
    row = null
  }

  if (!row) return

  if (action === 'edit') {
    openEdit(row)
    return
  }

  if (action === 'delete') {
    const ok = confirm(`Delete shoe type "${row.name}"? This cannot be undone.`)
    if (!ok) return

    router.delete(route('admin.shoes.types.destroy', row.id), {
      preserveScroll: true,
      onSuccess: () => {
        reloadKey.value = Date.now()
      },
    })
  }
}
</script>
