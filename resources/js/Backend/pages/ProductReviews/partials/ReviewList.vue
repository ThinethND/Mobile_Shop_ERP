<template>
  <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="font-semibold text-neutral-800">Reviews</div>
    </div>

    <div @click="onTableClick">
      <DataTable
        id="productReviewsTable"
        :url="dataUrl"
        :columns="columns"
        :columnDefs="columnDefs"
        :order="[[0, 'desc']]"
        :reloadKey="reloadKey"
      >
        <template #header>
          <tr>
            <th style="width: 60px">#</th>
            <th style="width: 120px">Rating</th>
            <th style="width: 220px">Customer</th>
            <th>Short Description</th>
            <th style="width: 140px">Images</th>
            <th style="width: 160px">Created</th>
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

const props = defineProps<{
  productId: number
}>()

type ReviewRow = {
  id: number
  customer_name?: string | null
}

const dataUrl = computed(() => route('product-reviews.reviews.data', props.productId))
const reloadKey = ref<number>(0)

const columns = [
  { data: 'id', name: 'id' },
  { data: 'rating', name: 'rating' },
  { data: 'customer', name: 'customer_name', orderable: true, searchable: true },
  { data: 'short_description', name: 'short_description', orderable: false, searchable: true },
  { data: 'images', name: 'images', orderable: false, searchable: false },
  { data: 'created_at', name: 'created_at' },
  { data: 'actions', name: 'actions', orderable: false, searchable: false },
]

const columnDefs = [{ targets: [1, 2, 3, 4, 6], render: (data: any) => data }]

function onTableClick(e: MouseEvent) {
  const target = e.target as HTMLElement
  const btn = target.closest('button[data-action]') as HTMLButtonElement | null
  if (!btn) return

  e.preventDefault()
  e.stopPropagation()

  const action = btn.dataset.action
  const payload = btn.dataset.payload
  if (!action || !payload) return

  let row: ReviewRow | null = null

  try {
    row = JSON.parse(payload)
  } catch {
    row = null
  }

  if (!row) return

  if (action === 'edit') {
    router.visit(
      route('product-reviews.reviews.edit', {
        product: props.productId,
        review: row.id,
      })
    )
    return
  }

  if (action === 'delete') {
    const label = row.customer_name ? `review from "${row.customer_name}"` : `review #${row.id}`
    const ok = confirm(`Delete ${label}? This cannot be undone.`)
    if (!ok) return

    router.delete(
      route('product-reviews.reviews.destroy', {
        product: props.productId,
        review: row.id,
      }),
      {
        preserveScroll: true,
        onSuccess: () => {
          reloadKey.value = Date.now()
        },
      }
    )
  }
}
</script>
