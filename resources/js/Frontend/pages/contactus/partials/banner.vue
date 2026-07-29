<script setup lang="ts">
const bannerImage = '/assets/images/contact-banner.jpg'
</script>

<template>
  <section class="w-full bg-white">
    <div class="w-full overflow-hidden">
      <img
        :src="bannerImage"
        alt="Contact us banner"
        class="h-[220px] w-full object-cover sm:h-[300px] lg:h-[420px] xl:h-[500px]"
      />
    </div>
  </section>
</template>