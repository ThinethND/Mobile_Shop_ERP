<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

const props = defineProps<{
  percentage?: number | null
}>()

const form = useForm({
  percentage: props.percentage ?? 0,
})

const previewPrice = 7900

const previewInstallment = computed(() => {
  const percentage = Number(form.percentage ?? 0)
  const safePercentage = Number.isFinite(percentage) && percentage > 0 ? percentage : 0
  const payable = previewPrice + ((previewPrice * safePercentage) / 100)

  return Math.floor((payable / 3) * 100) / 100
})

function formatPrice(value: number) {
  return `Rs ${Number(value).toLocaleString('en-LK', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}

function submit() {
  form.put(route('koko-pay.update'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Koko Pay" />

    <div class="p-6">
      <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-neutral-950">Koko Pay</h1>
          <p class="text-sm text-neutral-500">Set one Koko Pay percentage for every frontend product price.</p>
        </div>
      </div>

      <form
        class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_360px]"
        @submit.prevent="submit"
      >
        <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm">
          <div class="mb-5 flex items-center gap-3">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f3efff]">
              <img
                src="/images/kokologo.webp"
                alt="Koko"
                class="h-7 w-auto"
                loading="lazy"
                decoding="async"
              >
            </span>
            <div>
              <h2 class="text-base font-semibold text-neutral-950">Installment Percentage</h2>
              <p class="text-sm text-neutral-500">This amount is added to the product price before dividing into 3 installments.</p>
            </div>
          </div>

          <div class="max-w-md">
            <label class="mb-1 block text-sm font-medium text-neutral-700">Koko Pay Percentage (%)</label>
            <div class="relative">
              <input
                v-model="form.percentage"
                type="number"
                min="0"
                max="100"
                step="0.01"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2.5 pr-10 outline-none transition focus:border-[#38bdf8] focus:ring-2 focus:ring-sky-100"
                placeholder="e.g. 5"
              >
              <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-sm font-semibold text-neutral-400">%</span>
            </div>
            <p v-if="form.errors.percentage" class="mt-1 text-sm text-red-600">{{ form.errors.percentage }}</p>
          </div>

          <div class="mt-6 flex justify-end">
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center justify-center rounded-full bg-[#38bdf8] px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0ea5e9] disabled:opacity-50"
            >
              {{ form.processing ? 'Saving...' : 'Save Koko Pay' }}
            </button>
          </div>
        </section>

        <aside class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm">
          <p class="text-xs font-semibold uppercase text-neutral-400">Frontend Preview</p>
          <div class="mt-4 rounded-xl border border-neutral-100 bg-neutral-50 p-4">
            <div class="text-2xl font-bold leading-none text-[#151821]">
              {{ formatPrice(previewPrice) }}
            </div>
            <div class="mt-2 flex flex-wrap items-center gap-x-1.5 gap-y-1 text-sm font-medium text-neutral-500">
              <span>or pay in 3 x</span>
              <span class="font-extrabold text-neutral-500">{{ formatPrice(previewInstallment) }}</span>
              <span>with</span>
              <img
                src="/images/kokologo.webp"
                alt="Koko"
                class="h-5 w-auto"
                loading="lazy"
                decoding="async"
              >
            </div>
          </div>
        </aside>
      </form>
    </div>
  </AppLayout>
</template>
