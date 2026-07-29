<script setup lang="ts">
import AppLogo from '@/Backend/components/AppLogo.vue'
import InputError from '@/Backend/components/InputError.vue'
import TextLink from '@/Backend/components/TextLink.vue'
import { Button } from '@/Backend/components/ui/button'
import { Checkbox } from '@/Backend/components/ui/checkbox'
import { Input } from '@/Backend/components/ui/input'
import { Label } from '@/Backend/components/ui/label'
import { Head, router, useForm } from '@inertiajs/vue3'
import { Eye, EyeOff, LoaderCircle, Lock, Mail, ShieldCheck } from 'lucide-vue-next'
import { ref } from 'vue'

const showPassword = ref(false)

const props = defineProps<{
  status?: string
  canResetPassword: boolean
  canRegister: boolean
}>()

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post('/admin/login', {
    preserveScroll: true,
    onFinish: () => form.reset('password'),
    onSuccess: () => {
      router.visit('/admin/dashboard', { replace: true })
    },
  })
}
</script>

<template>
  <Head title="Admin Login" />

  <main class="admin-login-page">
    <section class="admin-login-shell">
      <div class="admin-login-brand">
        <AppLogo />
      </div>

      <div class="admin-login-grid">
        <aside class="admin-login-panel">
          <div class="admin-login-panel__content">
            <div class="admin-login-badge">
              <ShieldCheck class="h-4 w-4" />
              Admin Access
            </div>

            <h1>DezeStore Control Room</h1>
            <p>Sign in to manage orders, products, inventory, reviews, and store settings.</p>
          </div>
        </aside>

        <section class="admin-login-card" aria-label="Admin login form">
          <div class="admin-login-card__header">
            <h2>Welcome Back</h2>
            <p>Enter your admin credentials to continue.</p>
          </div>

          <div
            v-if="props.status"
            class="admin-login-status"
          >
            {{ props.status }}
          </div>

          <form class="admin-login-form" @submit.prevent="submit">
            <div class="admin-field">
              <Label for="email">Email address</Label>
              <div class="admin-field__control">
                <Mail class="admin-field__icon" />
                <Input
                  id="email"
                  v-model="form.email"
                  type="email"
                  name="email"
                  required
                  autofocus
                  autocomplete="email"
                  placeholder="admin@dezestore.lk"
                  :tabindex="1"
                  class="admin-input"
                />
              </div>
              <InputError :message="form.errors.email" />
            </div>

            <div class="admin-field">
              <div class="admin-field__top">
                <Label for="password">Password</Label>
                <TextLink
                  v-if="props.canResetPassword"
                  href="/admin/forgot-password"
                  class="admin-forgot-link"
                >
                  Forgot password?
                </TextLink>
              </div>

              <div class="admin-field__control">
                <Lock class="admin-field__icon" />
                <Input
                  id="password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  name="password"
                  required
                  autocomplete="current-password"
                  placeholder="Password"
                  :tabindex="2"
                  class="admin-input admin-input--password"
                />

                <button
                  type="button"
                  class="admin-password-toggle"
                  :aria-pressed="showPassword ? 'true' : 'false'"
                  :title="showPassword ? 'Hide password' : 'Show password'"
                  @click="showPassword = !showPassword"
                >
                  <Eye v-if="!showPassword" class="h-5 w-5" />
                  <EyeOff v-else class="h-5 w-5" />
                  <span class="sr-only">{{ showPassword ? 'Hide password' : 'Show password' }}</span>
                </button>
              </div>
              <InputError :message="form.errors.password" />
            </div>

            <label for="remember" class="admin-remember">
              <Checkbox
                id="remember"
                name="remember"
                :tabindex="3"
                :checked="form.remember"
                @update:checked="(value: boolean | 'indeterminate') => (form.remember = value === true)"
              />
              <span>Remember me on this device</span>
            </label>

            <Button
              type="submit"
              class="admin-login-button"
              :tabindex="4"
              :disabled="form.processing"
              data-test="login-button"
            >
              <LoaderCircle v-if="form.processing" class="mr-2 h-5 w-5 animate-spin" />
              Sign In
            </Button>
          </form>
        </section>
      </div>
    </section>
  </main>
</template>

<style scoped>
.admin-login-page {
  min-height: 100vh;
  background:
    linear-gradient(135deg, rgba(248, 250, 252, 0.98), rgba(239, 246, 255, 0.92)),
    #f8fafc;
  color: #0f172a;
  padding: clamp(18px, 4vw, 48px);
}

.admin-login-shell {
  display: flex;
  min-height: calc(100vh - clamp(36px, 8vw, 96px));
  max-width: 1180px;
  margin: 0 auto;
  flex-direction: column;
  gap: 22px;
}

.admin-login-brand {
  width: fit-content;
  border-radius: 18px;
  background: #0f172a;
  padding: 12px 14px;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
}

.admin-login-grid {
  display: grid;
  overflow: hidden;
  min-height: 620px;
  border: 1px solid #dbe3ef;
  border-radius: 24px;
  background: #ffffff;
  box-shadow: 0 24px 70px rgba(15, 23, 42, 0.12);
  grid-template-columns: minmax(0, 0.95fr) minmax(390px, 0.75fr);
}

.admin-login-panel {
  position: relative;
  display: flex;
  min-height: 100%;
  align-items: flex-end;
  background: linear-gradient(135deg, rgba(7, 31, 79, 0.96), rgba(15, 23, 42, 0.98));
  padding: clamp(32px, 5vw, 64px);
}

.admin-login-panel::before {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(180deg, rgba(15, 23, 42, 0.16), rgba(15, 23, 42, 0.72)),
    radial-gradient(circle at 22% 18%, rgba(56, 189, 248, 0.22), transparent 36%);
  content: '';
}

.admin-login-panel__content {
  position: relative;
  max-width: 520px;
}

.admin-login-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  border: 1px solid rgba(255, 255, 255, 0.22);
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.1);
  padding: 8px 12px;
  color: #dbeafe;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.admin-login-panel h1 {
  margin-top: 22px;
  color: #ffffff;
  font-size: clamp(2.25rem, 5vw, 4.8rem);
  font-weight: 900;
  letter-spacing: 0;
  line-height: 0.95;
}

.admin-login-panel p {
  max-width: 440px;
  margin-top: 20px;
  color: rgba(226, 232, 240, 0.88);
  font-size: 16px;
  line-height: 1.75;
}

.admin-login-card {
  display: flex;
  justify-content: center;
  flex-direction: column;
  padding: clamp(28px, 5vw, 56px);
}

.admin-login-card__header h2 {
  color: #0f172a;
  font-size: clamp(1.85rem, 3vw, 2.55rem);
  font-weight: 850;
  letter-spacing: 0;
  line-height: 1.05;
}

.admin-login-card__header p {
  margin-top: 10px;
  color: #64748b;
  font-size: 15px;
  line-height: 1.6;
}

.admin-login-status {
  margin-top: 22px;
  border: 1px solid #bbf7d0;
  border-radius: 12px;
  background: #f0fdf4;
  padding: 12px 14px;
  color: #166534;
  font-size: 14px;
  font-weight: 700;
}

.admin-login-form {
  margin-top: 30px;
  display: flex;
  flex-direction: column;
  gap: 22px;
}

.admin-field {
  display: flex;
  flex-direction: column;
  gap: 9px;
}

.admin-field__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.admin-field :deep(label),
.admin-field__top :deep(label) {
  color: #334155;
  font-size: 14px;
  font-weight: 750;
}

.admin-forgot-link {
  color: #071f4f !important;
  font-size: 13px;
  font-weight: 750;
  text-decoration: none;
}

.admin-forgot-link:hover {
  color: #0b2b62 !important;
  text-decoration: underline;
}

.admin-field__control {
  position: relative;
}

.admin-field__icon {
  position: absolute;
  left: 15px;
  top: 50%;
  z-index: 2;
  width: 20px;
  height: 20px;
  color: #64748b;
  transform: translateY(-50%);
}

.admin-input {
  min-height: 52px;
  width: 100%;
  border-color: #cbd5e1 !important;
  border-radius: 12px !important;
  background: #ffffff !important;
  padding-left: 46px !important;
  color: #0f172a !important;
  font-size: 15px !important;
  box-shadow: none !important;
}

.admin-input--password {
  padding-right: 50px !important;
}

.admin-input:focus {
  border-color: #071f4f !important;
  box-shadow: 0 0 0 4px rgba(7, 31, 79, 0.1) !important;
}

.admin-password-toggle {
  position: absolute;
  right: 12px;
  top: 50%;
  z-index: 3;
  display: inline-flex;
  width: 36px;
  height: 36px;
  align-items: center;
  justify-content: center;
  border: 0;
  border-radius: 10px;
  background: transparent;
  color: #64748b;
  cursor: pointer;
  transform: translateY(-50%);
  transition:
    background-color 0.18s ease,
    color 0.18s ease;
}

.admin-password-toggle:hover,
.admin-password-toggle:focus-visible {
  background: #f1f5f9;
  color: #071f4f;
  outline: none;
}

.admin-remember {
  display: flex;
  width: fit-content;
  align-items: center;
  gap: 10px;
  color: #475569;
  cursor: pointer;
  font-size: 14px;
  font-weight: 650;
}

.admin-login-button {
  min-height: 52px;
  width: 100%;
  border-radius: 12px !important;
  background: #071f4f !important;
  color: #ffffff !important;
  font-size: 15px !important;
  font-weight: 800 !important;
  box-shadow: 0 18px 34px rgba(7, 31, 79, 0.2);
  transition:
    transform 0.18s ease,
    background-color 0.18s ease,
    box-shadow 0.18s ease;
}

.admin-login-button:hover:not(:disabled) {
  background: #0b2b62 !important;
  box-shadow: 0 20px 38px rgba(7, 31, 79, 0.25);
  transform: translateY(-1px);
}

.admin-login-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

:deep(input:-webkit-autofill),
:deep(textarea:-webkit-autofill),
:deep(select:-webkit-autofill) {
  -webkit-box-shadow: 0 0 0 1000px #ffffff inset !important;
  -webkit-text-fill-color: #0f172a !important;
  caret-color: #0f172a;
  transition: background-color 99999s ease-out;
}

@media (max-width: 980px) {
  .admin-login-grid {
    grid-template-columns: 1fr;
  }

  .admin-login-panel {
    min-height: 300px;
  }
}

@media (max-width: 640px) {
  .admin-login-page {
    padding: 0;
  }

  .admin-login-shell {
    min-height: 100vh;
    gap: 0;
  }

  .admin-login-brand {
    margin: 16px;
  }

  .admin-login-grid {
    min-height: auto;
    border-right: 0;
    border-left: 0;
    border-radius: 0;
    box-shadow: none;
  }

  .admin-login-panel {
    min-height: 240px;
    padding: 28px 22px;
  }

  .admin-login-card {
    padding: 30px 20px 36px;
  }

  .admin-field__top {
    align-items: flex-start;
    flex-direction: column;
  }
}
</style>
