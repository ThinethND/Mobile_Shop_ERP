<script setup lang="ts">
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/Backend/components/ui/sidebar'
import { urlIsActive } from '@/Backend/lib/utils'
import { NavItemWithSubmenu } from '@/Backend/types'
import { Link, usePage } from '@inertiajs/vue3'
import Collapsible from './ui/collapsible/Collapsible.vue'
import CollapsibleTrigger from './ui/collapsible/CollapsibleTrigger.vue'
import CollapsibleContent from './ui/collapsible/CollapsibleContent.vue'
import SidebarMenuSub from './ui/sidebar/SidebarMenuSub.vue'
import SidebarMenuSubItem from './ui/sidebar/SidebarMenuSubItem.vue'
import { ChevronRight } from 'lucide-vue-next'
import {
    Popover,
    PopoverTrigger,
    PopoverContent,
} from '@/Backend/components/ui/popover'

defineProps<{ items: NavItemWithSubmenu[] }>()

const page = usePage()
const currentUrl = computed(() => page.url)

const sidebarCollapsed = ref(false)
const isMobile = ref(false)

const checkViewport = () => {
    isMobile.value = window.innerWidth < 769 // Tailwind's md breakpoint
}

onMounted(() => {

    checkViewport()
    window.addEventListener('resize', checkViewport)
    // Find the closest Sidebar element
    const sidebarEl = document.querySelector('[data-state]')

    if (!sidebarEl) return

    const observer = new MutationObserver(() => {
        const state = sidebarEl.getAttribute('data-state')
        sidebarCollapsed.value = state !== 'expanded'
    })

    observer.observe(sidebarEl, { attributes: true, attributeFilter: ['data-state'] })

    // Initialize
    sidebarCollapsed.value = sidebarEl.getAttribute('data-state') !== 'expanded'

    onBeforeUnmount(() => {
        window.removeEventListener('resize', checkViewport)
    })
})

const isSubmenuOpen = (item: NavItemWithSubmenu): boolean => {
    if (!item.submenu?.length) return false
    return (
        urlIsActive(item.href, currentUrl.value) ||
        item.submenu.some(sub => urlIsActive(sub.href, currentUrl.value))
    )
}
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <!-- <SidebarGroupLabel>Platform</SidebarGroupLabel> -->

        <SidebarMenu>
            <Collapsible v-for="item in items" :key="item.title" class="group/collapsible"
                v-slot="{ open }" :defaultOpen="isSubmenuOpen(item)">
                <SidebarMenuItem>
                    <div v-if="item.submenu">

                        <template v-if="sidebarCollapsed && !isMobile">
                            <Popover>
                                <PopoverTrigger as-child>
                                    <SidebarMenuButton type="button" :tooltip="item.title"
                                        class="flex items-center justify-center cursor-pointer">
                                        <component :is="item.icon" class="w-4 h-4" />
                                    </SidebarMenuButton>
                                </PopoverTrigger>
                                <PopoverContent side="right" align="start"
                                    class="w-48 bg-white dark:bg-zinc-900 shadow-md rounded-lg p-2">
                                    <div class="flex flex-col">
                                        <Link v-for="subitem in item.submenu" :key="subitem.title" :href="subitem.href"
                                            class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                        <component :is="subitem.icon" class="w-4 h-4" />
                                        <span class="text-sm">{{ subitem.title }}</span>
                                        </Link>
                                    </div>
                                </PopoverContent>
                            </Popover>
                        </template>

                        <template v-else>
                            <!-- Parent Trigger -->
                            <CollapsibleTrigger asChild>
                                <SidebarMenuButton  :data-active="urlIsActive(item.href, currentUrl)"
                                    :tooltip="item.title" class="flex items-center justify-between cursor-pointer">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <component :is="item.icon" class="h-6 w-6 shrink-0" />
                                        <span class="min-w-0 whitespace-nowrap text-sm">{{ item.title }}</span>
                                    </div>

                                    <ChevronRight class="h-4 w-4 shrink-0 transition-transform duration-300 ease-in-out"
                                        :class="{ 'rotate-90': open }" />
                                </SidebarMenuButton>
                            </CollapsibleTrigger>

                            <!-- Submenu -->
                            <CollapsibleContent>
                                <SidebarMenuSub v-for="subitem in item.submenu" :key="subitem.title"
                                    :data-active="urlIsActive(subitem.href, currentUrl)">
                                    <Link :href="subitem.href">
                                    <SidebarMenuSubItem :data-active="urlIsActive(subitem.href, currentUrl)">
                                        <Link :href="subitem.href" class="my-3 flex min-w-0 items-center pl-1">
                                        <component :is="subitem.icon"
                                            :data-active="urlIsActive(subitem.href, currentUrl)"
                                            class="h-5 w-5 shrink-0 transition-colors duration-200 data-[active=true]:text-[color:var(--primary)]" />
                                        <span class="min-w-0 whitespace-nowrap pl-2 text-sm">{{ subitem.title }}</span>
                                        </Link>
                                    </SidebarMenuSubItem>
                                    </Link>

                                </SidebarMenuSub>
                            </CollapsibleContent>
                        </template>


                    </div>

                    <!-- Normal single item -->
                    <div v-else>
                        <SidebarMenuButton as-child :data-active="urlIsActive(item.href, currentUrl)"
                            :tooltip="item.title" class="flex items-center gap-2">
                            <Link :href="item.href">
                            <component :is="item.icon" :data-active="urlIsActive(item.href, currentUrl)"
                                class="h-4 w-4 shrink-0 transition-colors duration-200 data-[active=true]:text-[color:var(--primary)]" />
                            <span class="whitespace-nowrap">{{ item.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </div>
                </SidebarMenuItem>
            </Collapsible>
        </SidebarMenu>
    </SidebarGroup>
</template>
