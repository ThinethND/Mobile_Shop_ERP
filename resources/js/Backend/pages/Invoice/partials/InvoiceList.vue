<template>
  <div class="space-y-4 rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <div class="font-semibold text-neutral-800">All Orders</div>
        <div class="text-sm text-neutral-500">
          Search and filter orders instantly, then update order status or open actions from the dropdowns.
        </div>
      </div>

      <button
        class="inline-flex items-center rounded-full border border-[#38bdf8] px-4 py-2 text-sm font-medium text-[#38bdf8] transition hover:bg-[#38bdf8] hover:text-white"
        @click="openCreate"
        type="button"
      >
        + New Order
      </button>
    </div>

    <div
      v-if="flashMessage"
      :class="flashMessage.type === 'success'
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
        : 'border-rose-200 bg-rose-50 text-rose-700'"
      class="rounded-2xl border px-4 py-3 text-sm"
    >
      {{ flashMessage.text }}
    </div>

    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
      <div class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_220px_220px_auto]">
        <div>
          <label class="mb-1 block text-sm font-medium text-neutral-700">Search</label>
          <input
            v-model="filters.q"
            type="text"
            placeholder="Search order no / customer name / contact"
            class="w-full rounded-xl border border-neutral-200 bg-white px-4 py-2.5 outline-none transition focus:border-slate-900"
          />
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-neutral-700">Order Status</label>
          <select
            v-model="filters.order_status"
            class="w-full rounded-xl border border-neutral-200 bg-white px-4 py-2.5 outline-none transition focus:border-slate-900"
          >
            <option value="">All statuses</option>
            <option value="reserved">Reserved</option>
            <option value="confirmed">Confirmed</option>
            <option value="dispatched">Dispatched</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-neutral-700">Payment</label>
          <select
            v-model="filters.payment_type"
            class="w-full rounded-xl border border-neutral-200 bg-white px-4 py-2.5 outline-none transition focus:border-slate-900"
          >
            <option value="">All payments</option>
            <option value="unpaid">Unpaid</option>
            <option value="mixed">Mixed</option>
            <option value="cash">Cash</option>
            <option value="card">Card</option>
            <option value="advance">Advance</option>
          </select>
        </div>

        <div class="flex items-end">
          <button
            type="button"
            class="inline-flex h-[46px] w-full items-center justify-center rounded-full border border-neutral-200 px-5 text-sm font-semibold text-neutral-700 transition hover:bg-neutral-100"
            @click="resetFilters"
          >
            Reset
          </button>
        </div>
      </div>
    </div>

    <div class="relative overflow-hidden rounded-2xl border border-neutral-200">
      <div
        v-if="tableLoading"
        class="absolute inset-0 z-10 flex items-center justify-center bg-white/75 backdrop-blur-[1px]"
      >
        <div class="flex items-center gap-3 rounded-2xl border border-neutral-200 bg-white px-5 py-3 shadow-sm">
          <svg class="h-5 w-5 animate-spin text-slate-700" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
          </svg>
          <span class="text-sm font-medium text-slate-700">Loading orders...</span>
        </div>
      </div>

      <div
        v-if="statusUpdateLoading"
        class="absolute inset-0 z-20 flex items-center justify-center bg-white/80 backdrop-blur-[1px]"
      >
        <div class="flex min-w-[280px] items-center gap-3 rounded-2xl border border-blue-200 bg-white px-5 py-4 shadow-lg">
          <svg class="h-5 w-5 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
          </svg>
          <div>
            <div class="text-sm font-semibold text-slate-800">Updating order status</div>
            <div class="text-xs text-slate-500">Please wait while the order updates and email is sent.</div>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto" @click="onTableClick">
        <table class="min-w-full border-collapse text-sm">
          <thead class="bg-neutral-50">
            <tr>
              <th class="border-b border-neutral-200 px-4 py-3 text-left font-semibold text-neutral-700">#</th>
              <th class="border-b border-neutral-200 px-4 py-3 text-left font-semibold text-neutral-700">Order No</th>
              <th class="border-b border-neutral-200 px-4 py-3 text-left font-semibold text-neutral-700">Date</th>
              <th class="border-b border-neutral-200 px-4 py-3 text-left font-semibold text-neutral-700">Customer</th>
              <th class="border-b border-neutral-200 px-4 py-3 text-left font-semibold text-neutral-700">Contact</th>
              <th class="border-b border-neutral-200 px-4 py-3 text-left font-semibold text-neutral-700">Sales Person</th>
              <th class="border-b border-neutral-200 px-4 py-3 text-left font-semibold text-neutral-700">Order Status</th>
              <th class="border-b border-neutral-200 px-4 py-3 text-left font-semibold text-neutral-700">Invoice Status</th>
              <th class="border-b border-neutral-200 px-4 py-3 text-left font-semibold text-neutral-700">Payment</th>
              <th class="border-b border-neutral-200 px-4 py-3 text-right font-semibold text-neutral-700">Total</th>
              <th class="border-b border-neutral-200 px-4 py-3 text-right font-semibold text-neutral-700">Paid</th>
              <th class="border-b border-neutral-200 px-4 py-3 text-right font-semibold text-neutral-700">Balance</th>
              <th class="border-b border-neutral-200 px-4 py-3 text-left font-semibold text-neutral-700">Actions</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="!rows.length && !tableLoading">
              <td colspan="13" class="px-4 py-10 text-center text-sm text-neutral-500">
                No orders found.
              </td>
            </tr>

            <tr
              v-for="row in rows"
              :key="row.id"
              class="odd:bg-white even:bg-neutral-50/50"
            >
              <td class="border-b border-neutral-100 px-4 py-3 text-neutral-700">{{ row.id }}</td>
              <td class="border-b border-neutral-100 px-4 py-3 font-medium text-neutral-800">{{ row.invoice_no }}</td>
              <td class="border-b border-neutral-100 px-4 py-3 text-neutral-700">{{ row.invoice_date || '-' }}</td>
              <td class="border-b border-neutral-100 px-4 py-3 text-neutral-800">{{ row.customer_name }}</td>
              <td class="border-b border-neutral-100 px-4 py-3 text-neutral-700">{{ row.customer_contact_number }}</td>
              <td class="border-b border-neutral-100 px-4 py-3 text-neutral-700">{{ row.sales_person }}</td>
              <td class="border-b border-neutral-100 px-4 py-3" v-html="row.order_status_dropdown" />
              <td class="border-b border-neutral-100 px-4 py-3" v-html="row.status_badge" />
              <td class="border-b border-neutral-100 px-4 py-3" v-html="row.payment_type_badge" />
              <td class="border-b border-neutral-100 px-4 py-3 text-right text-neutral-800">{{ row.grand_total }}</td>
              <td class="border-b border-neutral-100 px-4 py-3 text-right text-neutral-800">{{ row.paid_amount }}</td>
              <td class="border-b border-neutral-100 px-4 py-3 text-right text-neutral-800">{{ row.balance_due }}</td>
              <td class="border-b border-neutral-100 px-4 py-3" v-html="row.actions_dropdown" />
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="flex flex-col gap-3 rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="text-sm text-neutral-600">
        Showing
        <span class="font-semibold text-neutral-800">{{ pageStart }}</span>
        to
        <span class="font-semibold text-neutral-800">{{ pageEnd }}</span>
        of
        <span class="font-semibold text-neutral-800">{{ recordsFiltered }}</span>
        orders
      </div>

      <div class="flex items-center gap-2">
        <button
          type="button"
          class="inline-flex h-10 items-center justify-center rounded-full border border-neutral-200 px-4 text-sm font-medium text-neutral-700 transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-50"
          :disabled="currentPage <= 1 || tableLoading || statusUpdateLoading"
          @click="goToPage(currentPage - 1)"
        >
          Previous
        </button>

        <div class="rounded-full border border-neutral-200 bg-white px-4 py-2 text-sm font-semibold text-neutral-700">
          Page {{ currentPage }} / {{ totalPages }}
        </div>

        <button
          type="button"
          class="inline-flex h-10 items-center justify-center rounded-full border border-neutral-200 px-4 text-sm font-medium text-neutral-700 transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-50"
          :disabled="currentPage >= totalPages || tableLoading || statusUpdateLoading"
          @click="goToPage(currentPage + 1)"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import axios from 'axios'
import { onBeforeUnmount, onMounted, reactive, ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

type InvoiceRow = {
  id: number
  invoice_no: string
  invoice_date: string | null
  customer_name: string
  customer_contact_number: string
  sales_person: string
  order_status_dropdown: string
  status_badge: string
  payment_type_badge: string
  grand_total: string
  paid_amount: string
  balance_due: string
  actions_dropdown: string
}

const rows = ref<InvoiceRow[]>([])
const tableLoading = ref(false)
const statusUpdateLoading = ref(false)
const flashMessage = ref<{ type: 'success' | 'error'; text: string } | null>(null)

const filters = reactive({
  q: '',
  order_status: '',
  payment_type: '',
})

const currentPage = ref(1)
const perPage = ref(10)
const recordsFiltered = ref(0)
const recordsTotal = ref(0)

let flashTimer: ReturnType<typeof setTimeout> | null = null
let filterTimer: ReturnType<typeof setTimeout> | null = null

const totalPages = computed(() => Math.max(1, Math.ceil(recordsFiltered.value / perPage.value)))
const pageStart = computed(() => {
  if (!recordsFiltered.value) return 0
  return ((currentPage.value - 1) * perPage.value) + 1
})
const pageEnd = computed(() => {
  if (!recordsFiltered.value) return 0
  return Math.min(currentPage.value * perPage.value, recordsFiltered.value)
})

function showFlash(type: 'success' | 'error', text: string) {
  flashMessage.value = { type, text }

  if (flashTimer) clearTimeout(flashTimer)
  flashTimer = setTimeout(() => {
    flashMessage.value = null
  }, 4000)
}

function getCsrfToken(): string {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
}

function buildAjaxHeaders() {
  const csrfToken = getCsrfToken()

  // If meta csrf token is not present, try the XSRF-TOKEN cookie (Laravel default)
  function getCookie(name: string): string | null {
    const match = document.cookie.match(new RegExp('(^|; )' + name.replace(/([.$?*|{}()\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)'))
    return match ? decodeURIComponent(match[2]) : null
  }

  const xsrfFromCookie = getCookie('XSRF-TOKEN') || null

  return {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json',
    // Prefer explicit meta token, fallback to XSRF cookie (decoded)
    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : (xsrfFromCookie ? { 'X-XSRF-TOKEN': xsrfFromCookie } : {})),
  }
}

function getErrorMessage(error: any, fallback: string): string {
  const status = Number(error?.response?.status || 0)
  const data = error?.response?.data

  if (status === 419) {
    return 'Session expired or CSRF token mismatch. Refresh the page and try again.'
  }

  if (status === 401) {
    return 'You are not authenticated. Please log in again.'
  }

  if (status === 403) {
    return 'You do not have permission to perform this action.'
  }

  if (data?.errors?.tracking_id?.[0]) {
    return data.errors.tracking_id[0]
  }

  if (data?.errors?.delivery_agent?.[0]) {
    return data.errors.delivery_agent[0]
  }

  if (data?.errors?.order_status?.[0]) {
    return data.errors.order_status[0]
  }

  if (typeof data?.message === 'string' && data.message.trim() !== '') {
    return data.message
  }

  if (typeof data === 'string' && data.trim() !== '') {
    return data
  }

  return status ? `${fallback} (HTTP ${status})` : fallback
}

function closeAllDropdowns(except?: HTMLElement | null) {
  document.querySelectorAll<HTMLElement>('.table-dropdown-menu').forEach((menu) => {
    if (except && menu === except) return
    menu.classList.add('invisible', 'opacity-0', 'scale-95', 'pointer-events-none')
    menu.classList.remove('visible', 'opacity-100', 'scale-100', 'pointer-events-auto')
  })
}

function toggleDropdown(button: HTMLElement) {
  const wrapper = button.closest('.table-dropdown')
  const menu = wrapper?.querySelector<HTMLElement>('.table-dropdown-menu')
  if (!menu) return

  const isOpen = !menu.classList.contains('invisible')
  closeAllDropdowns(menu)

  if (isOpen) {
    menu.classList.add('invisible', 'opacity-0', 'scale-95', 'pointer-events-none')
    menu.classList.remove('visible', 'opacity-100', 'scale-100', 'pointer-events-auto')
    return
  }

  menu.classList.remove('invisible', 'opacity-0', 'scale-95', 'pointer-events-none')
  menu.classList.add('visible', 'opacity-100', 'scale-100', 'pointer-events-auto')
}

async function fetchInvoices() {
  tableLoading.value = true
  closeAllDropdowns()

  try {
    const response = await axios.get(route('invoices.data'), {
      withCredentials: true,
      headers: buildAjaxHeaders(),
      params: {
        draw: 1,
        start: (currentPage.value - 1) * perPage.value,
        length: perPage.value,
        q: filters.q || undefined,
        order_status_filter: filters.order_status || undefined,
        payment_type_filter: filters.payment_type || undefined,
        'order.0.column': 0,
        'order.0.dir': 'desc',
      },
    })

    rows.value = response.data?.data ?? []
    recordsFiltered.value = Number(response.data?.recordsFiltered ?? 0)
    recordsTotal.value = Number(response.data?.recordsTotal ?? 0)

    if (currentPage.value > totalPages.value) {
      currentPage.value = totalPages.value
      await fetchInvoices()
    }
  } catch (error: any) {
    showFlash('error', getErrorMessage(error, 'Could not load orders.'))
  } finally {
    tableLoading.value = false
  }
}

function queueFilterFetch() {
  if (filterTimer) clearTimeout(filterTimer)

  filterTimer = setTimeout(() => {
    currentPage.value = 1
    fetchInvoices()
  }, 350)
}

function resetFilters() {
  filters.q = ''
  filters.order_status = ''
  filters.payment_type = ''
  currentPage.value = 1
  fetchInvoices()
}

function goToPage(page: number) {
  if (page < 1 || page > totalPages.value || page === currentPage.value) return
  currentPage.value = page
  fetchInvoices()
}

function openCreate() {
  router.visit(route('invoices.create'))
}

function openEdit(id: number) {
  router.visit(route('invoices.edit', id))
}

function openView(id: number) {
  window.open(route('invoices.pdf', id), '_blank')
}

function openDownload(id: number) {
  window.open(route('invoices.download', id), '_blank')
}

async function changeOrderStatus(id: number, status: string) {
  statusUpdateLoading.value = true
  closeAllDropdowns()

  try {
    const response = await axios.post(
      route('invoices.order-status'),
      {
        invoice_id: id,
        order_status: status,
      },
      {
        withCredentials: true,
        headers: buildAjaxHeaders(),
      }
    )

    showFlash('success', response.data?.message || 'Order status updated successfully.')
    await fetchInvoices()
  } catch (error: any) {
    showFlash('error', getErrorMessage(error, 'Could not update the order status.'))
  } finally {
    statusUpdateLoading.value = false
  }
}

function deleteInvoice(id: number, name: string) {
  const ok = confirm(`Delete order "${name}"? This cannot be undone.`)
  if (!ok) return

  router.delete(route('invoices.destroy', id), {
    preserveScroll: true,
    onSuccess: () => {
      showFlash('success', 'Order deleted successfully.')
      fetchInvoices()
    },
    onError: () => {
      showFlash('error', 'Could not delete the order.')
    },
  })
}

function handleOutsideClick(event: MouseEvent) {
  const target = event.target as HTMLElement
  if (!target.closest('.table-dropdown')) {
    closeAllDropdowns()
  }
}

function onTableClick(e: MouseEvent) {
  const target = e.target as HTMLElement

  const toggleBtn = target.closest('button[data-action="toggle-dropdown"]') as HTMLButtonElement | null
  if (toggleBtn) {
    e.preventDefault()
    e.stopPropagation()
    if (!statusUpdateLoading.value) {
      toggleDropdown(toggleBtn)
    }
    return
  }

  const statusBtn = target.closest('button[data-action="change-order-status"]') as HTMLButtonElement | null
  if (statusBtn) {
    e.preventDefault()
    e.stopPropagation()

    if (statusUpdateLoading.value) return

    const id = Number(statusBtn.dataset.id)
    const status = String(statusBtn.dataset.status || '')

    if (!id || !status) return

    changeOrderStatus(id, status)
    return
  }

  const btn = target.closest('button[data-action]') as HTMLButtonElement | null
  if (!btn) return

  e.preventDefault()
  e.stopPropagation()

  const action = btn.dataset.action
  const id = Number(btn.dataset.id)
  const name = btn.dataset.name || 'this order'

  if (!action || !id) return

  if (action === 'edit') {
    openEdit(id)
    return
  }

  if (action === 'view') {
    openView(id)
    return
  }

  if (action === 'download') {
    openDownload(id)
    return
  }

  if (action === 'delete') {
    deleteInvoice(id, name)
  }
}

watch(
  () => [filters.q, filters.order_status, filters.payment_type],
  () => {
    queueFilterFetch()
  }
)

onMounted(() => {
  document.addEventListener('click', handleOutsideClick)
  fetchInvoices()
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleOutsideClick)

  if (flashTimer) clearTimeout(flashTimer)
  if (filterTimer) clearTimeout(filterTimer)
})
</script>
