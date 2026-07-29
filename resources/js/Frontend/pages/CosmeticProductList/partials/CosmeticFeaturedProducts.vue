<script setup lang="ts">
import FeaturedProducts from '@/Frontend/pages/Home/FeaturedProducts.vue'
</script>

<template>
  <FeaturedProducts
    endpoint="/home/featured-cosmetics"
    title="Featured Cosmetics"
    subtitle="Handpicked cosmetics chosen for formula, quality, and daily results."
    theme="rose"
    container-class="w-full px-0"
    explore-href="#cosmetic-product-list-section"
    label-mode="brand"
    card-tagline="cosmetic collection"
  />
</template>
