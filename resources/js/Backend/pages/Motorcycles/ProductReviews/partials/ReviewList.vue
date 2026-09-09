<template>
  <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="font-semibold text-neutral-800">All Product Reviews</div>

      <button class="inline-flex items-center rounded-full border border-[#38bdf8] px-4 py-2 text-sm font-medium text-[#38bdf8] transition hover:bg-[#38bdf8] hover:text-white" type="button" @click="openCreate">
        + Add Review
      </button>
    </div>

    <div @click="onTableClick">
      <DataTable id="motorcycleReviewsTable" :url="dataUrl" :columns="columns" :columnDefs="columnDefs" :order="[[0, 'desc']]" :reloadKey="reloadKey">
        <template #header>
          <tr>
            <th style="width: 60px">#</th>
            <th>Product</th>
            <th style="width: 100px">Rating</th>
            <th>Customer</th>
            <th>Summary</th>
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

const dataUrl = computed(() => route('admin.motorcycles.product-reviews.data'))
const reloadKey = ref(0)
const columns = [
  { data: 'id', name: 'id' },
  { data: 'product', name: 'product', orderable: false, searchable: false },
  { data: 'rating', name: 'rating' },
  { data: 'customer', name: 'customer', orderable: false },
  { data: 'summary', name: 'summary', orderable: false },
  { data: 'status_badge', name: 'status' },
  { data: 'actions', name: 'actions', orderable: false, searchable: false },
]
const columnDefs = [{ targets: [1, 2, 3, 4, 5, 6], render: (data: any) => data }]

function openCreate() {
  router.visit(route('admin.motorcycles.product-reviews.create'))
}

function onTableClick(e: MouseEvent) {
  const button = (e.target as HTMLElement).closest('button[data-action]') as HTMLButtonElement | null
  if (!button || !button.dataset.payload) return

  const row = JSON.parse(button.dataset.payload) as Row
  if (button.dataset.action === 'edit') {
    router.visit(route('admin.motorcycles.product-reviews.edit', row.id))
    return
  }

  if (button.dataset.action === 'delete' && confirm(`Delete "${row.name}"?`)) {
    router.delete(route('admin.motorcycles.product-reviews.destroy', row.id), {
      preserveScroll: true,
      onSuccess: () => {
        reloadKey.value = Date.now()
      },
    })
  }
}
</script>
