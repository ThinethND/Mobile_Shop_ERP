<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
</script>

<template>
    <div
        class="min-h-screen flex flex-col items-center justify-center bg-black text-white relative overflow-hidden font-['Orbitron',sans-serif]">

        <Head>
            <title>Welcome</title>
            <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&display=swap" rel="stylesheet" />
        </Head>

        <!-- Background glow -->
        <div
            class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(56,189,248,0.15)_0%,transparent_70%),radial-gradient(circle_at_bottom_right,rgba(56,189,248,0.15)_0%,transparent_80%)] blur-3xl">
        </div>


        <!-- Hero Section (Centered) -->
        <main
            class="relative z-10 flex flex-col items-center justify-center text-center w-full px-4 sm:px-6 md:px-10 py-20 flex-grow">
            <h1
                class="neon-stroke text-[2.5rem] sm:text-[3.5rem] md:text-[5rem] lg:text-[6rem] xl:text-[7rem] font-extrabold uppercase tracking-wider animate-fadeIn">
                DezeSt<span class="flicker-3">o</span>re
            </h1>

            <div class="mt-10 flex flex-col sm:flex-row justify-center items-center gap-4 w-full sm:w-auto animate-fadeIn">

                <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                    class="rounded-full bg-[#38bdf8] px-8 py-3 text-sm sm:text-base font-semibold text-white shadow-[0_0_15px_rgba(56,189,248,0.4)] hover:bg-[#7dd3fc] hover:shadow-[0_0_25px_rgba(56,189,248,0.6)] transition-all text-center w-[70%] sm:w-auto">
                Dashboard
                </Link>
                <template v-else>
                     <Link :href="route('login')"
                    class="rounded-full bg-[#38bdf8] px-8 py-3 text-sm sm:text-base font-semibold text-white shadow-[0_0_15px_rgba(56,189,248,0.4)] hover:bg-[#7dd3fc] hover:shadow-[0_0_25px_rgba(56,189,248,0.6)] transition-all text-center w-[70%] sm:w-auto">
                Login
                </Link>
                </template>


                <a href="https://laravel.com/docs" target="_blank"
                    class="rounded-full border border-white/30 px-8 py-3 text-sm sm:text-base font-semibold text-white hover:bg-white hover:text-black transition text-center w-[70%] sm:w-auto">
                    Learn More
                </a>
            </div>
        </main>

        <!-- Footer -->
        <footer class="z-10 py-6 px-4 text-center absolute bottom-2 w-full">
            <div class="text-xs sm:text-sm text-gray-400">
                (c) {{ new Date().getFullYear() }} DezeStore - Built for Gadgets
            </div>
            <div class="mt-1 text-[0.7rem] sm:text-xs text-gray-500">
                Powered by
                <a href="https://www.weblook.com" target="_blank" rel="noopener"
                    class="font-semibold text-[#38bdf8] hover:text-white hover:drop-shadow-[0_0_6px_rgba(56,189,248,0.6)] transition">
                    WEBLOOK
                </a>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Fade In Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(15px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.8s ease forwards;
}

/* Neon Stroke Text */
.neon-stroke {
    color: transparent;
    -webkit-text-stroke: 2px #38bdf8;
    text-fill-color: transparent;
    letter-spacing: 3px;
    text-shadow:
        0 0 6rem #38bdf8;

}

/* Subtle realistic flicker */
.flicker,
.flicker-2 {
    -webkit-text-stroke: 2px #38bdf8;
}

.flicker {
    animation: flickerBlink 2.5s infinite;
}

.flicker-2 {
    animation: flickerBlink 3s infinite;
}
.flicker-3 {
    animation: flickerBlink 3.5s infinite;
}
.flicker-4 {
    animation: flickerBlink 4s infinite;
}

@keyframes flickerBlink {

    0%,
    18%,
    22%,
    25%,
    53%,
    57%,
    100% {
        opacity: 1;
    }

    20%,
    24%,
    55% {
        opacity: 0.2;
    }
}

/* Scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-thumb {
    background: rgba(56, 189, 248, 0.5);
    border-radius: 9999px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(56, 189, 248, 0.8);
}

::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}
</style>
