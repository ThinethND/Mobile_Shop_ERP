<template>
    <div class="flex min-w-0 items-center gap-3 px-1">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white p-1.5
             ring-1 ring-white/20 shadow-[0_0_16px_rgba(56,189,248,.32)]
             group-data-[collapsible=icon]:h-8 group-data-[collapsible=icon]:w-8 group-data-[collapsible=icon]:rounded-lg group-data-[collapsible=icon]:p-1">
            <img src="/images/dezestoreblack.png" alt="DezeStore" class="h-full w-full object-contain" />
        </div>

        <div class="min-w-0 group-data-[collapsible=icon]:hidden">
            <h1 class="brand-neon whitespace-nowrap font-['Orbitron',sans-serif] font-extrabold uppercase leading-none">
                DEZESTORE
            </h1>
            <p class="mt-1 whitespace-nowrap text-[0.68rem] font-medium uppercase tracking-[0.18em] text-white/70">
                Admin Panel
            </p>
        </div>
    </div>
</template>

<style scoped>


/* flickers */
@keyframes flickerBlink {

    0%,
    18%,
    22%,
    25%,
    53%,
    57%,
    100% {
        opacity: 1
    }

    20%,
    24%,
    55% {
        opacity: .25
    }
}

.flicker {
    animation: flickerBlink 2.5s infinite
}

.flicker-2 {
    animation: flickerBlink 3.0s infinite
}

.flicker-3 {
    animation: flickerBlink 2.8s infinite
}

.flicker-4 {
    animation: flickerBlink 3.3s infinite
}
.brand-neon {
    color: #ffffff;
    letter-spacing: .08em;
    font-size: 1.05rem;
    text-shadow: 0 0 12px rgba(56, 189, 248, .32);
}

</style>
