<script setup lang="ts">
import AppLayout from '@/Frontend/layouts/AppLayout.vue'
import Banner from './partials/banner.vue'
import ContactUsSection from './partials/contactus.vue'

defineOptions({
  layout: AppLayout,
})

type BreadcrumbItem = {
  label: string
  href: string | null
}

type PageData = {
  title: string
  eyebrow?: string | null
  subtitle?: string | null
  breadcrumb?: BreadcrumbItem[]
}

type ContactData = {
  address: string
  phones: string[]
  email: string
  map_title: string
  map_url: string
  map_embed_url: string
}

const props = defineProps<{
  page: PageData
  contact: ContactData
}>()
</script>

<template>
  <div class="min-h-screen bg-white">
    <!-- <Banner :page="page" /> -->
    <ContactUsSection :contact="contact" />
  </div>
</template>
