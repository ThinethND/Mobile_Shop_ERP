<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

type ShowcaseSlide = {
  id: number | string
  badge: string
  title: string
  description: string
  image: string
  alt: string
}

const props = withDefaults(
  defineProps<{
    height?: string
    autoplayMs?: number
    slides?: ShowcaseSlide[]
  }>(),
  {
    height: '800px',
    autoplayMs: 4000,
    slides: () => [
      {
        id: 1,
        badge: 'Pre-Order Available',
        title: 'Introducing iPhone 17 Pro Max',
        description:
          'Discover a bold new flagship experience with the iPhone 17 Pro Max. Designed to look premium from every angle, it brings a refined silhouette, a striking pro finish, and a powerful first impression that belongs at the center of your next upgrade.',
        image: '/assets/images/ip171.webp',
        alt: 'iPhone 17 Pro Max',
      },
      {
        id: 2,
        badge: 'Coming Soon',
        title: 'Meet Samsung S36 Ultra',
        description:
          'Step into the next generation with the Samsung S36 Ultra. Built with a sleek modern profile, immersive display presence, and a confident premium finish, it delivers a standout flagship feel crafted for users who want power, style, and everyday impact.',
        image: '/assets/images/s24.webp',
        alt: 'Samsung S26 Ultra',
      },
    ],
  },
)

const currentIndex = ref(0)
const loading = ref(true)
const error = ref('')
const loadedImages = ref<string[]>([])

let sliderTimer: number | null = null
let pointerStartX = 0
let pointerStartY = 0
let pointerStarted = false

const currentSlide = computed(() => props.slides[currentIndex.value] ?? props.slides[0])
const hasMultipleSlides = computed(() => props.slides.length > 1)

const preloadImages = async () => {
  loading.value = true
  error.value = ''

  try {
    const promises = props.slides.map(
      (slide) =>
        new Promise<string>((resolve, reject) => {
          const img = new Image()
          img.onload = () => resolve(slide.image)
          img.onerror = () => reject(new Error(`Failed to load image: ${slide.image}`))
          img.src = slide.image
        }),
    )

    loadedImages.value = await Promise.all(promises)
    loading.value = false
  } catch (err) {
    console.error(err)
    error.value = 'Failed to load showcase images.'
    loading.value = false
  }
}

const nextSlide = () => {
  if (!props.slides.length) return
  currentIndex.value = (currentIndex.value + 1) % props.slides.length
}

const previousSlide = () => {
  if (!props.slides.length) return
  currentIndex.value = (currentIndex.value - 1 + props.slides.length) % props.slides.length
}

const goToSlide = (index: number) => {
  if (!props.slides[index]) return

  currentIndex.value = index
  restartAutoplay()
}

const startAutoplay = () => {
  stopAutoplay()

  if (props.slides.length <= 1) return

  sliderTimer = window.setInterval(() => {
    nextSlide()
  }, props.autoplayMs)
}

const stopAutoplay = () => {
  if (sliderTimer !== null) {
    window.clearInterval(sliderTimer)
    sliderTimer = null
  }
}

const restartAutoplay = () => {
  startAutoplay()
}

function handlePointerDown(event: PointerEvent) {
  pointerStartX = event.clientX
  pointerStartY = event.clientY
  pointerStarted = true
}

function handlePointerUp(event: PointerEvent) {
  if (!pointerStarted || !hasMultipleSlides.value) return

  const deltaX = event.clientX - pointerStartX
  const deltaY = event.clientY - pointerStartY
  pointerStarted = false

  if (Math.abs(deltaX) > 42 && Math.abs(deltaX) > Math.abs(deltaY)) {
    deltaX < 0 ? nextSlide() : previousSlide()
    restartAutoplay()
    return
  }

  if (Math.abs(deltaX) < 8 && Math.abs(deltaY) < 8) {
    nextSlide()
    restartAutoplay()
  }
}

function handlePointerCancel() {
  pointerStarted = false
}

onMounted(async () => {
  await preloadImages()
  if (!error.value) startAutoplay()
})

onBeforeUnmount(() => {
  stopAutoplay()
})
</script>

<template>
  <section
    class="phone-showcase relative w-full overflow-hidden text-white"
    :style="{ '--phone-stage-height': height }"
  >
    <div class="phone-showcase__ambient" aria-hidden="true" />

    <div class="phone-showcase__shell relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="phone-showcase__grid grid items-center gap-6 sm:gap-8 lg:grid-cols-[minmax(0,0.88fr)_minmax(0,1.12fr)] lg:gap-12 xl:gap-16">
        <div class="order-2 lg:order-1">
          <div class="phone-showcase__copy-stage">
            <Transition name="showcase-copy" mode="out-in">
              <div :key="currentSlide.id" class="phone-showcase__copy-slide">
                <!-- <div
                  class="inline-flex w-fit items-center self-start rounded-full border border-white/10 bg-white/[0.05] px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-white/75 sm:text-xs"
                >
                  {{ currentSlide.badge }}
                </div> -->

                <h2
                  class="mt-5 max-w-[11ch] text-[42px] font-semibold leading-[0.9] tracking-[-0.05em] text-white sm:text-[58px] lg:text-[68px] xl:text-[84px]"
                >
                  {{ currentSlide.title }}
                </h2>

                <p class="mt-5 max-w-[60ch] text-[15px] leading-7 text-white/72 sm:text-base sm:leading-8">
                  {{ currentSlide.description }}
                </p>
              </div>
            </Transition>
          </div>
        </div>

        <div class="order-1 lg:order-2">
          <div
            class="phone-showcase__stage-wrap relative ml-auto w-full"
            @mouseenter="stopAutoplay"
            @mouseleave="startAutoplay"
            @pointerdown="handlePointerDown"
            @pointerup="handlePointerUp"
            @pointercancel="handlePointerCancel"
          >
            <div class="phone-showcase__stage">
              <Transition name="showcase-image" mode="out-in">
                <img
                  v-if="!loading && !error"
                  :key="currentSlide.id"
                  :src="currentSlide.image"
                  :alt="currentSlide.alt"
                  class="phone-showcase__image"
                  draggable="false"
                >
              </Transition>
            </div>

            <div
              v-if="loading"
              class="phone-showcase__loading pointer-events-none absolute inset-0 z-20"
              role="status"
              aria-live="polite"
            >
              <div class="phone-showcase__loading-card">
                <div class="phone-showcase__spinner" />
                <p class="phone-showcase__loading-text">Loading ...</p>
              </div>
            </div>

            <div
              v-if="error"
              class="absolute inset-0 z-30 flex items-center justify-center px-6 text-center"
            >
              <div class="rounded-2xl border border-red-300/25 bg-red-500/15 px-5 py-4 text-sm text-white">
                {{ error }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="hasMultipleSlides && !loading && !error"
        class="phone-showcase__dots"
        aria-label="Phone showcase slides"
      >
        <button
          v-for="(slide, index) in slides"
          :key="`phone-showcase-dot-${slide.id}`"
          type="button"
          class="phone-showcase__dot"
          :class="{ 'phone-showcase__dot--active': index === currentIndex }"
          :aria-label="`Show ${slide.title}`"
          :aria-current="index === currentIndex ? 'true' : undefined"
          @click.stop="goToSlide(index)"
        />
      </div>
    </div>
  </section>
</template>

<style scoped>
.phone-showcase {
  min-height: 820px;
  background: #030303;
}

.phone-showcase__ambient {
  pointer-events: none;
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 18% 74%, rgba(255, 255, 255, 0.055), transparent 34%),
    radial-gradient(circle at 86% 78%, rgba(255, 255, 255, 0.045), transparent 32%),
    linear-gradient(
      180deg,
      #030303 0%,
      #030303 34%,
      rgba(255, 255, 255, 0.018) 72%,
      #030303 100%
    );
}

.phone-showcase__ambient::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 140px;
  background: #030303;
}

.phone-showcase__shell {
  min-height: 820px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding-top: 44px;
  padding-bottom: 44px;
}

.phone-showcase__grid {
  width: 100%;
}

.phone-showcase__copy-stage {
  position: relative;
  height: 460px;
  overflow: hidden;
}

.phone-showcase__copy-slide {
  position: absolute;
  inset: 0;
  width: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
  padding-top: 26px;
  padding-bottom: 26px;
}

.phone-showcase__stage-wrap {
  width: 100%;
  max-width: 100%;
  cursor: pointer;
  touch-action: pan-y;
}

.phone-showcase__stage {
  position: relative;
  width: 100%;
  height: clamp(420px, 96vw, 680px);
  min-height: clamp(420px, 96vw, 680px);
  overflow: hidden;
  border-radius: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.phone-showcase__image {
  display: block;
  width: 108%;
  height: 108%;
  max-width: none;
  max-height: none;
  object-fit: contain;
  object-position: center;
  user-select: none;
  -webkit-user-drag: none;
  will-change: transform, opacity;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
  transform: translateZ(0);
}

.phone-showcase__loading {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background: rgba(0, 0, 0, 0.18);
  border-radius: 28px;
}

.phone-showcase__loading-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.85rem;
  width: min(88%, 340px);
}

.phone-showcase__spinner {
  width: 56px;
  height: 56px;
  border-radius: 999px;
  border: 3px solid rgba(255, 255, 255, 0.16);
  border-top-color: rgba(255, 255, 255, 0.95);
  animation: showcase-spin 0.9s linear infinite;
}

.phone-showcase__loading-text {
  margin: 0;
  text-align: center;
  font-size: clamp(13px, 2.8vw, 16px);
  font-weight: 600;
  letter-spacing: 0.02em;
  color: rgba(255, 255, 255, 0.92);
}

.phone-showcase__dots {
  position: relative;
  z-index: 25;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  width: 100%;
  margin-top: 2px;
  padding: 0;
}

.phone-showcase__dot {
  width: 8px;
  height: 8px;
  border: 0;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.42);
  padding: 0;
  cursor: pointer;
  transition:
    width 240ms ease,
    background 240ms ease,
    transform 240ms ease;
}

.phone-showcase__dot--active {
  width: 25px;
  background: linear-gradient(90deg, #38bdf8, #ffffff);
  transform: translateY(-1px);
}

.showcase-copy-enter-active,
.showcase-copy-leave-active {
  transition:
    opacity 0.8s ease,
    transform 0.8s ease;
}

.showcase-copy-enter-from {
  opacity: 0;
  transform: translateY(26px);
}

.showcase-copy-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

.showcase-image-enter-active,
.showcase-image-leave-active {
  transition:
    opacity 0.95s ease,
    transform 0.95s cubic-bezier(0.22, 1, 0.36, 1);
}

.showcase-image-enter-from {
  opacity: 0;
  transform: scale(0.96) translateY(16px);
}

.showcase-image-leave-to {
  opacity: 0;
  transform: scale(1.03) translateY(-12px);
}

@keyframes showcase-spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 1023px) {
  .phone-showcase,
  .phone-showcase__shell {
    min-height: auto;
  }

  .phone-showcase__shell {
    display: block;
    padding-top: 28px;
    padding-bottom: 28px;
  }

  .phone-showcase__copy-stage {
    height: 300px;
    margin-top: 2px;
  }

  .phone-showcase__copy-slide {
    padding-top: 18px;
    padding-bottom: 18px;
  }
}

@media (max-width: 640px) {
  .phone-showcase__shell {
    padding-top: 22px;
    padding-bottom: 24px;
  }

  .phone-showcase__grid {
    gap: 14px;
  }

  .phone-showcase__stage {
    height: clamp(230px, 66vw, 310px);
    min-height: clamp(230px, 66vw, 310px);
    border-radius: 22px;
  }

  .phone-showcase__image {
    width: 112%;
    height: 112%;
  }

  .phone-showcase__copy-stage {
    height: 230px;
  }

  .phone-showcase__copy-slide {
    justify-content: flex-start;
    padding-top: 8px;
    padding-bottom: 8px;
  }

  .phone-showcase__copy-slide h2 {
    font-size: clamp(34px, 10vw, 42px);
  }

  .phone-showcase__copy-slide p {
    font-size: 14px;
  }

  .phone-showcase__copy-slide > div:first-child {
    font-size: 10px;
  }

  .phone-showcase__spinner {
    width: 46px;
    height: 46px;
  }

  .phone-showcase__dots {
    gap: 6px;
    margin-top: 2px;
  }

  .phone-showcase__dot {
    width: 6px;
    height: 6px;
  }

  .phone-showcase__dot--active {
    width: 20px;
  }
}

@media (min-width: 1024px) {
  .phone-showcase__stage-wrap {
    max-width: 840px;
  }

  .phone-showcase__stage {
    height: var(--phone-stage-height);
    min-height: var(--phone-stage-height);
  }
}
</style>