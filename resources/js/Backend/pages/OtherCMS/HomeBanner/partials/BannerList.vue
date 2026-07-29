<template>
  <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm">
    <div class="mb-4 font-semibold text-neutral-800">All Home Banners</div>

    <div @click="onTableClick">
      <DataTable
        id="homeBannerTable"
        :url="dataUrl"
        :columns="columns"
        :columnDefs="columnDefs"
        :order="[[0, 'desc']]"
        :reloadKey="reloadKey"
      >
        <template #header>
          <tr>
            <th style="width: 60px;">#</th>
            <th>Banner Name</th>
            <th style="width: 180px;">Desktop Image</th>
            <th style="width: 180px;">Mobile Image</th>
            <th>Description</th>
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

const dataUrl = computed(() => route('homebanners.data'))
const reloadKey = ref<number>(0)

const columns = [
  { data: 'id', name: 'id' },
  { data: 'name', name: 'name' },
  { data: 'desktop_image_url', name: 'desktop_image_path', orderable: false, searchable: false },
  { data: 'mobile_image_url', name: 'mobile_image_path', orderable: false, searchable: false },
  { data: 'description', name: 'description' },
  { data: 'actions', name: 'actions', orderable: false, searchable: false },
]

const columnDefs = [
  {
    targets: 2,
    render: (d: any) => {
      if (!d) return '<span class="text-xs text-neutral-400">No Image</span>'

      return `
        <img
          src="${d}"
          alt="Desktop Banner"
          class="h-16 w-28 rounded-lg border border-neutral-200 bg-neutral-100 object-cover"
        />
      `
    },
  },
  {
    targets: 3,
    render: (d: any) => {
      if (!d) return '<span class="text-xs text-neutral-400">No Image</span>'

      return `
        <img
          src="${d}"
          alt="Mobile Banner"
          class="h-16 w-28 rounded-lg border border-neutral-200 bg-neutral-100 object-cover"
        />
      `
    },
  },
  { targets: 5, render: (d: any) => d },
]

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
    router.visit(route('homebanners.edit', Number(id)))
    return
  }

  if (action === 'delete') {
    const ok = confirm(`Delete home banner "${name || ''}"? This cannot be undone.`)
    if (!ok) return

    router.delete(route('homebanners.destroy', Number(id)), {
      preserveScroll: true,
      onSuccess: () => (reloadKey.value = Date.now()),
    })
  }
}
</script>