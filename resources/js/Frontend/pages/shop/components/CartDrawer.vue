<script setup lang="ts">
import { computed, onBeforeUnmount, watch } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { useCart } from '../composables/useCart'

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
  isCartOpen,
  subtotal,
  totalItems,
  incrementQuantity,
  decrementQuantity,
  removeItem,
  closeCart,
} = useCart()

const hasItems = computed(() => items.value.length > 0)

function lockPageScroll(locked: boolean) {
  if (typeof document === 'undefined') return

  document.documentElement.style.overflow = locked ? 'hidden' : ''
  document.body.style.overflow = locked ? 'hidden' : ''
}

watch(
  isCartOpen,
  (open) => {
    lockPageScroll(!!open)
  },
  { immediate: true },
)

onBeforeUnmount(() => {
  lockPageScroll(false)
})
</script>

<template>
  <Teleport to="body">
    <Transition name="cart-overlay">
      <div
        v-if="isCartOpen"
        class="fixed inset-0 z-[110] bg-black/45 backdrop-blur-[2px]"
        @click="closeCart"
      />
    </Transition>

    <Transition name="cart-panel">
      <aside
        v-if="isCartOpen"
        class="fixed right-0 top-0 z-[120] flex h-[100dvh] w-full max-w-[560px] flex-col overflow-hidden bg-white shadow-[0_24px_80px_rgba(0,0,0,0.22)] sm:rounded-bl-[32px] sm:rounded-tl-[32px]"
        aria-label="Shopping cart"
      >
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-5 sm:px-7">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
              Shopping Cart
            </p>
            <h2 class="mt-1 text-lg font-semibold text-slate-950">
              {{ totalItems }} item{{ totalItems === 1 ? '' : 's' }}
            </h2>
          </div>

          <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-600 transition hover:border-slate-300 hover:text-slate-950"
            @click="closeCart"
          >
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
              <path
                fill-rule="evenodd"
                d="M4.72 4.72a.75.75 0 011.06 0L10 8.94l4.22-4.22a.75.75 0 111.06 1.06L11.06 10l4.22 4.22a.75.75 0 01-1.06 1.06L10 11.06l-4.22 4.22a.75.75 0 01-1.06-1.06L8.94 10 4.72 5.78a.75.75 0 010-1.06z"
                clip-rule="evenodd"
              />
            </svg>
          </button>
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-5 sm:px-7">
          <div
            v-if="!hasItems"
            class="flex h-full flex-col items-center justify-center px-6 text-center"
          >
            <div class="flex h-16 w-16 items-center justify-center rounded-full border border-slate-200">
              <svg class="h-7 w-7 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.4 10.2a1 1 0 00.98.8H18.8a1 1 0 00.97-.76L21 7H7" />
                <circle cx="10" cy="20" r="1.5" />
                <circle cx="18" cy="20" r="1.5" />
              </svg>
            </div>

            <h3 class="mt-5 text-lg font-semibold text-slate-950">Your cart is empty</h3>
            <p class="mt-2 text-sm leading-7 text-slate-500">
              Add a few products and they will appear here instantly.
            </p>
          </div>

          <div v-else class="space-y-6">
            <article
              v-for="item in items"
              :key="item.key"
              class="border-b border-slate-200 pb-6 last:border-b-0 last:pb-0"
            >
              <div class="flex items-start gap-4 sm:gap-5">
                <div class="flex h-[82px] w-[82px] shrink-0 items-center justify-center overflow-hidden">
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

                <div class="min-w-0 flex-1">
                  <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 pr-2">
                      <Link
                        v-if="item.url"
                        :href="item.url"
                        class="line-clamp-2 text-[15px] font-semibold leading-7 text-slate-900 transition hover:text-slate-700"
                        @click="closeCart"
                      >
                        {{ item.name }}
                      </Link>

                      <p
                        v-else
                        class="line-clamp-2 text-[15px] font-semibold leading-7 text-slate-900"
                      >
                        {{ item.name }}
                      </p>

                      <p
                        v-if="item.colorName || item.storageLabel"
                        class="mt-1 text-sm text-slate-500"
                      >
                        <span v-if="item.colorName">{{ item.colorName }}</span>
                        <span v-if="item.colorName && item.storageLabel"> • </span>
                        <span v-if="item.storageLabel">{{ item.storageLabel }}</span>
                      </p>
                    </div>

                    <div class="shrink-0 text-right">
                      <p
                        v-if="item.oldPrice && item.oldPrice > item.price"
                        class="text-xs text-slate-400 line-through"
                      >
                        {{ formatPrice(item.oldPrice) }}
                      </p>
                      <p class="text-[15px] font-medium text-slate-800 sm:text-[16px]">
                        {{ formatPrice(item.price) }}
                      </p>
                    </div>
                  </div>

                  <div class="mt-3 flex items-center gap-4">
                    <div class="inline-flex h-9 items-center overflow-hidden rounded-md border border-slate-300">
                      <button
                        type="button"
                        class="flex h-full w-10 items-center justify-center text-lg text-slate-500 transition hover:bg-slate-50 hover:text-slate-900"
                        @click="decrementQuantity(item.key)"
                      >
                        −
                      </button>

                      <span class="flex h-full min-w-[34px] items-center justify-center px-2 text-sm font-medium text-slate-800">
                        {{ item.quantity }}
                      </span>

                      <button
                        type="button"
                        class="flex h-full w-10 items-center justify-center text-lg text-slate-500 transition hover:bg-slate-50 hover:text-slate-900"
                        @click="incrementQuantity(item.key)"
                      >
                        +
                      </button>
                    </div>

                    <button
                      type="button"
                      class="inline-flex h-8 w-8 items-center justify-center text-[#ef5a4f] transition hover:opacity-70"
                      @click="removeItem(item.key)"
                      aria-label="Remove item"
                    >
                      <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path
                          fill-rule="evenodd"
                          d="M6 8a.75.75 0 011.5 0v6A.75.75 0 016 14V8zm6.75-.75A.75.75 0 0012 8v6a.75.75 0 001.5 0V8a.75.75 0 00-.75-.75z"
                          clip-rule="evenodd"
                        />
                        <path
                          fill-rule="evenodd"
                          d="M14.5 4.75V4a1 1 0 00-1-1h-7a1 1 0 00-1 1v.75H3.75a.75.75 0 000 1.5h.598l.585 8.768A2 2 0 006.93 17.9h6.14a2 2 0 001.997-1.882l.585-8.768h.598a.75.75 0 000-1.5H14.5zm-7.5-.75h6v.75h-6V4zm-.563 2.25h7.126l-.55 8.256a.5.5 0 01-.499.469H6.986a.5.5 0 01-.499-.469l-.55-8.256z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </article>
          </div>
        </div>

        <div class="border-t border-slate-200 bg-white px-5 py-5 sm:px-7">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-sm font-medium text-slate-600">Total</p>
              <p class="mt-1 text-xs text-slate-400">
                Shipping calculated at checkout
              </p>
            </div>

            <span class="text-lg font-semibold text-slate-950">
              {{ formatPrice(subtotal) }}
            </span>
          </div>

          <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <Link
              :href="route('frontend.cart.index')"
              class="inline-flex min-h-[50px] items-center justify-center rounded-full border border-slate-900 px-5 text-sm font-semibold text-slate-950 transition hover:bg-slate-50"
              @click="closeCart"
            >
              View Cart
            </Link>

            <Link
              :href="route('frontend.checkout.index')"
              class="inline-flex min-h-[50px] items-center justify-center rounded-full bg-slate-950 px-5 text-sm font-semibold text-white transition hover:bg-slate-800"
              @click="closeCart"
            >
              Pay Now
            </Link>
          </div>
        </div>
      </aside>
    </Transition>
  </Teleport>
</template>

<style scoped>
.cart-overlay-enter-active,
.cart-overlay-leave-active {
  transition: opacity 0.25s ease;
}

.cart-overlay-enter-from,
.cart-overlay-leave-to {
  opacity: 0;
}

.cart-panel-enter-active,
.cart-panel-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.cart-panel-enter-from,
.cart-panel-leave-to {
  opacity: 0;
  transform: translateX(28px);
}
</style>
