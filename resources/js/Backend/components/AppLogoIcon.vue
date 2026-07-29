<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg
        :class="className"
        v-bind="$attrs"
        viewBox="0 0 32 32"
        role="img"
        aria-label="DezeStore"
    >
        <rect width="32" height="32" rx="8" fill="currentColor" opacity="0.12" />
        <path
            d="M8.5 6.5h8.7c4.8 0 8.3 3.4 8.3 9.5s-3.5 9.5-8.3 9.5H8.5v-19Zm4.5 4.2v10.6h4c2.5 0 4-1.9 4-5.3s-1.5-5.3-4-5.3h-4Z"
            fill="currentColor"
        />
    </svg>
</template>
