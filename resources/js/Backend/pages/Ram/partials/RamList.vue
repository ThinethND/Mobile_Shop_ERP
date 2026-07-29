<template>
  <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm">
    <div class="font-semibold text-neutral-800 mb-4">All RAM Options</div>

    <div @click="onTableClick">
      <DataTable
        id="ramTable"
        :url="dataUrl"
        :columns="columns"
        :columnDefs="columnDefs"
        :order="[[0, 'desc']]"
        :reloadKey="reloadKey"
      >
        <template #header>
          <tr>
            <th style="width: 60px;">#</th>
            <th>RAM</th>
            <th style="width: 120px;">Status</th>
            <th style="width: 220px;">Actions</th>
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

const dataUrl = computed(() => route('ram.data'))
const reloadKey = ref<number>(0)

const columns = [
  { data: 'id', name: 'id' },
  { data: 'ram_label', name: 'value' },
  { data: 'status_badge', name: 'status', orderable: true, searchable: true },
  { data: 'actions', name: 'actions', orderable: false, searchable: false },
]

const columnDefs = [
  { targets: [2, 3], render: (data: any) => data },
]

function onTableClick(e: MouseEvent) {
  const target = e.target as HTMLElement
  const btn = target.closest('button[data-action]') as HTMLButtonElement | null
  if (!btn) return

  e.preventDefault()
  e.stopPropagation()

  const action = btn.dataset.action
  const id = btn.dataset.id
  const label = btn.dataset.label
  if (!action || !id) return

  if (action === 'edit') {
    router.visit(route('ram.edit', Number(id)))
    return
  }

  if (action === 'delete') {
    const ok = confirm(`Delete RAM "${label || ''}"? This cannot be undone.`)
    if (!ok) return

    router.delete(route('ram.destroy', Number(id)), {
      preserveScroll: true,
      onSuccess: () => (reloadKey.value = Date.now()),
    })
  }
}
</script>
