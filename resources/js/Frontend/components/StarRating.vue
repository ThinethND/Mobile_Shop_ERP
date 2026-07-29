<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    rating?: number | null
    count?: number | null
    showCount?: boolean
    showValue?: boolean
    size?: 'xs' | 'sm' | 'md'
  }>(),
  {
    rating: null,
    count: null,
    showCount: false,
    showValue: true,
    size: 'sm',
  }
)

const safeRating = computed(() => {
  const value = Number(props.rating ?? 0)
  if (Number.isNaN(value)) return 0
  return Math.max(0, Math.min(5, value))
})

function starFillStyle(starNumber: number) {
  const rating = safeRating.value
  const fill = Math.max(0, Math.min(1, rating - (starNumber - 1)))
  const unfilledPercent = (1 - fill) * 100

  return {
    clipPath: `inset(0 ${unfilledPercent}% 0 0)`,
  }
}

const displayValue = computed(() => {
  if (props.rating === null || typeof props.rating === 'undefined') {
    return '-'
  }

  return (Math.round(safeRating.value * 10) / 10).toFixed(1)
})

const starSizeClass = computed(() => {
  if (props.size === 'xs') return 'h-3 w-3'
  if (props.size === 'md') return 'h-5 w-5'
  return 'h-4 w-4'
})

const starGapClass = computed(() => {
  if (props.size === 'xs') return 'gap-[2px]'
  if (props.size === 'md') return 'gap-[3px] sm:gap-[4px]'
  return 'gap-[2px] sm:gap-[3px]'
})

const valueSizeClass = computed(() => {
  if (props.size === 'xs') return 'text-xs'
  if (props.size === 'md') return 'text-sm'
  return 'text-sm'
})
</script>

<template>
  <div class="inline-flex items-center gap-2">
    <div class="inline-flex items-center leading-none" :class="starGapClass" aria-hidden="true">
      <span
        v-for="starNumber in 5"
        :key="`star-${starNumber}`"
        class="relative inline-flex shrink-0"
        :class="starSizeClass"
      >
        <svg viewBox="0 0 24 24" class="block h-full w-full text-[#d1d5db]" fill="currentColor">
          <path
            d="M12 2.25l2.917 5.91 6.523.948-4.72 4.6 1.114 6.497L12 17.118 6.166 20.205l1.114-6.497-4.72-4.6 6.523-.948L12 2.25z"
          />
        </svg>

        <span class="absolute inset-0 block" :style="starFillStyle(starNumber)">
          <svg viewBox="0 0 24 24" class="block h-full w-full text-[#f2a536]" fill="currentColor">
            <path
              d="M12 2.25l2.917 5.91 6.523.948-4.72 4.6 1.114 6.497L12 17.118 6.166 20.205l1.114-6.497-4.72-4.6 6.523-.948L12 2.25z"
            />
          </svg>
        </span>
      </span>
    </div>

    <span v-if="props.showValue" :class="['font-medium text-slate-700', valueSizeClass]">
      {{ displayValue }}
    </span>

    <span v-if="props.showCount && props.count !== null" class="text-xs text-slate-500">
      ({{ props.count }})
    </span>
  </div>
</template>
