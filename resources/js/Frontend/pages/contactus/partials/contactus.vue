<script setup lang="ts">
import { computed, reactive, ref } from 'vue'

type ContactData = {
  address: string
  phones: string[]
  email: string
  map_title: string
  map_url: string
  map_embed_url: string
}

const props = defineProps<{
  contact: ContactData
}>()

const form = reactive({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
})

const errors = reactive({
  name: '',
  email: '',
  message: '',
})

const submitted = ref(false)

const phoneItems = computed(() => {
  return props.contact.phones.map((phone) => ({
    raw: phone,
    href: `tel:${phone.replace(/\D/g, '')}`,
  }))
})

function resetErrors() {
  errors.name = ''
  errors.email = ''
  errors.message = ''
}

function isValidEmail(value: string) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
}

function validateForm() {
  resetErrors()

  let valid = true

  if (!form.name.trim()) {
    errors.name = 'Please enter your name.'
    valid = false
  }

  if (!form.email.trim()) {
    errors.email = 'Please enter your email address.'
    valid = false
  } else if (!isValidEmail(form.email.trim())) {
    errors.email = 'Please enter a valid email address.'
    valid = false
  }

  if (!form.message.trim()) {
    errors.message = 'Please enter your message.'
    valid = false
  }

  return valid
}

function handleSubmit() {
  submitted.value = false

  if (!validateForm()) return

  const subject = form.subject.trim() || `Contact request from ${form.name.trim()}`
  const bodyLines = [
    `Name: ${form.name.trim()}`,
    `Email: ${form.email.trim()}`,
    `Phone: ${form.phone.trim() || 'N/A'}`,
    '',
    'Message:',
    form.message.trim(),
  ]

  const mailtoUrl = `mailto:${props.contact.email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(bodyLines.join('\n'))}`

  window.location.href = mailtoUrl
  submitted.value = true
}
</script>

<template>
  <section class="bg-[#f7f7f5]">
    <div class="mx-auto max-w-7xl px-4 pb-16 pt-12 sm:px-6 sm:pb-20 sm:pt-14 lg:px-8 lg:pb-24 lg:pt-20">
      <div class="mb-8 max-w-3xl lg:mb-12">
        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-neutral-500">
          Contact Us
        </p>
        <h1 class="mt-3 text-3xl font-semibold tracking-[-0.04em] text-neutral-950 sm:text-4xl lg:text-5xl">
          Let’s start a conversation
        </h1>
        <p class="mt-4 text-sm leading-7 text-neutral-600 sm:text-[15px] sm:leading-8">
          Please contact us for product inquiries, order support, delivery questions, or any general assistance. We shall be pleased to respond as promptly as possible.
        </p>
      </div>

      <div class="grid gap-6 lg:grid-cols-[minmax(0,1.08fr)_400px] lg:gap-8">
        <div class="overflow-hidden rounded-[30px] border border-neutral-200 bg-white shadow-[0_20px_60px_rgba(15,23,42,0.06)]">
          <div class="border-b border-neutral-200 px-6 py-6 sm:px-8 lg:px-10">
            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-neutral-500">
              Send us a message
            </p>
            <h2 class="mt-3 text-2xl font-semibold tracking-[-0.03em] text-neutral-950 sm:text-3xl">
              We are here to help
            </h2>
          </div>

          <div class="px-6 py-6 sm:px-8 lg:px-10 lg:py-8">
            <Transition name="fade-slide" mode="out-in">
              <div
                v-if="submitted"
                key="submitted"
                class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
              >
                Your email application should open now with your message details.
              </div>
            </Transition>

            <form class="grid gap-4 sm:grid-cols-2" @submit.prevent="handleSubmit">
              <div class="sm:col-span-1">
                <label class="mb-2 block text-sm font-medium text-neutral-800">
                  Full Name
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  placeholder="Enter your full name"
                  class="w-full rounded-2xl border border-neutral-200 bg-[#fafaf9] px-4 py-3 text-sm text-neutral-900 outline-none transition placeholder:text-neutral-400 focus:border-black focus:bg-white"
                />
                <p v-if="errors.name" class="mt-2 text-xs text-red-500">
                  {{ errors.name }}
                </p>
              </div>

              <div class="sm:col-span-1">
                <label class="mb-2 block text-sm font-medium text-neutral-800">
                  Email Address
                </label>
                <input
                  v-model="form.email"
                  type="email"
                  placeholder="Enter your email address"
                  class="w-full rounded-2xl border border-neutral-200 bg-[#fafaf9] px-4 py-3 text-sm text-neutral-900 outline-none transition placeholder:text-neutral-400 focus:border-black focus:bg-white"
                />
                <p v-if="errors.email" class="mt-2 text-xs text-red-500">
                  {{ errors.email }}
                </p>
              </div>

              <div class="sm:col-span-1">
                <label class="mb-2 block text-sm font-medium text-neutral-800">
                  Phone Number
                </label>
                <input
                  v-model="form.phone"
                  type="text"
                  placeholder="Enter your phone number"
                  class="w-full rounded-2xl border border-neutral-200 bg-[#fafaf9] px-4 py-3 text-sm text-neutral-900 outline-none transition placeholder:text-neutral-400 focus:border-black focus:bg-white"
                />
              </div>

              <div class="sm:col-span-1">
                <label class="mb-2 block text-sm font-medium text-neutral-800">
                  Subject
                </label>
                <input
                  v-model="form.subject"
                  type="text"
                  placeholder="Enter your subject"
                  class="w-full rounded-2xl border border-neutral-200 bg-[#fafaf9] px-4 py-3 text-sm text-neutral-900 outline-none transition placeholder:text-neutral-400 focus:border-black focus:bg-white"
                />
              </div>

              <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-medium text-neutral-800">
                  Message
                </label>
                <textarea
                  v-model="form.message"
                  rows="7"
                  placeholder="Write your message here"
                  class="w-full resize-none rounded-2xl border border-neutral-200 bg-[#fafaf9] px-4 py-3 text-sm text-neutral-900 outline-none transition placeholder:text-neutral-400 focus:border-black focus:bg-white"
                />
                <p v-if="errors.message" class="mt-2 text-xs text-red-500">
                  {{ errors.message }}
                </p>
              </div>

              <div class="sm:col-span-2 pt-2">
                <button
                  type="submit"
                  class="inline-flex min-h-[54px] items-center justify-center rounded-2xl bg-black px-6 text-sm font-semibold text-white transition hover:bg-neutral-800"
                >
                  Send Message
                </button>
              </div>
            </form>
          </div>
        </div>

        <aside class="overflow-hidden rounded-[30px] bg-black text-white shadow-[0_20px_60px_rgba(15,23,42,0.14)]">
          <div class="px-6 py-6 sm:px-8 lg:px-8">
            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-white/60">
              Contact details
            </p>

            <h2 class="mt-3 text-2xl font-semibold tracking-[-0.03em] text-white sm:text-3xl">
              Reach us directly
            </h2>

            <p class="mt-4 text-sm leading-7 text-white/70">
              You may contact us directly using the information below. Our team shall be pleased to assist you with any inquiry.
            </p>

            <div class="mt-8 space-y-4">
              <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-white/45">
                  Address
                </p>
                <p class="mt-2 text-sm leading-7 text-white/90">
                  {{ contact.address }}
                </p>
              </div>

              <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-white/45">
                  Contact Numbers
                </p>

                <div class="mt-3 flex flex-col gap-2">
                  <a
                    v-for="phone in phoneItems"
                    :key="phone.raw"
                    :href="phone.href"
                    class="text-sm font-medium text-white/90 transition hover:text-white"
                  >
                    {{ phone.raw }}
                  </a>
                </div>
              </div>

              <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-white/45">
                  Email
                </p>
                <a
                  :href="`mailto:${contact.email}`"
                  class="mt-2 inline-block text-sm font-medium text-white/90 transition hover:text-white"
                >
                  {{ contact.email }}
                </a>
              </div>
            </div>
          </div>
        </aside>
      </div>

      <div class="mt-8 overflow-hidden rounded-[30px] bg-black shadow-[0_20px_60px_rgba(15,23,42,0.14)]">
        <div class="border-b border-white/10 px-6 py-6 sm:px-8 lg:px-10">
          <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-white/60">
            Location
          </p>

          <div class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
              <h2 class="text-2xl font-semibold tracking-[-0.03em] text-white sm:text-3xl">
                {{ contact.map_title }}
              </h2>
              <p class="mt-2 text-sm leading-7 text-white/70">
                {{ contact.address }}
              </p>
            </div>

            <a
              :href="contact.map_url"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white px-5 py-3 text-sm font-semibold text-black transition hover:bg-neutral-200"
            >
              Open in Google Maps
            </a>
          </div>
        </div>

        <div class="h-[320px] bg-white sm:h-[380px] lg:h-[460px]">
          <iframe
            :src="contact.map_embed_url"
            class="h-full w-full border-0"
            allowfullscreen
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
          />
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition:
    opacity 0.22s ease,
    transform 0.22s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

@media (prefers-reduced-motion: reduce) {
  * {
    transition: none !important;
    animation: none !important;
  }
}
</style>