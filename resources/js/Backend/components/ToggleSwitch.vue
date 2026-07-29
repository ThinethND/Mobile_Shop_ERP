<template>
  <div :class="wrapperClass">
    <label
      v-if="label"
      :for="id"
      class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200"
    >
      {{ label }}
      <sup v-if="required" class="text-rose-600">*</sup>
    </label>

    <button
      :id="id"
      type="button"
      role="switch"
      :aria-checked="isOn ? 'true' : 'false'"
      :aria-disabled="disabled ? 'true' : 'false'"
      :disabled="disabled"
      class="relative inline-flex h-5 w-9 items-center rounded-full transition
             focus:outline-none focus-visible:ring-2 focus-visible:ring-[#38bdf8] focus-visible:ring-offset-1
             dark:focus-visible:ring-offset-zinc-900"
      :class="[
        disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
        isOn ? 'bg-[#38bdf8]' : 'bg-zinc-300 dark:bg-zinc-700'
      ]"
      @click="toggle"
      @keydown.enter.prevent="toggle"
      @keydown.space.prevent="toggle"
    >
      <span
        aria-hidden="true"
        class="inline-block h-4 w-4 transform rounded-full bg-white shadow ring-1 ring-zinc-200 transition-transform"
        :class="isOn ? 'translate-x-4' : 'translate-x-0.5'"
      />
    </button>

    <p v-if="error" class="mt-1 text-sm text-rose-600">{{ error }}</p>
    <p v-else-if="help" class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ help }}</p>

    <input v-if="name" type="hidden" :name="name" :value="isOn ? trueValue : falseValue" />
  </div>
</template>

<script>
export default {
  name: 'SmallToggle',
  emits: ['update:modelValue', 'change'],
  props: {
    id: { type: String, default: 'toggle' },

    modelValue: { default: false },
    trueValue:  { default: true },
    falseValue: { default: false },

    disabled: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
    label: { type: String, default: '' },
    help:  { type: String, default: '' },
    error: { type: String, default: '' },
    wrapperClass: { type: String, default: 'inline-flex flex-col gap-1' },

    name: { type: String, default: '' },
  },
  computed: {
    isOn() {
      return this.modelValue === this.trueValue ||
             (this.trueValue === true && this.modelValue === true);
    },
  },
  methods: {
    toggle() {
      if (this.disabled) return;
      const next = this.isOn ? this.falseValue : this.trueValue;
      this.$emit('update:modelValue', next);
      this.$emit('change', next);
    },
  },
};
</script>
