<script setup lang="ts">
import { Autoplay } from 'swiper/modules'
import { Swiper, SwiperSlide } from 'swiper/vue'

import 'swiper/css'

type IconType = 'delivery' | 'quality' | 'secure' | 'support' | 'price'

type CardItem = {
  id: number
  title: string
  description: string
  icon: IconType
}

const cards: CardItem[] = [
  {
    id: 1,
    title: 'Island-wide Delivery',
    description: 'Reliable delivery across the island.',
    icon: 'delivery',
  },
  {
    id: 2,
    title: 'Premium & Authentic',
    description: 'High-quality and genuine products only.',
    icon: 'quality',
  },
  {
    id: 3,
    title: 'Fast & Secure',
    description: 'Quick shipping with safe handling.',
    icon: 'secure',
  },
  {
    id: 4,
    title: '24/7 Customer Support',
    description: 'Always here for our valued customers.',
    icon: 'support',
  },
  {
    id: 5,
    title: 'Best Prices',
    description: 'Great value across the island.',
    icon: 'price',
  },
]

const loopCards = [...cards, ...cards, ...cards]
</script>

<template>
  <section class="card-swiper-section">
    <div class="card-swiper-container">
      <Swiper
        :modules="[Autoplay]"
        :slides-per-view="'auto'"
        :space-between="20"
        :loop="true"
        :allow-touch-move="true"
        :grab-cursor="true"
        :speed="5000"
        :autoplay="{
          delay: 0,
          disableOnInteraction: false,
          pauseOnMouseEnter: true,
        }"
        class="card-swiper"
      >
        <SwiperSlide
          v-for="(card, index) in loopCards"
          :key="`${card.id}-${index}`"
          class="card-slide"
        >
          <div class="info-card">
            <div class="icon-wrap">
              <div class="icon-shell">
                <svg
                  v-if="card.icon === 'delivery'"
                  viewBox="0 0 24 24"
                  fill="none"
                  class="card-icon"
                >
                  <path
                    d="M3 7.5C3 6.67157 3.67157 6 4.5 6H13.5C14.3284 6 15 6.67157 15 7.5V15H4.5C3.67157 15 3 14.3284 3 13.5V7.5Z"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M15 9H18.2C18.6761 9 19.1316 9.19048 19.4682 9.52918L20.7682 10.8292C21.1036 11.1647 21.292 11.6197 21.292 12.094V15H15V9Z"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linejoin="round"
                  />
                  <circle cx="7.5" cy="16.5" r="1.5" stroke="currentColor" stroke-width="1.8" />
                  <circle cx="17.5" cy="16.5" r="1.5" stroke="currentColor" stroke-width="1.8" />
                </svg>

                <svg
                  v-else-if="card.icon === 'quality'"
                  viewBox="0 0 24 24"
                  fill="none"
                  class="card-icon"
                >
                  <path
                    d="M12 3L14.7812 8.28125L20.5 9.09375L16.25 13.0625L17.2812 18.75L12 16.0312L6.71875 18.75L7.75 13.0625L3.5 9.09375L9.21875 8.28125L12 3Z"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linejoin="round"
                  />
                </svg>

                <svg
                  v-else-if="card.icon === 'secure'"
                  viewBox="0 0 24 24"
                  fill="none"
                  class="card-icon"
                >
                  <path
                    d="M12 3L19 6V11.5C19 16 15.9 19.9 12 21C8.1 19.9 5 16 5 11.5V6L12 3Z"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M9.5 11.8L11.2 13.5L14.8 9.9"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>

                <svg
                  v-else-if="card.icon === 'support'"
                  viewBox="0 0 24 24"
                  fill="none"
                  class="card-icon"
                >
                  <path
                    d="M6 12C6 8.68629 8.68629 6 12 6C15.3137 6 18 8.68629 18 12"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                  />
                  <path
                    d="M6.5 12H5.5C4.67157 12 4 12.6716 4 13.5V15.5C4 16.3284 4.67157 17 5.5 17H6.5V12Z"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M17.5 12H18.5C19.3284 12 20 12.6716 20 13.5V15.5C20 16.3284 19.3284 17 18.5 17H17.5V12Z"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M12 18H14"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                  />
                </svg>

                <svg
                  v-else
                  viewBox="0 0 24 24"
                  fill="none"
                  class="card-icon"
                >
                  <path
                    d="M12 4V20"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                  />
                  <path
                    d="M16 7.5C16 6.11929 14.2091 5 12 5C9.79086 5 8 6.11929 8 7.5C8 8.88071 9.79086 10 12 10C14.2091 10 16 11.1193 16 12.5C16 13.8807 14.2091 15 12 15C9.79086 15 8 13.8807 8 12.5"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </div>
            </div>

            <div class="card-content">
              <h3 class="card-title">{{ card.title }}</h3>
              <p class="card-text">{{ card.description }}</p>
            </div>
          </div>
        </SwiperSlide>
      </Swiper>
    </div>
  </section>
</template>

<style scoped>
.card-swiper-section {
  width: 100%;
  padding: 32px 0;
  background: transparent;
  overflow: hidden;
}

.card-swiper-container {
  width: 100%;
  overflow: hidden;
}

:deep(.card-swiper .swiper-wrapper) {
  transition-timing-function: linear !important;
  align-items: stretch;
}

.card-slide {
  width: 290px !important;
  height: auto;
}

.info-card {
  position: relative;
  min-height: 168px;
  height: 100%;
  padding: 22px 22px 20px;
  border-radius: 28px;
  display: flex;
  align-items: flex-start;
  gap: 16px;
  background:
    linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
  border: 1px solid rgba(15, 23, 42, 0.08);
  /* box-shadow:
    0 14px 40px rgba(15, 23, 42, 0.08),
    0 4px 14px rgba(15, 23, 42, 0.04); */
  overflow: hidden;
  transition:
    transform 0.3s ease,
    box-shadow 0.3s ease,
    border-color 0.3s ease;
}

.info-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), transparent 38%);
  pointer-events: none;
}

/* .info-card:hover {
  transform: translateY(-6px);
  border-color: rgba(59, 130, 246, 0.14);
 
} */

.icon-wrap {
  position: relative;
  z-index: 1;
  flex-shrink: 0;
}

.icon-shell {
  width: 64px;
  height: 64px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  background:
    linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  border: 1px solid rgba(59, 130, 246, 0.12);

}

.card-icon {
  width: 30px;
  height: 30px;
  color: #2563eb;
}

.card-content {
  position: relative;
  z-index: 1;
  min-width: 0;
  flex: 1;
}

.card-title {
  margin: 0 0 8px;
  font-size: 18px;
  font-weight: 700;
  line-height: 1.3;
  color: #0f172a;
  letter-spacing: -0.02em;
}

.card-text {
  margin: 0;
  font-size: 14px;
  font-weight: 500;
  line-height: 1.6;
  color: #475569;
}

@media (max-width: 1024px) {
  .card-slide {
    width: 260px !important;
  }

  .info-card {
    min-height: 156px;
    padding: 20px;
    border-radius: 24px;
    gap: 14px;
  }

  .icon-shell {
    width: 58px;
    height: 58px;
    border-radius: 18px;
  }

  .card-icon {
    width: 27px;
    height: 27px;
  }

  .card-title {
    font-size: 17px;
  }

  .card-text {
    font-size: 13.5px;
  }
}

@media (max-width: 640px) {
  .card-swiper-section {
    padding: 22px 0;
  }

  .card-slide {
    width: 220px !important;
  }

  .info-card {
    min-height: 144px;
    padding: 18px;
    border-radius: 22px;
    gap: 12px;
  }

  .icon-shell {
    width: 52px;
    height: 52px;
    border-radius: 16px;
  }

  .card-icon {
    width: 24px;
    height: 24px;
  }

  .card-title {
    font-size: 15px;
    margin-bottom: 6px;
  }

  .card-text {
    font-size: 12.5px;
    line-height: 1.5;
  }
}
</style>