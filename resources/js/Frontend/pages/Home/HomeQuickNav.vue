<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';

const appleIcon = '/assets/images/apple.png';
const shoeIcon = '/assets/images/running-shoes.png';
const cosmeticsIcon = '/assets/images/skin-care.png';

defineOptions({
    name: 'HomeQuickNav',
});

type NavTarget = {
    id: string;
    label: string;
    scrollTargetId: string;
    activeStartId: string;
    activeEndId: string;
    accentClass: string;
    iconAnimClass: string;
    iconSrc: string;
    iconAlt: string;
    iconWrapClass: string;
};

const isOpen = ref(true);
const activeTargetId = ref<string | null>(null);

const headerOffset = 96;
let rafHandle: number | null = null;

const targets: NavTarget[] = [
    {
        id: 'mobile',
        label: 'Mobile Essentials',
        scrollTargetId: 'products-section',
        activeStartId: 'phone-showcase-section',
        activeEndId: 'products-section',
        accentClass: 'from-sky-50 to-indigo-100 ring-1 ring-sky-200/60',
        iconAnimClass: 'icon-ring',
        iconSrc: appleIcon,
        iconAlt: 'Mobile essentials icon',
        iconWrapClass: 'bg-sky-100/70',
    },
    {
        id: 'shoes',
        label: 'Shoe Collection',
        scrollTargetId: 'shoe-featured-products-section',
        activeStartId: 'shoe-categories-section',
        activeEndId: 'shoe-featured-products-section',
        accentClass: 'from-emerald-50 to-lime-100 ring-1 ring-emerald-200/60',
        iconAnimClass: 'icon-hop',
        iconSrc: shoeIcon,
        iconAlt: 'Shoe collection icon',
        iconWrapClass: 'bg-emerald-100/70',
    },
    {
        id: 'cosmetics',
        label: 'Cosmetics Collection',
        scrollTargetId: 'cosmetics-products-section',
        activeStartId: 'cosmetics-section',
        activeEndId: 'cosmetics-products-section',
        accentClass: 'from-rose-50 to-pink-100 ring-1 ring-rose-200/60',
        iconAnimClass: 'icon-float',
        iconSrc: cosmeticsIcon,
        iconAlt: 'Skincare icon',
        iconWrapClass: 'bg-rose-100/70',
    },
];

function setActiveTarget(targetId: string | null) {
    activeTargetId.value = targetId;
}

function isActiveTarget(targetId: string) {
    return activeTargetId.value === targetId;
}

function computeActiveTargetId() {
    if (typeof window === 'undefined') return null;

    const focusY = window.scrollY + headerOffset + 1;

    for (const target of targets) {
        const startElement = document.getElementById(target.activeStartId);
        const endElement = document.getElementById(target.activeEndId);

        if (!startElement) continue;

        const rangeStart = startElement.getBoundingClientRect().top + window.scrollY;
        const rangeEndElement = endElement ?? startElement;
        const rangeEnd =
            rangeEndElement.getBoundingClientRect().top +
            window.scrollY +
            rangeEndElement.offsetHeight;

        if (focusY >= rangeStart && focusY <= rangeEnd) {
            return target.id;
        }
    }

    return null;
}

function scheduleActiveSync() {
    if (typeof window === 'undefined') return;
    if (rafHandle !== null) return;

    rafHandle = window.requestAnimationFrame(() => {
        rafHandle = null;

        const nextActive = computeActiveTargetId();
        if (nextActive === activeTargetId.value) return;

        activeTargetId.value = nextActive;
    });
}

function scrollToTarget(targetId: string) {
    if (typeof window === 'undefined') return;

    const target = targets.find((target) => target.id === targetId);
    if (!target) return;

    const element = document.getElementById(target.scrollTargetId);
    if (!element) return;

    setActiveTarget(targetId);

    const y = Math.max(
        0,
        element.getBoundingClientRect().top + window.scrollY - headerOffset,
    );

    window.scrollTo({ top: y, behavior: 'smooth' });

    const url = new URL(window.location.href);
    url.hash = target.scrollTargetId;
    window.history.replaceState({}, '', url.toString());
}

onMounted(() => {
    if (typeof window === 'undefined') return;

    const hashTarget = window.location.hash.replace('#', '').trim();
    const targetFromHash = targets.find((target) => {
        return [
            target.id,
            target.scrollTargetId,
            target.activeStartId,
            target.activeEndId,
        ].includes(hashTarget);
    });

    if (targetFromHash) {
        activeTargetId.value = targetFromHash.id;
    }

    scheduleActiveSync();
    window.addEventListener('scroll', scheduleActiveSync, { passive: true });
    window.addEventListener('resize', scheduleActiveSync, { passive: true });
});

onBeforeUnmount(() => {
    if (typeof window === 'undefined') return;

    window.removeEventListener('scroll', scheduleActiveSync);
    window.removeEventListener('resize', scheduleActiveSync);

    if (rafHandle !== null) {
        window.cancelAnimationFrame(rafHandle);
        rafHandle = null;
    }
});
</script>

<template>
    <div
        class="fixed top-1/2 right-3 z-[95] md:right-6"
        aria-label="Quick section navigation"
    >
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-x-4"
            enter-to-class="opacity-100 translate-x-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-x-0"
            leave-to-class="opacity-0 translate-x-4"
        >
            <div
                v-if="isOpen"
                key="open"
                class="absolute top-0 right-0 z-10 -translate-y-1/2"
            >
                <div
                    class="rounded-2xl border border-white/70 bg-white/90 shadow-[0_18px_50px_rgba(15,23,42,0.14)] backdrop-blur-md"
                >
                    <div class="flex flex-col items-center gap-2 p-2.5">
                        <button
                            v-for="target in targets"
                            :key="target.id"
                            type="button"
                            class="quicknav-button group relative inline-flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br shadow-sm transition hover:-translate-y-0.5 hover:shadow-md active:-translate-y-0.5 active:shadow-md focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:ring-offset-2 sm:h-11 sm:w-11"
                            :class="[target.accentClass, isActiveTarget(target.id) ? 'is-active' : '']"
                            :aria-label="`Go to ${target.label}`"
                            :title="target.label"
                            @click="scrollToTarget(target.id)"
                        >
                            <span
                                class="pointer-events-none absolute inset-0 rounded-xl bg-white/40 opacity-0 transition group-hover:opacity-100 group-active:opacity-100"
                            />

                            <span
                                class="pointer-events-none absolute inset-x-2 bottom-1 h-2 rounded-full bg-black/10 blur-md"
                            />

                            <span
                                :class="[
                                    'relative inline-flex items-center justify-center rounded-lg p-1.5 shadow-[inset_0_1px_0_rgba(255,255,255,0.7)]',
                                    target.iconAnimClass,
                                    target.iconWrapClass,
                                ]"
                            >
                                <img
                                    :src="target.iconSrc"
                                    :alt="target.iconAlt"
                                    class="h-5 w-5 object-contain select-none sm:h-6 sm:w-6"
                                    draggable="false"
                                />
                            </span>
                        </button>

                        <div class="my-1 h-px w-8 bg-neutral-200/80 sm:w-10" />

                        <button
                            type="button"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-neutral-200 bg-white text-neutral-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-neutral-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:ring-offset-2 sm:h-10 sm:w-10"
                            aria-label="Hide quick navigation"
                            title="Hide"
                            @click="isOpen = false"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <button
                v-else
                key="closed"
                type="button"
                class="group absolute top-0 right-0 z-20 inline-flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-2xl border border-white/70 bg-white/90 text-neutral-800 shadow-[0_18px_50px_rgba(15,23,42,0.14)] backdrop-blur-md transition hover:shadow-[0_24px_70px_rgba(15,23,42,0.18)] focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:ring-offset-2"
                aria-label="Open quick navigation"
                title="Quick navigation"
                @click="isOpen = true"
            >
                <span
                    class="absolute inset-0 rounded-2xl bg-gradient-to-br from-rose-100/30 via-white/10 to-sky-100/30 opacity-0 transition group-hover:opacity-100"
                />
                <span class="relative inline-flex items-center justify-center">
                    <span
                        class="cta-pulse absolute inline-flex h-9 w-9 rounded-xl bg-red-500/10 blur-[1px]"
                    />
                    <svg
                        viewBox="0 0 24 24"
                        class="relative h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                </span>
            </button>
        </Transition>
    </div>
</template>

<style scoped>
.quicknav-button {
    -webkit-tap-highlight-color: transparent;
}

.quicknav-button.is-active {
    z-index: 1;
    transform: translateX(-2px) scale(1.08);
    box-shadow:
        0 22px 60px rgba(15, 23, 42, 0.18),
        0 0 0 2px rgba(239, 68, 68, 0.28);
}

.quicknav-button.is-active::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 0.75rem;
    box-shadow: inset 0 0 0 1px rgba(239, 68, 68, 0.22);
    pointer-events: none;
}

@keyframes ring {
    0%,
    82%,
    100% {
        transform: rotate(0deg) translateY(0);
    }
    86% {
        transform: rotate(10deg) translateY(-1px);
    }
    90% {
        transform: rotate(-10deg) translateY(0);
    }
    94% {
        transform: rotate(6deg) translateY(-1px);
    }
    98% {
        transform: rotate(-6deg) translateY(0);
    }
}

@keyframes hop {
    0%,
    100% {
        transform: translateY(0) rotate(0deg) scale(1);
    }
    30% {
        transform: translateY(-2px) rotate(-4deg) scale(1.02);
    }
    50% {
        transform: translateY(-4px) rotate(4deg) scale(1.05);
    }
    70% {
        transform: translateY(-1px) rotate(-2deg) scale(1.01);
    }
}

@keyframes floaty {
    0%,
    100% {
        transform: translateY(0) rotate(0deg);
    }
    25% {
        transform: translateY(-2px) rotate(-2deg);
    }
    50% {
        transform: translateY(-4px) rotate(1deg);
    }
    75% {
        transform: translateY(-2px) rotate(2deg);
    }
}

@keyframes ctaPulse {
    0%,
    100% {
        transform: scale(0.92);
        opacity: 0.55;
    }
    50% {
        transform: scale(1.08);
        opacity: 0.9;
    }
}

.icon-ring {
    animation: ring 2.8s ease-in-out infinite;
    transform-origin: 50% 85%;
    will-change: transform;
}

.icon-hop {
    animation: hop 2.2s ease-in-out infinite;
    will-change: transform;
}

.icon-float {
    animation: floaty 2.9s ease-in-out infinite;
    will-change: transform;
}

.cta-pulse {
    animation: ctaPulse 2.2s ease-in-out infinite;
}

@media (prefers-reduced-motion: reduce) {
    .icon-ring,
    .icon-hop,
    .icon-float,
    .cta-pulse {
        animation: none;
    }
}
</style>
