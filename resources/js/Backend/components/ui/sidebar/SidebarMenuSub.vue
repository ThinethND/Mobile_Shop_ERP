<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/Backend/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <ul
    data-slot="sidebar-menu-sub"
    data-sidebar="menu-badge"
    :class="cn(
      'border-sidebar-border mx-3.5 my-1 flex min-w-0 translate-x-px flex-col gap-1 border-0  border-gray-600/40',
      'group-data-[collapsible=icon]:hidden',
      props.class,
    )"
  >
    <slot />
  </ul>
</template>
