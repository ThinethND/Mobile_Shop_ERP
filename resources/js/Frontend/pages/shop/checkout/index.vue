<script setup lang="ts">
import axios from 'axios'
import { Link } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { route } from 'ziggy-js'
import AppLayout from '@/Frontend/layouts/AppLayout.vue'
import { useCart } from '../composables/useCart'

defineOptions({
  layout: AppLayout,
})

const props = defineProps<{
  shippingMethods?: ShippingMethod[]
}>()

type CheckoutStep = 'information' | 'shipping'

type ShippingMethodCode = '' | 'cash_on_delivery' | 'store_pickup' | 'bank_transfer_delivery'

type ShippingMethod = {
  code: Exclude<ShippingMethodCode, ''>
  name: string
  description: string
  fee: number
}

type CartItem = {
  key: string
  id?: number | string | null
  productType?: string | null
  name: string
  image?: string | null
  quantity: number
  price: number
  colorName?: string | null
  storageLabel?: string | null
  sizeLabel?: string | null
  variantLabel?: string | null
  sku?: string | null
}

function formatPrice(value: number | null | undefined) {
  if (value === null || typeof value === 'undefined' || Number.isNaN(Number(value))) {
    return 'Rs 0.00'
  }

  return `Rs ${Number(value).toLocaleString('en-LK', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}

const {
  items,
  subtotal,
  totalItems,
  removeItem,
  clearCart,
} = useCart()

const canRemoveSummaryItems = computed(() => items.value.length > 1)

const step = ref<CheckoutStep>('information')
const shippingUnlocked = ref(false)
const orderPlaced = ref(false)
const placingOrder = ref(false)
const serverMessage = ref('')
const orderNumber = ref('')
const formErrors = ref<Record<string, string[]>>({})

const contactForm = ref({
  full_name: '',
  email: '',
  phone: '',
  address_line_1: '',
  address_line_2: '',
  city: '',
  postal_code: '',
  delivery_note: '',
})

const shippingMethod = ref<ShippingMethodCode>('')

const shippingMethods: ShippingMethod[] = [
  {
    code: 'cash_on_delivery',
    name: 'Cash on delivery',
    description: 'Delivered in 3–7 business days. Pay when the parcel arrives.',
    fee: 450,
  },
  {
    code: 'store_pickup',
    name: 'Store pickup',
    description: 'Collect your order from the store after confirmation.',
    fee: 0,
  },
  {
    code: 'bank_transfer_delivery',
    name: 'Bank transfer delivery',
    description: 'Delivered in 3–7 business days after payment confirmation.',
    fee: 450,
  },
]

const checkoutShippingMethods = computed<ShippingMethod[]>(() => {
  return Array.isArray(props.shippingMethods) && props.shippingMethods.length
    ? props.shippingMethods
    : shippingMethods.map((method) => ({
        ...method,
        description: method.description
          .replace('3â€“7', '3-7')
          .replace('3–7', '3-7'),
      }))
})

watch(
  () => contactForm.value.phone,
  (value) => {
    const onlyDigits = String(value ?? '').replace(/\D/g, '')
    const withoutLeadingZero = onlyDigits.startsWith('0') ? onlyDigits.slice(1) : onlyDigits
    const limited = withoutLeadingZero.slice(0, 9)

    if (limited !== value) {
      contactForm.value.phone = limited
    }
  },
)

const selectedShippingMethod = computed(() => {
  return checkoutShippingMethods.value.find((item) => item.code === shippingMethod.value) || null
})

const shippingFee = computed(() => selectedShippingMethod.value?.fee ?? 0)
const grandTotal = computed(() => subtotal.value + shippingFee.value)

const emailIsValid = computed(() => {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(contactForm.value.email.trim())
})

const phoneIsValid = computed(() => {
  return contactForm.value.phone.trim().length === 9
})

const infoCompleted = computed(() => {
  return !!contactForm.value.full_name.trim()
    && emailIsValid.value
    && phoneIsValid.value
    && !!contactForm.value.address_line_1.trim()
    && !!contactForm.value.city.trim()
})

const displayPhone = computed(() => {
  return contactForm.value.phone ? `+94 ${contactForm.value.phone}` : ''
})

const displayAddress = computed(() => {
  return [
    contactForm.value.address_line_1,
    contactForm.value.address_line_2,
    contactForm.value.city,
    contactForm.value.postal_code,
  ]
    .filter((item) => String(item || '').trim().length)
    .join(', ')
})

function fieldError(field: string) {
  return formErrors.value[field]?.[0] || ''
}

function clearServerState() {
  serverMessage.value = ''
  formErrors.value = {}
}

function goToShipping() {
  if (!infoCompleted.value) return
  clearServerState()
  shippingUnlocked.value = true
  step.value = 'shipping'
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function goToInformation() {
  clearServerState()
  step.value = 'information'
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function openShippingFromBreadcrumb() {
  if (!shippingUnlocked.value) return
  clearServerState()
  step.value = 'shipping'
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function mappedItems() {
  return (items.value as CartItem[]).map((item) => ({
    key: item.key,
    id: item.id ?? null,
    productType: item.productType ?? null,
    name: item.name,
    sku: item.sku ?? null,
    price: Number(item.price || 0),
    quantity: Number(item.quantity || 1),
    image: item.image ?? null,
    colorName: item.colorName ?? null,
    storageLabel: item.storageLabel ?? null,
    sizeLabel: item.sizeLabel ?? null,
    variantLabel: item.variantLabel ?? null,
  }))
}

async function submitOrder() {
  if (!items.value.length || !selectedShippingMethod.value || placingOrder.value) return

  clearServerState()
  placingOrder.value = true

  try {
    const response = await axios.post(route('frontend.checkout.store'), {
      ...contactForm.value,
      shipping_method: {
        code: selectedShippingMethod.value.code,
        name: selectedShippingMethod.value.name,
        fee: selectedShippingMethod.value.fee,
      },
      items: mappedItems(),
    })

    orderNumber.value = response.data?.order_number || ''
    serverMessage.value = response.data?.message || 'Order placed successfully.'
    orderPlaced.value = true
    clearCart()
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } catch (error: any) {
    if (error?.response?.status === 422) {
      formErrors.value = error.response.data?.errors || {}
      serverMessage.value = 'Please check the checkout details and try again.'

      if (formErrors.value.email || formErrors.value.full_name || formErrors.value.phone || formErrors.value.address_line_1 || formErrors.value.city) {
        step.value = 'information'
      }
    } else {
      serverMessage.value = error?.response?.data?.message || 'Something went wrong while placing the order.'
    }

    window.scrollTo({ top: 0, behavior: 'smooth' })
  } finally {
    placingOrder.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-[#f8f8fa] text-slate-950">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
      <nav
        v-if="!orderPlaced && items.length"
        aria-label="Breadcrumb"
        class="mb-6"
      >
        <ol class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
          <li class="flex items-center gap-2">
            <Link :href="route('frontend.root')" class="transition hover:text-slate-900">
              Home
            </Link>
            <span class="text-slate-300">/</span>
          </li>

          <li class="flex items-center gap-2">
            <Link :href="route('frontend.cart.index')" class="transition hover:text-slate-900">
              Cart
            </Link>
            <span class="text-slate-300">/</span>
          </li>

          <li class="flex items-center gap-2">
            <button
              v-if="shippingUnlocked"
              type="button"
              class="transition hover:text-slate-900"
              :class="step === 'information' ? 'font-medium text-slate-900' : ''"
              @click="goToInformation"
            >
              Information
            </button>
            <span
              v-else
              class="font-medium text-slate-900"
            >
              Information
            </span>
            <span class="text-slate-300">/</span>
          </li>

          <li>
            <button
              type="button"
              class="transition"
              :class="shippingUnlocked
                ? (step === 'shipping' ? 'font-medium text-slate-900 hover:text-slate-900' : 'text-slate-500 hover:text-slate-900')
                : 'cursor-not-allowed text-slate-300'"
              :disabled="!shippingUnlocked"
              @click="openShippingFromBreadcrumb"
            >
              Shipping
            </button>
          </li>
        </ol>
      </nav>

      <div class="flex flex-col gap-3 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">
            {{ step === 'information' ? 'Information' : 'Shipping' }}
          </p>
          <h1 class="mt-2 text-3xl font-semibold tracking-[-0.03em] text-slate-950 sm:text-[2.35rem]">
            {{ step === 'information' ? 'Checkout Information' : 'Shipping Method' }}
          </h1>
          <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600 sm:text-[15px]">
            {{ step === 'information'
              ? 'Complete your contact and delivery details to continue.'
              : 'Review your details, choose a shipping method, and confirm your order.' }}
          </p>
        </div>

        <p class="text-sm font-medium text-slate-500">
          {{ totalItems }} item{{ totalItems === 1 ? '' : 's' }}
        </p>
      </div>

      <div
        v-if="serverMessage && !orderPlaced"
        class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
      >
        {{ serverMessage }}
      </div>

      <div
        v-if="orderPlaced"
        class="mt-8 rounded-[30px] border border-emerald-200 bg-emerald-50 px-6 py-10 text-center shadow-sm"
      >
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white text-emerald-600 shadow-sm">
          <svg class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor">
            <path
              fill-rule="evenodd"
              d="M16.704 5.29a1 1 0 010 1.415l-7.25 7.25a1 1 0 01-1.415 0l-3-3a1 1 0 111.414-1.414l2.293 2.293 6.543-6.544a1 1 0 011.415 0z"
              clip-rule="evenodd"
            />
          </svg>
        </div>

        <h2 class="mt-5 text-2xl font-semibold text-emerald-700">Order placed successfully</h2>
        <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-emerald-700/80 sm:text-[15px]">
          {{ serverMessage || 'Your order has been confirmed. A confirmation email has been sent to your email address.' }}
        </p>
       <p v-if="orderNumber" class="mt-3 text-sm font-semibold text-emerald-800">
  Invoice Number: {{ orderNumber }}
</p>
        <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
          <Link
            :href="route('frontend.tech-products.index')"
            class="inline-flex min-h-[52px] items-center justify-center rounded-full bg-slate-950 px-7 text-sm font-semibold text-white transition hover:bg-slate-800"
          >
            Continue Shopping
          </Link>

          <Link
            :href="route('frontend.cart.index')"
            class="inline-flex min-h-[52px] items-center justify-center rounded-full border border-slate-900 px-7 text-sm font-semibold text-slate-950 transition hover:bg-slate-50"
          >
            Back to Cart
          </Link>
        </div>
      </div>

      <div
        v-else-if="!items.length"
        class="mt-8 rounded-[32px] border border-dashed border-slate-200 bg-white px-6 py-14 text-center shadow-sm"
      >
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-50">
          <svg class="h-8 w-8 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.4 10.2a1 1 0 00.98.8H18.8a1 1 0 00.97-.76L21 7H7" />
            <circle cx="10" cy="20" r="1.5" />
            <circle cx="18" cy="20" r="1.5" />
          </svg>
        </div>

        <h2 class="mt-5 text-2xl font-semibold text-slate-950">No items available for checkout</h2>
        <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-slate-500 sm:text-[15px]">
          Add products to the cart first, then return here to complete the purchase.
        </p>

        <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
          <Link
            :href="route('frontend.tech-products.index')"
            class="inline-flex min-h-[52px] items-center justify-center rounded-full bg-slate-950 px-7 text-sm font-semibold text-white transition hover:bg-slate-800"
          >
            Browse Products
          </Link>

          <Link
            :href="route('frontend.cart.index')"
            class="inline-flex min-h-[52px] items-center justify-center rounded-full border border-slate-900 px-7 text-sm font-semibold text-slate-950 transition hover:bg-slate-50"
          >
            View Cart
          </Link>
        </div>
      </div>

      <div
        v-else
        class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_380px]"
      >
        <Transition name="checkout-step" mode="out-in">
          <section
            :key="step"
            class="min-w-0"
          >
            <div v-if="step === 'information'" class="space-y-8">
              <div class="border-b border-slate-200 pb-8">
                <h2 class="text-xl font-semibold text-slate-950">Contact Information</h2>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                  <label class="space-y-2 sm:col-span-2">
                    <span class="text-sm font-medium text-slate-700">Full name</span>
                    <input
                      v-model="contactForm.full_name"
                      type="text"
                      placeholder="Enter your full name"
                      class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3.5 text-sm outline-none transition focus:border-slate-900"
                    />
                    <p v-if="fieldError('full_name')" class="text-xs text-rose-600">{{ fieldError('full_name') }}</p>
                  </label>

                  <label class="space-y-2">
                    <span class="text-sm font-medium text-slate-700">Email address</span>
                    <input
                      v-model="contactForm.email"
                      type="email"
                      placeholder="your@email.com"
                      class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3.5 text-sm outline-none transition focus:border-slate-900"
                    />
                    <p v-if="fieldError('email')" class="text-xs text-rose-600">{{ fieldError('email') }}</p>
                  </label>

                  <label class="space-y-2">
                    <span class="text-sm font-medium text-slate-700">Contact number</span>

                    <div class="flex overflow-hidden rounded-2xl border border-slate-300 bg-white transition focus-within:border-slate-900">
                      <div class="inline-flex items-center gap-2 border-r border-slate-200 px-4 text-sm font-medium text-slate-700">
                        <span class="text-base">🇱🇰</span>
                        <span>+94</span>
                      </div>

                      <input
                        v-model="contactForm.phone"
                        type="text"
                        inputmode="numeric"
                        maxlength="9"
                        placeholder="771234567"
                        class="w-full border-0 bg-transparent px-4 py-3.5 text-sm outline-none"
                      />
                    </div>
                    <p v-if="fieldError('phone')" class="text-xs text-rose-600">{{ fieldError('phone') }}</p>
                  </label>
                </div>
              </div>

              <div class="border-b border-slate-200 pb-8">
                <h2 class="text-xl font-semibold text-slate-950">Shipping Address</h2>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                  <label class="space-y-2 sm:col-span-2">
                    <span class="text-sm font-medium text-slate-700">Address line 1</span>
                    <input
                      v-model="contactForm.address_line_1"
                      type="text"
                      placeholder="Street address"
                      class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3.5 text-sm outline-none transition focus:border-slate-900"
                    />
                    <p v-if="fieldError('address_line_1')" class="text-xs text-rose-600">{{ fieldError('address_line_1') }}</p>
                  </label>

                  <label class="space-y-2 sm:col-span-2">
                    <span class="text-sm font-medium text-slate-700">Address line 2 <span class="text-slate-400">(optional)</span></span>
                    <input
                      v-model="contactForm.address_line_2"
                      type="text"
                      placeholder="Apartment, suite, unit, etc."
                      class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3.5 text-sm outline-none transition focus:border-slate-900"
                    />
                  </label>

                  <label class="space-y-2">
                    <span class="text-sm font-medium text-slate-700">City</span>
                    <input
                      v-model="contactForm.city"
                      type="text"
                      placeholder="City"
                      class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3.5 text-sm outline-none transition focus:border-slate-900"
                    />
                    <p v-if="fieldError('city')" class="text-xs text-rose-600">{{ fieldError('city') }}</p>
                  </label>

                  <label class="space-y-2">
                    <span class="text-sm font-medium text-slate-700">Postal code <span class="text-slate-400">(optional)</span></span>
                    <input
                      v-model="contactForm.postal_code"
                      type="text"
                      placeholder="Postal code"
                      class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3.5 text-sm outline-none transition focus:border-slate-900"
                    />
                  </label>

                  <label class="space-y-2 sm:col-span-2">
                    <span class="text-sm font-medium text-slate-700">Delivery note <span class="text-slate-400">(optional)</span></span>
                    <textarea
                      v-model="contactForm.delivery_note"
                      rows="4"
                      placeholder="Add a note for delivery"
                      class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3.5 text-sm outline-none transition focus:border-slate-900"
                    />
                  </label>
                </div>
              </div>

              <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <Link
                  :href="route('frontend.cart.index')"
                  class="inline-flex min-h-[50px] items-center justify-center rounded-full border border-slate-300 px-6 text-sm font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-950"
                >
                  Return to cart
                </Link>

                <button
                  type="button"
                  class="inline-flex min-h-[52px] items-center justify-center rounded-full bg-slate-950 px-7 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300"
                  :disabled="!infoCompleted"
                  @click="goToShipping"
                >
                  Continue to shipping
                </button>
              </div>
            </div>

            <div v-else class="space-y-8">
              <div class="border-b border-slate-200 pb-8">
                <h2 class="text-xl font-semibold text-slate-950">Shipping Details</h2>

                <div class="mt-5 divide-y divide-slate-200 border-y border-slate-200">
                  <div class="flex flex-col gap-3 py-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="sm:w-[120px] text-sm font-medium text-slate-500">Contact</div>
                    <div class="min-w-0 flex-1 text-sm text-slate-900">
                      {{ contactForm.email }}
                    </div>
                    <button
                      type="button"
                      class="text-sm font-medium text-blue-600 transition hover:text-blue-700"
                      @click="goToInformation"
                    >
                      Change
                    </button>
                  </div>

                  <div class="flex flex-col gap-3 py-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="sm:w-[120px] text-sm font-medium text-slate-500">Phone</div>
                    <div class="min-w-0 flex-1 text-sm text-slate-900">
                      {{ displayPhone }}
                    </div>
                    <button
                      type="button"
                      class="text-sm font-medium text-blue-600 transition hover:text-blue-700"
                      @click="goToInformation"
                    >
                      Change
                    </button>
                  </div>

                  <div class="flex flex-col gap-3 py-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="sm:w-[120px] text-sm font-medium text-slate-500">Ship to</div>
                    <div class="min-w-0 flex-1 text-sm text-slate-900">
                      {{ displayAddress }}
                    </div>
                    <button
                      type="button"
                      class="text-sm font-medium text-blue-600 transition hover:text-blue-700"
                      @click="goToInformation"
                    >
                      Change
                    </button>
                  </div>
                </div>
              </div>

              <div class="border-b border-slate-200 pb-8">
                <h2 class="text-xl font-semibold text-slate-950">Shipping Method</h2>

                <div class="mt-5 space-y-3">
                  <label
                    v-for="method in checkoutShippingMethods"
                    :key="method.code"
                    class="flex cursor-pointer items-start gap-4 rounded-[24px] border border-slate-200 bg-white px-4 py-4 transition hover:border-slate-900"
                    :class="shippingMethod === method.code ? 'border-slate-900 ring-1 ring-slate-900' : ''"
                  >
                    <input
                      v-model="shippingMethod"
                      :value="method.code"
                      type="radio"
                      class="mt-1 h-4 w-4 accent-slate-950"
                    />

                    <div class="min-w-0 flex-1">
                      <div class="flex items-start justify-between gap-4">
                        <div>
                          <p class="text-sm font-semibold text-slate-950">
                            {{ method.name }}
                          </p>
                          <p class="mt-1 text-xs leading-6 text-slate-400">
                            {{ method.description }}
                          </p>
                        </div>

                        <div class="whitespace-nowrap text-sm font-semibold text-slate-950">
                          {{ method.fee > 0 ? formatPrice(method.fee) : 'Free' }}
                        </div>
                      </div>
                    </div>
                  </label>
                </div>
                <p v-if="fieldError('shipping_method.code')" class="mt-3 text-xs text-rose-600">{{ fieldError('shipping_method.code') }}</p>
              </div>

              <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <button
                  type="button"
                  class="inline-flex min-h-[50px] items-center justify-center rounded-full border border-slate-300 px-6 text-sm font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-950"
                  @click="goToInformation"
                >
                  Return to information
                </button>

                <button
                  type="button"
                  class="inline-flex min-h-[52px] items-center justify-center rounded-full bg-slate-950 px-7 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300"
                  :disabled="!selectedShippingMethod || placingOrder"
                  @click="submitOrder"
                >
                  {{ placingOrder ? 'Placing order...' : 'Confirm order' }}
                </button>
              </div>
            </div>
          </section>
        </Transition>

        <aside class="h-fit rounded-[30px] border border-slate-200 bg-white p-5 shadow-sm sm:sticky sm:top-24 sm:p-6">
          <h2 class="text-xl font-semibold text-slate-950">Order Summary</h2>

          <div class="mt-5 divide-y divide-slate-200 border-y border-slate-200">
            <article
              v-for="item in items"
              :key="item.key"
              class="flex gap-3 py-4"
            >
              <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-[18px] bg-white">
                <img
                  v-if="item.image"
                  :src="item.image"
                  :alt="item.name"
                  class="h-full w-full object-contain p-2"
                />
              </div>

              <div class="min-w-0 flex-1">
                <h3 class="line-clamp-2 text-sm font-semibold leading-6 text-slate-950">
                  {{ item.name }}
                </h3>
                <p class="mt-1 text-xs text-slate-500">
                  Qty {{ item.quantity }}
                  <span v-if="item.colorName"> • {{ item.colorName }}</span>
                  <span v-if="item.storageLabel"> • {{ item.storageLabel }}</span>
                  <span v-if="item.sizeLabel"> • {{ item.sizeLabel }}</span>
                </p>
                <p class="mt-2 text-sm font-semibold text-slate-950">
                  {{ formatPrice(item.price * item.quantity) }}
                </p>
              </div>

              <button
                v-if="canRemoveSummaryItems"
                type="button"
                class="mt-1 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-rose-300 hover:text-rose-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:ring-offset-2"
                aria-label="Remove item"
                @click="removeItem(item.key)"
              >
                <svg viewBox="0 0 24 24" class="h-4 w-4" aria-hidden="true">
                  <path
                    d="M6 6l12 12M18 6L6 18"
                    fill="none"
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.2"
                  />
                </svg>
              </button>
            </article>
          </div>

          <div class="mt-6 space-y-3 text-sm">
            <div class="flex items-center justify-between">
              <span class="text-slate-500">Subtotal</span>
              <span class="font-semibold text-slate-950">{{ formatPrice(subtotal) }}</span>
            </div>

            <div class="flex items-center justify-between">
              <span class="text-slate-500">Shipping</span>
              <span class="font-semibold text-slate-950">{{ formatPrice(shippingFee) }}</span>
            </div>

            <div class="flex items-center justify-between pt-2 text-base">
              <span class="font-medium text-slate-700">Total</span>
              <span class="text-2xl font-semibold text-slate-950">{{ formatPrice(grandTotal) }}</span>
            </div>
          </div>
        </aside>
      </div>
    </main>
  </div>
</template>

<style scoped>
.checkout-step-enter-active,
.checkout-step-leave-active {
  transition:
    opacity 0.28s ease,
    transform 0.28s ease,
    filter 0.28s ease;
}

.checkout-step-enter-from,
.checkout-step-leave-to {
  opacity: 0;
  transform: translateY(16px);
  filter: blur(1px);
}

@media (prefers-reduced-motion: reduce) {
  .checkout-step-enter-active,
  .checkout-step-leave-active {
    transition: none !important;
  }
}
</style>
