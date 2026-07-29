<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

type CustomerGalleryImage = {
  id: number | string
  image_url: string | null
}

type CustomerGalleryPayload = {
  images?: CustomerGalleryImage[]
}

const props = withDefaults(defineProps<{
  endpoint?: string
}>(), {
  endpoint: '/home/customer-gallery',
})

const sectionRef = ref<HTMLElement | null>(null)
const images = ref<CustomerGalleryImage[]>([])
const loading = ref(false)
const loaded = ref(false)
const error = ref('')

let observer: IntersectionObserver | null = null

const sliderImages = computed(() => {
  const validImages = images.value.filter((image) => image.image_url)

  if (validImages.length <= 1) {
    return validImages
  }

  return [...validImages, ...validImages]
})

async function loadGallery() {
  if (loaded.value || loading.value) return

  loading.value = true
  error.value = ''

  try {
    const response = await fetch(props.endpoint, {
      cache: 'no-store',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })

    if (!response.ok) {
      throw new Error('Unable to load customer photos.')
    }

    const payload = await response.json() as CustomerGalleryPayload
    images.value = Array.isArray(payload.images) ? payload.images.slice(0, 6) : []
    loaded.value = true
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unable to load customer photos.'
    images.value = []
  } finally {
    loading.value = false
  }
}

function retry() {
  loaded.value = false
  loadGallery()
}

onMounted(() => {
  if ('IntersectionObserver' in window && sectionRef.value) {
    observer = new IntersectionObserver(
      (entries) => {
        if (entries[0]?.isIntersecting) {
          loadGallery()
          observer?.disconnect()
          observer = null
        }
      },
      {
        root: null,
        rootMargin: '220px 0px',
        threshold: 0.08,
      },
    )

    observer.observe(sectionRef.value)
  } else {
    loadGallery()
  }
})

onBeforeUnmount(() => {
  observer?.disconnect()
})
</script>

<template>
  <section ref="sectionRef" class="customer-gallery-section">
    <div class="customer-gallery-shell">
      <header class="customer-gallery-header">
        <span>Real Customers</span>
        <h2>Loved By Real Customers</h2>
        <p>Real customer moments with DezeStore picks.</p>
      </header>

      <div v-if="error" class="customer-gallery-state customer-gallery-state--error">
        <span>{{ error }}</span>
        <button type="button" @click="retry">Retry</button>
      </div>

      <div v-else-if="!loaded || loading" class="customer-gallery-placeholder">
        <div v-for="index in 4" :key="`customer-placeholder-${index}`" />
      </div>

      <div v-else-if="sliderImages.length" class="customer-gallery-window">
        <div
          class="customer-gallery-track"
          :class="{ 'customer-gallery-track--static': sliderImages.length <= 1 }"
        >
          <figure
            v-for="(image, index) in sliderImages"
            :key="`${image.id}-${index}`"
            class="customer-gallery-card"
          >
            <img
              :src="image.image_url || ''"
              :alt="`DezeStore customer photo ${index + 1}`"
              loading="lazy"
              decoding="async"
            />
          </figure>
        </div>
      </div>

      <div v-else class="customer-gallery-state">
        Customer photos will appear here soon.
      </div>
    </div>
  </section>
</template>

<style scoped>
.customer-gallery-section {
  width: 100%;
  padding: 46px 20px;
  font-family: 'DezeStoreCustomFont', var(--font-sans, ui-sans-serif, system-ui, sans-serif);
  color: #0f172a;
}

.customer-gallery-shell {
  max-width: 1280px;
  margin: 0 auto;
  overflow: hidden;
}

.customer-gallery-header {
  margin-bottom: 22px;
}

.customer-gallery-header span {
  display: inline-block;
  margin-bottom: 8px;
  padding: 5px 10px;
  border-radius: 999px;
  background: linear-gradient(135deg, rgba(255, 91, 58, 0.12), rgba(255, 91, 58, 0.04));
  color: #ff5b3a;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0;
  text-transform: uppercase;
}

.customer-gallery-header h2 {
  margin: 0;
  color: #0b1a44;
  font-size: 32px;
  font-weight: 800;
  line-height: 1.1;
  letter-spacing: 0;
}

.customer-gallery-header p {
  position: relative;
  max-width: 520px;
  margin: 10px 0 0;
  padding-left: 16px;
  color: #43506e;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.6;
}

.customer-gallery-header p::before {
  position: absolute;
  top: 0.38em;
  left: 0;
  width: 4px;
  min-height: 22px;
  height: calc(100% - 0.76em);
  border-radius: 999px;
  background: linear-gradient(180deg, #16876a, #ff980f);
  content: '';
}

.customer-gallery-window {
  overflow: hidden;
  border-radius: 8px;
}

.customer-gallery-track {
  display: flex;
  width: max-content;
  gap: 16px;
  animation: customerGallerySlide 34s linear infinite;
  will-change: transform;
}

.customer-gallery-window:hover .customer-gallery-track {
  animation-play-state: paused;
}

.customer-gallery-track--static {
  width: 100%;
  animation: none;
}

.customer-gallery-card {
  position: relative;
  width: clamp(180px, 24vw, 300px);
  aspect-ratio: 4 / 5;
  flex: 0 0 auto;
  margin: 0;
  overflow: hidden;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #f8fafc;
}

.customer-gallery-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transform: scale(1.001);
}

.customer-gallery-placeholder {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 16px;
}

.customer-gallery-placeholder div,
.customer-gallery-state {
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #f8fafc;
}

.customer-gallery-placeholder div {
  aspect-ratio: 4 / 5;
  animation: customerGalleryPulse 1.35s ease-in-out infinite;
}

.customer-gallery-state {
  display: flex;
  min-height: 180px;
  align-items: center;
  justify-content: center;
  color: #64748b;
  text-align: center;
}

.customer-gallery-state--error {
  flex-direction: column;
  gap: 12px;
  border-color: #fecaca;
  background: #fff7f7;
  color: #b91c1c;
}

.customer-gallery-state--error button {
  border-radius: 999px;
  background: #b91c1c;
  padding: 8px 18px;
  color: #ffffff;
  font-size: 13px;
  font-weight: 700;
}

@keyframes customerGallerySlide {
  from {
    transform: translateX(0);
  }

  to {
    transform: translateX(calc(-50% - 8px));
  }
}

@keyframes customerGalleryPulse {
  0%,
  100% {
    opacity: 0.55;
  }

  50% {
    opacity: 1;
  }
}

@media (max-width: 720px) {
  .customer-gallery-section {
    padding: 38px 16px;
  }

  .customer-gallery-header h2 {
    font-size: 22px;
  }

  .customer-gallery-header p {
    max-width: none;
    font-size: 13.5px;
    line-height: 1.55;
  }

  .customer-gallery-track {
    gap: 10px;
    animation-duration: 28s;
  }

  .customer-gallery-card {
    width: 168px;
  }

  .customer-gallery-placeholder {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .customer-gallery-track,
  .customer-gallery-placeholder div {
    animation: none !important;
  }
}
</style>
