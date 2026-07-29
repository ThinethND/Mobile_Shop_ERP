<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import {
  Activity,
  AlertTriangle,
  ArrowUpRight,
  CheckCircle2,
  CircleDollarSign,
  Clock3,
  Package,
  ShoppingCart,
} from 'lucide-vue-next'
import BarMonthlyBookings from './BarMonthlyBookings.vue'
import DonutServiceMix from './DonutServiceMix.vue'
import LineRevenue from './LineRevenue.vue'

type KpiCard = {
  key: string
  label: string
  value: number
  format: 'currency' | 'number'
  caption: string
}

type ChartSeries = {
  labels: string[]
  data: number[]
}

type DepartmentMixItem = {
  key: string
  label: string
  units: number
  revenue: number
}

type PipelineItem = {
  key: string
  label: string
  count: number
}

type InventoryDepartment = {
  key: string
  label: string
  total: number
  active: number
  low_stock: number
  href: string
}

type RecentOrder = {
  id: number | string
  invoice_no: string
  customer: string
  amount: number
  status: string
  payment_type: string
  items_count: number
  date: string | null
  href: string
}

type LowStockItem = {
  id: number | string
  key: string
  department: string
  name: string
  stock: number
  href: string
}

type DashboardPayload = {
  generated_at: string
  kpis: KpiCard[]
  summary: {
    website_orders_total: number
    low_stock_count: number
    total_products: number
    active_products: number
  }
  charts: {
    daily_revenue: ChartSeries
    monthly_orders: ChartSeries
    department_mix: DepartmentMixItem[]
  }
  order_pipeline: PipelineItem[]
  inventory_departments: InventoryDepartment[]
  recent_orders: RecentOrder[]
  low_stock_items: LowStockItem[]
}

const props = defineProps<{
  message?: string
  dashboard: DashboardPayload
}>()

const kpiIcons = {
  revenue: CircleDollarSign,
  orders: ShoppingCart,
  website_orders: Activity,
  catalog: Package,
}

const kpiAccentClasses: Record<string, string> = {
  revenue: 'bg-emerald-50 text-emerald-700 ring-emerald-100',
  orders: 'bg-blue-50 text-blue-700 ring-blue-100',
  website_orders: 'bg-amber-50 text-amber-700 ring-amber-100',
  catalog: 'bg-violet-50 text-violet-700 ring-violet-100',
}

const statusClasses: Record<string, string> = {
  reserved: 'bg-slate-100 text-slate-700',
  confirmed: 'bg-blue-50 text-blue-700',
  dispatched: 'bg-amber-50 text-amber-700',
  delivered: 'bg-emerald-50 text-emerald-700',
  cancelled: 'bg-rose-50 text-rose-700',
}

const departmentClasses: Record<string, string> = {
  electronics: 'bg-blue-500',
  cosmetics: 'bg-rose-500',
  motorcycle: 'bg-slate-700',
  fashion: 'bg-violet-500',
  'home-needs': 'bg-emerald-500',
  shoes: 'bg-amber-500',
}

function kpiIcon(key: string) {
  return kpiIcons[key as keyof typeof kpiIcons] || Activity
}

function formatValue(value: number, format: 'currency' | 'number') {
  if (format === 'currency') {
    return `Rs ${Number(value || 0).toLocaleString('en-LK', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })}`
  }

  return Number(value || 0).toLocaleString('en-LK')
}

function formatCurrency(value: number) {
  return formatValue(value, 'currency')
}

function statusLabel(value: string | null | undefined) {
  return String(value || 'pending')
    .replace(/[_-]/g, ' ')
    .replace(/\b\w/g, (letter) => letter.toUpperCase())
}

function inventoryPercent(item: InventoryDepartment) {
  if (!item.total) return 0
  return Math.min(100, Math.round((item.active / item.total) * 100))
}

function pipelinePercent(item: PipelineItem) {
  const max = Math.max(...props.dashboard.order_pipeline.map((entry) => entry.count), 1)
  return Math.round((item.count / max) * 100)
}
</script>

<template>
  <AppLayout>
    <Head title="Dashboard" />

    <div class="min-h-screen bg-slate-50/70 p-4 sm:p-6 lg:p-8">
      <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-500">
              Store Command Center
            </p>
            <h1 class="mt-2 text-3xl font-bold tracking-[-0.03em] text-slate-950 sm:text-4xl">
              Dashboard
            </h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
              Live sales, order flow, inventory health, and catalog movement for DezeStore.
            </p>
          </div>

          <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-500">
              Updated {{ dashboard.generated_at }}
            </div>

            <Link
              :href="route('invoices.index')"
              class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-slate-950 px-4 text-sm font-semibold text-white transition hover:bg-slate-800"
            >
              Orders
              <ArrowUpRight class="h-4 w-4" />
            </Link>
          </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <article
            v-for="card in dashboard.kpis"
            :key="card.key"
            class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
          >
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-sm font-medium text-slate-500">
                  {{ card.label }}
                </p>
                <p class="mt-3 text-2xl font-bold tracking-[-0.03em] text-slate-950">
                  {{ formatValue(card.value, card.format) }}
                </p>
              </div>

              <div
                class="flex h-11 w-11 items-center justify-center rounded-lg ring-1"
                :class="kpiAccentClasses[card.key] || 'bg-slate-100 text-slate-700 ring-slate-200'"
              >
                <component :is="kpiIcon(card.key)" class="h-5 w-5" />
              </div>
            </div>

            <div class="mt-5 text-sm font-medium text-slate-500">
              {{ card.caption }}
            </div>
          </article>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.75fr)]">
          <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-5 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 class="text-lg font-semibold text-slate-950">Revenue Pulse</h2>
                <p class="text-sm text-slate-500">Daily billed revenue for the last 14 days.</p>
              </div>
            </div>
            <div class="h-[320px]">
              <LineRevenue
                :labels="dashboard.charts.daily_revenue.labels"
                :data="dashboard.charts.daily_revenue.data"
                label="Revenue"
                theme="light"
              />
            </div>
          </section>

          <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-5">
              <h2 class="text-lg font-semibold text-slate-950">Department Mix</h2>
              <p class="text-sm text-slate-500">Current month product revenue split.</p>
            </div>

            <div v-if="dashboard.charts.department_mix.length" class="h-[280px]">
              <DonutServiceMix
                :labels="dashboard.charts.department_mix.map((item) => item.label)"
                :data="dashboard.charts.department_mix.map((item) => item.revenue)"
                theme="light"
              />
            </div>

            <div v-else class="flex h-[280px] items-center justify-center rounded-lg border border-dashed border-slate-200 text-sm text-slate-400">
              No sales mix yet
            </div>
          </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(320px,0.85fr)_minmax(0,1.15fr)]">
          <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-5">
              <h2 class="text-lg font-semibold text-slate-950">Monthly Orders</h2>
              <p class="text-sm text-slate-500">Invoice order count over the last 6 months.</p>
            </div>

            <div class="h-[280px]">
              <BarMonthlyBookings
                :labels="dashboard.charts.monthly_orders.labels"
                :data="dashboard.charts.monthly_orders.data"
                label="Orders"
                theme="light"
              />
            </div>
          </section>

          <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-5 flex items-center justify-between gap-3">
              <div>
                <h2 class="text-lg font-semibold text-slate-950">Order Pipeline</h2>
                <p class="text-sm text-slate-500">Current status distribution.</p>
              </div>
              <Clock3 class="h-5 w-5 text-slate-400" />
            </div>

            <div class="space-y-4">
              <div
                v-for="item in dashboard.order_pipeline"
                :key="item.key"
                class="space-y-2"
              >
                <div class="flex items-center justify-between gap-3 text-sm">
                  <span class="font-medium text-slate-700">{{ item.label }}</span>
                  <span class="font-semibold text-slate-950">{{ item.count }}</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                  <div
                    class="h-full rounded-full bg-slate-950"
                    :style="{ width: `${pipelinePercent(item)}%` }"
                  />
                </div>
              </div>
            </div>
          </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(340px,0.9fr)]">
          <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
              <h2 class="text-lg font-semibold text-slate-950">Recent Orders</h2>
              <p class="text-sm text-slate-500">Latest invoices and checkout orders.</p>
            </div>

            <div class="divide-y divide-slate-100">
              <Link
                v-for="order in dashboard.recent_orders"
                :key="order.id"
                :href="order.href"
                class="grid gap-3 p-5 transition hover:bg-slate-50 sm:grid-cols-[1fr_auto] sm:items-center"
              >
                <div class="min-w-0">
                  <div class="flex flex-wrap items-center gap-2">
                    <p class="font-semibold text-slate-950">{{ order.invoice_no }}</p>
                    <span
                      class="rounded-full px-2.5 py-1 text-xs font-semibold"
                      :class="statusClasses[order.status] || 'bg-slate-100 text-slate-700'"
                    >
                      {{ statusLabel(order.status) }}
                    </span>
                  </div>
                  <p class="mt-1 truncate text-sm text-slate-500">
                    {{ order.customer }} · {{ order.items_count }} item{{ order.items_count === 1 ? '' : 's' }} · {{ order.date || 'No date' }}
                  </p>
                </div>

                <div class="text-left sm:text-right">
                  <p class="font-semibold text-slate-950">{{ formatCurrency(order.amount) }}</p>
                  <p class="mt-1 text-xs uppercase tracking-[0.12em] text-slate-400">
                    {{ statusLabel(order.payment_type) }}
                  </p>
                </div>
              </Link>

              <div
                v-if="!dashboard.recent_orders.length"
                class="p-8 text-center text-sm text-slate-400"
              >
                No orders yet
              </div>
            </div>
          </section>

          <section class="space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
              <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                  <h2 class="text-lg font-semibold text-slate-950">Inventory Health</h2>
                  <p class="text-sm text-slate-500">
                    {{ dashboard.summary.low_stock_count }} products need stock attention.
                  </p>
                </div>
                <AlertTriangle class="h-5 w-5 text-amber-500" />
              </div>

              <div class="space-y-4">
                <Link
                  v-for="item in dashboard.inventory_departments"
                  :key="item.key"
                  :href="item.href"
                  class="block rounded-lg border border-slate-100 p-4 transition hover:border-slate-200 hover:bg-slate-50"
                >
                  <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                      <span
                        class="h-2.5 w-2.5 rounded-full"
                        :class="departmentClasses[item.key] || 'bg-slate-400'"
                      />
                      <span class="font-medium text-slate-800">{{ item.label }}</span>
                    </div>
                    <span class="text-sm text-slate-500">{{ item.active }}/{{ item.total }}</span>
                  </div>

                  <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                    <div
                      class="h-full rounded-full"
                      :class="departmentClasses[item.key] || 'bg-slate-500'"
                      :style="{ width: `${inventoryPercent(item)}%` }"
                    />
                  </div>

                  <div class="mt-3 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Active products</span>
                    <span :class="item.low_stock > 0 ? 'font-semibold text-amber-600' : 'font-semibold text-emerald-600'">
                      {{ item.low_stock }} low stock
                    </span>
                  </div>
                </Link>
              </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
              <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                  <h2 class="text-lg font-semibold text-slate-950">Low Stock Watch</h2>
                  <p class="text-sm text-slate-500">Lowest stock products across departments.</p>
                </div>
                <CheckCircle2
                  v-if="!dashboard.low_stock_items.length"
                  class="h-5 w-5 text-emerald-500"
                />
              </div>

              <div v-if="dashboard.low_stock_items.length" class="space-y-3">
                <Link
                  v-for="item in dashboard.low_stock_items"
                  :key="`${item.key}-${item.id}`"
                  :href="item.href"
                  class="flex items-center justify-between gap-4 rounded-lg border border-slate-100 p-3 transition hover:border-slate-200 hover:bg-slate-50"
                >
                  <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-slate-900">{{ item.name }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ item.department }}</p>
                  </div>

                  <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                    {{ item.stock }} left
                  </span>
                </Link>
              </div>

              <div v-else class="rounded-lg border border-dashed border-slate-200 p-6 text-center text-sm text-slate-400">
                Stock levels look clear
              </div>
            </div>
          </section>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
