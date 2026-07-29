<script setup lang="ts">
import AppContent from '@/Backend/components/AppContent.vue';
import AppHeader from '@/Backend/components/AppHeader.vue';
import AppShell from '@/Backend/components/AppShell.vue';
import type { BreadcrumbItemType } from '@/Backend/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell class="flex-col">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent>
            <slot />
        </AppContent>
    </AppShell>
</template>
