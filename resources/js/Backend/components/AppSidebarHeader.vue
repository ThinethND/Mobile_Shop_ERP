<script setup lang="ts">
import Breadcrumbs from '@/Backend/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/Backend/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/Backend/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <header
        class="flex h-full min-h-10 min-w-0 shrink items-center gap-2 px-0 transition-[width,height] ease-linear md:px-0"
    >
        <div class="flex min-w-0 items-center gap-2">
            <SidebarTrigger class="h-10 w-10 shrink-0" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
    </header>
</template>
