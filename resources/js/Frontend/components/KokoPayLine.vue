<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(defineProps<{
  amount: number | null | undefined
  compact?: boolean
  detail?: boolean
}>(), {
  compact: false,
  detail: false,
})

const resolvedAmount = computed(() => {
  const value = Number(props.amount ?? 0)

  return Number.isFinite(value) && value > 0 ? value : null
})

function formatPrice(value: number) {
  return `Rs ${Number(value).toLocaleString('en-LK', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}
</script>

<template>
  <div
    v-if="resolvedAmount !== null"
    class="koko-pay-line"
    :class="{
      'koko-pay-line--compact': compact,
      'koko-pay-line--detail': detail,
    }"
  >
    <span>or pay in 3 x</span>
    <span class="koko-pay-line__amount">{{ formatPrice(resolvedAmount) }}</span>
    <span>with</span>
    <img
      src="/images/kokologo.webp"
      alt="Koko"
      class="koko-pay-line__logo"
      loading="lazy"
      decoding="async"
    />
  </div>
</template>

<style scoped>
.koko-pay-line {
  display: flex;
  min-width: 0;
  align-items: center;
  flex-wrap: wrap;
  gap: 3px 5px;
  color: #8a8a8a;
  font-size: 12px;
  font-weight: 500;
  letter-spacing: 0;
  line-height: 1.2;
}

.koko-pay-line__amount {
  color: #767676;
  font-weight: 800;
  white-space: nowrap;
}

.koko-pay-line__logo {
  display: inline-block;
  width: auto;
  height: 19px;
  flex: 0 0 auto;
  object-fit: contain;
}

.koko-pay-line--compact {
  gap: 2px 4px;
  font-size: 11px;
}

.koko-pay-line--compact .koko-pay-line__logo {
  height: 17px;
}

.koko-pay-line--detail {
  gap: 4px 6px;
  color: #8a8a8a;
  font-size: 15px;
}

.koko-pay-line--detail .koko-pay-line__logo {
  height: 26px;
}

@media (max-width: 640px) {
  .koko-pay-line {
    gap: 2px 4px;
    font-size: 10px;
  }

  .koko-pay-line__logo {
    height: 15px;
  }

  .koko-pay-line--compact {
    font-size: 9.5px;
  }

  .koko-pay-line--compact .koko-pay-line__logo {
    height: 14px;
  }

  .koko-pay-line--detail {
    gap: 3px 5px;
    font-size: 13px;
  }

  .koko-pay-line--detail .koko-pay-line__logo {
    height: 21px;
  }
}
</style>
