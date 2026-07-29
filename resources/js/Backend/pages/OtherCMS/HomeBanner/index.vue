<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import BannerList from './partials/BannerList.vue'
import { route } from 'ziggy-js'
</script>

<template>
  <AppLayout>
    <Head title="Home Banners" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">Home Banners</h1>
          <p class="text-sm text-neutral-500">
            Manage desktop and mobile home banner images.
          </p>
        </div>

        <Link
          :href="route('homebanners.create')"
          class="inline-flex w-full items-center justify-center rounded-full border border-[#38bdf8] px-4 py-2 text-sm font-medium text-[#38bdf8] transition hover:bg-[#38bdf8] hover:text-white sm:w-auto"
        >
          + New Home Banner
        </Link>
      </div>

      <BannerList />
    </div>
  </AppLayout>
</template>