<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

type BrandItem = {
  id: number | string
  name: string
  logo_url?: string | null
}

type CategoryItem = {
  id: number | string
  name: string
  slug?: string | null
  brands?: BrandItem[]
}

type CountryItem = {
  id: number | string
  name: string
  code?: string | null
  flag_image_url?: string | null
}

type Filters = {
  search?: string | null
  cosmetic_category?: string | null
  cosmetic_brand?: string | null
  cosmetic_country?: string | null
  stock?: string | null
  sale?: boolean
  sort?: string | null
  min_price?: string | number | null
  max_price?: string | number | null
  page?: number
}

const props = defineProps<{
  filters: Filters
  categories: CategoryItem[]
  countries: CountryItem[]
  loading?: boolean
}>()

const emit = defineEmits<{
  (e: 'apply', value: Filters): void
  (e: 'reset'): void
}>()

const localFilters = ref<Filters>({
  search: '',
  cosmetic_category: '',
  cosmetic_brand: '',
  cosmetic_country: '',
  stock: '',
  sale: false,
  sort: 'oldest',
  min_price: '',
  max_price: '',
  page: 1,
})

const isMobileFilterOpen = ref(false)
const isDesktopView = ref(false)
const isSyncingFromProps = ref(false)

let autoApplyTimer: ReturnType<typeof setTimeout> | null = null

function normalize(value: string | null | undefined) {
  return String(value ?? '').trim().toLowerCase()
}

function syncViewportState() {
  isDesktopView.value = window.innerWidth >= 768
  isMobileFilterOpen.value = isDesktopView.value
}

function queueApply() {
  if (isSyncingFromProps.value) return

  if (autoApplyTimer) {
    clearTimeout(autoApplyTimer)
  }

  autoApplyTimer = setTimeout(() => {
    emit('apply', {
      search: String(localFilters.value.search || '').trim(),
      cosmetic_category: localFilters.value.cosmetic_category || '',
      cosmetic_brand: localFilters.value.cosmetic_brand || '',
      cosmetic_country: localFilters.value.cosmetic_country || '',
      stock: localFilters.value.stock || '',
      sale: !!localFilters.value.sale,
      sort: localFilters.value.sort || 'oldest',
      min_price: localFilters.value.min_price ?? '',
      max_price: localFilters.value.max_price ?? '',
      page: 1,
    })
  }, 320)
}

watch(
  () => props.filters,
  (value) => {
    isSyncingFromProps.value = true

    localFilters.value = {
      search: value?.search || '',
      cosmetic_category: value?.cosmetic_category || '',
      cosmetic_brand: value?.cosmetic_brand || '',
      cosmetic_country: value?.cosmetic_country || '',
      stock: value?.stock || '',
      sale: !!value?.sale,
      sort: value?.sort || 'oldest',
      min_price: value?.min_price ?? '',
      max_price: value?.max_price ?? '',
      page: Number(value?.page || 1),
    }

    requestAnimationFrame(() => {
      isSyncingFromProps.value = false
    })
  },
  { immediate: true, deep: true }
)

const activeCategory = computed(() => {
  return props.categories.find(
    (category) => normalize(category.name) === normalize(localFilters.value.cosmetic_category)
  )
})

const visibleBrands = computed(() => {
  if (activeCategory.value?.brands?.length) {
    return activeCategory.value.brands
  }

  const allBrands = props.categories.flatMap((category) => category.brands || [])
  const seen = new Set<string>()

  return allBrands.filter((brand) => {
    const key = normalize(brand.name)
    if (!key || seen.has(key)) return false
    seen.add(key)
    return true
  })
})

watch(
  () => localFilters.value.cosmetic_category,
  (newValue, oldValue) => {
    if (newValue !== oldValue) {
      const exists = visibleBrands.value.some(
        (brand) => normalize(brand.name) === normalize(localFilters.value.cosmetic_brand)
      )

      if (!exists) {
        localFilters.value.cosmetic_brand = ''
      }
    }
  }
)

watch(
  localFilters,
  () => {
    queueApply()
  },
  { deep: true }
)

function toggleMobileFilter() {
  if (isDesktopView.value) return
  isMobileFilterOpen.value = !isMobileFilterOpen.value
}

function resetFilters() {
  if (autoApplyTimer) {
    clearTimeout(autoApplyTimer)
    autoApplyTimer = null
  }

  localFilters.value = {
    search: '',
    cosmetic_category: '',
    cosmetic_brand: '',
    cosmetic_country: '',
    stock: '',
    sale: false,
    sort: 'oldest',
    min_price: '',
    max_price: '',
    page: 1,
  }

  emit('reset')
}

onMounted(() => {
  syncViewportState()
  window.addEventListener('resize', syncViewportState, { passive: true })
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', syncViewportState)

  if (autoApplyTimer) {
    clearTimeout(autoApplyTimer)
    autoApplyTimer = null
  }
})
</script>

<template>
  <div class="legacy-filter-frame">
    <div class="legacy-collection-filter overflow-hidden rounded-[28px] border border-neutral-200 bg-white shadow-[0_14px_34px_rgba(15,23,42,0.06)]">
      <div class="border-b border-neutral-100 px-5 py-4 sm:px-6">
        <div class="flex items-center justify-between gap-3">
          <div>
            <h2 class="text-lg font-semibold text-neutral-900">
              Filters
            </h2>
            <p class="mt-1 text-sm text-neutral-500">
              Refine cosmetic products
            </p>
          </div>

          <div class="flex items-center gap-3">
            <button
              type="button"
              class="text-sm font-medium text-neutral-500 transition hover:text-neutral-900"
              @click="resetFilters"
            >
              Reset
            </button>

            <button
              type="button"
              class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-neutral-200 bg-neutral-50 text-neutral-700 transition hover:border-neutral-900 hover:bg-white hover:text-neutral-900 xl:hidden"
              :aria-expanded="isMobileFilterOpen ? 'true' : 'false'"
              aria-label="Toggle filters"
              @click="toggleMobileFilter"
            >
              <svg
                class="h-4 w-4 transition-transform duration-300"
                :class="isMobileFilterOpen ? 'rotate-180' : ''"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <Transition name="filter-collapse">
        <div
          v-show="isMobileFilterOpen || isDesktopView"
          class="px-5 py-5 sm:px-6"
        >
          <div class="space-y-5">
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-neutral-900">
                Search
              </label>

              <input
                v-model="localFilters.search"
                type="text"
                placeholder="Search name, batch, brand..."
                class="w-full rounded-2xl border border-neutral-200 bg-neutral-50/70 px-4 py-3 text-sm text-neutral-900 outline-none transition placeholder:text-neutral-400 focus:border-neutral-900 focus:bg-white"
              />
            </div>

            <div class="space-y-3">
              <label class="block text-sm font-semibold text-neutral-900">
                Category
              </label>

              <div class="flex flex-wrap gap-2">
                <button
                  type="button"
                  class="rounded-full border px-3.5 py-2 text-sm font-medium transition"
                  :class="
                    !localFilters.cosmetic_category
                      ? 'border-neutral-900 bg-neutral-900 text-white shadow-sm'
                      : 'border-neutral-200 bg-white text-neutral-700 hover:border-neutral-900 hover:text-neutral-900'
                  "
                  @click="localFilters.cosmetic_category = ''"
                >
                  All
                </button>

                <button
                  v-for="category in categories"
                  :key="category.id"
                  type="button"
                  class="rounded-full border px-3.5 py-2 text-sm font-medium transition"
                  :class="
                    localFilters.cosmetic_category === category.name
                      ? 'border-neutral-900 bg-neutral-900 text-white shadow-sm'
                      : 'border-neutral-200 bg-white text-neutral-700 hover:border-neutral-900 hover:text-neutral-900'
                  "
                  @click="localFilters.cosmetic_category = category.name"
                >
                  {{ category.name }}
                </button>
              </div>
            </div>

            <Transition name="field-fade" mode="out-in">
              <div
                :key="localFilters.cosmetic_category || 'no-category'"
                class="space-y-3"
              >
                <label class="block text-sm font-semibold text-neutral-900">
                  Brand
                </label>

                <div
                  v-if="visibleBrands.length"
                  class="grid grid-cols-2 gap-3 sm:grid-cols-3"
                >
                  <button
                    type="button"
                    class="flex min-h-[92px] flex-col items-center justify-center rounded-2xl border px-3 py-3 transition"
                    :class="
                      !localFilters.cosmetic_brand
                        ? 'border-neutral-900 bg-neutral-900 text-white shadow-sm'
                        : 'border-neutral-200 bg-white text-neutral-700 hover:border-neutral-900 hover:text-neutral-900'
                    "
                    @click="localFilters.cosmetic_brand = ''"
                  >
                    <span class="text-sm font-semibold">All Brands</span>
                  </button>

                  <button
                    v-for="brand in visibleBrands"
                    :key="brand.id"
                    type="button"
                    class="flex min-h-[92px] flex-col items-center justify-center rounded-2xl border px-3 py-3 transition"
                    :class="
                      localFilters.cosmetic_brand === brand.name
                        ? 'border-neutral-900 bg-neutral-900 text-white shadow-sm'
                        : 'border-neutral-200 bg-white text-neutral-700 hover:border-neutral-900 hover:text-neutral-900'
                    "
                    @click="localFilters.cosmetic_brand = brand.name"
                  >
                    <img
                      v-if="brand.logo_url"
                      :src="brand.logo_url"
                      :alt="brand.name"
                      class="mb-2 h-10 w-10 rounded-full bg-white p-1 object-contain"
                    />
                    <span class="line-clamp-1 text-center text-xs font-semibold">
                      {{ brand.name }}
                    </span>
                  </button>
                </div>

                <div
                  v-else
                  class="rounded-2xl border border-dashed border-neutral-200 bg-neutral-50 px-4 py-4 text-sm text-neutral-500"
                >
                  No brands available for this category.
                </div>
              </div>
            </Transition>

            <div class="space-y-2">
              <label class="block text-sm font-semibold text-neutral-900">
                Country of Origin
              </label>

              <div class="relative">
                <select
                  v-model="localFilters.cosmetic_country"
                  class="w-full appearance-none rounded-2xl border border-neutral-200 bg-neutral-50/70 px-4 py-3 pr-11 text-sm font-medium text-neutral-900 outline-none transition focus:border-neutral-900 focus:bg-white"
                >
                  <option value="">All countries</option>
                  <option
                    v-for="country in countries"
                    :key="country.id"
                    :value="country.name"
                  >
                    {{ country.name }}
                  </option>
                </select>

                <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-neutral-400">
                  <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path
                      fill-rule="evenodd"
                      d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </span>
              </div>
            </div>

            <div class="space-y-4">
              <div class="space-y-2">
                <label class="block text-sm font-semibold text-neutral-900">
                  Sort By
                </label>

                <div class="relative">
                  <select
                    v-model="localFilters.sort"
                    class="w-full appearance-none rounded-2xl border border-neutral-200 bg-neutral-50/70 px-4 py-3 pr-11 text-sm font-medium text-neutral-900 outline-none transition focus:border-neutral-900 focus:bg-white"
                  >
                    <option value="oldest">Oldest</option>
                    <option value="latest">Newest</option>
                    <option value="price_low_high">Price: Low to High</option>
                    <option value="price_high_low">Price: High to Low</option>
                    <option value="name_az">Name: A to Z</option>
                    <option value="name_za">Name: Z to A</option>
                  </select>

                  <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-neutral-400">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                      <path
                        fill-rule="evenodd"
                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </span>
                </div>
              </div>

              <div class="space-y-2">
                <label class="block text-sm font-semibold text-neutral-900">
                  Stock
                </label>

                <div class="relative">
                  <select
                    v-model="localFilters.stock"
                    class="w-full appearance-none rounded-2xl border border-neutral-200 bg-neutral-50/70 px-4 py-3 pr-11 text-sm font-medium text-neutral-900 outline-none transition focus:border-neutral-900 focus:bg-white"
                  >
                    <option value="">All stock types</option>
                    <option value="in_stock">In Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                  </select>

                  <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-neutral-400">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                      <path
                        fill-rule="evenodd"
                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </span>
                </div>
              </div>
            </div>

            <div class="space-y-2">
              <label class="block text-sm font-semibold text-neutral-900">
                Price Range
              </label>

              <div class="grid grid-cols-2 gap-3">
                <input
                  v-model="localFilters.min_price"
                  type="number"
                  min="0"
                  placeholder="Min"
                  class="w-full rounded-2xl border border-neutral-200 bg-neutral-50/70 px-4 py-3 text-sm text-neutral-900 outline-none transition placeholder:text-neutral-400 focus:border-neutral-900 focus:bg-white"
                />

                <input
                  v-model="localFilters.max_price"
                  type="number"
                  min="0"
                  placeholder="Max"
                  class="w-full rounded-2xl border border-neutral-200 bg-neutral-50/70 px-4 py-3 text-sm text-neutral-900 outline-none transition placeholder:text-neutral-400 focus:border-neutral-900 focus:bg-white"
                />
              </div>
            </div>

            <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-neutral-200 bg-neutral-50/70 px-4 py-3 transition hover:border-neutral-900 hover:bg-white">
              <input
                v-model="localFilters.sale"
                type="checkbox"
                class="h-4 w-4 rounded border-neutral-300 text-neutral-900 focus:ring-neutral-900"
              />
              <span class="text-sm font-medium text-neutral-800">
                Show sale products only
              </span>
            </label>

            <div
              v-if="loading"
              class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3 text-sm font-medium text-neutral-500"
            >
              Updating products...
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>

<style scoped>
.filter-collapse-enter-active,
.filter-collapse-leave-active {
  transition:
    max-height 0.35s ease,
    opacity 0.28s ease,
    transform 0.35s ease;
  overflow: hidden;
}

.filter-collapse-enter-from,
.filter-collapse-leave-to {
  max-height: 0;
  opacity: 0;
  transform: translateY(-10px);
}

.filter-collapse-enter-to,
.filter-collapse-leave-from {
  max-height: 1400px;
  opacity: 1;
  transform: translateY(0);
}

.field-fade-enter-active,
.field-fade-leave-active {
  transition:
    opacity 0.24s ease,
    transform 0.24s ease;
}

.field-fade-enter-from,
.field-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

.legacy-filter-frame {
  width: 100%;
}

.legacy-collection-filter {
  border-radius: 8px !important;
  border-color: #e2e8f0 !important;
  background:
    radial-gradient(circle at top left, rgba(56, 189, 248, 0.11), transparent 32%),
    linear-gradient(135deg, rgba(7, 31, 79, 0.045), transparent 44%),
    #ffffff !important;
  box-shadow: 0 18px 55px rgba(15, 23, 42, 0.07) !important;
}

.legacy-collection-filter > div:first-child {
  border-bottom-color: rgba(226, 232, 240, 0.85) !important;
  padding: 14px 16px !important;
}

.legacy-collection-filter h2 {
  color: #0f172a;
  font-size: 15px;
  font-weight: 800;
}

.legacy-collection-filter p {
  color: #64748b;
  font-size: 12px;
  font-weight: 700;
}

.legacy-collection-filter :where(input[type='text'], input[type='number'], select) {
  min-height: 44px;
  border-color: #dbe3ef !important;
  border-radius: 8px !important;
  background: rgba(255, 255, 255, 0.92) !important;
  transition:
    border-color 0.18s ease,
    box-shadow 0.18s ease,
    background-color 0.18s ease;
}

.legacy-collection-filter :where(input[type='text'], input[type='number'], select):focus {
  border-color: #071f4f !important;
  background: #ffffff !important;
  box-shadow: 0 0 0 3px rgba(7, 31, 79, 0.1);
}

.legacy-collection-filter :where(button, label) {
  border-radius: 8px !important;
}

.legacy-collection-filter button {
  transition:
    border-color 0.2s ease,
    background-color 0.2s ease,
    color 0.2s ease,
    transform 0.2s ease;
}

.legacy-collection-filter button:hover {
  transform: translateY(-1px);
}

@media (max-width: 640px) {
  .legacy-collection-filter > div:first-child,
  .legacy-collection-filter > div:last-child {
    padding-inline: 12px !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .legacy-collection-filter,
  .legacy-collection-filter * {
    transition: none !important;
    transform: none !important;
  }
}
</style>
