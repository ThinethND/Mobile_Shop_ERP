<script setup lang="ts">
import {
  ExternalLink,
  RefreshCw,
  Send,
  ShoppingBag,
  Sparkles,
  X,
} from 'lucide-vue-next'
import { nextTick, ref } from 'vue'
import { route } from 'ziggy-js'

type ProductCard = {
  id: string
  type: string
  name: string
  subtitle?: string | null
  price?: string | null
  stock?: string | null
  image_url?: string | null
  url?: string | null
}

type ChatMessage = {
  id: string
  role: 'assistant' | 'user'
  content: string
  products?: ProductCard[]
  source?: string
}

const isOpen = ref(false)
const input = ref('')
const loading = ref(false)
const messagesRef = ref<HTMLElement | null>(null)

const welcomeMessage =
  'Hello! I am DezeStore AI. Ask me about products, prices, stock, delivery, warranty, or product links.'

const messages = ref<ChatMessage[]>([
  {
    id: cryptoId(),
    role: 'assistant',
    content: welcomeMessage,
    products: [],
    source: 'local',
  },
])

function cryptoId() {
  if ('crypto' in window && typeof window.crypto?.randomUUID === 'function') {
    return window.crypto.randomUUID()
  }

  return `${Date.now()}-${Math.random().toString(16).slice(2)}`
}

function toggleChat() {
  isOpen.value = !isOpen.value

  if (isOpen.value) {
    nextTick(scrollToBottom)
  }
}

function resetChat() {
  messages.value = [
    {
      id: cryptoId(),
      role: 'assistant',
      content: welcomeMessage,
      products: [],
      source: 'local',
    },
  ]

  nextTick(scrollToBottom)
}

async function submitMessage() {
  const message = input.value.trim()

  if (!message || loading.value) {
    return
  }

  const userMessage: ChatMessage = {
    id: cryptoId(),
    role: 'user',
    content: message,
  }

  messages.value.push(userMessage)
  input.value = ''
  loading.value = true
  await nextTick(scrollToBottom)

  try {
    const response = await fetch(route('frontend.ai.chat'), {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken(),
      },
      body: JSON.stringify({
        message,
        history: messages.value
          .slice(0, -1)
          .slice(-8)
          .map(item => ({
            role: item.role,
            content: item.content,
          })),
      }),
    })

    if (!response.ok) {
      throw new Error('Chat request failed')
    }

    const payload = await response.json()

    messages.value.push({
      id: cryptoId(),
      role: 'assistant',
      content: String(payload?.message || 'I could not prepare an answer right now.'),
      products: Array.isArray(payload?.products) ? payload.products : [],
      source: payload?.source || 'openai',
    })
  } catch {
    messages.value.push({
      id: cryptoId(),
      role: 'assistant',
      content: 'I could not reach the assistant right now. Please try again in a moment or contact DezeStore.',
      products: [],
      source: 'local',
    })
  } finally {
    loading.value = false
    await nextTick(scrollToBottom)
  }
}

function handleInputKeydown(event: KeyboardEvent) {
  if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault()
    submitMessage()
  }
}

function scrollToBottom() {
  if (!messagesRef.value) {
    return
  }

  messagesRef.value.scrollTop = messagesRef.value.scrollHeight
}

function csrfToken() {
  return document
    .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
    ?.content || ''
}
</script>

<template>
  <Teleport to="body">
    <div class="fh-ai-chat">
      <Transition name="fh-chat-scrim">
        <button
          v-if="isOpen"
          type="button"
          class="fh-ai-scrim"
          aria-label="Close DezeStore AI"
          @click="isOpen = false"
        ></button>
      </Transition>

      <Transition name="fh-chat-panel">
        <section
          v-if="isOpen"
          class="fh-ai-panel"
          aria-label="DezeStore AI chat"
        >
          <header class="fh-ai-header">
            <span class="fh-ai-header-glow fh-ai-header-glow-one" aria-hidden="true"></span>
            <span class="fh-ai-header-glow fh-ai-header-glow-two" aria-hidden="true"></span>

            <div class="fh-ai-avatar" aria-hidden="true">
              <span class="fh-ai-avatar-halo"></span>
              <img src="/images/robot.png" alt="" />
            </div>

            <div class="fh-ai-title-block">
              <div class="fh-ai-title-line">
                <p class="fh-ai-title">DezeStore AI</p>
                <span class="fh-ai-ai-chip">
                  <Sparkles :size="12" />
                  AI
                </span>
              </div>

              <div class="fh-ai-status">
                <span class="fh-ai-status-dot"></span>
                Live store assistant
              </div>
            </div>

            <button
              type="button"
              class="fh-ai-icon-btn"
              aria-label="Reset chat"
              title="Reset chat"
              @click="resetChat"
            >
              <RefreshCw :size="17" />
            </button>

            <button
              type="button"
              class="fh-ai-icon-btn"
              aria-label="Close chat"
              title="Close chat"
              @click="isOpen = false"
            >
              <X :size="18" />
            </button>
          </header>

          <div ref="messagesRef" class="fh-ai-messages">
            <div class="fh-ai-conversation-watermark" aria-hidden="true">
              <span></span>
              <span></span>
              <span></span>
            </div>

            <div
              v-for="message in messages"
              :key="message.id"
              class="fh-ai-message-row"
              :class="message.role === 'user' ? 'fh-ai-message-row-user' : 'fh-ai-message-row-assistant'"
            >
              <span
                v-if="message.role === 'assistant'"
                class="fh-ai-mini-avatar"
                aria-hidden="true"
              >
                <img src="/images/robot.png" alt="" />
              </span>

              <div
                class="fh-ai-message"
                :class="message.role === 'user' ? 'fh-ai-message-user' : 'fh-ai-message-assistant'"
              >
                <p>{{ message.content }}</p>

                <div
                  v-if="message.products?.length"
                  class="fh-ai-products"
                >
                  <a
                    v-for="product in message.products"
                    :key="product.id"
                    :href="product.url || '#'"
                    class="fh-ai-product"
                  >
                    <span class="fh-ai-product-image">
                      <img
                        v-if="product.image_url"
                        :src="product.image_url"
                        :alt="product.name"
                        loading="lazy"
                        decoding="async"
                      />
                      <ShoppingBag v-else :size="18" />
                    </span>

                    <span class="fh-ai-product-copy">
                      <span class="fh-ai-product-name">
                        {{ product.name }}
                      </span>
                      <span
                        v-if="product.subtitle"
                        class="fh-ai-product-subtitle"
                      >
                        {{ product.subtitle }}
                      </span>
                      <span class="fh-ai-product-meta">
                        <span>{{ product.price }}</span>
                        <span>{{ product.stock }}</span>
                      </span>
                    </span>

                    <ExternalLink :size="15" class="fh-ai-product-link-icon" />
                  </a>
                </div>
              </div>
            </div>

            <div v-if="loading" class="fh-ai-message-row fh-ai-message-row-assistant">
              <span class="fh-ai-mini-avatar fh-ai-mini-avatar-thinking" aria-hidden="true">
                <img src="/images/robot.png" alt="" />
              </span>

              <div class="fh-ai-message fh-ai-message-assistant fh-ai-thinking">
                <div class="fh-ai-orb-loader" aria-hidden="true">
                  <span class="fh-ai-orb-core"></span>
                  <span class="fh-ai-orb-ring fh-ai-orb-ring-one"></span>
                  <span class="fh-ai-orb-ring fh-ai-orb-ring-two"></span>
                </div>

                <div class="fh-ai-thinking-copy">
                  <span>Generating answer</span>
                  <span class="fh-ai-thinking-line">
                    <i></i>
                    <i></i>
                    <i></i>
                    <i></i>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <form class="fh-ai-form" @submit.prevent="submitMessage">
            <div class="fh-ai-input-wrap">
              <textarea
                v-model="input"
                rows="1"
                class="fh-ai-input"
                placeholder="Ask DezeStore AI..."
                :disabled="loading"
                @keydown="handleInputKeydown"
              ></textarea>
            </div>

            <button
              type="submit"
              class="fh-ai-send"
              :disabled="loading || !input.trim()"
              aria-label="Send message"
              title="Send message"
            >
              <span v-if="loading" class="fh-ai-send-loader" aria-hidden="true"></span>
              <Send v-else :size="18" />
            </button>
          </form>

          <p class="fh-ai-footer-note">
            Powered by <span>FrozioLabs</span>
          </p>
        </section>
      </Transition>

      <button
        type="button"
        class="fh-ai-launcher"
        :aria-expanded="isOpen"
        aria-label="Open DezeStore AI chat"
        title="Open DezeStore AI chat"
        @click="toggleChat"
      >
        <span class="fh-ai-launcher-aura" aria-hidden="true"></span>
        <span class="fh-ai-launcher-outer" aria-hidden="true"></span>
        <span class="fh-ai-launcher-siri" aria-hidden="true">
          <span class="fh-ai-siri-blob fh-ai-siri-blob-one"></span>
          <span class="fh-ai-siri-blob fh-ai-siri-blob-two"></span>
          <span class="fh-ai-siri-blob fh-ai-siri-blob-three"></span>
          <span class="fh-ai-siri-blob fh-ai-siri-blob-four"></span>
        </span>
        <span class="fh-ai-launcher-ring" aria-hidden="true"></span>
        <span class="fh-ai-launcher-glass" aria-hidden="true"></span>
        <img v-if="!isOpen" src="/images/robot.png" alt="" class="fh-ai-launcher-img" />
        <X v-else :size="27" stroke-width="2.2" />
        <span v-if="!isOpen" class="fh-ai-launcher-badge" aria-hidden="true"></span>
      </button>
    </div>
  </Teleport>
</template>

<style scoped>
.fh-ai-chat {
  position: fixed;
  right: 18px;
  bottom: 18px;
  width: 72px;
  height: 72px;
  z-index: 2147483000;
  font-family: var(--font-sans, Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif);
  pointer-events: none;
}

@supports (right: env(safe-area-inset-right)) {
  .fh-ai-chat {
    right: calc(18px + env(safe-area-inset-right));
    bottom: calc(18px + env(safe-area-inset-bottom));
  }
}

.fh-ai-scrim {
  position: fixed;
  inset: 0;
  z-index: 0;
  cursor: default;
  background:
    radial-gradient(circle at 82% 88%, rgba(125, 211, 252, 0.18), transparent 34%),
    rgba(15, 23, 42, 0.18);
  backdrop-filter: blur(4px);
  pointer-events: auto;
}

.fh-ai-panel {
  position: absolute;
  right: 0;
  bottom: 92px;
  z-index: 2;
  display: flex;
  width: min(418px, calc(100vw - 28px));
  height: min(642px, calc(100vh - 124px));
  max-height: 642px;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid rgba(14, 165, 233, 0.22);
  border-radius: 30px;
  background:
    linear-gradient(180deg, #ffffff 0%, #f7fbff 48%, #eef8ff 100%);
  box-shadow:
    0 30px 96px rgba(15, 23, 42, 0.18),
    0 0 38px rgba(125, 211, 252, 0.28);
  pointer-events: auto;
  isolation: isolate;
}

.fh-ai-panel::before {
  position: absolute;
  inset: 0;
  z-index: -1;
  content: "";
  background:
    radial-gradient(circle at 13% 7%, rgba(186, 230, 253, 0.62), transparent 34%),
    radial-gradient(circle at 100% 18%, rgba(147, 197, 253, 0.3), transparent 32%),
    radial-gradient(circle at 50% 100%, rgba(224, 242, 254, 0.72), transparent 45%);
  opacity: 0.96;
}

.fh-ai-header {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.78rem;
  overflow: hidden;
  padding: 1rem;
  border-bottom: 1px solid rgba(14, 165, 233, 0.13);
  background:
    radial-gradient(circle at 12% 10%, rgba(255, 255, 255, 0.92), transparent 30%),
    linear-gradient(135deg, #effbff 0%, #dff5ff 46%, #cfeaff 100%);
}

.fh-ai-header::after {
  position: absolute;
  right: -82px;
  bottom: -132px;
  width: 214px;
  height: 214px;
  border-radius: 999px;
  background: rgba(14, 165, 233, 0.13);
  filter: blur(18px);
  content: "";
}

.fh-ai-header-glow {
  position: absolute;
  border-radius: 999px;
  filter: blur(18px);
  opacity: 0.9;
  pointer-events: none;
  animation: fh-ai-header-glow 5.6s ease-in-out infinite;
}

.fh-ai-header-glow-one {
  top: -42px;
  left: 30px;
  width: 130px;
  height: 130px;
  background: rgba(186, 230, 253, 0.76);
}

.fh-ai-header-glow-two {
  right: 44px;
  bottom: -54px;
  width: 110px;
  height: 110px;
  background: rgba(125, 211, 252, 0.38);
  animation-delay: -2.4s;
}

.fh-ai-avatar {
  position: relative;
  display: inline-flex;
  width: 50px;
  height: 50px;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border: 1px solid rgba(14, 165, 233, 0.18);
  border-radius: 18px;
  background:
    radial-gradient(circle at 35% 20%, #ffffff 0%, #e0f7ff 46%, #bfe9ff 100%);
  box-shadow:
    inset 0 1px 14px rgba(255, 255, 255, 0.78),
    0 14px 28px rgba(14, 165, 233, 0.17);
}

.fh-ai-avatar-halo {
  position: absolute;
  inset: -10px;
  background: conic-gradient(from 90deg, transparent, rgba(186, 230, 253, 0.95), transparent, rgba(14, 165, 233, 0.42), transparent);
  animation: fh-ai-rotate 4.4s linear infinite;
}

.fh-ai-avatar img {
  position: relative;
  z-index: 1;
  width: 42px;
  height: 42px;
  object-fit: contain;
  filter: drop-shadow(0 7px 10px rgba(14, 165, 233, 0.18));
}

.fh-ai-title-block {
  position: relative;
  z-index: 1;
  min-width: 0;
  flex: 1;
}

.fh-ai-title-line {
  display: flex;
  min-width: 0;
  align-items: center;
  gap: 0.5rem;
}

.fh-ai-title {
  overflow: hidden;
  margin: 0;
  color: #082f49;
  font-size: 1.02rem;
  font-weight: 850;
  letter-spacing: -0.025em;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.fh-ai-ai-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.22rem;
  border: 1px solid rgba(14, 165, 233, 0.18);
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.74);
  padding: 0.18rem 0.42rem;
  color: #0284c7;
  font-size: 0.62rem;
  font-weight: 850;
  letter-spacing: 0.06em;
  box-shadow: 0 6px 14px rgba(14, 165, 233, 0.1);
}

.fh-ai-status {
  display: inline-flex;
  align-items: center;
  gap: 0.44rem;
  margin-top: 0.38rem;
  color: #0369a1;
  font-size: 0.72rem;
  font-weight: 750;
}

.fh-ai-status-dot {
  width: 7px;
  height: 7px;
  border-radius: 999px;
  background: #22c55e;
  box-shadow:
    0 0 0 4px rgba(34, 197, 94, 0.13),
    0 0 16px rgba(34, 197, 94, 0.36);
  animation: fh-ai-status-pulse 1.8s ease-in-out infinite;
}

.fh-ai-icon-btn {
  position: relative;
  z-index: 1;
  display: inline-flex;
  width: 36px;
  height: 36px;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(14, 165, 233, 0.15);
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.72);
  color: #0369a1;
  box-shadow: 0 8px 18px rgba(14, 165, 233, 0.1);
  transition: border-color 0.2s ease, background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
}

.fh-ai-icon-btn:hover {
  border-color: rgba(14, 165, 233, 0.34);
  background: #ffffff;
  color: #082f49;
  transform: translateY(-1px);
}

.fh-ai-messages {
  position: relative;
  flex: 1;
  overflow-y: auto;
  padding: 1.05rem;
  background:
    radial-gradient(circle at 12% 12%, rgba(224, 242, 254, 0.82), transparent 32%),
    radial-gradient(circle at 94% 40%, rgba(186, 230, 253, 0.42), transparent 28%),
    linear-gradient(180deg, #f7fcff 0%, #ffffff 45%, #f0f9ff 100%);
  scrollbar-width: thin;
  scrollbar-color: rgba(14, 165, 233, 0.28) transparent;
}

.fh-ai-messages::-webkit-scrollbar {
  width: 8px;
}

.fh-ai-messages::-webkit-scrollbar-thumb {
  border: 2px solid #f7fcff;
  border-radius: 999px;
  background: rgba(14, 165, 233, 0.28);
}

.fh-ai-conversation-watermark {
  position: absolute;
  inset: 0;
  overflow: hidden;
  pointer-events: none;
}

.fh-ai-conversation-watermark span {
  position: absolute;
  width: 150px;
  height: 150px;
  border-radius: 999px;
  background: rgba(186, 230, 253, 0.38);
  filter: blur(18px);
  animation: fh-ai-floating-light 8s ease-in-out infinite;
}

.fh-ai-conversation-watermark span:nth-child(1) {
  top: 8%;
  left: -72px;
}

.fh-ai-conversation-watermark span:nth-child(2) {
  right: -78px;
  bottom: 22%;
  background: rgba(147, 197, 253, 0.2);
  animation-delay: -2.8s;
}

.fh-ai-conversation-watermark span:nth-child(3) {
  left: 32%;
  bottom: -88px;
  background: rgba(224, 242, 254, 0.52);
  animation-delay: -5.2s;
}

.fh-ai-message-row {
  position: relative;
  z-index: 1;
  display: flex;
  gap: 0.58rem;
  margin-bottom: 0.88rem;
}

.fh-ai-message-row-user {
  justify-content: flex-end;
}

.fh-ai-message-row-assistant {
  justify-content: flex-start;
}

.fh-ai-mini-avatar {
  display: inline-flex;
  width: 31px;
  height: 31px;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border: 1px solid rgba(14, 165, 233, 0.18);
  border-radius: 12px;
  background:
    radial-gradient(circle at 32% 20%, #ffffff 0%, #e0f7ff 48%, #bdeaff 100%);
  box-shadow: 0 8px 20px rgba(14, 165, 233, 0.12);
}

.fh-ai-mini-avatar img {
  width: 25px;
  height: 25px;
  object-fit: contain;
}

.fh-ai-mini-avatar-thinking {
  animation: fh-ai-mini-thinking 1.4s ease-in-out infinite;
}

.fh-ai-message {
  max-width: 83%;
  border-radius: 20px;
  padding: 0.82rem 0.92rem;
  font-size: 0.875rem;
  line-height: 1.55;
}

.fh-ai-message p {
  margin: 0;
}

.fh-ai-message-user {
  border: 1px solid rgba(14, 165, 233, 0.12);
  border-bottom-right-radius: 8px;
  background:
    linear-gradient(135deg, #38bdf8 0%, #0ea5e9 55%, #2563eb 100%);
  color: #ffffff;
  box-shadow:
    0 14px 30px rgba(14, 165, 233, 0.22),
    inset 0 1px 0 rgba(255, 255, 255, 0.22);
}

.fh-ai-message-assistant {
  border: 1px solid rgba(14, 165, 233, 0.13);
  border-bottom-left-radius: 8px;
  background:
    linear-gradient(180deg, #ffffff 0%, #f6fbff 100%);
  color: #0f172a;
  box-shadow:
    0 14px 34px rgba(15, 23, 42, 0.08),
    inset 0 1px 0 rgba(255, 255, 255, 0.72);
}

.fh-ai-products {
  display: grid;
  gap: 0.55rem;
  margin-top: 0.78rem;
}

.fh-ai-product {
  display: flex;
  align-items: center;
  gap: 0.72rem;
  border: 1px solid rgba(14, 165, 233, 0.14);
  border-radius: 16px;
  background: #f8fcff;
  padding: 0.55rem;
  text-decoration: none;
  transition: border-color 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
}

.fh-ai-product:hover {
  border-color: rgba(14, 165, 233, 0.36);
  background: #ffffff;
  transform: translateY(-1px);
}

.fh-ai-product-image {
  display: inline-flex;
  width: 46px;
  height: 46px;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border-radius: 14px;
  background: #e0f7ff;
  color: #0284c7;
}

.fh-ai-product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.fh-ai-product-copy {
  min-width: 0;
  flex: 1;
}

.fh-ai-product-name {
  display: block;
  overflow: hidden;
  color: #0f172a;
  font-size: 0.82rem;
  font-weight: 800;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.fh-ai-product-subtitle {
  display: block;
  overflow: hidden;
  margin-top: 0.18rem;
  color: #64748b;
  font-size: 0.7rem;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.fh-ai-product-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.46rem;
  margin-top: 0.36rem;
  color: #0284c7;
  font-size: 0.72rem;
  font-weight: 800;
}

.fh-ai-product-meta span:last-child {
  color: #64748b;
  font-weight: 700;
}

.fh-ai-product-link-icon {
  flex-shrink: 0;
  color: #38bdf8;
}

.fh-ai-thinking {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 208px;
  padding: 0.7rem 0.82rem;
  background:
    radial-gradient(circle at 16% 20%, rgba(255, 255, 255, 0.95), transparent 34%),
    linear-gradient(180deg, #ffffff 0%, #eff9ff 100%);
  color: #0f172a;
}

.fh-ai-orb-loader {
  position: relative;
  width: 38px;
  height: 38px;
  flex-shrink: 0;
}

.fh-ai-orb-core {
  position: absolute;
  inset: 10px;
  border-radius: 999px;
  background: #7dd3fc;
  box-shadow:
    0 0 14px rgba(125, 211, 252, 0.82),
    0 0 22px rgba(14, 165, 233, 0.36);
  animation: fh-ai-orb-core 1.35s ease-in-out infinite;
}

.fh-ai-orb-ring {
  position: absolute;
  inset: 2px;
  border: 1px solid rgba(14, 165, 233, 0.32);
  border-top-color: rgba(37, 99, 235, 0.58);
  border-radius: 999px;
  animation: fh-ai-orb-spin 0.95s linear infinite;
}

.fh-ai-orb-ring-two {
  inset: 7px;
  border-color: rgba(186, 230, 253, 0.72);
  border-right-color: rgba(14, 165, 233, 0.82);
  animation-duration: 1.28s;
  animation-direction: reverse;
}

.fh-ai-thinking-copy {
  display: inline-flex;
  flex-direction: column;
  gap: 0.38rem;
  color: #0369a1;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.01em;
}

.fh-ai-thinking-line {
  display: inline-flex;
  gap: 4px;
  align-items: center;
}

.fh-ai-thinking-line i {
  display: inline-block;
  width: 18px;
  height: 4px;
  border-radius: 999px;
  background: linear-gradient(90deg, rgba(186, 230, 253, 0.72), rgba(14, 165, 233, 0.7));
  animation: fh-ai-thinking-line 1s ease-in-out infinite;
}

.fh-ai-thinking-line i:nth-child(2) {
  animation-delay: 0.12s;
}

.fh-ai-thinking-line i:nth-child(3) {
  animation-delay: 0.24s;
}

.fh-ai-thinking-line i:nth-child(4) {
  animation-delay: 0.36s;
}

.fh-ai-form {
  display: flex;
  align-items: flex-end;
  gap: 0.62rem;
  border-top: 1px solid rgba(14, 165, 233, 0.12);
  padding: 0.82rem;
  background:
    linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%);
}

.fh-ai-input-wrap {
  position: relative;
  flex: 1;
}

.fh-ai-input-wrap::before {
  position: absolute;
  inset: -1px;
  border-radius: 18px;
  background:
    linear-gradient(135deg, rgba(14, 165, 233, 0.28), rgba(186, 230, 253, 0.78), rgba(14, 165, 233, 0.22));
  opacity: 0;
  content: "";
  transition: opacity 0.2s ease;
}

.fh-ai-input-wrap:focus-within::before {
  opacity: 1;
}

.fh-ai-input {
  position: relative;
  z-index: 1;
  min-height: 46px;
  max-height: 116px;
  width: 100%;
  resize: none;
  border: 1px solid rgba(14, 165, 233, 0.16);
  border-radius: 17px;
  background: #ffffff;
  padding: 0.78rem 0.88rem;
  color: #0f172a;
  font-size: 0.89rem;
  line-height: 1.35;
  outline: none;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.88), 0 8px 18px rgba(14, 165, 233, 0.08);
  transition: border-color 0.2s ease, background-color 0.2s ease;
}

.fh-ai-input::placeholder {
  color: rgba(3, 105, 161, 0.52);
}

.fh-ai-input:focus {
  border-color: rgba(14, 165, 233, 0.42);
  background: #faffff;
}

.fh-ai-input:disabled {
  cursor: not-allowed;
  opacity: 0.62;
}

.fh-ai-send {
  position: relative;
  display: inline-flex;
  width: 46px;
  height: 46px;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border: 1px solid rgba(14, 165, 233, 0.24);
  border-radius: 17px;
  background:
    radial-gradient(circle at 30% 20%, rgba(255, 255, 255, 0.7), transparent 28%),
    linear-gradient(135deg, #7dd3fc 0%, #38bdf8 46%, #2563eb 100%);
  color: #ffffff;
  box-shadow:
    0 14px 28px rgba(14, 165, 233, 0.24),
    inset 0 1px 0 rgba(255, 255, 255, 0.28);
  transition: opacity 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}

.fh-ai-send::before {
  position: absolute;
  inset: -30%;
  background: conic-gradient(from 90deg, transparent, rgba(255, 255, 255, 0.28), transparent);
  content: "";
  animation: fh-ai-rotate 3.2s linear infinite;
}

.fh-ai-send > svg,
.fh-ai-send-loader {
  position: relative;
  z-index: 1;
}

.fh-ai-send:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow:
    0 18px 34px rgba(14, 165, 233, 0.32),
    0 0 22px rgba(125, 211, 252, 0.28);
}

.fh-ai-send:disabled {
  cursor: not-allowed;
  opacity: 0.48;
}

.fh-ai-send-loader {
  width: 17px;
  height: 17px;
  border: 2px solid rgba(255, 255, 255, 0.42);
  border-top-color: #ffffff;
  border-radius: 999px;
  animation: fh-ai-orb-spin 0.8s linear infinite;
}

.fh-ai-footer-note {
  border-top: 1px solid rgba(14, 165, 233, 0.1);
  background: #f8fcff;
  padding: 0.55rem 0.85rem 0.68rem;
  text-align: center;
  color: #64748b;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.01em;
}

.fh-ai-footer-note span {
  color: #0284c7;
  font-weight: 900;
}

.fh-ai-launcher {
  position: fixed;
  right: 18px;
  bottom: 18px;
  z-index: 3;
  display: inline-flex;
  width: 74px;
  height: 74px;
  align-items: center;
  justify-content: center;
  overflow: visible;
  border: 1px solid rgba(255, 255, 255, 0.92);
  border-radius: 27px;
  background:
    radial-gradient(circle at 28% 20%, rgba(255, 255, 255, 0.98), transparent 27%),
    linear-gradient(145deg, #f0fbff 0%, #d8f4ff 43%, #a7ddff 100%);
  color: #0369a1;
  box-shadow:
    0 22px 58px rgba(14, 165, 233, 0.22),
    0 0 42px rgba(186, 230, 253, 0.7),
    inset 0 1px 0 rgba(255, 255, 255, 0.72);
  pointer-events: auto;
  visibility: visible;
  opacity: 1;
  isolation: isolate;
  transition: transform 0.22s ease, box-shadow 0.22s ease;
  animation: fh-ai-launcher-breathe 3.8s ease-in-out infinite;
}

@supports (right: env(safe-area-inset-right)) {
  .fh-ai-launcher {
    right: calc(18px + env(safe-area-inset-right));
    bottom: calc(18px + env(safe-area-inset-bottom));
  }
}

.fh-ai-launcher:hover {
  transform: translateY(-4px) scale(1.025);
  box-shadow:
    0 28px 70px rgba(14, 165, 233, 0.28),
    0 0 58px rgba(186, 230, 253, 0.92),
    0 0 84px rgba(37, 99, 235, 0.12);
}

.fh-ai-launcher-aura {
  position: absolute;
  inset: -16px;
  z-index: 0;
  border-radius: 38px;
  background:
    radial-gradient(circle at 50% 50%, rgba(186, 230, 253, 0.68), transparent 58%),
    radial-gradient(circle at 34% 26%, rgba(224, 242, 254, 0.82), transparent 46%),
    radial-gradient(circle at 72% 76%, rgba(37, 99, 235, 0.12), transparent 48%);
  filter: blur(10px);
  opacity: 0.96;
  pointer-events: none;
  animation: fh-ai-aura-pulse 2.9s ease-in-out infinite;
}

.fh-ai-launcher-outer {
  position: absolute;
  inset: -4px;
  z-index: 1;
  border-radius: 31px;
  background:
    linear-gradient(145deg, #e0f7ff 0%, #bfeaff 58%, #73b8f4 100%);
  box-shadow:
    inset 0 0 18px rgba(255, 255, 255, 0.62),
    0 0 24px rgba(14, 165, 233, 0.18);
}

.fh-ai-launcher-siri {
  position: absolute;
  inset: 5px;
  z-index: 2;
  overflow: hidden;
  border-radius: 24px;
  background:
    radial-gradient(circle at 24% 18%, rgba(255, 255, 255, 0.94), transparent 28%),
    linear-gradient(135deg, rgba(240, 249, 255, 0.98), rgba(186, 230, 253, 0.82), rgba(147, 197, 253, 0.48));
}

.fh-ai-siri-blob {
  position: absolute;
  width: 70px;
  height: 70px;
  border-radius: 999px;
  filter: blur(13px);
  opacity: 0.86;
  mix-blend-mode: screen;
  animation: fh-ai-siri-orbit 5.2s ease-in-out infinite;
}

.fh-ai-siri-blob-one {
  top: -24px;
  left: -14px;
  background: rgba(224, 242, 254, 0.98);
}

.fh-ai-siri-blob-two {
  right: -26px;
  bottom: -16px;
  background: rgba(125, 211, 252, 0.72);
  animation-delay: -1.6s;
}

.fh-ai-siri-blob-three {
  top: 20px;
  left: 20px;
  background: rgba(186, 230, 253, 0.86);
  animation-delay: -3.1s;
}

.fh-ai-siri-blob-four {
  top: -18px;
  right: 6px;
  background: rgba(59, 130, 246, 0.18);
  animation-delay: -4.2s;
}

.fh-ai-launcher-ring {
  position: absolute;
  inset: 7px;
  z-index: 3;
  border: 1px solid rgba(255, 255, 255, 0.68);
  border-radius: 24px;
  box-shadow:
    inset 0 0 24px rgba(255, 255, 255, 0.42),
    inset 0 0 32px rgba(14, 165, 233, 0.1),
    0 0 20px rgba(125, 211, 252, 0.36);
  animation: fh-ai-ring-shimmer 3.4s linear infinite;
}

.fh-ai-launcher-glass {
  position: absolute;
  inset: 0;
  z-index: 4;
  border-radius: inherit;
  background:
    linear-gradient(145deg, rgba(255, 255, 255, 0.48), transparent 42%),
    radial-gradient(circle at 72% 82%, rgba(255, 255, 255, 0.18), transparent 38%);
  pointer-events: none;
}

.fh-ai-launcher-img {
  position: relative;
  z-index: 5;
  width: 55px;
  height: 55px;
  border-radius: 18px;
  object-fit: contain;
  filter:
    drop-shadow(0 10px 15px rgba(14, 165, 233, 0.18))
    drop-shadow(0 0 12px rgba(186, 230, 253, 0.58));
  transform-origin: 50% 58%;
  animation:
    fh-ai-robot-float 3.25s ease-in-out infinite,
    fh-ai-robot-vibrate 1.48s ease-in-out infinite;
}

.fh-ai-launcher:hover .fh-ai-launcher-img {
  animation:
    fh-ai-robot-float 2.35s ease-in-out infinite,
    fh-ai-robot-vibrate-fast 0.58s ease-in-out infinite;
}

.fh-ai-launcher > svg {
  position: relative;
  z-index: 5;
  filter: drop-shadow(0 8px 16px rgba(14, 165, 233, 0.22));
}

.fh-ai-launcher-badge {
  position: absolute;
  top: 8px;
  right: 8px;
  z-index: 6;
  width: 13px;
  height: 13px;
  border: 2px solid #ffffff;
  border-radius: 999px;
  background: #22c55e;
  box-shadow:
    0 0 0 4px rgba(34, 197, 94, 0.16),
    0 0 16px rgba(34, 197, 94, 0.45);
  animation: fh-ai-badge-ping 1.9s ease-out infinite;
}

.fh-chat-scrim-enter-active,
.fh-chat-scrim-leave-active {
  transition: opacity 0.22s ease;
}

.fh-chat-scrim-enter-from,
.fh-chat-scrim-leave-to {
  opacity: 0;
}

.fh-chat-panel-enter-active,
.fh-chat-panel-leave-active {
  transition: opacity 0.24s ease, transform 0.24s ease;
}

.fh-chat-panel-enter-from,
.fh-chat-panel-leave-to {
  opacity: 0;
  transform: translateY(14px) scale(0.975);
}

@keyframes fh-ai-header-glow {
  0%,
  100% {
    transform: translate3d(0, 0, 0) scale(1);
    opacity: 0.68;
  }
  50% {
    transform: translate3d(18px, 8px, 0) scale(1.12);
    opacity: 1;
  }
}

@keyframes fh-ai-status-pulse {
  0%,
  100% {
    opacity: 0.72;
    transform: scale(0.95);
  }
  50% {
    opacity: 1;
    transform: scale(1.15);
  }
}

@keyframes fh-ai-floating-light {
  0%,
  100% {
    transform: translate3d(0, 0, 0) scale(1);
  }
  50% {
    transform: translate3d(20px, -18px, 0) scale(1.18);
  }
}

@keyframes fh-ai-mini-thinking {
  0%,
  100% {
    transform: translateY(0) rotate(0deg);
  }
  50% {
    transform: translateY(-2px) rotate(-3deg);
  }
}

@keyframes fh-ai-orb-core {
  0%,
  100% {
    transform: scale(0.86);
    opacity: 0.72;
  }
  50% {
    transform: scale(1.14);
    opacity: 1;
  }
}

@keyframes fh-ai-orb-spin {
  to {
    transform: rotate(360deg);
  }
}

@keyframes fh-ai-thinking-line {
  0%,
  100% {
    transform: scaleX(0.62);
    opacity: 0.42;
  }
  50% {
    transform: scaleX(1);
    opacity: 1;
  }
}

@keyframes fh-ai-rotate {
  to {
    transform: rotate(360deg);
  }
}

@keyframes fh-ai-launcher-breathe {
  0%,
  100% {
    transform: translateY(0) scale(1);
  }
  50% {
    transform: translateY(-1px) scale(1.016);
  }
}

@keyframes fh-ai-aura-pulse {
  0%,
  100% {
    opacity: 0.7;
    transform: scale(0.96);
  }
  50% {
    opacity: 1;
    transform: scale(1.09);
  }
}

@keyframes fh-ai-siri-orbit {
  0%,
  100% {
    transform: translate3d(0, 0, 0) scale(1);
  }
  33% {
    transform: translate3d(19px, 11px, 0) scale(1.18);
  }
  66% {
    transform: translate3d(-9px, 18px, 0) scale(0.93);
  }
}

@keyframes fh-ai-ring-shimmer {
  0% {
    transform: rotate(0deg) scale(1);
  }
  50% {
    transform: rotate(180deg) scale(1.018);
  }
  100% {
    transform: rotate(360deg) scale(1);
  }
}

@keyframes fh-ai-robot-float {
  0%,
  100% {
    transform: translate3d(0, 0, 0);
  }
  50% {
    transform: translate3d(0, -3px, 0);
  }
}

@keyframes fh-ai-robot-vibrate {
  0%,
  74%,
  100% {
    rotate: 0deg;
  }
  77% {
    rotate: -2.2deg;
  }
  80% {
    rotate: 2.2deg;
  }
  83% {
    rotate: -1.5deg;
  }
  86% {
    rotate: 1.5deg;
  }
  89% {
    rotate: 0deg;
  }
}

@keyframes fh-ai-robot-vibrate-fast {
  0%,
  100% {
    rotate: -1.8deg;
  }
  50% {
    rotate: 1.8deg;
  }
}

@keyframes fh-ai-badge-ping {
  0%,
  100% {
    box-shadow:
      0 0 0 4px rgba(34, 197, 94, 0.16),
      0 0 16px rgba(34, 197, 94, 0.45);
  }
  55% {
    box-shadow:
      0 0 0 9px rgba(34, 197, 94, 0),
      0 0 20px rgba(34, 197, 94, 0.66);
  }
}

@media (prefers-reduced-motion: reduce) {
  .fh-ai-header-glow,
  .fh-ai-avatar-halo,
  .fh-ai-status-dot,
  .fh-ai-conversation-watermark span,
  .fh-ai-mini-avatar-thinking,
  .fh-ai-orb-core,
  .fh-ai-orb-ring,
  .fh-ai-thinking-line i,
  .fh-ai-send::before,
  .fh-ai-launcher,
  .fh-ai-launcher-aura,
  .fh-ai-siri-blob,
  .fh-ai-launcher-ring,
  .fh-ai-launcher-img,
  .fh-ai-launcher-badge {
    animation: none !important;
  }
}

@media (max-width: 640px) {
  .fh-ai-chat {
    position: fixed !important;
    right: 14px !important;
    bottom: 18px !important;
    left: auto !important;
    top: auto !important;
    width: 64px;
    height: 64px;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    transform: none !important;
  }

  @supports (right: env(safe-area-inset-right)) {
    .fh-ai-chat {
      right: calc(14px + env(safe-area-inset-right)) !important;
      bottom: calc(18px + env(safe-area-inset-bottom)) !important;
    }
  }

  .fh-ai-panel {
    position: fixed;
    right: 10px;
    left: 10px;
    bottom: calc(88px + env(safe-area-inset-bottom));
    width: auto;
    height: min(650px, calc(100vh - 110px));
    border-radius: 26px;
  }

  .fh-ai-header {
    padding: 0.9rem;
  }

  .fh-ai-avatar {
    width: 46px;
    height: 46px;
    border-radius: 16px;
  }

  .fh-ai-avatar img {
    width: 38px;
    height: 38px;
  }

  .fh-ai-title {
    font-size: 0.96rem;
  }

  .fh-ai-message {
    max-width: 86%;
  }

  .fh-ai-launcher {
    position: fixed !important;
    right: 14px !important;
    bottom: 18px !important;
    left: auto !important;
    top: auto !important;
    width: 66px !important;
    height: 66px !important;
    display: inline-flex !important;
    border-radius: 24px;
    visibility: visible !important;
    opacity: 1 !important;
    transform: none;
  }

  @supports (right: env(safe-area-inset-right)) {
    .fh-ai-launcher {
      right: calc(14px + env(safe-area-inset-right)) !important;
      bottom: calc(18px + env(safe-area-inset-bottom)) !important;
    }
  }

  .fh-ai-launcher-img {
    width: 50px;
    height: 50px;
  }

  .fh-ai-launcher-siri {
    border-radius: 22px;
  }
}

@supports (height: 100dvh) {
  @media (max-width: 640px) {
    .fh-ai-panel {
      height: min(650px, calc(100dvh - 110px));
    }
  }
}
</style>
