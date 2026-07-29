<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { computed, reactive, ref, watch } from 'vue'
import { route } from 'ziggy-js'

type OrderStatus = 'reserved' | 'confirmed' | 'dispatched' | 'delivered' | 'cancelled'
type InvoiceProductType = 'electronics' | 'motorcycle' | 'cosmetics' | 'fashion' | 'home-needs'

type CatalogProduct = {
  id: number
  product_type: InvoiceProductType
  label: string
  name: string
  sku?: string | null
  brand?: string | null
  category?: string | null
  price: number
  warranty?: string | null
  storages: string[]
  colors: string[]
  sizes: string[]
}

type InvoiceItem = {
  id?: number | null
  item_no: number
  product_type: InvoiceProductType
  product_id: number | null
  model_name: string
  storage: string
  color: string
  size: string
  imei_serial: string
  warranty: string
  is_preorder: boolean
  description: string
  qty: number
  regular_price: number
  discount_type: '' | 'percentage' | 'fixed'
  discount_value: number | null
  discount_percent_display: number | null
  discounted_unit_price: number
  line_total: number
}

type InvoicePayload = {
  id: number
  invoice_no: string
  invoice_date: string
  customer_name: string
  customer_contact_number: string
  customer_address?: string | null
  customer_email?: string | null
  sales_person?: string | null
  ship_date?: string | null
  ship_via?: string | null
  delivery_enabled?: boolean
  delivery_method?: string | null
  delivery_payment_status?: string | null
  tracking_id?: string | null
  delivery_agent?: string | null
  delivery_amount?: number
  payment_type?: string | null
  cash_paid: number
  card_paid: number
  advance_amount: number
  paid_amount: number
  subtotal: number
  total_discount: number
  tax_amount: number
  grand_total: number
  balance_due: number
  notes?: string | null
  terms?: string | null
  status: 'draft' | 'finalized' | 'cancelled'
  order_status: OrderStatus
  pdf_path?: string | null
  pdf_url?: string | null
  items: InvoiceItem[]
}

const props = defineProps<{
  mode: 'create' | 'edit'
  invoice?: InvoicePayload | null
  catalogProducts: CatalogProduct[]
  techProducts: unknown[]
  shoeProducts: unknown[]
  nextInvoiceNo: string
  shop: {
    name: string
    address_lines: string[]
    phone?: string
    website?: string
    logo_url?: string
  }
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.invoice?.id)

function normalizeProductType(value: string | null | undefined): InvoiceProductType {
  const type = String(value ?? '').trim().toLowerCase().replace(/_/g, '-')

  if (type === 'tech' || type === 'electronic' || type === 'electronics') return 'electronics'
  if (type === 'motorcycle' || type === 'motorcycles') return 'motorcycle'
  if (type === 'cosmetic' || type === 'cosmetics') return 'cosmetics'
  if (type === 'fashion') return 'fashion'
  if (type === 'home-need' || type === 'home-needs') return 'home-needs'

  return 'electronics'
}

const productTypeOptions: { value: InvoiceProductType; label: string }[] = [
  { value: 'electronics', label: 'Electronics' },
  { value: 'motorcycle', label: 'Motorcycle' },
  { value: 'cosmetics', label: 'Cosmetics' },
  { value: 'fashion', label: 'Fashion' },
  { value: 'home-needs', label: 'Home Needs' },
]

const orderStatusOptions = [
  { value: 'reserved', label: 'Reserved' },
  { value: 'confirmed', label: 'Confirmed' },
  { value: 'dispatched', label: 'Dispatched' },
  { value: 'delivered', label: 'Delivered' },
  { value: 'cancelled', label: 'Cancelled' },
] as const

const deliveryMethodOptions = [
  { value: 'cash_on_delivery', label: 'Cash on Delivery' },
  { value: 'paid_delivery', label: 'Paid Delivery' },
  { value: 'pickme_flash', label: 'PickMe Flash' },
  { value: 'uber_flash', label: 'Uber Flash' },
] as const

const deliveryPaymentStatusOptions = [
  { value: 'paid', label: 'Paid' },
  { value: 'non_paid', label: 'Non-Paid' },
] as const

const deliveryAgentOptions = [
  { value: 'domex', label: 'Domex' },
  { value: 'pickme', label: 'PickMe' },
] as const

const deliveryMethodLabelMap = Object.fromEntries(deliveryMethodOptions.map((item) => [item.value, item.label]))
const deliveryPaymentStatusLabelMap = Object.fromEntries(deliveryPaymentStatusOptions.map((item) => [item.value, item.label]))
const deliveryAgentLabelMap = Object.fromEntries(deliveryAgentOptions.map((item) => [item.value, item.label]))

const form = useForm({
  invoice_no: props.invoice?.invoice_no ?? props.nextInvoiceNo,
  invoice_date: props.invoice?.invoice_date ?? new Date().toISOString().slice(0, 10),
  customer_name: props.invoice?.customer_name ?? '',
  customer_contact_number: props.invoice?.customer_contact_number ?? '',
  customer_address: props.invoice?.customer_address ?? '',
  customer_email: props.invoice?.customer_email ?? '',
  sales_person: props.invoice?.sales_person ?? '',
  ship_date: props.invoice?.ship_date ?? '',
  ship_via: props.invoice?.ship_via ?? '',
  delivery_enabled: props.invoice?.delivery_enabled ?? false,
  delivery_method: props.invoice?.delivery_method ?? '',
  delivery_payment_status: props.invoice?.delivery_payment_status ?? '',
  tracking_id: props.invoice?.tracking_id ?? '',
  delivery_agent: props.invoice?.delivery_agent ?? '',
  delivery_amount: props.invoice?.delivery_amount ?? 0,
  cash_paid: props.invoice?.cash_paid ?? 0,
  card_paid: props.invoice?.card_paid ?? 0,
  advance_amount: props.invoice?.advance_amount ?? 0,
  tax_amount: props.invoice?.tax_amount ?? 0,
  notes: props.invoice?.notes ?? '',
  terms: props.invoice?.terms ?? '',
  status: props.invoice?.status ?? 'draft',
  order_status: props.invoice?.order_status ?? 'reserved',
  submit_action: 'draft',
  items: (props.invoice?.items ?? []).map((item, index) => ({
    ...item,
    product_type: normalizeProductType(item.product_type),
    item_no: index + 1,
  })) as InvoiceItem[],
})

const productSearch = reactive({ keyword: '' })
const cardAutoSuggested = ref(!(props.invoice && Number(props.invoice.card_paid || 0) > 0))

const draftItem = reactive<InvoiceItem>({
  item_no: 1,
  product_type: 'electronics',
  product_id: null,
  model_name: '',
  storage: '',
  color: '',
  size: '',
  imei_serial: '',
  warranty: '',
  is_preorder: false,
  description: '',
  qty: 1,
  regular_price: 0,
  discount_type: '',
  discount_value: null,
  discount_percent_display: null,
  discounted_unit_price: 0,
  line_total: 0,
})

const activeCatalogProducts = computed(() => {
  return props.catalogProducts.filter((product) => product.product_type === draftItem.product_type)
})

const filteredCatalogProducts = computed(() => {
  const keyword = productSearch.keyword.trim().toLowerCase()
  if (!keyword) return activeCatalogProducts.value

  return activeCatalogProducts.value.filter((product) =>
    [product.label, product.name, product.sku, product.brand, product.category]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()
      .includes(keyword)
  )
})

const selectedCatalogProduct = computed(() => {
  return activeCatalogProducts.value.find((product) => product.id === Number(draftItem.product_id)) ?? null
})

const productStorages = computed(() => selectedCatalogProduct.value?.storages ?? [])
const productColors = computed(() => selectedCatalogProduct.value?.colors ?? [])
const productSizes = computed(() => selectedCatalogProduct.value?.sizes ?? [])

const deliveryAmountValue = computed(() =>
  Number(form.delivery_enabled ? form.delivery_amount || 0 : 0)
)

const discountedItemsTotal = computed(() =>
  Number(form.items.reduce((sum, item) => sum + Number(item.line_total || 0), 0).toFixed(2))
)

const subtotal = computed(() =>
  Number((
    form.items.reduce((sum, item) => sum + (Number(item.regular_price || 0) * Number(item.qty || 1)), 0) +
    deliveryAmountValue.value
  ).toFixed(2))
)

const totalDiscount = computed(() =>
  Number((subtotal.value - (discountedItemsTotal.value + deliveryAmountValue.value)).toFixed(2))
)

const grandTotalBeforeTax = computed(() =>
  Number((discountedItemsTotal.value + deliveryAmountValue.value).toFixed(2))
)

const grandTotal = computed(() =>
  Number((grandTotalBeforeTax.value + Number(form.tax_amount || 0)).toFixed(2))
)

const totalPaid = computed(() =>
  Number((
    Number(form.cash_paid || 0) +
    Number(form.card_paid || 0) +
    Number(form.advance_amount || 0)
  ).toFixed(2))
)

const balanceDue = computed(() =>
  Number(Math.max(0, grandTotal.value - totalPaid.value).toFixed(2))
)

const remainingCardSuggestion = computed(() =>
  Number(Math.max(0, grandTotal.value - Number(form.cash_paid || 0) - Number(form.advance_amount || 0)).toFixed(2))
)

const paymentTypeLabel = computed(() => {
  const cash = Number(form.cash_paid || 0)
  const card = Number(form.card_paid || 0)
  const advance = Number(form.advance_amount || 0)

  const count = [cash > 0, card > 0, advance > 0].filter(Boolean).length

  if (count === 0) return 'Unpaid'
  if (count > 1) return 'Mixed'
  if (cash > 0) return 'Cash'
  if (card > 0) return 'Card'
  return 'Advance'
})

const deliveryMethodPreviewLabel = computed(() =>
  deliveryMethodLabelMap[form.delivery_method as keyof typeof deliveryMethodLabelMap] ?? '-'
)

const deliveryPaymentStatusPreviewLabel = computed(() =>
  deliveryPaymentStatusLabelMap[form.delivery_payment_status as keyof typeof deliveryPaymentStatusLabelMap] ?? '-'
)

const deliveryAgentPreviewLabel = computed(() =>
  deliveryAgentLabelMap[form.delivery_agent as keyof typeof deliveryAgentLabelMap] ?? '-'
)

const orderStatusHelper = computed(() => {
  if (form.order_status === 'dispatched') {
    return 'Tracking ID and delivery agent are required before dispatch.'
  }

  if (form.order_status === 'delivered') {
    return 'Delivered will auto-finalize the invoice and mark the full amount as cash paid.'
  }

  if (form.order_status === 'cancelled') {
    return 'Cancelled will mark the invoice status as cancelled.'
  }

  return ''
})

function resetDraftItem(type: InvoiceProductType = 'electronics') {
  draftItem.item_no = form.items.length + 1
  draftItem.product_type = type
  draftItem.product_id = null
  draftItem.model_name = ''
  draftItem.storage = ''
  draftItem.color = ''
  draftItem.size = ''
  draftItem.imei_serial = ''
  draftItem.warranty = ''
  draftItem.is_preorder = false
  draftItem.description = ''
  draftItem.qty = 1
  draftItem.regular_price = 0
  draftItem.discount_type = ''
  draftItem.discount_value = null
  draftItem.discount_percent_display = null
  draftItem.discounted_unit_price = 0
  draftItem.line_total = 0
}

function onTypeChange(type: InvoiceProductType) {
  productSearch.keyword = ''
  resetDraftItem(type)
}

function syncSuggestedCardAmount() {
  if (!cardAutoSuggested.value) return

  const cash = Number(form.cash_paid || 0)
  const advance = Number(form.advance_amount || 0)

  if (cash > 0 || advance > 0) {
    form.card_paid = remainingCardSuggestion.value
    return
  }

  if (!isEdit.value) {
    form.card_paid = 0
  }
}

function onCardPaidManual() {
  cardAutoSuggested.value = false
}

function useRemainingForCard() {
  cardAutoSuggested.value = true
  form.card_paid = remainingCardSuggestion.value
}

watch(
  () => draftItem.product_id,
  () => {
    const product = selectedCatalogProduct.value
    if (!product) return

    draftItem.model_name = product.name || product.label
    draftItem.regular_price = Number(product.price || 0)
    draftItem.discounted_unit_price = Number(product.price || 0)
    draftItem.warranty = product.warranty ?? ''
    draftItem.storage = ''
    draftItem.color = ''
    draftItem.size = ''
    draftItem.imei_serial = ''
    draftItem.is_preorder = false
    recalcDraft()
  }
)

watch(
  () => [
    draftItem.product_type,
    draftItem.model_name,
    draftItem.storage,
    draftItem.color,
    draftItem.size,
    draftItem.imei_serial,
    draftItem.warranty,
    draftItem.is_preorder,
    draftItem.qty,
    draftItem.regular_price,
    draftItem.discount_type,
    draftItem.discount_value,
  ],
  () => recalcDraft(),
  { deep: true }
)

watch(
  () => [Number(form.cash_paid || 0), Number(form.advance_amount || 0), grandTotal.value],
  () => syncSuggestedCardAmount(),
  { immediate: true }
)

watch(
  () => form.delivery_enabled,
  (enabled) => {
    if (!enabled) {
      form.delivery_method = ''
      form.delivery_payment_status = ''
      form.tracking_id = ''
      form.delivery_agent = ''
      form.delivery_amount = 0
    }

    syncSuggestedCardAmount()
  }
)

watch(
  () => Number(form.delivery_amount || 0),
  () => syncSuggestedCardAmount()
)

watch(
  () => [form.order_status, grandTotal.value],
  ([status]) => {
    if (status === 'delivered') {
      form.status = 'finalized'
      form.cash_paid = Number(grandTotal.value.toFixed(2))
      form.card_paid = 0
      form.advance_amount = 0
      if (form.delivery_enabled) {
        form.delivery_payment_status = 'paid'
      }
    }

    if (status === 'cancelled') {
      form.status = 'cancelled'
    }
  },
  { immediate: true }
)

function recalcDraft() {
  const regularPrice = Number(draftItem.regular_price || 0)
  const qty = Math.max(1, Number(draftItem.qty || 1))
  const discountType = draftItem.discount_type
  const discountValue = Number(draftItem.discount_value || 0)

  let discountedUnitPrice = regularPrice
  let discountPercentDisplay: number | null = null

  if (discountType === 'percentage') {
    discountedUnitPrice = regularPrice - ((regularPrice * discountValue) / 100)
    discountPercentDisplay = discountValue
  } else if (discountType === 'fixed') {
    discountedUnitPrice = regularPrice - discountValue
    discountPercentDisplay = null
  }

  discountedUnitPrice = Math.max(0, discountedUnitPrice)

  draftItem.discounted_unit_price = Number(discountedUnitPrice.toFixed(2))
  draftItem.discount_percent_display = discountPercentDisplay !== null ? Number(discountPercentDisplay.toFixed(2)) : null
  draftItem.line_total = Number((discountedUnitPrice * qty).toFixed(2))
  draftItem.description = buildDescription(draftItem)
}

function buildDescription(item: InvoiceItem) {
  const bits = [
    item.model_name,
    item.storage,
    item.color,
    item.size,
  ].filter(Boolean)

  const details = []

  if (item.imei_serial) details.push(`IMEI/Serial: ${item.imei_serial}`)
  if (item.warranty) details.push(`Warranty: ${item.warranty}`)
  if (item.is_preorder) details.push('Pre-Order')

  return [...bits, ...details].join(' / ')
}

function addDraftItem() {
  recalcDraft()

  if (!draftItem.product_id || !draftItem.model_name || !draftItem.description) {
    alert('Please select a product and complete the required item details.')
    return
  }

  form.items.push({
    ...JSON.parse(JSON.stringify(draftItem)),
    item_no: form.items.length + 1,
  })

  syncItemNumbers()
  resetDraftItem(draftItem.product_type)
}

function removeItem(index: number) {
  form.items.splice(index, 1)
  syncItemNumbers()
}

function syncItemNumbers() {
  form.items = form.items.map((item, index) => ({
    ...item,
    item_no: index + 1,
  }))
}

function resetForm() {
  if (!confirm('Reset the invoice form?')) return

  form.invoice_no = props.nextInvoiceNo
  form.invoice_date = new Date().toISOString().slice(0, 10)
  form.customer_name = ''
  form.customer_contact_number = ''
  form.customer_address = ''
  form.customer_email = ''
  form.sales_person = ''
  form.ship_date = ''
  form.ship_via = ''
  form.delivery_enabled = false
  form.delivery_method = ''
  form.delivery_payment_status = ''
  form.tracking_id = ''
  form.delivery_agent = ''
  form.delivery_amount = 0
  form.cash_paid = 0
  form.card_paid = 0
  form.advance_amount = 0
  form.tax_amount = 0
  form.notes = ''
  form.terms = ''
  form.status = 'draft'
  form.order_status = 'reserved'
  form.items = []
  cardAutoSuggested.value = true
  resetDraftItem('electronics')
}

function submit(action: 'draft' | 'finalize', options: { redirectToList?: boolean } = {}) {
  form.clearErrors()
  form.submit_action = action
  const redirectToList = Boolean(options.redirectToList)

  const payload = {
    ...form.data(),
    delivery_enabled: Boolean(form.delivery_enabled),
    delivery_method: form.delivery_enabled ? (form.delivery_method || null) : null,
    delivery_payment_status: form.delivery_enabled ? (form.delivery_payment_status || null) : null,
    tracking_id: form.delivery_enabled ? (form.tracking_id || null) : null,
    delivery_agent: form.delivery_enabled ? (form.delivery_agent || null) : null,
    delivery_amount: Number(form.delivery_enabled ? form.delivery_amount || 0 : 0),
    cash_paid: Number(form.cash_paid || 0),
    card_paid: Number(form.card_paid || 0),
    advance_amount: Number(form.advance_amount || 0),
    tax_amount: Number(form.tax_amount || 0),
    order_status: form.order_status,
    items: form.items.map((item, index) => ({
      ...item,
      item_no: index + 1,
      description: buildDescription(item),
      discounted_unit_price: Number(item.discounted_unit_price || 0),
      line_total: Number(item.line_total || 0),
      regular_price: Number(item.regular_price || 0),
      qty: Number(item.qty || 1),
      discount_value: item.discount_value !== null && item.discount_value !== undefined ? Number(item.discount_value) : null,
      discount_percent_display: item.discount_percent_display !== null && item.discount_percent_display !== undefined
        ? Number(item.discount_percent_display)
        : null,
    })),
  }

  if (!isEdit.value) {
    form.transform(() => payload).post(route('invoices.store'), {
      preserveScroll: true,
      onSuccess: () => {
        if (redirectToList) router.visit(route('invoices.index'))
      },
      onFinish: () => form.transform((d) => d),
    })
    return
  }

  form.transform(() => ({
    ...payload,
    _method: 'PUT',
  })).post(route('invoices.update', props.invoice!.id), {
    preserveScroll: true,
    onSuccess: () => {
      if (redirectToList) router.visit(route('invoices.index'))
    },
    onFinish: () => form.transform((d) => d),
  })
}
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Order' : 'Create Order'" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">
            {{ isEdit ? 'Update Order' : 'Create Order' }}
          </h1>
          <p class="text-sm text-neutral-500">
            Build the order invoice on the left and preview the PDF layout on the right
          </p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
          <Link
            :href="route('invoices.index')"
            class="inline-flex items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100"
          >
            Back
          </Link>

          <div v-if="isEdit" class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <a
              v-if="invoice?.pdf_url"
              :href="route('invoices.download', invoice.id)"
              target="_blank"
              class="inline-flex items-center justify-center rounded-full border border-emerald-200 px-4 py-2 text-sm font-medium text-emerald-600 transition hover:bg-emerald-50"
            >
              Download PDF
            </a>

            <button
              type="button"
              @click="submit('finalize', { redirectToList: true })"
              :disabled="form.processing"
              class="inline-flex items-center justify-center rounded-full bg-[#38bdf8] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#7dd3fc] disabled:opacity-50"
            >
              {{ form.processing ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <form
          @submit.prevent
          class="space-y-4 rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6"
        >
          <div class="rounded-2xl border border-neutral-200 p-4">
            <h2 class="mb-4 text-lg font-semibold text-neutral-800">Order Information</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Invoice Number</label>
                <input
                  v-model="form.invoice_no"
                  type="text"
                  readonly
                  class="w-full rounded-xl border border-neutral-200 bg-neutral-50 px-4 py-2 outline-none"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Date</label>
                <input
                  v-model="form.invoice_date"
                  type="date"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                />
                <p v-if="form.errors.invoice_date" class="mt-1 text-sm text-red-600">{{ form.errors.invoice_date }}</p>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Ship Date</label>
                <input
                  v-model="form.ship_date"
                  type="date"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Ship Via</label>
                <input
                  v-model="form.ship_via"
                  type="text"
                  placeholder="Courier / Pickup / Delivery"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Invoice Status</label>
                <select
                  v-model="form.status"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                >
                  <option value="draft">Draft</option>
                  <option value="finalized">Finalized</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Order Status</label>
                <select
                  v-model="form.order_status"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                >
                  <option
                    v-for="option in orderStatusOptions"
                    :key="option.value"
                    :value="option.value"
                  >
                    {{ option.label }}
                  </option>
                </select>
                <p v-if="orderStatusHelper" class="mt-1 text-xs text-neutral-500">{{ orderStatusHelper }}</p>
                <p v-if="form.errors.order_status" class="mt-1 text-sm text-red-600">{{ form.errors.order_status }}</p>
              </div>

              <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-neutral-700">Notes</label>
                <textarea
                  v-model="form.notes"
                  rows="3"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                />
              </div>

              <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-neutral-700">Terms / Remarks</label>
                <textarea
                  v-model="form.terms"
                  rows="3"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                />
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-neutral-200 p-4">
            <h2 class="mb-4 text-lg font-semibold text-neutral-800">Payment Options</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Payment Type</label>
                <input
                  :value="paymentTypeLabel"
                  type="text"
                  readonly
                  class="w-full rounded-xl border border-neutral-200 bg-neutral-50 px-4 py-2 outline-none"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Tax Amount</label>
                <input
                  v-model="form.tax_amount"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                />
                <p v-if="form.errors.tax_amount" class="mt-1 text-sm text-red-600">{{ form.errors.tax_amount }}</p>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Cash Paid</label>
                <input
                  v-model="form.cash_paid"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                />
                <p v-if="form.errors.cash_paid" class="mt-1 text-sm text-red-600">{{ form.errors.cash_paid }}</p>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Card Paid</label>
                <input
                  v-model="form.card_paid"
                  type="number"
                  min="0"
                  step="0.01"
                  @input="onCardPaidManual"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                />
                <div class="mt-1 flex items-center justify-between text-xs">
                  <span class="text-neutral-500">Remaining suggestion: {{ remainingCardSuggestion.toFixed(2) }}</span>
                  <button
                    type="button"
                    @click="useRemainingForCard"
                    class="rounded-full border border-blue-200 px-3 py-1 font-medium text-blue-600 hover:bg-blue-50"
                  >
                    Use Remaining
                  </button>
                </div>
                <p v-if="form.errors.card_paid" class="mt-1 text-sm text-red-600">{{ form.errors.card_paid }}</p>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Advance Amount</label>
                <input
                  v-model="form.advance_amount"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                />
                <p v-if="form.errors.advance_amount" class="mt-1 text-sm text-red-600">{{ form.errors.advance_amount }}</p>
              </div>

              <div class="rounded-xl border border-neutral-200 px-4 py-3">
                <div class="flex items-center justify-between gap-4">
                  <div>
                    <div class="text-sm font-medium text-neutral-800">Delivery</div>
                    <div class="text-xs text-neutral-500">Enable delivery details for this invoice</div>
                  </div>

                  <button
                    type="button"
                    @click="form.delivery_enabled = !form.delivery_enabled"
                    :class="[
                      'relative inline-flex h-7 w-14 items-center rounded-full transition',
                      form.delivery_enabled ? 'bg-blue-600' : 'bg-neutral-300'
                    ]"
                  >
                    <span
                      :class="[
                        'inline-block h-5 w-5 transform rounded-full bg-white transition',
                        form.delivery_enabled ? 'translate-x-8' : 'translate-x-1'
                      ]"
                    />
                  </button>
                </div>
              </div>

              <template v-if="form.delivery_enabled">
                <div>
                  <label class="mb-1 block text-sm font-medium text-neutral-700">Delivery Method</label>
                  <select
                    v-model="form.delivery_method"
                    class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                  >
                    <option value="">Select delivery method</option>
                    <option
                      v-for="option in deliveryMethodOptions"
                      :key="option.value"
                      :value="option.value"
                    >
                      {{ option.label }}
                    </option>
                  </select>
                  <p v-if="form.errors.delivery_method" class="mt-1 text-sm text-red-600">{{ form.errors.delivery_method }}</p>
                </div>

                <div>
                  <label class="mb-1 block text-sm font-medium text-neutral-700">Payment Status</label>
                  <select
                    v-model="form.delivery_payment_status"
                    class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                  >
                    <option value="">Select payment status</option>
                    <option
                      v-for="option in deliveryPaymentStatusOptions"
                      :key="option.value"
                      :value="option.value"
                    >
                      {{ option.label }}
                    </option>
                  </select>
                  <p v-if="form.errors.delivery_payment_status" class="mt-1 text-sm text-red-600">{{ form.errors.delivery_payment_status }}</p>
                </div>

                <div>
                  <label class="mb-1 block text-sm font-medium text-neutral-700">Tracking ID</label>
                  <input
                    v-model="form.tracking_id"
                    type="text"
                    placeholder="TRK-12345"
                    class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                  />
                  <p v-if="form.errors.tracking_id" class="mt-1 text-sm text-red-600">{{ form.errors.tracking_id }}</p>
                </div>

                <div>
                  <label class="mb-1 block text-sm font-medium text-neutral-700">Delivery Agent</label>
                  <select
                    v-model="form.delivery_agent"
                    class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                  >
                    <option value="">Select delivery agent</option>
                    <option
                      v-for="option in deliveryAgentOptions"
                      :key="option.value"
                      :value="option.value"
                    >
                      {{ option.label }}
                    </option>
                  </select>
                  <p v-if="form.errors.delivery_agent" class="mt-1 text-sm text-red-600">{{ form.errors.delivery_agent }}</p>
                </div>

                <div class="md:col-span-2">
                  <label class="mb-1 block text-sm font-medium text-neutral-700">Delivery Amount</label>
                  <input
                    v-model="form.delivery_amount"
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                  />
                  <p v-if="form.errors.delivery_amount" class="mt-1 text-sm text-red-600">{{ form.errors.delivery_amount }}</p>
                </div>
              </template>
            </div>
          </div>

          <div class="rounded-2xl border border-neutral-200 p-4">
            <h2 class="mb-4 text-lg font-semibold text-neutral-800">Bill To</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Customer Name</label>
                <input
                  v-model="form.customer_name"
                  type="text"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                />
                <p v-if="form.errors.customer_name" class="mt-1 text-sm text-red-600">{{ form.errors.customer_name }}</p>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Customer Contact Number</label>
                <input
                  v-model="form.customer_contact_number"
                  type="text"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                />
                <p v-if="form.errors.customer_contact_number" class="mt-1 text-sm text-red-600">{{ form.errors.customer_contact_number }}</p>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Customer Address</label>
                <input
                  v-model="form.customer_address"
                  type="text"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Customer Email</label>
                <input
                  v-model="form.customer_email"
                  type="email"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                />
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-neutral-200 p-4">
            <div class="mb-4 flex items-center justify-between">
              <h2 class="text-lg font-semibold text-neutral-800">Product Items</h2>

              <div class="flex flex-wrap gap-2">
                <button
                  v-for="option in productTypeOptions"
                  :key="option.value"
                  type="button"
                  @click="onTypeChange(option.value)"
                  :class="[
                    'rounded-full px-4 py-2 text-sm font-medium transition',
                    draftItem.product_type === option.value
                      ? 'bg-[#38bdf8] text-white'
                      : 'border border-neutral-200 text-neutral-700 hover:bg-neutral-100'
                  ]"
                >
                  {{ option.label }}
                </button>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-neutral-700">Search Product</label>
                <input
                  v-model="productSearch.keyword"
                  type="text"
                  placeholder="Search product / brand / category / SKU"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                />
              </div>

              <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-neutral-700">Product</label>
                <select
                  v-model="draftItem.product_id"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                >
                  <option :value="null">Select product</option>
                  <option v-for="product in filteredCatalogProducts" :key="`${product.product_type}-${product.id}`" :value="product.id">
                    {{ product.label }}{{ product.brand ? ` - ${product.brand}` : '' }}
                  </option>
                </select>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Storage / Unit</label>
                <select
                  v-model="draftItem.storage"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                >
                  <option value="">Select option</option>
                  <option v-for="storage in productStorages" :key="storage" :value="storage">
                    {{ storage }}
                  </option>
                </select>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Color</label>
                <select
                  v-model="draftItem.color"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                >
                  <option value="">Select color</option>
                  <option v-for="color in productColors" :key="color" :value="color">
                    {{ color }}
                  </option>
                </select>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Size / Volume</label>
                <select
                  v-model="draftItem.size"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                >
                  <option value="">Select size / volume</option>
                  <option v-for="size in productSizes" :key="size" :value="size">
                    {{ size }}
                  </option>
                </select>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">IMEI / Serial</label>
                <input
                  v-model="draftItem.imei_serial"
                  type="text"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Warranty</label>
                <input
                  v-model="draftItem.warranty"
                  type="text"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                />
              </div>

              <div class="flex items-center gap-3">
                <input
                  id="preorder"
                  v-model="draftItem.is_preorder"
                  type="checkbox"
                  class="h-4 w-4 rounded border-neutral-300 text-[#38bdf8] focus:ring-[#38bdf8]"
                />
                <label for="preorder" class="text-sm font-medium text-neutral-700">Pre-Order</label>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Qty</label>
                <input
                  v-model="draftItem.qty"
                  type="number"
                  min="1"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Regular Price</label>
                <input
                  v-model="draftItem.regular_price"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Discount Type</label>
                <select
                  v-model="draftItem.discount_type"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                >
                  <option value="">No discount</option>
                  <option value="percentage">Percentage</option>
                  <option value="fixed">Fixed Amount</option>
                </select>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Discount Value</label>
                <input
                  v-model="draftItem.discount_value"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-[#38bdf8]"
                />
              </div>

              <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-neutral-700">Discounted Price</label>
                <input
                  :value="draftItem.discounted_unit_price"
                  type="number"
                  readonly
                  class="w-full rounded-xl border border-neutral-200 bg-neutral-50 px-4 py-2 outline-none"
                />
              </div>
            </div>

            <div class="mt-4 flex justify-end">
              <button
                type="button"
                @click="addDraftItem"
                class="rounded-full bg-[#38bdf8] px-5 py-2 text-sm font-medium text-white transition hover:bg-[#7dd3fc]"
              >
                Add This Product
              </button>
            </div>

            <div class="mt-6 overflow-x-auto" v-if="form.items.length">
              <table class="min-w-full border-collapse text-sm">
                <thead>
                  <tr class="bg-neutral-50">
                    <th class="border border-neutral-200 px-3 py-2 text-left">Item</th>
                    <th class="border border-neutral-200 px-3 py-2 text-left">Description</th>
                    <th class="border border-neutral-200 px-3 py-2 text-center">Qty</th>
                    <th class="border border-neutral-200 px-3 py-2 text-right">Unit Price</th>
                    <th class="border border-neutral-200 px-3 py-2 text-center">%</th>
                    <th class="border border-neutral-200 px-3 py-2 text-right">Total</th>
                    <th class="border border-neutral-200 px-3 py-2 text-center">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in form.items" :key="`${item.product_type}-${index}`">
                    <td class="border border-neutral-200 px-3 py-2">{{ index + 1 }}</td>
                    <td class="border border-neutral-200 px-3 py-2">{{ item.description }}</td>
                    <td class="border border-neutral-200 px-3 py-2 text-center">{{ item.qty }}</td>
                    <td class="border border-neutral-200 px-3 py-2 text-right">{{ Number(item.regular_price).toFixed(2) }}</td>
                    <td class="border border-neutral-200 px-3 py-2 text-center">
                      {{ item.discount_type === 'percentage' ? (item.discount_percent_display ?? item.discount_value ?? 0) : '-' }}
                    </td>
                    <td class="border border-neutral-200 px-3 py-2 text-right">{{ Number(item.line_total).toFixed(2) }}</td>
                    <td class="border border-neutral-200 px-3 py-2 text-center">
                      <button
                        type="button"
                        @click="removeItem(index)"
                        class="rounded-full border border-red-200 px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-50"
                      >
                        Remove
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
              <p v-if="form.errors.items" class="mt-2 text-sm text-red-600">{{ form.errors.items }}</p>
            </div>
          </div>

          <div class="rounded-2xl border border-neutral-200 p-4">
            <h2 class="mb-4 text-lg font-semibold text-neutral-800">Summary</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="rounded-xl bg-neutral-50 px-4 py-3">
                <div class="text-xs uppercase tracking-wide text-neutral-500">Subtotal</div>
                <div class="text-lg font-semibold text-neutral-800">{{ subtotal.toFixed(2) }}</div>
              </div>
              <div class="rounded-xl bg-neutral-50 px-4 py-3">
                <div class="text-xs uppercase tracking-wide text-neutral-500">Total Discount</div>
                <div class="text-lg font-semibold text-neutral-800">{{ totalDiscount.toFixed(2) }}</div>
              </div>
              <div class="rounded-xl bg-neutral-50 px-4 py-3">
                <div class="text-xs uppercase tracking-wide text-neutral-500">Delivery Amount</div>
                <div class="text-lg font-semibold text-neutral-800">{{ deliveryAmountValue.toFixed(2) }}</div>
              </div>
              <div class="rounded-xl bg-neutral-50 px-4 py-3">
                <div class="text-xs uppercase tracking-wide text-neutral-500">Total Paid</div>
                <div class="text-lg font-semibold text-neutral-800">{{ totalPaid.toFixed(2) }}</div>
              </div>
              <div class="rounded-xl bg-neutral-50 px-4 py-3 md:col-span-2">
                <div class="text-xs uppercase tracking-wide text-neutral-500">Balance Due</div>
                <div class="text-lg font-semibold text-neutral-800">{{ balanceDue.toFixed(2) }}</div>
              </div>
            </div>
          </div>

          <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
            <button
              type="button"
              @click="resetForm"
              class="inline-flex items-center justify-center rounded-full border border-neutral-200 px-5 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100"
            >
              Reset
            </button>

            <button
              type="button"
              @click="submit('draft')"
              :disabled="form.processing"
              class="inline-flex items-center justify-center rounded-full border border-yellow-300 px-5 py-2 text-sm font-medium text-yellow-700 transition hover:bg-yellow-50 disabled:opacity-50"
            >
              {{ form.processing ? 'Saving...' : 'Save Draft' }}
            </button>

            <button
              type="button"
              @click="submit('finalize')"
              :disabled="form.processing"
              class="inline-flex items-center justify-center rounded-full bg-[#38bdf8] px-6 py-2 text-sm font-medium text-white transition hover:bg-[#7dd3fc] disabled:opacity-50"
            >
              {{ form.processing ? 'Generating...' : 'Create Order Invoice' }}
            </button>
          </div>
        </form>

        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-neutral-800">Live Invoice Preview</h2>
            <span class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-medium text-neutral-600">
              PDF Layout Preview
            </span>
          </div>

          <div class="mx-auto max-w-[800px] overflow-hidden rounded-2xl border border-[#012d62]/20 bg-white text-sm text-slate-800 shadow-sm">
            <div class="h-2 bg-[#012d62]" />

            <div class="p-6">
              <div class="border-b-2 border-[#012d62]/15 pb-6">
                <div class="grid grid-cols-2 items-start gap-8">
                  <div>
                    <div class="mb-4">
                      <img
                        v-if="shop.logo_url"
                        :src="shop.logo_url"
                        alt="Logo"
                        class="max-h-[62px] max-w-[220px] object-contain object-left"
                      />
                      <div v-else class="text-[34px] font-bold text-[#012d62]">
                        {{ shop.name }}
                      </div>
                    </div>

                    <div class="space-y-1 text-[15px] leading-7 text-slate-700">
                      <div v-for="line in shop.address_lines" :key="line">
                        {{ line }}
                      </div>
                      <div v-if="shop.phone">
                        <span class="font-semibold text-slate-900">Phone:</span> {{ shop.phone }}
                      </div>
                      <div v-if="shop.website">
                        <span class="font-semibold text-slate-900">Web:</span> {{ shop.website }}
                      </div>
                    </div>
                  </div>

                  <div class="text-right">
                    <div class="text-[30px] font-bold leading-none tracking-[0.05em] text-[#012d62]">
                      INVOICE
                    </div>

                    <div class="mt-10 ml-auto w-full max-w-[280px] space-y-1 text-[15px] leading-7">
                      <div class="flex items-start justify-between gap-4">
                        <span class="text-slate-500">Date:</span>
                        <span class="font-semibold text-slate-800">{{ form.invoice_date || '-' }}</span>
                      </div>

                      <div class="flex items-start justify-between gap-4">
                        <span class="text-slate-500">Invoice No:</span>
                        <span class="font-semibold text-slate-800">{{ form.invoice_no }}</span>
                      </div>

                      <div class="flex items-start justify-between gap-4">
                        <span class="text-slate-500">Invoice Status:</span>
                        <span class="font-semibold capitalize text-slate-800">{{ form.status }}</span>
                      </div>

                      <div class="flex items-start justify-between gap-4">
                        <span class="text-slate-500">Order Status:</span>
                        <span class="font-semibold capitalize text-slate-800">{{ form.order_status }}</span>
                      </div>

                      <div class="flex items-start justify-between gap-4">
                        <span class="text-slate-500">Payment Type:</span>
                        <span class="font-semibold text-slate-800">{{ paymentTypeLabel }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="py-5">
                <div class="mb-3 border-b-2 border-[#012d62]/15 pb-2 text-sm font-bold uppercase tracking-wide text-blue-900">
                  Bill To
                </div>

                <div class="grid grid-cols-1 items-start gap-4 rounded-xl border border-[#012d62]/15 bg-slate-50 p-4 md:grid-cols-2">
                  <div class="space-y-1.5">
                    <div><span class="font-semibold text-slate-500">Name:</span> {{ form.customer_name || '-' }}</div>
                    <div><span class="font-semibold text-slate-500">Contact:</span> {{ form.customer_contact_number || '-' }}</div>
                    <div v-if="form.customer_address"><span class="font-semibold text-slate-500">Address:</span> {{ form.customer_address }}</div>
                    <div v-if="form.customer_email"><span class="font-semibold text-slate-500">Email:</span> {{ form.customer_email }}</div>
                  </div>

                  <div class="space-y-1.5 md:text-right">
                    <template v-if="form.delivery_enabled">
                      <div><span class="font-semibold text-slate-500">Delivery Type:</span> {{ deliveryMethodPreviewLabel }}</div>
                      <div><span class="font-semibold text-slate-500">Delivery Agent:</span> {{ deliveryAgentPreviewLabel }}</div>
                      <div><span class="font-semibold text-slate-500">Payment Status:</span> {{ deliveryPaymentStatusPreviewLabel }}</div>
                      <div v-if="form.tracking_id"><span class="font-semibold text-slate-500">Tracking ID:</span> {{ form.tracking_id }}</div>
                      <div><span class="font-semibold text-slate-500">Delivery Amount:</span> {{ deliveryAmountValue.toFixed(2) }}</div>
                    </template>

                    <template v-else>
                      <div v-if="form.ship_date"><span class="font-semibold text-slate-500">Ship Date:</span> {{ form.ship_date }}</div>
                      <div v-if="form.ship_via"><span class="font-semibold text-slate-500">Ship Via:</span> {{ form.ship_via }}</div>
                    </template>
                  </div>
                </div>
              </div>

              <div class="pb-5">
                <div class="mb-3 border-b-2 border-[#012d62]/15 pb-2 text-sm font-bold uppercase tracking-wide text-blue-900">
                  Items
                </div>

                <div class="overflow-x-auto">
                  <table class="min-w-full border-collapse text-sm">
                    <thead>
                      <tr>
                        <th class="border border-[#012d62]/15 bg-blue-50 px-3 py-2 text-left font-bold text-blue-900">ITEM</th>
                        <th class="border border-[#012d62]/15 bg-blue-50 px-3 py-2 text-left font-bold text-blue-900">DESCRIPTION</th>
                        <th class="border border-[#012d62]/15 bg-blue-50 px-3 py-2 text-center font-bold text-blue-900">QTY</th>
                        <th class="border border-[#012d62]/15 bg-blue-50 px-3 py-2 text-right font-bold text-blue-900">UNIT PRICE</th>
                        <th class="border border-[#012d62]/15 bg-blue-50 px-3 py-2 text-center font-bold text-blue-900">%</th>
                        <th class="border border-[#012d62]/15 bg-blue-50 px-3 py-2 text-right font-bold text-blue-900">TOTAL</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-if="!form.items.length">
                        <td colspan="6" class="border border-blue-100 px-3 py-8 text-center text-slate-400">
                          No invoice items added yet.
                        </td>
                      </tr>

                      <tr
                        v-for="(item, index) in form.items"
                        :key="`preview-${index}`"
                        class="odd:bg-white even:bg-slate-50"
                      >
                        <td class="border border-[#012d62]/15 px-3 py-2">{{ index + 1 }}</td>
                        <td class="border border-[#012d62]/15 px-3 py-2">{{ item.description }}</td>
                        <td class="border border-blue-100 px-3 py-2 text-center">{{ item.qty }}</td>
                        <td class="border border-blue-100 px-3 py-2 text-right">{{ Number(item.regular_price).toFixed(2) }}</td>
                        <td class="border border-blue-100 px-3 py-2 text-center">
                          {{ item.discount_type === 'percentage' ? (item.discount_percent_display ?? item.discount_value ?? 0) : '-' }}
                        </td>
                        <td class="border border-blue-100 px-3 py-2 text-right">{{ Number(item.line_total).toFixed(2) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="ml-auto mt-2 w-full max-w-sm rounded-xl border border-[#012d62]/20 bg-slate-50 p-4">
                <div class="flex items-center justify-between border-b border-blue-100 py-2">
                  <span class="text-slate-500">Subtotal</span>
                  <span class="font-medium">{{ subtotal.toFixed(2) }}</span>
                </div>

                <div class="flex items-center justify-between border-b border-blue-100 py-2">
                  <span class="text-slate-500">Total Discount</span>
                  <span class="font-medium">{{ totalDiscount.toFixed(2) }}</span>
                </div>

                <div class="flex items-center justify-between border-b border-blue-100 py-2">
                  <span class="text-slate-500">Tax</span>
                  <span class="font-medium">{{ Number(form.tax_amount || 0).toFixed(2) }}</span>
                </div>

                <div class="my-2 flex items-center justify-between rounded-lg bg-blue-100 px-3 py-3 text-blue-900">
                  <span class="font-bold">Grand Total</span>
                  <span class="text-base font-bold">{{ grandTotal.toFixed(2) }}</span>
                </div>

                <div class="flex items-center justify-between border-b border-blue-100 py-2">
                  <span class="text-slate-500">Cash Paid</span>
                  <span class="font-medium">{{ Number(form.cash_paid || 0).toFixed(2) }}</span>
                </div>

                <div class="flex items-center justify-between border-b border-blue-100 py-2">
                  <span class="text-slate-500">Card Paid</span>
                  <span class="font-medium">{{ Number(form.card_paid || 0).toFixed(2) }}</span>
                </div>

                <div class="flex items-center justify-between border-b border-blue-100 py-2">
                  <span class="text-slate-500">Advance Amount</span>
                  <span class="font-medium">{{ Number(form.advance_amount || 0).toFixed(2) }}</span>
                </div>

                <div class="flex items-center justify-between border-b border-blue-100 py-2">
                  <span class="text-slate-500">Delivery Amount</span>
                  <span class="font-medium">{{ deliveryAmountValue.toFixed(2) }}</span>
                </div>

                <div class="flex items-center justify-between border-b border-blue-100 py-2">
                  <span class="text-slate-500">Total Paid</span>
                  <span class="font-medium">{{ totalPaid.toFixed(2) }}</span>
                </div>

                <div class="flex items-center justify-between pt-3 text-base font-bold text-slate-900">
                  <span>Balance Due</span>
                  <span>{{ balanceDue.toFixed(2) }}</span>
                </div>
              </div>

              <div v-if="form.notes" class="mt-6 rounded-xl border border-blue-100 bg-slate-50 p-4">
                <div class="mb-1 font-semibold text-blue-900">Notes</div>
                <div class="text-slate-700">{{ form.notes }}</div>
              </div>

              <div v-if="form.terms" class="mt-4 rounded-xl border border-blue-100 bg-slate-50 p-4">
                <div class="mb-1 font-semibold text-blue-900">Terms / Remarks</div>
                <div class="text-slate-700">{{ form.terms }}</div>
              </div>

              <div class="mt-8 border-t border-blue-100 pt-4 text-center text-xs font-semibold text-[#012d62]">
                {{ shop.website || 'www.dezestore.com' }}
              </div>
            </div>

            <div class="h-2 bg-[#012d62]" />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
