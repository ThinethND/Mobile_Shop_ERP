<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-white text-center px-4">
    <h1 class="text-7xl font-bold text-zinc-800">404</h1>
    <p class="text-zinc-500 text-lg mt-2">The page you’re looking for doesn’t exist.</p>

    <Link
      :href="route('dashboard')"
      class="mt-6 px-5 py-2 rounded-lg bg-black text-white hover:bg-zinc-800 transition"
    >
      Back to Home
    </Link>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>
