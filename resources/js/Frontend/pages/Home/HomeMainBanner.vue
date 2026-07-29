<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'

type BannerImage = {
  id: number
  src: string | null
  mobileSrc?: string | null
  alt: string
}

const banners: BannerImage[] = [
  {
    id: 1,
    src: null,
    mobileSrc: null,
    alt: 'DezeStore banner placeholder',
  },
]

const active = ref(0)
const isPaused = ref(false)

let autoplayTimer: number | null = null

function goTo(index: number) {
  if (!banners.length) return
  active.value = (index + banners.length) % banners.length
}

function next() {
  goTo(active.value + 1)
}

function stopAutoplay() {
  if (autoplayTimer !== null) {
    window.clearInterval(autoplayTimer)
    autoplayTimer = null
  }
}

function startAutoplay() {
  stopAutoplay()

  if (banners.length <= 1) return

  autoplayTimer = window.setInterval(() => {
    if (!isPaused.value && !document.hidden) {
      next()
    }
  }, 5600)
}

onMounted(() => {
  startAutoplay()
})

onBeforeUnmount(() => {
  stopAutoplay()
})
</script>

<template>
  <section
    v-if="banners.length"
    class="home-main-banner"
    :class="{ 'home-main-banner--paused': isPaused }"
    aria-label="Home banners"
    @mouseenter="isPaused = true"
    @mouseleave="isPaused = false"
    @focusin="isPaused = true"
    @focusout="isPaused = false"
  >
    <div class="home-main-banner__viewport">
      <div
        class="home-main-banner__track"
        :style="{ transform: `translateX(-${active * 100}%)` }"
      >
        <div
          v-for="banner in banners"
          :key="banner.id"
          class="home-main-banner__slide"
        >
          <picture v-if="banner.src">
            <source
              v-if="banner.mobileSrc"
              media="(max-width: 640px)"
              :srcset="banner.mobileSrc"
            >
            <img
              :src="banner.src"
              :alt="banner.alt"
              class="home-main-banner__image"
              loading="eager"
              decoding="async"
              draggable="false"
            >
          </picture>
          <div
            v-else
            class="home-main-banner__empty"
            role="img"
            :aria-label="banner.alt"
          />
        </div>
      </div>

      <div
        v-if="banners.length > 1"
        class="home-main-banner__progress"
        aria-label="Banner progress"
      >
        <button
          v-for="(banner, index) in banners"
          :key="`banner-progress-${banner.id}`"
          type="button"
          class="home-main-banner__progress-item"
          :class="index === active ? 'home-main-banner__progress-item--active' : ''"
          :aria-label="`Show banner ${index + 1}`"
          :aria-current="index === active ? 'true' : undefined"
          @click="goTo(index)"
        >
          <span />
        </button>
      </div>
    </div>
  </section>
</template>

<style scoped>
.home-main-banner {
  width: 100%;
  background: #ffffff;
}

.home-main-banner__viewport {
  position: relative;
  width: 100%;
  height: clamp(360px, 31vw, 620px);
  overflow: hidden;
  background: #f8fafc;
}

.home-main-banner__track {
  display: flex;
  height: 100%;
  transition: transform 850ms cubic-bezier(0.22, 1, 0.36, 1);
}

.home-main-banner__slide {
  min-width: 100%;
  height: 100%;
  overflow: hidden;
}

.home-main-banner__slide picture {
  display: block;
  width: 100%;
  height: 100%;
}

.home-main-banner__empty {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 52%, #eef2f7 100%);
}

.home-main-banner__image {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  user-select: none;
  -webkit-user-drag: none;
}

.home-main-banner__progress {
  position: absolute;
  left: 50%;
  bottom: clamp(12px, 1.8vw, 22px);
  z-index: 2;
  display: none;
  align-items: center;
  width: min(156px, 34vw);
  gap: 6px;
  padding: 5px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.42);
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.16);
  backdrop-filter: blur(14px);
  transform: translateX(-50%);
}

.home-main-banner__progress-item {
  height: 3px;
  flex: 1 1 0;
  border: 0;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.16);
  overflow: hidden;
  padding: 0;
  cursor: pointer;
}

.home-main-banner__progress-item span {
  display: block;
  width: 0;
  height: 100%;
  border-radius: inherit;
  background: linear-gradient(90deg, #0ea5e9, #22c55e);
}

.home-main-banner__progress-item--active span {
  animation: banner-progress 5600ms linear forwards;
}

.home-main-banner--paused .home-main-banner__progress-item--active span {
  animation-play-state: paused;
}

@keyframes banner-progress {
  from {
    width: 0;
  }

  to {
    width: 100%;
  }
}

@media (max-width: 1023px) {
  .home-main-banner__viewport {
    height: clamp(260px, 42vw, 380px);
  }
}

@media (max-width: 640px) {
  .home-main-banner__viewport {
    height: clamp(260px, 74vw, 380px);
  }

  .home-main-banner__progress {
    bottom: 12px;
    width: min(124px, 38vw);
    gap: 5px;
    padding: 4px;
  }

  .home-main-banner__progress-item {
    height: 3px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .home-main-banner__track {
    transition: none !important;
  }

  .home-main-banner__progress-item--active span {
    animation: none !important;
    width: 100%;
  }
}
</style>
