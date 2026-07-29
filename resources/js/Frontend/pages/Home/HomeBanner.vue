<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

type BannerTone = 'light' | 'dark'

type BannerCard = {
  id: string
  eyebrow: string
  title: string
  description?: string
  buttonText: string
  buttonUrl: string
  desktopImage: string
  mobileImage?: string
  alt: string
  accent: string
  tone?: BannerTone
  imageOnly?: boolean
}

type LayoutItem = {
  name: 'hero' | 'left' | 'middle' | 'side'
  banner: BannerCard
  className: string
}

const AUTO_SLIDE_TIME = 5500
const SWIPE_MIN_DISTANCE = 42

function publicImage(fileName: string) {
  return `/images/${encodeURIComponent(fileName)}`
}

const heroSlides: BannerCard[] = [
  {
    id: 'hero-1',
    eyebrow: 'Helmet Collection',
    title: 'White Series Ride Gear',
    description:
      'Clean shell design, secure comfort, and everyday protection for riders who want a sharper road look.',
    buttonText: 'Shop Helmets',
    buttonUrl: '/motorcycle-products',
    desktopImage: publicImage('whitehelmetdesktopbanner.webp'),
    mobileImage: publicImage('whitebannersquare.webp'),
    alt: 'Premium motorcycle helmet',
    accent:
      'radial-gradient(120% 90% at 0% 50%, rgba(15,23,42,0.9) 0%, rgba(15,23,42,0.54) 42%, rgba(15,23,42,0) 74%)',
    tone: 'light',
  },
  {
    id: 'hero-2',
    eyebrow: 'Performance Helmets',
    title: 'Built For Fast Roads',
    description:
      'Aerodynamic helmet picks with bold finishes, dependable fit, and road-ready riding confidence.',
    buttonText: 'Explore Helmets',
    buttonUrl: '/motorcycle-products',
    desktopImage: publicImage('desktopbannerhelmet.webp'),
    mobileImage: publicImage('blackbannersquare.webp'),
    alt: 'Sunset series motorcycle helmet',
    accent:
      'radial-gradient(120% 90% at 0% 60%, rgba(24,24,27,0.88) 0%, rgba(24,24,27,0.5) 34%, rgba(24,24,27,0) 76%)',
    tone: 'light',
  },
]

const promoBanners = {
  left: {
    id: 'left-fashion',
    eyebrow: '',
    title: '',
    description: '',
    buttonText: '',
    buttonUrl: '/tech-products',
    desktopImage: publicImage('kokobanner.webp'),
    mobileImage: publicImage('kokobanner.webp'),
    alt: 'Koko banner',
    accent: '',
    tone: 'dark',
    imageOnly: true,
  },
  middle: {
    id: 'middle-summer',
    eyebrow: 'Fashion Edit',
    title: 'Statement Bags',
    description: 'Fresh carry styles for daily looks.',
    buttonText: 'Shop Now',
    buttonUrl: '/fashion',
    desktopImage: publicImage('lvbagsdesktop.webp'),
    mobileImage: publicImage('lvbagsmobile2.webp'),
    alt: 'Fashion bags collection',
    accent:
      'linear-gradient(180deg, rgba(255,255,255,0.94) 0%, rgba(255,255,255,0.5) 48%, rgba(255,255,255,0) 100%)',
    tone: 'dark',
  },
  side: {
    id: 'side-season',
    eyebrow: 'Tech Essentials',
    title: 'Smart Watch Picks',
    description: 'Wearable tech for work, fitness, and everyday style.',
    buttonText: 'Shop Watches',
    buttonUrl: '/tech-products',
    desktopImage: publicImage('watchdesktop.webp'),
    mobileImage: publicImage('watchmobile.webp'),
    alt: 'Smart watch collection',
    accent:
      'linear-gradient(180deg, rgba(255,255,255,0.92) 0%, rgba(255,255,255,0.36) 46%, rgba(255,255,255,0) 100%)',
    tone: 'dark',
  },
} satisfies Record<'left' | 'middle' | 'side', BannerCard>

const activeHero = ref(0)
const touchStartX = ref(0)
const didSwipeHero = ref(false)
let autoplayTimer: number | null = null

const hasHeroSlides = computed(() => heroSlides.length > 0)
const canSlideHero = computed(() => heroSlides.length > 1)

const activeHeroCard = computed(() => {
  if (!heroSlides.length) return null
  return heroSlides[activeHero.value] ?? heroSlides[0]
})

const layoutItems = computed<LayoutItem[]>(() => {
  if (!activeHeroCard.value) return []

  return [
    {
      name: 'hero',
      banner: activeHeroCard.value,
      className: 'banner-card--hero',
    },
    {
      name: 'left',
      banner: promoBanners.left,
      className: 'banner-card--left',
    },
    {
      name: 'middle',
      banner: promoBanners.middle,
      className: 'banner-card--middle',
    },
    {
      name: 'side',
      banner: promoBanners.side,
      className: 'banner-card--side',
    },
  ]
})

function stopAutoplay() {
  if (autoplayTimer !== null) {
    window.clearInterval(autoplayTimer)
    autoplayTimer = null
  }
}

function startAutoplay() {
  stopAutoplay()

  if (!canSlideHero.value) return

  autoplayTimer = window.setInterval(() => {
    nextHero(false)
  }, AUTO_SLIDE_TIME)
}

function nextHero(restart = true) {
  if (!canSlideHero.value) return
  activeHero.value = (activeHero.value + 1) % heroSlides.length
  if (restart) startAutoplay()
}

function prevHero(restart = true) {
  if (!canSlideHero.value) return
  activeHero.value = (activeHero.value - 1 + heroSlides.length) % heroSlides.length
  if (restart) startAutoplay()
}

function goToHero(index: number) {
  if (index === activeHero.value) return
  activeHero.value = index
  startAutoplay()
}

function onCardClick(item: LayoutItem) {
  if (item.name !== 'hero') return
  if (!canSlideHero.value) return
  if (didSwipeHero.value) return

  nextHero()
}

function onCardTouchStart(item: LayoutItem, event: TouchEvent) {
  if (item.name !== 'hero') return
  touchStartX.value = event.changedTouches[0]?.clientX ?? 0
}

function onCardTouchEnd(item: LayoutItem, event: TouchEvent) {
  if (item.name !== 'hero') return

  const endX = event.changedTouches[0]?.clientX ?? 0
  const distance = endX - touchStartX.value

  if (Math.abs(distance) < SWIPE_MIN_DISTANCE) return

  didSwipeHero.value = true

  if (distance > 0) {
    prevHero()
  } else {
    nextHero()
  }

  window.setTimeout(() => {
    didSwipeHero.value = false
  }, 260)
}

function desktopImageFor(item: LayoutItem) {
  return item.banner.desktopImage
}

function mobileImageFor(item: LayoutItem) {
  return item.banner.mobileImage || item.banner.desktopImage
}

function loadingFor(item: LayoutItem) {
  return item.name === 'hero' ? 'eager' : 'lazy'
}

function fetchPriorityFor(item: LayoutItem) {
  return item.name === 'hero' ? 'high' : 'auto'
}

onMounted(() => {
  startAutoplay()
})

onBeforeUnmount(() => {
  stopAutoplay()
})
</script>

<template>
  <section v-if="hasHeroSlides" class="home-banner" aria-label="Home promotional banners">
    <div
      class="home-banner__inner"
      @mouseenter="stopAutoplay"
      @mouseleave="startAutoplay"
    >
      <div class="home-banner__grid">
        <article
          v-for="item in layoutItems"
          :key="item.name"
          class="banner-card"
          :class="[
            item.className,
            `tone-${item.banner.tone ?? 'dark'}`,
            {
              'banner-card--image-only': item.banner.imageOnly,
              'banner-card--clickable': item.name === 'hero' && canSlideHero,
              'banner-card--hero-2': item.banner.id === 'hero-2',
            },
          ]"
          @click="onCardClick(item)"
          @touchstart="onCardTouchStart(item, $event)"
          @touchend="onCardTouchEnd(item, $event)"
        >
          <Transition
            :name="item.name === 'hero' ? 'hero-bg-smooth' : 'banner-bg-fade'"
          >
            <picture
              :key="`${item.name}-image-${item.banner.id}`"
              class="banner-card__bg"
            >
              <source
                media="(max-width: 640px)"
                :srcset="mobileImageFor(item)"
              >
              <img
                :src="desktopImageFor(item)"
                :alt="item.banner.alt"
                :loading="loadingFor(item)"
                :fetchpriority="fetchPriorityFor(item)"
                decoding="async"
                draggable="false"
              >
            </picture>
          </Transition>

          <a
            v-if="item.banner.imageOnly && item.banner.buttonUrl"
            :href="item.banner.buttonUrl"
            class="banner-card__image-link"
            :aria-label="item.banner.alt"
            @click.stop
          ></a>

          <template v-if="!item.banner.imageOnly">
            <div class="banner-card__accent" :style="{ background: item.banner.accent }"></div>
            <div class="banner-card__sheen" aria-hidden="true"></div>

            <Transition
              :name="item.name === 'hero' ? 'hero-text-smooth' : 'banner-text-fade'"
              mode="out-in"
            >
              <div :key="`${item.name}-content-${item.banner.id}`" class="banner-card__content">
                <span class="banner-card__eyebrow">
                  <span class="banner-card__eyebrow-dot" aria-hidden="true"></span>
                  {{ item.banner.eyebrow }}
                </span>

                <h2 class="banner-card__title">
                  {{ item.banner.title }}
                </h2>

                <p v-if="item.banner.description" class="banner-card__description">
                  {{ item.banner.description }}
                </p>

                <a
                  :href="item.banner.buttonUrl"
                  class="banner-card__button"
                  @click.stop
                >
                  <span>{{ item.banner.buttonText }}</span>
                  <svg
                    class="banner-card__button-icon"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                  >
                    <path d="M5 12h14M13 5l7 7-7 7" />
                  </svg>
                </a>
              </div>
            </Transition>
          </template>

          <div
            v-if="item.name === 'hero' && canSlideHero"
            class="banner-card__dots"
          >
            <button
              v-for="(_, index) in heroSlides"
              :key="index"
              type="button"
              :aria-label="`Go to slide ${index + 1}`"
              class="banner-card__dot"
              :class="{ 'banner-card__dot--active': index === activeHero }"
              @click.stop="goToHero(index)"
            ></button>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>

<style scoped>
.home-banner {
  width: 100%;
  padding: 28px 20px 36px;
  background: linear-gradient(180deg, #f7f8fb 0%, #ffffff 100%);
  font-family: 'DezeStoreCustomFont', var(--font-sans, ui-sans-serif, system-ui, sans-serif);
}

.home-banner__inner {
  width: min(100%, 1280px);
  margin-inline: auto;
}

.home-banner__grid {
  display: grid;
  grid-template-columns: repeat(12, minmax(0, 1fr));
  grid-auto-rows: minmax(0, auto);
  gap: 18px;
}

.banner-card {
  position: relative;
  isolation: isolate;
  overflow: hidden;
  display: flex;
  min-height: 240px;
  border-radius: 8px;
  background: #0b1a44;
  box-shadow:
    0 1px 0 rgba(255, 255, 255, 0.6) inset,
    0 24px 60px -20px rgba(11, 26, 68, 0.35),
    0 8px 24px -12px rgba(11, 26, 68, 0.18);
  transform: translateZ(0);
  transition:
    transform 480ms cubic-bezier(0.2, 0.8, 0.2, 1),
    box-shadow 480ms ease;
}

.banner-card--clickable {
  cursor: pointer;
}

.banner-card:hover {
  transform: translateY(-3px);
  box-shadow:
    0 1px 0 rgba(255, 255, 255, 0.6) inset,
    0 32px 70px -20px rgba(11, 26, 68, 0.42),
    0 10px 28px -12px rgba(11, 26, 68, 0.22);
}

.banner-card__bg {
  position: absolute;
  inset: 0;
  z-index: 1;
  display: block;
  width: 100%;
  height: 100%;
  animation: banner-fade 600ms ease both;
  user-select: none;
}

@keyframes banner-fade {
  from {
    opacity: 0;
    transform: scale(1.04);
  }

  to {
    opacity: 1;
    transform: scale(1);
  }
}

.banner-card__bg img {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center right;
  transition: transform 900ms cubic-bezier(0.2, 0.8, 0.2, 1);
  user-select: none;
  -webkit-user-drag: none;
}

.banner-card:hover .banner-card__bg img {
  transform: scale(1.06);
}

.banner-card__image-link {
  position: absolute;
  inset: 0;
  z-index: 6;
  display: block;
}

.banner-card__accent {
  position: absolute;
  inset: 0;
  z-index: 2;
  pointer-events: none;
}

.banner-card__sheen {
  position: absolute;
  inset: 0;
  z-index: 3;
  pointer-events: none;
  background:
    radial-gradient(60% 80% at 90% 0%, rgba(255, 255, 255, 0.18), transparent 60%),
    linear-gradient(180deg, transparent 60%, rgba(0, 0, 0, 0.12) 100%);
  mix-blend-mode: overlay;
  opacity: 0.7;
}

.banner-card__content {
  position: relative;
  z-index: 4;
  display: flex;
  flex-direction: column;
  justify-content: center;
  width: 56%;
  padding: 32px 34px;
}

.banner-card.tone-light .banner-card__content {
  color: #ffffff;
}

.banner-card.tone-dark .banner-card__content {
  color: #0b1a44;
}

.banner-card__eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  width: max-content;
  max-width: 100%;
  margin-bottom: 14px;
  padding: 6px 12px 6px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 800;
  line-height: 1;
  letter-spacing: 0;
  text-transform: uppercase;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

.banner-card.tone-light .banner-card__eyebrow {
  background: rgba(255, 255, 255, 0.16);
  color: #ffffff;
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.25);
}

.banner-card.tone-dark .banner-card__eyebrow {
  background: rgba(11, 26, 68, 0.08);
  color: #0b1a44;
  box-shadow: inset 0 0 0 1px rgba(11, 26, 68, 0.1);
}

.banner-card__eyebrow-dot {
  width: 6px;
  height: 6px;
  flex: 0 0 auto;
  border-radius: 999px;
  background: #ff5b3a;
  box-shadow: 0 0 0 3px rgba(255, 91, 58, 0.18);
}

.banner-card__title {
  max-width: 380px;
  margin: 0;
  font-size: 44px;
  font-weight: 800;
  line-height: 1.02;
  letter-spacing: 0;
  text-wrap: balance;
}

.banner-card__description {
  max-width: 360px;
  margin: 12px 0 0;
  font-size: 14.5px;
  font-weight: 500;
  line-height: 1.55;
  opacity: 0.82;
}

.banner-card__button {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  width: max-content;
  margin-top: 22px;
  padding: 12px 20px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 800;
  line-height: 1;
  letter-spacing: 0;
  text-decoration: none;
  cursor: pointer;
  transition:
    transform 220ms ease,
    box-shadow 220ms ease,
    background 220ms ease,
    color 220ms ease;
}

.banner-card.tone-light .banner-card__button {
  background: #ffffff;
  color: #0b1a44;
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.25);
}

.banner-card.tone-light .banner-card__button:hover {
  background: #ff5b3a;
  color: #ffffff;
  transform: translateY(-2px);
}

.banner-card.tone-dark .banner-card__button {
  background: #0b1a44;
  color: #ffffff;
  box-shadow: 0 10px 24px rgba(11, 26, 68, 0.28);
}

.banner-card.tone-dark .banner-card__button:hover {
  background: #ff5b3a;
  transform: translateY(-2px);
  box-shadow: 0 14px 28px rgba(255, 91, 58, 0.35);
}

.banner-card__button-icon {
  width: 14px;
  height: 14px;
  transition: transform 220ms ease;
}

.banner-card__button:hover .banner-card__button-icon {
  transform: translateX(3px);
}

.banner-card--hero {
  grid-column: 1 / span 8;
  grid-row: 1;
  min-height: 340px;
}

.banner-card--left {
  grid-column: 1 / span 5;
  grid-row: 2;
  min-height: 260px;
}

.banner-card--middle {
  grid-column: 6 / span 3;
  grid-row: 2;
  min-height: 260px;
}

.banner-card--middle .banner-card__content {
  width: 100%;
  padding: 24px;
}

.banner-card--middle .banner-card__title {
  max-width: 180px;
  font-size: 38px;
}

.banner-card--middle .banner-card__description {
  max-width: 170px;
  font-size: 12.5px;
}

.banner-card--middle .banner-card__button {
  margin-top: 16px;
  padding: 10px 16px;
  font-size: 12px;
}

.banner-card--side {
  grid-column: 9 / span 4;
  grid-row: 1 / span 2;
}

.banner-card--side .banner-card__content {
  width: 100%;
  justify-content: flex-start;
  padding: 32px;
}

.banner-card--side .banner-card__bg img {
  object-position: center;
}

.banner-card__dots {
  position: absolute;
  left: 34px;
  bottom: 28px;
  z-index: 6;
  display: flex;
  gap: 8px;
}

.banner-card__dot {
  width: 8px;
  height: 8px;
  padding: 0;
  border: 0;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.45);
  cursor: pointer;
  transition:
    width 260ms ease,
    background 260ms ease;
}

.banner-card__dot--active {
  width: 26px;
  background: #ffffff;
}

.banner-bg-fade-enter-active,
.banner-bg-fade-leave-active,
.banner-text-fade-enter-active,
.banner-text-fade-leave-active {
  transition:
    opacity 320ms ease,
    transform 320ms ease;
}

.banner-bg-fade-enter-from,
.banner-text-fade-enter-from {
  opacity: 0;
  transform: scale(1.02);
}

.banner-bg-fade-leave-to,
.banner-text-fade-leave-to {
  opacity: 0;
  transform: scale(0.98);
}

.hero-bg-smooth-enter-active,
.hero-bg-smooth-leave-active {
  transition:
    opacity 760ms cubic-bezier(0.16, 1, 0.3, 1),
    transform 760ms cubic-bezier(0.16, 1, 0.3, 1);
}

.hero-bg-smooth-enter-from {
  opacity: 0;
  transform: scale(1.035);
}

.hero-bg-smooth-leave-to {
  opacity: 0;
  transform: scale(1.015);
}

.hero-text-smooth-enter-active,
.hero-text-smooth-leave-active {
  transition:
    opacity 520ms cubic-bezier(0.16, 1, 0.3, 1),
    transform 520ms cubic-bezier(0.16, 1, 0.3, 1);
}

.hero-text-smooth-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.hero-text-smooth-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

@media (max-width: 1023px) {
  .home-banner__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .banner-card--hero,
  .banner-card--left,
  .banner-card--middle,
  .banner-card--side {
    grid-column: auto;
    grid-row: auto;
  }

  .banner-card--hero {
    grid-column: 1 / -1;
    min-height: 320px;
  }

  .banner-card--side {
    grid-column: 1 / -1;
    min-height: 320px;
  }
}

@media (max-width: 640px) {
  .home-banner {
    padding: 16px 14px 24px;
  }

  .home-banner__grid {
    grid-template-columns: 1fr;
    gap: 14px;
  }

  .banner-card,
  .banner-card--hero,
  .banner-card--left,
  .banner-card--middle,
  .banner-card--side {
    grid-column: 1 / -1;
    min-height: 360px;
  }

  .banner-card--hero {
    min-height: 390px;
  }

  .banner-card__content,
  .banner-card--middle .banner-card__content,
  .banner-card--side .banner-card__content {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    justify-content: flex-start;
    padding: 24px 22px 84px;
  }

  .banner-card--hero .banner-card__content {
    padding: 72px 22px 82px;
  }

  .banner-card--hero.banner-card--hero-2 .banner-card__content {
    padding-top: 42px;
  }

  .banner-card__bg img,
  .banner-card--side .banner-card__bg img {
    object-position: center;
  }

  .banner-card--hero .banner-card__bg img {
    object-fit: cover;
    object-position: center bottom;
    transform: scale(1.12);
    transform-origin: center bottom;
  }

  .banner-card--hero:hover .banner-card__bg img {
    transform: scale(1.12);
  }

  .banner-card__button,
  .banner-card--hero .banner-card__button,
  .banner-card--middle .banner-card__button {
    position: absolute;
    right: 18px;
    bottom: 18px;
    margin-top: 0;
    z-index: 7;
  }

  .banner-card__accent {
    background: linear-gradient(
      180deg,
      rgba(0, 0, 0, 0.55) 0%,
      rgba(0, 0, 0, 0.25) 45%,
      rgba(0, 0, 0, 0) 100%
    ) !important;
  }

  .banner-card.tone-dark .banner-card__content {
    color: #ffffff;
  }

  .banner-card.tone-dark .banner-card__eyebrow {
    background: rgba(255, 255, 255, 0.16);
    color: #ffffff;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.25);
  }

  .banner-card.tone-dark .banner-card__button {
    background: #ffffff;
    color: #0b1a44;
  }

  .banner-card__title,
  .banner-card--middle .banner-card__title {
    max-width: 100%;
    font-size: 30px;
  }

  .banner-card__description,
  .banner-card--middle .banner-card__description {
    max-width: min(100%, 320px);
    font-size: 13px;
  }

  .banner-card--hero .banner-card__title {
    max-width: 92%;
  }

  .banner-card--hero .banner-card__description {
    max-width: 84%;
  }

  .banner-card__dots {
    left: 24px;
    bottom: 28px;
  }
}

@media (max-width: 380px) {
  .banner-card,
  .banner-card--hero,
  .banner-card--left,
  .banner-card--middle,
  .banner-card--side {
    min-height: 340px;
  }

  .banner-card--hero {
    min-height: 370px;
  }

  .banner-card__content,
  .banner-card--middle .banner-card__content,
  .banner-card--side .banner-card__content {
    padding: 20px 20px 78px;
  }

  .banner-card--hero .banner-card__content {
    padding: 64px 20px 78px;
  }

  .banner-card--hero.banner-card--hero-2 .banner-card__content {
    padding-top: 34px;
  }

  .banner-card__title,
  .banner-card--middle .banner-card__title {
    font-size: 27px;
  }

  .banner-card__button,
  .banner-card--hero .banner-card__button {
    right: 16px;
    bottom: 16px;
  }
}

/* Image-only style for left-fashion koko banner */
.banner-card.banner-card--image-only {
  min-height: 0;
  display: block;
  background: transparent;
}

.banner-card.banner-card--image-only:hover {
  transform: translateY(-3px);
}

.banner-card.banner-card--image-only .banner-card__bg {
  position: relative;
  inset: auto;
  z-index: 1;
  width: 100%;
  height: auto;
}

.banner-card.banner-card--image-only .banner-card__bg img {
  width: 100%;
  height: auto;
  object-fit: contain;
  object-position: center;
  transform: scale(1);
  transition: transform 900ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.banner-card.banner-card--image-only:hover .banner-card__bg img {
  transform: scale(1.04);
}
</style>
