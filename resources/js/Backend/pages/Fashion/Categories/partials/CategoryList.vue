<template>
  <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="font-semibold text-neutral-800">All Fashion Categories</div>

      <button
        class="inline-flex items-center rounded-full border border-[#38bdf8] px-4 py-2 text-sm font-medium text-[#38bdf8] transition hover:bg-[#38bdf8] hover:text-white"
        type="button"
        @click="openCreate"
      >
        + Add Category
      </button>
    </div>

    <div @click="onTableClick">
      <DataTable
        id="fashionCategoriesTable"
        :url="dataUrl"
        :columns="columns"
        :columnDefs="columnDefs"
        :order="[[0, 'desc']]"
        :reloadKey="reloadKey"
      >
        <template #header>
          <tr>
            <th style="width: 60px">#</th>
            <th>Category Name</th>
            <th style="width: 110px">Products</th>
            <th>Description</th>
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

const dataUrl = computed(() => route('admin.fashion.categories.data'))
const reloadKey = ref(0)

const columns = [
  { data: 'id', name: 'id' },
  { data: 'name', name: 'name' },
  { data: 'products_count', name: 'products_count', orderable: false, searchable: false },
  { data: 'description', name: 'description', orderable: false },
  { data: 'status_badge', name: 'status' },
  { data: 'actions', name: 'actions', orderable: false, searchable: false },
]

const columnDefs = [{ targets: [3, 4, 5], render: (data: any) => data }]

function openCreate() {
  router.visit(route('admin.fashion.categories.create'))
}

function onTableClick(e: MouseEvent) {
  const button = (e.target as HTMLElement).closest('button[data-action]') as HTMLButtonElement | null
  if (!button) return

  e.preventDefault()
  const payload = button.dataset.payload
  if (!payload) return

  const row = JSON.parse(payload) as Row
  if (button.dataset.action === 'edit') {
    router.visit(route('admin.fashion.categories.edit', row.id))
    return
  }

  if (button.dataset.action === 'delete' && confirm(`Delete fashion category "${row.name}"?`)) {
    router.delete(route('admin.fashion.categories.destroy', row.id), {
      preserveScroll: true,
      onSuccess: () => {
        reloadKey.value = Date.now()
      },
    })
  }
}
</script>
