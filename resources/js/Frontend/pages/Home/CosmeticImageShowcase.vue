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
    height: '700px',
    autoplayMs: 5000,
    slides: () => [
      {
        id: 1,
        badge: 'Skin Essential',
        title: 'The Ordinary Niacinamide',
        description:
          'A lightweight everyday serum designed to help visibly reduce excess oil, refine pores, and support a smoother-looking complexion.',
        image: '/assets/images/ip171.webp',
        alt: 'The Ordinary Niacinamide',
      },
      {
        id: 2,
        badge: 'Daily Hydration',
        title: 'CeraVe Moisturizer',
        description:
          'A simple daily moisturizer made to support long-lasting hydration and help keep the skin barrier feeling soft, balanced, and comfortable.',
        image: '/assets/images/s24.webp',
        alt: 'CeraVe Moisturizer',
      },
      {
        id: 3,
        badge: 'Daily Protection',
        title: 'CeraVe Suncream',
        description:
          'An everyday sun protection essential created to help shield skin while maintaining a lightweight, comfortable feel throughout the day.',
        image: '/assets/images/ip171.webp',
        alt: 'CeraVe Suncream',
      },
    ],
  },
)

const currentIndex = ref(0)
const loading = ref(true)
const error = ref('')

let sliderTimer: number | null = null

const currentSlide = computed(() => props.slides[currentIndex.value] ?? props.slides[0])

const preloadImages = async () => {
  loading.value = true
  error.value = ''

  try {
    await Promise.all(
      props.slides.map(
        (slide) =>
          new Promise<void>((resolve, reject) => {
            const img = new Image()
            img.onload = () => resolve()
            img.onerror = () => reject(new Error(`Failed to load image: ${slide.image}`))
            img.src = slide.image
          }),
      ),
    )

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

const goToSlide = (index: number) => {
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
    class="cosmetic-showcase relative w-full overflow-hidden text-slate-900"
    :style="{ '--cosmetic-stage-height': height }"
  >
    <div class="cosmetic-showcase__shell relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="cosmetic-showcase__grid grid items-center gap-6 sm:gap-8 lg:grid-cols-[minmax(0,0.88fr)_minmax(0,1.12fr)] lg:gap-12 xl:gap-16">
        <div class="order-2 lg:order-1">
          <div class="cosmetic-showcase__copy-stage">
            <Transition name="showcase-copy" mode="out-in">
              <div :key="currentSlide.id" class="cosmetic-showcase__copy-slide">
                <div
                  class="inline-flex w-fit items-center self-start rounded-full border border-slate-200 bg-white px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500 shadow-sm sm:text-xs"
                >
                  {{ currentSlide.badge }}
                </div>

                <h2
                  class="mt-5 max-w-[11ch] text-[42px] font-semibold leading-[0.9] tracking-[-0.05em] text-slate-900 sm:text-[58px] lg:text-[68px] xl:text-[84px]"
                >
                  {{ currentSlide.title }}
                </h2>

                <p class="mt-5 max-w-[60ch] text-[15px] leading-7 text-slate-600 sm:text-base sm:leading-8">
                  {{ currentSlide.description }}
                </p>
              </div>
            </Transition>
          </div>
        </div>

        <div class="order-1 lg:order-2">
          <div
            class="cosmetic-showcase__stage-wrap relative ml-auto w-full"
            @mouseenter="stopAutoplay"
            @mouseleave="startAutoplay"
          >
            <div class="cosmetic-showcase__stage">
              <Transition name="showcase-image" mode="out-in">
                <img
                  v-if="!loading && !error"
                  :key="currentSlide.id"
                  :src="currentSlide.image"
                  :alt="currentSlide.alt"
                  class="cosmetic-showcase__image"
                  draggable="false"
                >
              </Transition>
            </div>

            <div
              v-if="loading"
              class="cosmetic-showcase__loading pointer-events-none absolute inset-0 z-20"
              role="status"
              aria-live="polite"
            >
              <div class="cosmetic-showcase__loading-card">
                <div class="cosmetic-showcase__spinner" />
                <p class="cosmetic-showcase__loading-text">Loading ...</p>
              </div>
            </div>

            <div
              v-if="error"
              class="absolute inset-0 z-30 flex items-center justify-center px-6 text-center"
            >
              <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 shadow-sm">
                {{ error }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.cosmetic-showcase {
  min-height: 820px;
}

.cosmetic-showcase__shell {
  min-height: 820px;
  display: flex;
  align-items: center;
  padding-top: 44px;
  padding-bottom: 44px;
}

.cosmetic-showcase__grid {
  width: 100%;
}

.cosmetic-showcase__copy-stage {
  position: relative;
  height: 460px;
  overflow: hidden;
}

.cosmetic-showcase__copy-slide {
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

.cosmetic-showcase__stage-wrap {
  width: 100%;
  max-width: 100%;
}

.cosmetic-showcase__stage {
  position: relative;
  width: 100%;
  height: clamp(420px, 96vw, 680px);
  min-height: clamp(420px, 96vw, 680px);
  overflow: hidden;
  border-radius: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
}

.cosmetic-showcase__image {
  display: block;
  width: 100%;
  height: 100%;
  max-width: 92%;
  max-height: 92%;
  object-fit: contain;
  object-position: center;
  user-select: none;
  -webkit-user-drag: none;
  will-change: transform, opacity;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
  transform: translateZ(0);
}

.cosmetic-showcase__loading {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.cosmetic-showcase__loading-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.85rem;
  width: min(88%, 340px);
}

.cosmetic-showcase__spinner {
  width: 56px;
  height: 56px;
  border-radius: 999px;
  border: 3px solid rgba(148, 163, 184, 0.22);
  border-top-color: rgb(51 65 85);
  animation: showcase-spin 0.9s linear infinite;
}

.cosmetic-showcase__loading-text {
  margin: 0;
  text-align: center;
  font-size: clamp(13px, 2.8vw, 16px);
  font-weight: 600;
  letter-spacing: 0.02em;
  color: rgb(71 85 105);
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
  .cosmetic-showcase,
  .cosmetic-showcase__shell {
    min-height: auto;
  }

  .cosmetic-showcase__shell {
    display: block;
    padding-top: 28px;
    padding-bottom: 28px;
  }

  .cosmetic-showcase__copy-stage {
    height: 360px;
    margin-top: 6px;
  }

  .cosmetic-showcase__copy-slide {
    padding-top: 18px;
    padding-bottom: 18px;
  }
}

@media (max-width: 640px) {
  .cosmetic-showcase__stage {
    height: clamp(360px, 92vw, 500px);
    min-height: clamp(360px, 92vw, 500px);
    border-radius: 22px;
  }

  .cosmetic-showcase__image {
    max-width: 94%;
    max-height: 94%;
  }

  .cosmetic-showcase__copy-stage {
    height: 320px;
  }

  .cosmetic-showcase__copy-slide {
    padding-top: 20px;
    padding-bottom: 20px;
  }

  .cosmetic-showcase__spinner {
    width: 46px;
    height: 46px;
  }
}

@media (min-width: 1024px) {
  .cosmetic-showcase__stage-wrap {
    max-width: 760px;
  }

  .cosmetic-showcase__stage {
    height: var(--cosmetic-stage-height);
    min-height: var(--cosmetic-stage-height);
  }
}
</style>