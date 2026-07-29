<template>
  <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="font-semibold text-neutral-800">All Bike Compatibility Entries</div>

      <button class="inline-flex items-center rounded-full border border-[#38bdf8] px-4 py-2 text-sm font-medium text-[#38bdf8] transition hover:bg-[#38bdf8] hover:text-white" type="button" @click="openCreate">
        + Add Bike Brand & Model
      </button>
    </div>

    <div @click="onTableClick">
      <DataTable id="motorcycleBikeModelsTable" :url="dataUrl" :columns="columns" :columnDefs="columnDefs" :order="[[0, 'desc']]" :reloadKey="reloadKey">
        <template #header>
          <tr>
            <th style="width: 60px">#</th>
            <th>Bike Brand</th>
            <th>Bike Model</th>
            <th style="width: 140px">Year Range</th>
            <th style="width: 110px">Engine CC</th>
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
import DataTable from '@/Backend/components/DataTable.vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

type Row = { id: number; name: string }

const dataUrl = computed(() => route('admin.motorcycles.bike-models.data'))
const reloadKey = ref(0)
const columns = [
  { data: 'id', name: 'id' },
  { data: 'brand', name: 'brand', orderable: false },
  { data: 'model', name: 'name' },
  { data: 'year_range', name: 'start_year' },
  { data: 'engine_cc', name: 'engine_cc' },
  { data: 'status_badge', name: 'status' },
  { data: 'actions', name: 'actions', orderable: false, searchable: false },
]
const columnDefs = [{ targets: [3, 4, 5, 6], render: (data: any) => data }]

function openCreate() {
  router.visit(route('admin.motorcycles.bike-models.create'))
}

function onTableClick(e: MouseEvent) {
  const button = (e.target as HTMLElement).closest('button[data-action]') as HTMLButtonElement | null
  if (!button || !button.dataset.payload) return

  const row = JSON.parse(button.dataset.payload) as Row
  if (button.dataset.action === 'edit') {
    router.visit(route('admin.motorcycles.bike-models.edit', row.id))
    return
  }

  if (button.dataset.action === 'delete' && confirm(`Delete bike compatibility "${row.name}"?`)) {
    router.delete(route('admin.motorcycles.bike-models.destroy', row.id), {
      preserveScroll: true,
      onSuccess: () => {
        reloadKey.value = Date.now()
      },
    })
  }
}
</script>
