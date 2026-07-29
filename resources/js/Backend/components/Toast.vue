<template>
  <transition name="slide-up">
    <div
      v-if="visible"
      class="fixed top-8 left-1/2 -translate-x-1/2 z-[9999]
             flex items-center gap-4 bg-neutral-900 text-white
             px-6 py-4 rounded-2xl shadow-2xl animate-pop
             min-w-[320px] max-w-[380px]"
    >
      <!-- Bigger Animated Bell -->
      <div class="text-[#FFB800] text-3xl animate-bell">🔔</div>

      <div class="flex flex-col">
        <span class="font-bold text-base">{{ title }}</span>
        <span class="text-sm opacity-80 leading-snug">{{ message }}</span>
      </div>
    </div>
  </transition>
</template>

<script setup lang="ts">
import { ref } from "vue";

const visible = ref(false);
const title = ref("");
const message = ref("");

let timeout: any = null;

// Notification sound
const sound = new Audio("/sounds/notify.wav");

function showToast(t: string, m: string) {
    title.value = t;
    message.value = m;

    // sound.currentTime = 0;
    // sound.play().catch(() => {});

    visible.value = true;
    clearTimeout(timeout);

    timeout = setTimeout(() => {
        visible.value = false;
    }, 8000);
}

// global access
// @ts-ignore
window.AppToast = { show: showToast };
</script>

<style scoped>
/* Slide-up animation */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.5s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

/* Pop animation */
@keyframes pop {
  0% { transform: scale(0.7); opacity: 0; }
  60% { transform: scale(1.1); opacity: 1; }
  100% { transform: scale(1); }
}
.animate-pop {
  animation: pop 0.35s ease-out;
}

/* Bell shake */
@keyframes bellShake {
  0% { transform: rotate(0deg); }
  20% { transform: rotate(-17deg); }
  40% { transform: rotate(12deg); }
  60% { transform: rotate(-10deg); }
  80% { transform: rotate(6deg); }
  100% { transform: rotate(0deg); }
}
.animate-bell {
  animation: bellShake 0.9s ease;
}
</style>
