<script setup lang="ts">
import { TooltipTrigger, type TooltipTriggerProps } from 'reka-ui'

const props = defineProps<TooltipTriggerProps>()
</script>

<template>
  <TooltipTrigger
  class="h-10 [&>svg]:size-6 group-data-[collapsible=icon]:size-10! gap-3! text-md"
    data-slot="tooltip-trigger"
    v-bind="props"

  >
    <slot />
  </TooltipTrigger>
</template>
