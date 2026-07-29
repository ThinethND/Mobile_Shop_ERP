<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

const props = defineProps<{
  cashOnDeliveryFee?: number | null
  bankTransferDeliveryFee?: number | null
}>()

const form = useForm({
  cash_on_delivery_fee: props.cashOnDeliveryFee ?? 450,
  bank_transfer_delivery_fee: props.bankTransferDeliveryFee ?? 450,
})

const previewMethods = computed(() => [
  {
    label: 'Cash on delivery',
    fee: Number(form.cash_on_delivery_fee || 0),
  },
  {
    label: 'Store pickup',
    fee: 0,
  },
  {
    label: 'Bank transfer delivery',
    fee: Number(form.bank_transfer_delivery_fee || 0),
  },
])

function formatPrice(value: number) {
  return `Rs ${Number(value).toLocaleString('en-LK', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}

function submit() {
  form.put(route('delivery-charges.update'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Delivery Charges" />

    <div class="p-6">
      <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-neutral-950">Delivery Charges</h1>
          <p class="text-sm text-neutral-500">Manage the frontend checkout delivery fees without touching code.</p>
        </div>
      </div>

      <form
        class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_360px]"
        @submit.prevent="submit"
      >
        <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm">
          <div class="mb-5">
            <h2 class="text-base font-semibold text-neutral-950">Checkout Delivery Fees</h2>
            <p class="mt-1 text-sm text-neutral-500">These values are shown in the checkout shipping step for customers.</p>
          </div>

          <div class="grid gap-5 sm:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Cash on Delivery Fee</label>
              <div class="relative">
                <input
                  v-model="form.cash_on_delivery_fee"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2.5 pr-12 outline-none transition focus:border-[#38bdf8] focus:ring-2 focus:ring-sky-100"
                  placeholder="450.00"
                >
                <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-sm font-semibold text-neutral-400">LKR</span>
              </div>
              <p v-if="form.errors.cash_on_delivery_fee" class="mt-1 text-sm text-red-600">{{ form.errors.cash_on_delivery_fee }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Bank Transfer Delivery Fee</label>
              <div class="relative">
                <input
                  v-model="form.bank_transfer_delivery_fee"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2.5 pr-12 outline-none transition focus:border-[#38bdf8] focus:ring-2 focus:ring-sky-100"
                  placeholder="450.00"
                >
                <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-sm font-semibold text-neutral-400">LKR</span>
              </div>
              <p v-if="form.errors.bank_transfer_delivery_fee" class="mt-1 text-sm text-red-600">{{ form.errors.bank_transfer_delivery_fee }}</p>
            </div>
          </div>

          <div class="mt-6 flex justify-end">
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center justify-center rounded-full bg-[#38bdf8] px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0ea5e9] disabled:opacity-50"
            >
              {{ form.processing ? 'Saving...' : 'Save Delivery Charges' }}
            </button>
          </div>
        </section>

        <aside class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm">
          <p class="text-xs font-semibold uppercase text-neutral-400">Frontend Preview</p>
          <div class="mt-4 space-y-3">
            <div
              v-for="method in previewMethods"
              :key="method.label"
              class="rounded-xl border border-neutral-100 bg-neutral-50 p-4"
            >
              <div class="text-sm font-semibold text-neutral-900">
                {{ method.label }}
              </div>
              <div class="mt-2 text-lg font-bold text-neutral-950">
                {{ method.fee > 0 ? formatPrice(method.fee) : 'Free' }}
              </div>
            </div>
          </div>
        </aside>
      </form>
    </div>
  </AppLayout>
</template>
