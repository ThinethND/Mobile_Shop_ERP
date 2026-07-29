<script setup lang="ts">
import AppLayout from '@/Backend/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/Backend/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
           <Snowfall />
        <slot />
    </AppLayout>
</template>
