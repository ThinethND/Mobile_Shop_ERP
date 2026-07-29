<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { KeyRound, ShieldCheck, UserRound } from 'lucide-vue-next'
import { route } from 'ziggy-js'

const props = defineProps<{
  user: {
    id: number
    name: string
    email: string
  }
}>()

const breadcrumbs = [
  {
    title: 'Profile',
    href: route('admin.profile.edit'),
  },
]

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

function submit() {
  form.put(route('admin.profile.update-password'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset('current_password', 'password', 'password_confirmation')
    },
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Admin Profile" />

    <div class="p-6">
      <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-neutral-950">Admin Profile</h1>
          <p class="text-sm text-neutral-500">View the admin login details and change the account password.</p>
        </div>

        <div
          v-if="form.recentlySuccessful"
          class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700"
        >
          <ShieldCheck class="h-4 w-4" />
          Password saved
        </div>
      </div>

      <div class="grid gap-4 lg:grid-cols-[360px_minmax(0,1fr)]">
        <section class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
          <div class="mb-5 flex items-center gap-3">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-sky-50 text-[#0ea5e9]">
              <UserRound class="h-5 w-5" />
            </span>
            <div>
              <h2 class="text-base font-semibold text-neutral-950">Login Identity</h2>
              <p class="text-sm text-neutral-500">These details are read-only.</p>
            </div>
          </div>

          <div class="space-y-4">
            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Username</label>
              <input
                :value="props.user.name"
                type="text"
                disabled
                class="w-full rounded-lg border border-neutral-200 bg-neutral-50 px-4 py-2.5 text-neutral-700 outline-none"
              >
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700">Email</label>
              <input
                :value="props.user.email"
                type="email"
                disabled
                class="w-full rounded-lg border border-neutral-200 bg-neutral-50 px-4 py-2.5 text-neutral-700 outline-none"
              >
            </div>
          </div>
        </section>

        <form class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm" @submit.prevent="submit">
          <div class="mb-5 flex items-center gap-3">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-neutral-100 text-neutral-900">
              <KeyRound class="h-5 w-5" />
            </span>
            <div>
              <h2 class="text-base font-semibold text-neutral-950">Change Password</h2>
              <p class="text-sm text-neutral-500">Enter the current password before saving a new one.</p>
            </div>
          </div>

          <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-neutral-700" for="current_password">
                Current Password
              </label>
              <input
                id="current_password"
                v-model="form.current_password"
                type="password"
                autocomplete="current-password"
                class="w-full rounded-lg border border-neutral-200 px-4 py-2.5 outline-none transition focus:border-[#38bdf8] focus:ring-2 focus:ring-sky-100"
              >
              <p v-if="form.errors.current_password" class="mt-1 text-sm text-red-600">
                {{ form.errors.current_password }}
              </p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700" for="password">
                New Password
              </label>
              <input
                id="password"
                v-model="form.password"
                type="password"
                autocomplete="new-password"
                class="w-full rounded-lg border border-neutral-200 px-4 py-2.5 outline-none transition focus:border-[#38bdf8] focus:ring-2 focus:ring-sky-100"
              >
              <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                {{ form.errors.password }}
              </p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-neutral-700" for="password_confirmation">
                Confirm New Password
              </label>
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                autocomplete="new-password"
                class="w-full rounded-lg border border-neutral-200 px-4 py-2.5 outline-none transition focus:border-[#38bdf8] focus:ring-2 focus:ring-sky-100"
              >
            </div>
          </div>

          <div class="mt-6 flex justify-end">
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center justify-center rounded-lg bg-[#38bdf8] px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0ea5e9] disabled:opacity-50"
            >
              {{ form.processing ? 'Saving...' : 'Save Password' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
