<script setup lang="ts">
import InputError from '@/Backend/components/InputError.vue'
import { Button } from '@/Backend/components/ui/button'
import { Input } from '@/Backend/components/ui/input'
import { Label } from '@/Backend/components/ui/label'
import { Head, Form } from '@inertiajs/vue3'
import { LoaderCircle, Mail, Lock } from 'lucide-vue-next'
import { update } from '@/routes/password'
import { ref } from 'vue'

const props = defineProps<{
  token: string
  email: string
}>()

const inputEmail = ref(props.email)
</script>

<template>
  <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2 lg:gap-0 bg-black text-white relative overflow-hidden">
    <Head>
      <title>Reset password</title>
      <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&display=swap" rel="stylesheet" />
    </Head>

    <!-- Background glows -->
    <div
      class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(56,189,248,0.16)_0%,transparent_70%),radial-gradient(circle_at_bottom_right,rgba(56,189,248,0.18)_0%,transparent_80%)] blur-3xl">
    </div>

    <!-- Left: Reset form card -->
    <section class="relative z-10 flex items-center justify-end px-4 sm:px-6 lg:px-8 py-10 lg:py-0">
      <div
        class="w-full max-w-xl rounded-2xl border border-white/10 bg-white/5 backdrop-blur-xl shadow-[0_0_50px_rgba(56,189,248,0.18)] animate-slideUpSoft">
        <div class="px-7 sm:px-8 pt-9 pb-5">
          <h2 class="text-3xl font-bold tracking-wide text-white">Reset password</h2>
          <p class="mt-2 text-base text-white">Please enter your new password below.</p>

          <Form
            v-bind="update.form()"
            :transform="(data) => ({ ...data, token: props.token, email: inputEmail })"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="mt-7 space-y-6"
          >
            <!-- Email (readonly) -->
            <div>
              <Label for="email" class="mb-2 block text-white">Email</Label>
              <div class="relative">
                <Mail class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-white" />
                <Input
                  id="email"
                  type="email"
                  name="email"
                  v-model="inputEmail"
                  autocomplete="email"
                  readonly
                  class="h-12 pl-11 text-white placeholder-white bg-black/40 border-white/20 focus:border-[#38bdf8] focus:ring-0"
                />
              </div>
              <InputError :message="errors.email" />
            </div>

            <!-- New password -->
            <div>
              <Label for="password" class="mb-2 block text-white">Password</Label>
              <div class="relative">
                <Lock class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-white" />
                <Input
                  id="password"
                  type="password"
                  name="password"
                  autocomplete="new-password"
                  autofocus
                  placeholder="New password"
                  class="h-12 pl-11 text-white placeholder-white bg-black/40 border-white/20 focus:border-[#38bdf8] focus:ring-0"
                />
              </div>
              <InputError :message="errors.password" />
            </div>

            <!-- Confirm password -->
            <div>
              <Label for="password_confirmation" class="mb-2 block text-white">Confirm password</Label>
              <div class="relative">
                <Lock class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-white" />
                <Input
                  id="password_confirmation"
                  type="password"
                  name="password_confirmation"
                  autocomplete="new-password"
                  placeholder="Confirm password"
                  class="h-12 pl-11 text-white placeholder-white bg-black/40 border-white/20 focus:border-[#38bdf8] focus:ring-0"
                />
              </div>
              <InputError :message="errors.password_confirmation" />
            </div>

            <!-- Submit -->
            <Button
              type="submit"
              :class="[
                'mt-1 w-full h-12 rounded-full bg-[#38bdf8] text-base font-semibold shadow-[0_0_22px_rgba(56,189,248,0.5)] transition-all btn-animated',
                processing ? 'cursor-wait' : 'cursor-pointer'
              ]"
              :disabled="processing"
              data-test="reset-password-button"
            >
              <LoaderCircle v-if="processing" class="mr-2 h-5 w-5 animate-spin" />
              Reset password
            </Button>
          </Form>
        </div>

        <div class="h-2 w-full rounded-b-2xl bg-gradient-to-r from-transparent via-[#38bdf8]/40 to-transparent"></div>
      </div>
    </section>

    <!-- Right: Neon headline + logo -->
    <section class="relative z-10 flex items-center justify-start px-4 sm:px-6 lg:px-8">
      <div class="relative flex w-full max-w-3xl flex-col items-center justify-center">
        <h1
          class="neon-stroke neon-pulse text-center text-[2.3rem] sm:text-[3.2rem] md:text-[4.8rem] lg:text-[6rem] xl:text-[7rem] font-extrabold uppercase tracking-wider animate-fadeIn font-['Orbitron',sans-serif]">
          R<span class="flicker">E</span>S<span class="flicker-2">E</span>T
        </h1>

        <div class="auth-wordmark" aria-label="DezeStore">
          <span>Deze</span><span>Store</span>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
/* Fade-in */
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px);} to { opacity: 1; transform: translateY(0);} }
.animate-fadeIn { animation: fadeIn .8s ease forwards; }

/* Card entrance */
@keyframes slideUpSoft { from { opacity:0; transform:translateY(24px) scale(.98);} to { opacity:1; transform:translateY(0) scale(1);} }
.animate-slideUpSoft { animation: slideUpSoft .7s cubic-bezier(.22,.61,.36,1) both; }

/* Neon headline */
.neon-stroke {
  color: transparent;
  -webkit-text-stroke: 2px #38bdf8;
  text-fill-color: transparent;
  letter-spacing: 3px;
  text-shadow: 0 0 6rem #38bdf8;
}

.auth-wordmark {
  display: inline-flex;
  align-items: center;
  gap: 0.18em;
  margin-top: 1.5rem;
  font-family: 'Orbitron', sans-serif;
  font-size: clamp(2rem, 4vw, 3.5rem);
  font-weight: 800;
  line-height: 1;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #38bdf8;
  text-shadow: 0 0 36px rgba(56, 189, 248, 0.55);
}

.auth-wordmark span:last-child {
  color: #ffffff;
}

.flicker, .flicker-2 { -webkit-text-stroke: 2px #38bdf8; }
@keyframes flickerBlink { 0%,18%,22%,25%,53%,57%,100%{opacity:1;} 20%,24%,55%{opacity:.2;} }
.flicker { animation: flickerBlink 2.5s infinite; }
.flicker-2 { animation: flickerBlink 3s infinite; }

/* Button shimmer */
.btn-animated { background-image: linear-gradient(90deg, #38bdf8, #7dd3fc, #38bdf8); background-size: 200% 100%; }
.btn-animated:hover { animation: btnShimmer 1.2s linear infinite; }
@keyframes btnShimmer { from { background-position: 0% 50%; } to { background-position: 100% 50%; } }

/* Scrollbar */
::-webkit-scrollbar { width: 8px; }
::-webkit-scrollbar-thumb { background: rgba(56,189,248,0.5); border-radius: 9999px; }
::-webkit-scrollbar-thumb:hover { background: rgba(56,189,248,0.8); }
::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
</style>
