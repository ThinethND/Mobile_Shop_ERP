<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'
import AppLayout from '@/Frontend/layouts/AppLayout.vue'
import { useCart } from '../composables/useCart'
import { useWishlist } from '../composables/useWishlist'

defineOptions({
  layout: AppLayout,
})

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
  totalItems,
  removeItem,
  clearWishlist,
} = useWishlist()

const {
  addItem: addCartItem,
  totalItems: cartTotalItems,
} = useCart()

const subtotal = computed(() => {
  return items.value.reduce((sum, item) => sum + Number(item.price || 0), 0)
})

function addToCart(key: string) {
  const item = items.value.find((entry) => entry.key === key)
  if (!item) return

  addCartItem(item, false)
}

function addAllToCart() {
  items.value.forEach((item) => {
    addCartItem(item, false)
  })
}

function goToCheckout() {
  if (!cartTotalItems.value && items.value.length) {
    addAllToCart()
  }

  router.visit(route('frontend.checkout.index'))
}
</script>

<template>
  <div class="min-h-screen bg-[#f8f8fa] text-slate-950">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
      <nav aria-label="Breadcrumb" class="mb-6">
        <ol class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
          <li class="flex items-center gap-2">
            <Link :href="route('frontend.root')" class="transition hover:text-slate-900">
              Home
            </Link>
            <span class="text-slate-300">/</span>
          </li>
          <li>
            <span class="font-medium text-slate-900">Wishlist</span>
          </li>
        </ol>
      </nav>

      <div class="flex flex-col gap-3 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Wishlist</p>
          <h1 class="mt-2 text-3xl font-semibold tracking-[-0.03em] text-slate-950 sm:text-[2.35rem]">
            Saved Products
          </h1>
          <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600 sm:text-[15px]">
            Products you tap with the heart will stay here until the browser refreshes.
          </p>
        </div>

        <div class="flex items-center gap-3">
          <p class="text-sm font-medium text-slate-500">
            {{ totalItems }} item{{ totalItems === 1 ? '' : 's' }}
          </p>

          <button
            v-if="items.length"
            type="button"
            class="inline-flex min-h-[42px] items-center justify-center rounded-full border border-slate-300 px-4 text-sm font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-950"
            @click="clearWishlist"
          >
            Clear
          </button>
        </div>
      </div>

      <div
        v-if="!items.length"
        class="mt-8 rounded-[32px] border border-dashed border-slate-200 bg-white px-6 py-14 text-center shadow-sm"
      >
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-rose-50 text-[#ef5a4f]">
          <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 000-7.78z" />
          </svg>
        </div>

        <h2 class="mt-5 text-2xl font-semibold text-slate-950">Your wishlist is empty</h2>
        <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-slate-500 sm:text-[15px]">
          Tap the heart on any product card and it will appear here right away.
        </p>

        <Link
          :href="route('frontend.root')"
          class="mt-7 inline-flex min-h-[52px] items-center justify-center rounded-full bg-slate-950 px-7 text-sm font-semibold text-white transition hover:bg-slate-800"
        >
          Continue Shopping
        </Link>
      </div>

      <template v-else>
        <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_340px]">
          <section class="space-y-4">
            <article
              v-for="item in items"
              :key="item.key"
              class="grid gap-4 rounded-[28px] border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[120px_minmax(0,1fr)_auto] sm:items-center sm:gap-6 sm:p-5"
            >
              <div class="flex h-[120px] w-full items-center justify-center overflow-hidden rounded-[22px] bg-white">
                <img
                  v-if="item.image"
                  :src="item.image"
                  :alt="item.name"
                  class="max-h-full max-w-full object-contain"
                />
                <div
                  v-else
                  class="flex h-full w-full items-center justify-center text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400"
                >
                  No Image
                </div>
              </div>

              <div class="min-w-0">
                <div class="flex flex-wrap items-start justify-between gap-3">
                  <div class="min-w-0">
                    <Link
                      v-if="item.url"
                      :href="item.url"
                      class="line-clamp-2 text-lg font-semibold text-slate-950 transition hover:text-slate-700"
                    >
                      {{ item.name }}
                    </Link>

                    <p v-else class="line-clamp-2 text-lg font-semibold text-slate-950">
                      {{ item.name }}
                    </p>

                    <p class="mt-2 text-sm text-slate-500">
                      <span v-if="item.colorName">{{ item.colorName }}</span>
                      <span v-if="item.storageLabel">{{ item.colorName ? ' / ' : '' }}{{ item.storageLabel }}</span>
                      <span v-if="item.sizeLabel">{{ item.colorName || item.storageLabel ? ' / ' : '' }}{{ item.sizeLabel }}</span>
                    </p>
                  </div>

                  <div class="text-right">
                    <p
                      v-if="item.oldPrice && item.oldPrice > item.price"
                      class="text-xs text-slate-400 line-through"
                    >
                      {{ formatPrice(item.oldPrice) }}
                    </p>
                    <p class="text-lg font-semibold text-slate-950">
                      {{ formatPrice(item.price) }}
                    </p>
                  </div>
                </div>

                <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
                  <button
                    type="button"
                    class="inline-flex min-h-[48px] items-center justify-center rounded-full bg-slate-950 px-5 text-sm font-semibold text-white transition hover:bg-slate-800"
                    @click="addToCart(item.key)"
                  >
                    Add to cart
                  </button>

                  <button
                    type="button"
                    class="inline-flex min-h-[48px] items-center justify-center rounded-full border border-slate-300 px-5 text-sm font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-950"
                    @click="removeItem(item.key)"
                  >
                    Remove
                  </button>

                  <button
                    type="button"
                    class="inline-flex min-h-[48px] items-center justify-center rounded-full border border-blue-200 bg-blue-50 px-5 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100"
                    @click="addToCart(item.key); goToCheckout()"
                  >
                    Buy now
                  </button>
                </div>
              </div>
            </article>
          </section>

          <aside class="h-fit rounded-[30px] border border-slate-200 bg-white p-5 shadow-sm sm:sticky sm:top-24 sm:p-6">
            <h2 class="text-xl font-semibold text-slate-950">Wishlist Summary</h2>

            <div class="mt-6 space-y-3 text-sm">
              <div class="flex items-center justify-between">
                <span class="text-slate-500">Saved items</span>
                <span class="font-semibold text-slate-950">{{ totalItems }}</span>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-slate-500">Wishlist value</span>
                <span class="font-semibold text-slate-950">{{ formatPrice(subtotal) }}</span>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-slate-500">Cart items</span>
                <span class="font-semibold text-slate-950">{{ cartTotalItems }}</span>
              </div>
            </div>

            <div class="mt-6 flex flex-col gap-3">
              <button
                type="button"
                class="inline-flex min-h-[52px] items-center justify-center rounded-full bg-slate-950 px-6 text-sm font-semibold text-white transition hover:bg-slate-800"
                @click="addAllToCart"
              >
                Add all to cart
              </button>

              <Link
                :href="route('frontend.cart.index')"
                class="inline-flex min-h-[50px] items-center justify-center rounded-full border border-slate-300 px-6 text-sm font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-950"
              >
                Open cart
              </Link>

              <button
                type="button"
                class="inline-flex min-h-[52px] items-center justify-center rounded-full border border-blue-200 bg-blue-50 px-6 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100"
                @click="goToCheckout"
              >
                Checkout
              </button>
            </div>
          </aside>
        </div>
      </template>
    </main>
  </div>
</template>
