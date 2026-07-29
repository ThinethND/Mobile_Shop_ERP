<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import { route } from 'ziggy-js'

type Category = {
  id: number | string
  name: string
  image_url: string | null
  status?: string | null
}

const props = defineProps<{
  categories?: Category[]
}>()

const loading = ref(true)
const loadedCategories = ref<Category[]>([])
const loadError = ref(false)

const fetchCategories = async () => {
  loading.value = true
  loadError.value = false

  try {
    const response = await fetch('/home/categories', {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })

    if (!response.ok) {
      throw new Error('Failed to fetch categories')
    }

    const data = await response.json()
    loadedCategories.value = Array.isArray(data?.categories) ? data.categories : []
  } catch (error) {
    console.error('Categories fetch error:', error)
    loadError.value = true
    loadedCategories.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if ((props.categories ?? []).length > 0) {
    loadedCategories.value = props.categories ?? []
    loading.value = false
    return
  }

  fetchCategories()
})

const visibleCategories = computed(() => loadedCategories.value.slice(0, 4))
const skeletonCards = computed(() => [0, 1, 2, 3])

const cardClass = (index: number) => {
  switch (index) {
    case 0:
      return 'min-h-[220px] sm:col-start-1 sm:row-start-1 sm:row-span-2 sm:min-h-[380px] xl:min-h-[460px]'
    case 1:
      return 'min-h-[220px] sm:col-start-2 sm:row-start-1 sm:row-span-2 sm:min-h-[380px] xl:min-h-[460px]'
    case 2:
      return 'min-h-[180px] sm:col-start-3 sm:row-start-1 sm:min-h-[182px] xl:min-h-[220px]'
    case 3:
      return 'min-h-[180px] sm:col-start-3 sm:row-start-2 sm:min-h-[182px] xl:min-h-[220px]'
    default:
      return 'min-h-[180px]'
  }
}

const titleClass = (index: number) => {
  return index < 2
    ? 'text-[20px] leading-[1] sm:text-[24px] xl:text-[36px]'
    : 'text-[18px] leading-[1.05] sm:text-[16px] xl:text-[28px]'
}

const contentClass = (index: number) => {
  return index < 2
    ? 'p-4 sm:p-5 xl:p-8'
    : 'p-4 sm:p-4 xl:p-6'
}

const buttonClass = (index: number) => {
  return index < 2
    ? 'px-3 py-1.5 text-[10px] tracking-[0.14em] sm:px-3 sm:py-1.5 sm:text-[9px] sm:tracking-[0.18em] xl:px-5 xl:py-2.5 xl:text-[12px] xl:tracking-[0.24em]'
    : 'px-3 py-1.5 text-[10px] tracking-[0.12em] sm:px-2.5 sm:py-1.5 sm:text-[8px] sm:tracking-[0.14em] xl:px-4 xl:py-2 xl:text-[11px] xl:tracking-[0.18em]'
}
</script>

<template>
  <section class="mx-auto max-w-7xl px-3 py-8 sm:px-6 sm:py-14 lg:px-8">
    <div class="mb-5 sm:mb-8">
      <h2 class="text-2xl font-semibold tracking-[-0.03em] text-gray-900 sm:text-4xl">
       Mobile Essentials
      </h2>
    </div>

    <!-- Skeleton loader -->
    <div v-if="loading" class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:grid-rows-2 sm:gap-4 xl:gap-5">
      <div
        v-for="index in skeletonCards"
        :key="`skeleton-${index}`"
        class="tech-card tech-card--skeleton"
        :class="cardClass(index)"
      >
        <div class="tech-card__skeleton-bg shimmer"></div>

        <div class="relative z-10 flex h-full flex-col justify-between" :class="contentClass(index)">
          <div>
            <div
              class="skeleton-line shimmer rounded-full"
              :class="index < 2 ? 'h-6 w-32 sm:h-8 sm:w-44 xl:h-10 xl:w-56' : 'h-5 w-28 sm:h-6 sm:w-32 xl:h-8 xl:w-40'"
            ></div>

            <div
              class="skeleton-line shimmer mt-3 rounded-full opacity-80"
              :class="index < 2 ? 'h-3 w-24 sm:h-4 sm:w-32 xl:h-5 xl:w-40' : 'h-3 w-20 sm:h-3 sm:w-24 xl:h-4 xl:w-28'"
            ></div>
          </div>

          <div class="mt-3 flex items-end justify-between gap-1.5 sm:mt-6 sm:gap-2 xl:mt-10">
            <div
              class="skeleton-pill shimmer rounded-full"
              :class="buttonClass(index)"
              style="width: 90px; height: 30px;"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error -->
    <div
      v-else-if="loadError"
      class="rounded-3xl border border-red-200 bg-red-50 px-5 py-6 text-sm text-red-600"
    >
      Failed to load categories. Please refresh the page.
    </div>

    <!-- Real categories -->
    <div
      v-else-if="visibleCategories.length"
      class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:grid-rows-2 sm:gap-4 xl:gap-5"
    >
      <Link
        v-for="(category, index) in visibleCategories"
        :key="category.id"
        :href="route('frontend.tech-products.index', { category: category.name })"
        class="tech-card block"
        :class="[cardClass(index), { 'tech-card--no-image': !category.image_url }]"
        :style="{
          '--card-delay': `${index * 120}ms`,
          '--float-duration': `${5 + (index % 3) * 0.6}s`,
        }"
      >
        <div
          class="tech-card__bg"
          :style="category.image_url ? { backgroundImage: `url(${category.image_url})` } : undefined"
        />

        <div class="tech-card__overlay" />
        <div class="tech-card__glow" />

        <div class="relative z-10 flex h-full flex-col justify-between" :class="contentClass(index)">
          <div>
            <h3
              class="max-w-[98%] font-semibold tracking-[-0.04em] text-white drop-shadow-[0_12px_28px_rgba(0,0,0,0.38)] sm:max-w-[88%] xl:max-w-[82%]"
              :class="titleClass(index)"
            >
              {{ category.name }}
            </h3>
          </div>

          <div class="mt-3 flex items-end justify-between gap-1.5 sm:mt-6 sm:gap-2 xl:mt-10">
            <div
              class="inline-flex max-w-full items-center gap-1 rounded-full border border-white/20 bg-white/10 font-semibold uppercase text-white backdrop-blur-[6px] sm:gap-1.5 xl:gap-2"
              :class="buttonClass(index)"
            >
              <span class="truncate">View All</span>
              <span class="tech-card__arrow text-[10px] leading-none sm:text-xs xl:text-base">›</span>
            </div>

            <div
              v-if="!category.image_url"
              class="rounded-full border border-white/16 bg-white/10 px-1.5 py-1 text-[8px] font-semibold uppercase tracking-[0.1em] text-white/80 backdrop-blur sm:px-2 sm:text-[7px] xl:px-3 xl:py-1.5 xl:text-[10px] xl:tracking-[0.2em]"
            >
              No Image
            </div>
          </div>
        </div>
      </Link>
    </div>
  </section>
</template>

<style scoped>
.tech-card {
  position: relative;
  overflow: hidden;
  border-radius: 18px;
  background: #0f172a;
  isolation: isolate;
  translate: 0 0;
  min-width: 0;
  box-shadow: 0 12px 36px rgba(15, 23, 42, 0.08);
  
}

.tech-card:hover {
 
  box-shadow: 0 28px 70px rgba(15, 23, 42, 0.18);
}

.tech-card__bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  opacity: 0.64;
  transform: scale(1);
  filter: saturate(1.08);
  transition:
    transform 0.9s cubic-bezier(0.22, 1, 0.36, 1),
    opacity 0.9s cubic-bezier(0.22, 1, 0.36, 1),
    filter 0.9s cubic-bezier(0.22, 1, 0.36, 1);
}

.tech-card__overlay {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(
      180deg,
      rgba(15, 23, 42, 0.16) 0%,
      rgba(15, 23, 42, 0.12) 28%,
      rgba(15, 23, 42, 0.3) 68%,
      rgba(15, 23, 42, 0.52) 100%
    );
}

.tech-card__glow {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at top left, rgba(255, 255, 255, 0.18), transparent 45%),
    radial-gradient(circle at bottom right, rgba(255, 255, 255, 0.08), transparent 42%);
  pointer-events: none;
}

.tech-card--no-image .tech-card__bg {
  background:
    linear-gradient(135deg, rgba(51, 65, 85, 0.96), rgba(15, 23, 42, 1));
  opacity: 1;
  filter: none;
}

.tech-card__arrow {
  display: inline-block;
  transform: translateX(0);
  transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
}

.tech-card:hover .tech-card__bg {
  transform: scale(1.08);
  opacity: 0.74;
  filter: saturate(1.14);
}

.tech-card:hover .tech-card__arrow {
  transform: translateX(4px);
}

/* Skeleton */
.tech-card--skeleton {
  background: linear-gradient(135deg, #111827, #1f2937);
  box-shadow: 0 12px 36px rgba(15, 23, 42, 0.08);
  animation: none;
}

.tech-card--skeleton:hover {
  transform: none;
  box-shadow: 0 12px 36px rgba(15, 23, 42, 0.08);
}

.tech-card__skeleton-bg {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(135deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02)),
    linear-gradient(135deg, #0f172a, #1e293b);
  opacity: 1;
}

.skeleton-line,
.skeleton-pill {
  background: rgba(255, 255, 255, 0.16);
}

.shimmer {
  position: relative;
  overflow: hidden;
}

.shimmer::after {
  content: '';
  position: absolute;
  inset: 0;
  transform: translateX(-100%);
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255,255,255,0.18),
    transparent
  );
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  100% {
    transform: translateX(100%);
  }
}

@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(22px) scale(0.985);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes floatCard {
  0%,
  100% {
    translate: 0 0;
  }
  50% {
    translate: 0 -15px;
  }
}

@media (min-width: 640px) {
  .tech-card {
    border-radius: 26px;
  }
}

@media (min-width: 1280px) {
  .tech-card {
    border-radius: 32px;
  }
}

@media (max-width: 420px) {
  .tech-card__bg {
    opacity: 0.68;
  }

  .tech-card__overlay {
    background:
      linear-gradient(
        180deg,
        rgba(15, 23, 42, 0.2) 0%,
        rgba(15, 23, 42, 0.14) 30%,
        rgba(15, 23, 42, 0.35) 68%,
        rgba(15, 23, 42, 0.58) 100%
      );
  }
}

@media (prefers-reduced-motion: reduce) {
  .tech-card,
  .tech-card__bg,
  .tech-card__arrow,
  .shimmer::after {
    animation: none !important;
    transition: none !important;
    transform: none !important;
    translate: 0 0 !important;
  }
}
</style>
