<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import Topnavigationbar from './partials/Topnavigationbar.vue'
import Footer from './partials/Footer.vue'
import AiChatBot from '@/Frontend/components/AiChatBot.vue'

const page = usePage()

const pageTitle = computed(() => {
  const seo = page.props.seo || {}

  return typeof seo.seoTitle === 'string' && seo.seoTitle.trim().length
    ? seo.seoTitle
    : 'DezeStore'
})
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <Head :title="pageTitle" />

    <Topnavigationbar />

    <main class="flex-1">
      <slot />
    </main>

    <Footer />

    <AiChatBot />
  </div>
</template>
