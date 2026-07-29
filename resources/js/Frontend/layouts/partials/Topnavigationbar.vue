<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { route } from 'ziggy-js'
import CartDrawer from '@/Frontend/pages/shop/components/CartDrawer.vue'
import { useCart } from '@/Frontend/pages/shop/composables/useCart'
import { useWishlist } from '@/Frontend/pages/shop/composables/useWishlist'

type CategoryItem = {
  id: number | string
  name: string
  url: string
}

type CategoryGroup = {
  key: string
  label: string
  url: string
  items: CategoryItem[]
}

type SearchSuggestion = {
  id: number | string
  name: string
  image_url?: string | null
  type?: string
  type_label?: string | null
  target_url?: string | null
}

const page = usePage()
const { totalItems, openCart } = useCart()
const { totalItems: wishlistTotalItems } = useWishlist()

const navRef = ref<HTMLElement | null>(null)
const desktopSearchInputRef = ref<HTMLInputElement | null>(null)
const mobileSearchInputRef = ref<HTMLInputElement | null>(null)

const mobileMenuOpen = ref(false)
const allCategoriesOpen = ref(false)
const mobileCategoriesOpen = ref(false)
const openMobileGroup = ref<string | null>(null)

const categoryGroups = ref<CategoryGroup[]>([])
const categoriesLoaded = ref(false)
const categoriesLoading = ref(false)
const categoriesError = ref('')

const searchQuery = ref('')
const searchSuggestions = ref<SearchSuggestion[]>([])
const searchLoading = ref(false)
const searchDropdownOpen = ref(false)
const highlightedSuggestionIndex = ref(-1)

let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null
let searchAbortController: AbortController | null = null
let skipNextSuggestionFetch = false

const hotlineNumber = '077 203 0597'

function safeRoute(name: string, fallback: string) {
  try {
    return route(name)
  } catch {
    return fallback
  }
}

const wishlistHref = computed(() => safeRoute('frontend.wishlist.index', '/wishlist'))

const hotlineHref = computed(() => {
  const cleanNumber = hotlineNumber.replace(/[^\d+]/g, '')
  return `tel:${cleanNumber}`
})

const navLinks = computed(() => [
  { label: 'Home', href: route('frontend.root'), match: ['/home'] },
  { label: 'Motorcycle Products', href: route('frontend.motorcycle-products.index'), match: ['/motorcycle-products'] },
  { label: 'Electronics', href: route('frontend.tech-products.index'), match: ['/tech-products'] },
  { label: 'Cosmetics', href: route('frontend.cosmetic-products.index'), match: ['/cosmetics', '/cosmetic-products'] },
  { label: 'Fashion', href: route('frontend.fashion.index'), match: ['/fashion'] },
  { label: 'Home Needs', href: route('frontend.home-needs.index'), match: ['/home-needs'] },
])

const currentParams = computed(() => {
  const url = page.url || ''
  const query = url.includes('?') ? url.split('?')[1] : ''
  return new URLSearchParams(query)
})

const currentPath = computed(() => {
  const url = page.url || ''
  return url.includes('?') ? url.split('?')[0] : url
})

const currentSearch = computed(() => currentParams.value.get('search') || '')
const cartBadgeCount = computed(() => totalItems.value > 99 ? '99+' : String(totalItems.value))
const wishlistBadgeCount = computed(() => wishlistTotalItems.value > 99 ? '99+' : String(wishlistTotalItems.value))
const wishlistActive = computed(() => currentPath.value.startsWith('/wishlist'))

watch(
  () => page.url,
  () => {
    mobileMenuOpen.value = false
    allCategoriesOpen.value = false
    mobileCategoriesOpen.value = false
    openMobileGroup.value = null
    closeSuggestionDropdown()
    cancelSuggestionRequest()

    skipNextSuggestionFetch = true
    searchQuery.value = currentSearch.value
  },
  { immediate: true }
)

watch(searchQuery, (value) => {
  if (skipNextSuggestionFetch) {
    skipNextSuggestionFetch = false
    return
  }

  scheduleSuggestionFetch(value)
})

watch(mobileMenuOpen, (open) => {
  document.body.style.overflow = open ? 'hidden' : ''

  if (open) {
    allCategoriesOpen.value = false
  }
})

function isActive(match: string[]) {
  if (currentPath.value === '/' && match.includes('/')) {
    return true
  }

  return match.some((path) => path !== '/' && currentPath.value.startsWith(path))
}

function normalize(value: string | null | undefined) {
  return String(value ?? '').trim().toLowerCase()
}

async function ensureCategoriesLoaded() {
  if (categoriesLoaded.value || categoriesLoading.value) return

  categoriesLoading.value = true
  categoriesError.value = ''

  try {
    const response = await fetch(route('frontend.home.all-categories'), {
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })

    if (!response.ok) {
      throw new Error('Failed to load categories')
    }

    const payload = await response.json()
    categoryGroups.value = Array.isArray(payload?.groups) ? payload.groups : []
    categoriesLoaded.value = true
  } catch {
    categoriesError.value = 'Unable to load categories. Please try again.'
    categoriesLoaded.value = false
  } finally {
    categoriesLoading.value = false
  }
}

async function toggleAllCategories() {
  allCategoriesOpen.value = !allCategoriesOpen.value

  if (allCategoriesOpen.value) {
    mobileMenuOpen.value = false
    await ensureCategoriesLoaded()
  }
}

async function toggleMobileCategories() {
  mobileCategoriesOpen.value = !mobileCategoriesOpen.value

  if (mobileCategoriesOpen.value) {
    await ensureCategoriesLoaded()
  }
}

function toggleMobileGroup(key: string) {
  openMobileGroup.value = openMobileGroup.value === key ? null : key
}

function cancelSuggestionRequest() {
  if (searchDebounceTimer) {
    clearTimeout(searchDebounceTimer)
    searchDebounceTimer = null
  }

  if (searchAbortController) {
    searchAbortController.abort()
    searchAbortController = null
  }

  searchLoading.value = false
}

function closeSuggestionDropdown() {
  searchDropdownOpen.value = false
  highlightedSuggestionIndex.value = -1
}

function scheduleSuggestionFetch(value: string) {
  cancelSuggestionRequest()

  const query = value.trim()

  if (!query) {
    searchSuggestions.value = []
    closeSuggestionDropdown()
    return
  }

  searchDebounceTimer = setTimeout(() => {
    fetchSearchSuggestions(query)
  }, 180)
}

function rankSuggestion(name: string, query: string) {
  const normalizedName = normalize(name)
  const normalizedQuery = normalize(query)

  if (!normalizedQuery) return 999
  if (normalizedName === normalizedQuery) return 0
  if (normalizedName.startsWith(normalizedQuery)) return 1
  if (normalizedName.split(/\s+/).some(word => word.startsWith(normalizedQuery))) return 2
  if (normalizedName.includes(normalizedQuery)) return 3
  return 4
}

async function fetchSearchSuggestions(query: string) {
  const cleanQuery = query.trim()

  if (!cleanQuery) {
    searchSuggestions.value = []
    closeSuggestionDropdown()
    return
  }

  searchLoading.value = true
  searchAbortController = new AbortController()

  try {
    const response = await fetch(
      `${route('frontend.products.suggestions')}?q=${encodeURIComponent(cleanQuery)}`,
      {
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        signal: searchAbortController.signal,
      }
    )

    if (!response.ok) {
      throw new Error('Failed to load suggestions')
    }

    const payload = await response.json()
    const uniqueMap = new Map<string, SearchSuggestion>()

    if (Array.isArray(payload)) {
      payload.forEach((item: any) => {
        const name = String(item?.name ?? '').trim()
        const targetUrl = String(item?.target_url ?? '').trim()

        if (!name || !targetUrl) return

        const key = `${String(item?.type ?? 'product').toLowerCase()}::${name.toLowerCase()}::${targetUrl}`

        if (!uniqueMap.has(key)) {
          uniqueMap.set(key, {
            id: item?.id ?? key,
            name,
            image_url: item?.image_url ?? null,
            type: item?.type ?? 'product',
            type_label: item?.type_label ?? 'Product',
            target_url: targetUrl,
          })
        }
      })
    }

    searchSuggestions.value = Array.from(uniqueMap.values())
      .sort((a, b) => {
        const rankA = rankSuggestion(a.name, cleanQuery)
        const rankB = rankSuggestion(b.name, cleanQuery)

        if (rankA !== rankB) return rankA - rankB
        return a.name.localeCompare(b.name)
      })
      .slice(0, 10)

    searchDropdownOpen.value = true
    highlightedSuggestionIndex.value = searchSuggestions.value.length ? 0 : -1
  } catch (error: any) {
    if (error?.name !== 'AbortError') {
      searchSuggestions.value = []
      closeSuggestionDropdown()
    }
  } finally {
    searchLoading.value = false
    searchAbortController = null
  }
}

function searchDestination() {
  if (currentPath.value.startsWith('/tech-products')) {
    return route('frontend.tech-products.index')
  }

  if (currentPath.value.startsWith('/cosmetics') || currentPath.value.startsWith('/cosmetic-products')) {
    return route('frontend.cosmetic-products.index')
  }

  return route('frontend.root')
}

function submitSearch() {
  const query = searchQuery.value.trim()

  cancelSuggestionRequest()
  closeSuggestionDropdown()
  mobileMenuOpen.value = false

  router.get(
    searchDestination(),
    { search: query || undefined },
    {
      preserveScroll: true,
      preserveState: false,
    }
  )
}

function clearSearch() {
  skipNextSuggestionFetch = true
  searchQuery.value = ''
  searchSuggestions.value = []
  cancelSuggestionRequest()
  closeSuggestionDropdown()
  submitSearch()
}

function goToSuggestion(suggestion: SearchSuggestion) {
  if (!suggestion?.target_url) return

  skipNextSuggestionFetch = true
  searchQuery.value = suggestion.name

  cancelSuggestionRequest()
  searchSuggestions.value = []
  closeSuggestionDropdown()
  mobileMenuOpen.value = false

  router.visit(suggestion.target_url, {
    preserveScroll: true,
    preserveState: false,
  })
}

function handleSearchInputKeydown(event: KeyboardEvent) {
  if (event.key === 'ArrowDown') {
    if (!searchSuggestions.value.length) return

    event.preventDefault()
    searchDropdownOpen.value = true
    highlightedSuggestionIndex.value = Math.min(
      highlightedSuggestionIndex.value + 1,
      searchSuggestions.value.length - 1
    )
    return
  }

  if (event.key === 'ArrowUp') {
    if (!searchSuggestions.value.length) return

    event.preventDefault()
    highlightedSuggestionIndex.value = Math.max(highlightedSuggestionIndex.value - 1, 0)
    return
  }

  if (event.key === 'Enter') {
    if (
      searchDropdownOpen.value &&
      highlightedSuggestionIndex.value >= 0 &&
      searchSuggestions.value[highlightedSuggestionIndex.value]
    ) {
      event.preventDefault()
      goToSuggestion(searchSuggestions.value[highlightedSuggestionIndex.value])
    }
  }

  if (event.key === 'Escape') {
    closeSuggestionDropdown()
  }
}

function openCartDrawer() {
  mobileMenuOpen.value = false
  allCategoriesOpen.value = false
  openCart()
}

function closeMenus() {
  mobileMenuOpen.value = false
  allCategoriesOpen.value = false
  closeSuggestionDropdown()
}

function handleClickOutside(event: MouseEvent) {
  const target = event.target as Node | null
  if (!target) return

  if (!navRef.value?.contains(target)) {
    allCategoriesOpen.value = false
    closeSuggestionDropdown()
  }
}

function handleKeydown(event: KeyboardEvent) {
  if (event.key === 'Escape') {
    mobileMenuOpen.value = false
    allCategoriesOpen.value = false
    closeSuggestionDropdown()
  }
}

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
  window.addEventListener('keydown', handleKeydown)
})

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleClickOutside)
  window.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = ''
  cancelSuggestionRequest()
})
</script>

<template>
  <header ref="navRef" class="store-navbar sticky top-0 z-50 bg-white text-slate-950">
    <div class="border-b border-slate-100">
      <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-3 sm:px-5 lg:gap-5 lg:py-4">
        <Link
          :href="route('frontend.root')"
          class="store-logo flex shrink-0 items-center gap-2.5"
          aria-label="Deze Store home"
        >
          <img
            src="/images/bluelogodardeze.png"
            alt="Deze Store"
            class="h-9 w-auto object-contain sm:h-10 lg:h-12"
            loading="eager"
            decoding="async"
          />

          <!-- <span class="store-logo__text">
            Deze Store
          </span> -->
        </Link>

        <form
          class="relative ml-auto hidden w-full min-w-0 max-w-[520px] md:block lg:max-w-[560px]"
          role="search"
          @submit.prevent="submitSearch"
        >
          <div class="search-box flex h-11 w-full items-center rounded-full border border-slate-100 bg-slate-50 shadow-sm transition focus-within:border-[#071f4f] focus-within:bg-white focus-within:shadow-md">
            <input
              ref="desktopSearchInputRef"
              v-model="searchQuery"
              type="search"
              autocomplete="off"
              placeholder="Search for any product or brand"
              class="h-full min-w-0 flex-1 rounded-l-full bg-transparent px-5 text-sm text-slate-700 outline-none placeholder:text-slate-400"
              @focus="searchDropdownOpen = !!(searchQuery.trim() && (searchSuggestions.length || searchLoading))"
              @keydown="handleSearchInputKeydown"
            />

            <button
              v-if="searchQuery"
              type="button"
              class="mr-1 inline-flex h-8 w-8 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
              aria-label="Clear search"
              @click="clearSearch"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>

            <button
              type="submit"
              class="mr-1 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#071f4f] text-white shadow-sm transition hover:bg-[#0b2b62]"
              aria-label="Search"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="7" />
                <path stroke-linecap="round" d="m20 20-3.5-3.5" />
              </svg>
            </button>
          </div>

          <Transition name="menu-fade">
            <div
              v-if="searchDropdownOpen"
              class="absolute inset-x-0 top-full z-50 mx-auto mt-2 w-full max-w-[560px] overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl"
            >
              <div v-if="searchLoading" class="px-4 py-3 text-sm text-slate-500">Searching...</div>

              <div v-else-if="searchSuggestions.length" class="max-h-[420px] overflow-y-auto py-2">
                <button
                  v-for="(suggestion, index) in searchSuggestions"
                  :key="suggestion.id"
                  type="button"
                  class="suggestion-row"
                  :class="{ 'bg-slate-50': highlightedSuggestionIndex === index }"
                  @mousedown.prevent="goToSuggestion(suggestion)"
                >
                  <span class="suggestion-thumb">
                    <img
                      v-if="suggestion.image_url"
                      :src="suggestion.image_url"
                      :alt="suggestion.name"
                      loading="lazy"
                      decoding="async"
                    />
                  </span>

                  <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-semibold text-slate-900">
                      {{ suggestion.name }}
                    </span>
                    <span class="mt-0.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">
                      {{ suggestion.type_label || 'Product' }}
                    </span>
                  </span>
                </button>
              </div>

              <div v-else class="px-4 py-3 text-sm text-slate-500">No matching products found.</div>
            </div>
          </Transition>
        </form>

        <div class="ml-auto flex shrink-0 items-center justify-end gap-1.5 sm:gap-2">
          <Link
            :href="wishlistHref"
            class="navbar-action"
            :class="{ 'navbar-action--active': wishlistActive }"
            aria-label="Open wishlist"
            @click="closeMenus"
          >
            <span class="relative inline-flex">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 000-7.78z"
                />
              </svg>

              <span
                v-if="wishlistTotalItems"
                class="absolute -right-2 -top-2 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-[#ef5a4f] px-1 text-[10px] font-bold text-white"
              >
                {{ wishlistBadgeCount }}
              </span>
            </span>
            <span class="hidden sm:inline">Wishlist</span>
          </Link>

          <button
            type="button"
            class="navbar-action"
            aria-label="Open cart"
            @click="openCartDrawer"
          >
            <span class="relative inline-flex">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.4 10.2a1 1 0 00.98.8H18.8a1 1 0 00.97-.76L21 7H7" />
                <circle cx="10" cy="20" r="1.5" />
                <circle cx="18" cy="20" r="1.5" />
              </svg>

              <span class="absolute -right-2 -top-2 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-[#071f4f] px-1 text-[10px] font-bold text-white">
                {{ cartBadgeCount }}
              </span>
            </span>
            <span class="hidden sm:inline">Cart</span>
          </button>

          <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-100 text-slate-900 transition hover:bg-slate-50 lg:hidden"
            :aria-expanded="mobileMenuOpen"
            aria-label="Toggle menu"
            @click="mobileMenuOpen = !mobileMenuOpen"
          >
            <svg v-if="!mobileMenuOpen" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
            </svg>

            <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <div class="px-4 pb-3 md:hidden">
        <form class="relative" role="search" @submit.prevent="submitSearch">
          <div class="flex h-11 items-center rounded-full border border-slate-100 bg-slate-50 shadow-sm focus-within:border-[#071f4f] focus-within:bg-white">
            <input
              ref="mobileSearchInputRef"
              v-model="searchQuery"
              type="search"
              autocomplete="off"
              placeholder="Search for any product or brand"
              class="h-full min-w-0 flex-1 rounded-l-full bg-transparent px-4 text-sm text-slate-700 outline-none placeholder:text-slate-400"
              @focus="searchDropdownOpen = !!(searchQuery.trim() && (searchSuggestions.length || searchLoading))"
              @keydown="handleSearchInputKeydown"
            />

            <button
              v-if="searchQuery"
              type="button"
              class="mr-1 inline-flex h-8 w-8 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
              aria-label="Clear search"
              @click="clearSearch"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>

            <button
              type="submit"
              class="mr-1 inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#071f4f] text-white"
              aria-label="Search"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="7" />
                <path stroke-linecap="round" d="m20 20-3.5-3.5" />
              </svg>
            </button>
          </div>

          <Transition name="menu-fade">
            <div
              v-if="searchDropdownOpen"
              class="absolute inset-x-0 top-full z-50 mt-2 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl"
            >
              <div v-if="searchLoading" class="px-4 py-3 text-sm text-slate-500">Searching...</div>

              <div v-else-if="searchSuggestions.length" class="max-h-[340px] overflow-y-auto py-2">
                <button
                  v-for="(suggestion, index) in searchSuggestions"
                  :key="suggestion.id"
                  type="button"
                  class="suggestion-row"
                  :class="{ 'bg-slate-50': highlightedSuggestionIndex === index }"
                  @mousedown.prevent="goToSuggestion(suggestion)"
                >
                  <span class="suggestion-thumb">
                    <img
                      v-if="suggestion.image_url"
                      :src="suggestion.image_url"
                      :alt="suggestion.name"
                      loading="lazy"
                      decoding="async"
                    />
                  </span>

                  <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-semibold text-slate-900">
                      {{ suggestion.name }}
                    </span>
                    <span class="mt-0.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">
                      {{ suggestion.type_label || 'Product' }}
                    </span>
                  </span>
                </button>
              </div>

              <div v-else class="px-4 py-3 text-sm text-slate-500">No matching products found.</div>
            </div>
          </Transition>
        </form>
      </div>
    </div>

    <div class="hidden border-b border-slate-100 lg:block">
      <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-3">
        <div class="flex min-w-0 flex-1 items-center justify-start gap-6">
          <div class="relative shrink-0">
            <button
              type="button"
              class="inline-flex items-center gap-2 rounded-full px-2 py-1.5 text-sm font-bold text-slate-900 transition hover:bg-slate-50"
              :aria-expanded="allCategoriesOpen"
              @click="toggleAllCategories"
            >
              <svg class="h-4 w-4 text-slate-700" viewBox="0 0 24 24" fill="currentColor">
                <circle cx="6" cy="6" r="2" />
                <circle cx="12" cy="6" r="2" />
                <circle cx="18" cy="6" r="2" />
                <circle cx="6" cy="12" r="2" />
                <circle cx="12" cy="12" r="2" />
                <circle cx="18" cy="12" r="2" />
                <circle cx="6" cy="18" r="2" />
                <circle cx="12" cy="18" r="2" />
                <circle cx="18" cy="18" r="2" />
              </svg>

              All Categories

              <svg class="h-3.5 w-3.5 transition-transform" :class="{ 'rotate-180': allCategoriesOpen }" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
              </svg>
            </button>

            <Transition name="menu-fade">
              <div
                v-if="allCategoriesOpen"
                class="absolute left-0 top-full z-50 mt-3 w-[min(92vw,920px)] overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-2xl"
              >
                <div v-if="categoriesLoading" class="grid gap-4 p-5 md:grid-cols-5">
                  <div v-for="item in 5" :key="item" class="space-y-3">
                    <div class="h-4 w-28 rounded-full bg-slate-100"></div>
                    <div class="h-3 w-20 rounded-full bg-slate-100"></div>
                    <div class="h-3 w-24 rounded-full bg-slate-100"></div>
                  </div>
                </div>

                <div v-else-if="categoriesError" class="flex items-center justify-between gap-4 p-5 text-sm text-red-600">
                  <span>{{ categoriesError }}</span>

                  <button
                    type="button"
                    class="rounded-full border border-red-100 px-3 py-1.5 text-xs font-bold text-red-700 transition hover:bg-red-50"
                    @click="ensureCategoriesLoaded"
                  >
                    Retry
                  </button>
                </div>

                <div v-else-if="!categoryGroups.length" class="p-5 text-sm text-slate-500">
                  No categories available yet.
                </div>

                <div v-else class="grid gap-2 p-4 md:grid-cols-5">
                  <div v-for="group in categoryGroups" :key="group.key" class="rounded-2xl p-2">
                    <Link
                      :href="group.url"
                      class="mb-2 block rounded-xl px-3 py-2 text-sm font-bold text-slate-950 transition hover:bg-slate-50"
                      @click="allCategoriesOpen = false"
                    >
                      {{ group.label }}
                    </Link>

                    <div class="space-y-0.5">
                      <Link
                        v-for="category in group.items"
                        :key="category.id"
                        :href="category.url"
                        class="block rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-50 hover:text-[#071f4f]"
                        @click="allCategoriesOpen = false"
                      >
                        {{ category.name }}
                      </Link>

                      <p v-if="!group.items.length" class="px-3 py-2 text-xs text-slate-400">
                        No categories yet
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </Transition>
          </div>

          <nav class="flex min-w-0 flex-1 flex-wrap items-center justify-start gap-x-5 gap-y-2 whitespace-normal">
            <Link
              v-for="item in navLinks"
              :key="item.label"
              :href="item.href"
              class="nav-link"
              :class="{ 'nav-link--active': isActive(item.match) }"
            >
              {{ item.label }}
            </Link>
          </nav>
        </div>

        <a :href="hotlineHref" class="hotline-link">
          <span class="hotline-link__icon">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M22 16.92v3a2 2 0 01-2.18 2A19.8 19.8 0 013.1 5.18 2 2 0 015.11 3h3a2 2 0 012 1.72c.12.9.33 1.78.63 2.63a2 2 0 01-.45 2.11L9 10.75a16 16 0 006.25 6.25l1.29-1.29a2 2 0 012.11-.45c.85.3 1.73.51 2.63.63A2 2 0 0122 16.92z"
              />
            </svg>
          </span>

          <span class="leading-none">
            <span class="block text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
              Hotline
            </span>
            <span class="mt-1 block text-sm font-black text-slate-950">
              {{ hotlineNumber }}
            </span>
          </span>
        </a>
      </div>
    </div>

    <Transition name="mobile-panel">
      <div
        v-if="mobileMenuOpen"
        class="fixed inset-x-0 top-[116px] z-40 max-h-[calc(100vh-116px)] overflow-y-auto border-t border-slate-100 bg-white px-4 py-4 shadow-2xl md:top-[72px] md:max-h-[calc(100vh-72px)] lg:hidden"
      >
        <a :href="hotlineHref" class="mb-4 flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
          <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#071f4f] text-white">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M22 16.92v3a2 2 0 01-2.18 2A19.8 19.8 0 013.1 5.18 2 2 0 015.11 3h3a2 2 0 012 1.72c.12.9.33 1.78.63 2.63a2 2 0 01-.45 2.11L9 10.75a16 16 0 006.25 6.25l1.29-1.29a2 2 0 012.11-.45c.85.3 1.73.51 2.63.63A2 2 0 0122 16.92z"
              />
            </svg>
          </span>

          <span>
            <span class="block text-xs font-black uppercase tracking-[0.14em] text-slate-400">
              Hotline
            </span>
            <span class="mt-0.5 block text-sm font-black text-slate-950">
              {{ hotlineNumber }}
            </span>
          </span>
        </a>

        <button
          type="button"
          class="flex w-full items-center justify-between rounded-2xl border border-slate-100 px-4 py-3 text-left text-sm font-bold text-slate-950"
          :aria-expanded="mobileCategoriesOpen"
          @click="toggleMobileCategories"
        >
          <span class="inline-flex items-center gap-2">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
              <circle cx="6" cy="6" r="2" />
              <circle cx="12" cy="6" r="2" />
              <circle cx="18" cy="6" r="2" />
              <circle cx="6" cy="12" r="2" />
              <circle cx="12" cy="12" r="2" />
              <circle cx="18" cy="12" r="2" />
              <circle cx="6" cy="18" r="2" />
              <circle cx="12" cy="18" r="2" />
              <circle cx="18" cy="18" r="2" />
            </svg>
            All Categories
          </span>

          <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': mobileCategoriesOpen }" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
          </svg>
        </button>

        <Transition name="accordion">
          <div v-if="mobileCategoriesOpen" class="mt-3 rounded-2xl border border-slate-100 bg-slate-50 p-2">
            <div v-if="categoriesLoading" class="space-y-2 p-2">
              <div v-for="item in 4" :key="item" class="h-10 rounded-xl bg-white"></div>
            </div>

            <div v-else-if="categoriesError" class="flex items-center justify-between gap-3 p-3 text-sm text-red-600">
              <span>{{ categoriesError }}</span>

              <button
                type="button"
                class="rounded-full border border-red-100 px-3 py-1.5 text-xs font-bold text-red-700"
                @click="ensureCategoriesLoaded"
              >
                Retry
              </button>
            </div>

            <div v-else-if="!categoryGroups.length" class="p-3 text-sm text-slate-500">
              No categories available yet.
            </div>

            <div v-else class="space-y-2">
              <div v-for="group in categoryGroups" :key="group.key" class="rounded-xl bg-white">
                <button
                  type="button"
                  class="flex w-full items-center justify-between px-4 py-3 text-left text-sm font-bold text-slate-900"
                  @click="toggleMobileGroup(group.key)"
                >
                  {{ group.label }}

                  <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': openMobileGroup === group.key }" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                  </svg>
                </button>

                <Transition name="accordion">
                  <div v-if="openMobileGroup === group.key" class="space-y-1 px-3 pb-3">
                    <Link :href="group.url" class="mobile-category-link" @click="mobileMenuOpen = false">
                      View All
                    </Link>

                    <Link
                      v-for="category in group.items"
                      :key="category.id"
                      :href="category.url"
                      class="mobile-category-link"
                      @click="mobileMenuOpen = false"
                    >
                      {{ category.name }}
                    </Link>

                    <p v-if="!group.items.length" class="px-3 py-2 text-xs text-slate-400">
                      No categories yet
                    </p>
                  </div>
                </Transition>
              </div>
            </div>
          </div>
        </Transition>

        <nav class="mt-4 space-y-1">
          <Link
            v-for="item in navLinks"
            :key="item.label"
            :href="item.href"
            class="mobile-nav-link"
            :class="{ 'mobile-nav-link--active': isActive(item.match) }"
            @click="mobileMenuOpen = false"
          >
            {{ item.label }}
          </Link>
        </nav>
      </div>
    </Transition>
  </header>

  <CartDrawer />
</template>

<style scoped>
.store-navbar {
  font-family: 'DezeStoreCustomFont', var(--font-sans, sans-serif);
  border-bottom: 1px solid rgba(203, 213, 225, 0.78);
  box-shadow:
    0 10px 26px rgba(15, 23, 42, 0.075),
    0 1px 0 rgba(255, 255, 255, 0.85);
}

.store-logo {
  min-width: max-content;
}

.store-logo__text {
  display: inline-flex;
  align-items: center;
  color: #071f4f;
  font-size: 1.18rem;
  font-weight: 950;
  line-height: 1;
  letter-spacing: 0;
  white-space: nowrap;
}

.search-box {
  background:
    radial-gradient(circle at 4% 0%, rgba(255, 255, 255, 0.94), transparent 34%),
    linear-gradient(90deg, #f8fafc, #ffffff);
}

.navbar-action {
  position: relative;
  display: inline-flex;
  height: 2.5rem;
  align-items: center;
  gap: 0.5rem;
  border-radius: 999px;
  padding-inline: 0.65rem;
  color: #1f2937;
  font-size: 0.875rem;
  font-weight: 750;
  text-decoration: none;
  transition:
    background-color 0.18s ease,
    color 0.18s ease,
    transform 0.18s ease;
}

.navbar-action:hover,
.navbar-action--active {
  background: #f0f9ff;
  color: #0284c7;
}

.navbar-action:hover {
  transform: translateY(-1px);
}

.nav-link {
  position: relative;
  display: inline-flex;
  align-items: center;
  padding: 0.42rem 0;
  font-size: 0.86rem;
  font-weight: 700;
  color: #1f2937;
  text-decoration: none;
  transition: color 0.18s ease;
}

.nav-link:hover {
  color: #0284c7;
}

.nav-link--active {
  color: #38bdf8;
}

.hotline-link {
  display: inline-flex;
  flex-shrink: 0;
  align-items: center;
  gap: 0.7rem;
  border-radius: 999px;
  padding: 0.35rem 0.85rem 0.35rem 0.4rem;
  background: #f8fafc;
  color: #071f4f;
  text-decoration: none;
  transition:
    background-color 0.18s ease,
    transform 0.18s ease,
    box-shadow 0.18s ease;
}

.hotline-link:hover {
  transform: translateY(-1px);
  background: #ffffff;
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
}

.hotline-link__icon {
  display: inline-flex;
  height: 2.25rem;
  width: 2.25rem;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  background: #071f4f;
  color: #ffffff;
}

.suggestion-row {
  display: flex;
  width: 100%;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  text-align: left;
  transition: background-color 0.16s ease;
}

.suggestion-row:hover {
  background: #f8fafc;
}

.suggestion-thumb {
  display: flex;
  height: 44px;
  width: 44px;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border-radius: 14px;
  background: #f1f5f9;
}

.suggestion-thumb img {
  height: 100%;
  width: 100%;
  object-fit: cover;
}

.mobile-nav-link {
  position: relative;
  display: flex;
  width: 100%;
  align-items: center;
  border-radius: 1rem;
  padding: 0.95rem 1rem;
  font-size: 0.96rem;
  font-weight: 750;
  color: #111827;
  text-decoration: none;
  transition:
    background-color 0.16s ease,
    color 0.16s ease;
}

.mobile-nav-link:hover {
  background: #f0f9ff;
  color: #0284c7;
}

.mobile-nav-link--active {
  background: #e0f2fe;
  color: #0284c7;
}

.mobile-category-link {
  display: block;
  border-radius: 0.85rem;
  padding: 0.7rem 0.85rem;
  font-size: 0.9rem;
  font-weight: 600;
  color: #475569;
  text-decoration: none;
}

.mobile-category-link:hover {
  background: #f8fafc;
  color: #071f4f;
}

.menu-fade-enter-active,
.menu-fade-leave-active {
  transition: opacity 0.16s ease, transform 0.16s ease;
}

.menu-fade-enter-from,
.menu-fade-leave-to {
  opacity: 0;
  transform: translateY(8px);
}

.mobile-panel-enter-active,
.mobile-panel-leave-active {
  transition: opacity 0.22s ease, transform 0.22s ease;
}

.mobile-panel-enter-from,
.mobile-panel-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.accordion-enter-active,
.accordion-leave-active {
  overflow: hidden;
  transition: max-height 0.22s ease, opacity 0.18s ease;
}

.accordion-enter-from,
.accordion-leave-to {
  max-height: 0;
  opacity: 0;
}

.accordion-enter-to,
.accordion-leave-from {
  max-height: 720px;
  opacity: 1;
}

@media (max-width: 640px) {
  .store-logo__text {
    font-size: 0.9rem;
    letter-spacing: 0;
  }

  .navbar-action {
    height: 2.5rem;
    width: 2.5rem;
    justify-content: center;
    padding-inline: 0;
  }
}

@media (max-width: 380px) {
  .store-logo__text {
    display: none;
  }
}
</style>
