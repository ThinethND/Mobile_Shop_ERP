<template>
  <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm">
    <div class="font-semibold text-neutral-800 mb-4">All Brands</div>

    <DataTable
      id="brandsTable"
      :url="dataUrl"
      :columns="columns"
      :columnDefs="columnDefs"
      :order="[[0, 'desc']]"
      :reloadKey="reloadKey"
    >
      <template #header>
        <tr>
          <th style="width: 60px;">#</th>
          <th>Brand Name</th>
          <th style="width: 120px;">Status</th>
          <th style="width: 220px;">Actions</th>
        </tr>
      </template>
    </DataTable>
  </div>
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import DataTable from '@/Backend/components/DataTable.vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

type BrandRow = {
  id: number
  name: string
  status: 'active' | 'inactive'
}

const dataUrl = computed(() => route('brands.data'))
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

function onTableClick(e: Event) {
  const target = e.target as HTMLElement
  const btn = target.closest('button[data-action]') as HTMLButtonElement | null
  if (!btn) return

  const action = btn.dataset.action
  const id = btn.dataset.id
  const name = btn.dataset.name
  if (!action || !id) return

  if (action === 'edit') {
    router.visit(route('brands.edit', Number(id)))
    return
  }

  if (action === 'delete') {
    const ok = confirm(`Delete brand "${name || ''}"? This cannot be undone.`)
    if (!ok) return

    router.delete(route('brands.destroy', Number(id)), {
      preserveScroll: true,
      onSuccess: () => {
        reloadKey.value = Date.now()
      },
    })
  }
}

onMounted(async () => {
  await nextTick()
  document.addEventListener('click', onTableClick, true)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', onTableClick, true)
})
</script>
