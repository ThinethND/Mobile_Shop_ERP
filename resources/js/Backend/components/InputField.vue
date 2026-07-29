
<template>
  <div :class="wrapperClass">
    <label
      v-if="label"
      :for="id"
      class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200"
    >
      {{ label }} <sup v-if="isRequired" class="text-rose-600">*</sup>
    </label>

    <div class="relative">
      <div v-if="$slots.prefix" class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400">
        <slot name="prefix" />
      </div>

      <component
        :is="multiline ? 'textarea' : 'input'"
        :id="id"
        :type="inputType"
        :rows="multiline ? rows : undefined"
        :value="modelValue"
        :placeholder="placeholder"
        :required="isRequired"
        :disabled="disabled"
        :readonly="readonly"
        :autocomplete="autocomplete"
        :inputmode="inputmode"
        :min="min"
        :max="max"
        :step="step"
        class="w-full h-10 rounded-md border-1  border-[#d3d3d3] bg-white text-zinc-900 placeholder-zinc-400  outline-none focus:ring-2 dark:bg-zinc-900 dark:text-zinc-100"
        :class="[
          sizeClass,
          borderClass,
          disabled ? 'cursor-not-allowed opacity-60' : '',
          $slots.prefix ? 'pl-9' : 'pl-3',
          needsRightPad ? 'pr-10' : 'pr-3'
        ]"
        @input="$emit('update:modelValue', $event.target.value)"
        @change="$emit('change', $event.target.value)"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
        v-bind="$attrs"
      />

      <div class="absolute inset-y-0 right-0 flex items-center pr-2">
        <slot name="suffix"></slot>

        <button
          v-if="clearable && !!modelValue && !disabled && !readonly"
          type="button"
          class="ml-1 inline-flex h-7 w-7 items-center justify-center rounded-full text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800"
          @click="onClear"
          aria-label="Clear"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 8.586l4.95-4.95 1.414 1.414L11.414 10l4.95 4.95-1.414 1.414L10 11.414l-4.95 4.95-1.414-1.414L8.586 10l-4.95-4.95L5.05 3.636 10 8.586z" clip-rule="evenodd" />
          </svg>
        </button>

        <button
          v-if="type === 'password'"
          type="button"
          class="ml-1 inline-flex h-7 w-7 items-center justify-center rounded-full text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800"
          @click="showPwd = !showPwd"
          :aria-label="showPwd ? 'Hide password' : 'Show password'"
        >
          <svg v-if="!showPwd" xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" />
            <circle cx="12" cy="12" r="3.5" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M9.9 9.9A3.5 3.5 0 0012 15.5c1.9 0 3.5-1.6 3.5-3.5 0-.8-.3-1.5-.7-2.1M6 6c-2.1 1.5-3.5 3.4-4 6 0 0 3.5 7 10 7 2.2 0 4.1-.6 5.6-1.5M18 6.4C16.6 5.6 14.7 5 12 5c-1.1 0-2.1.2-3 .4" />
          </svg>
        </button>
      </div>
    </div>

    <p v-if="error" class="mt-1 text-sm text-rose-600">{{ error }}</p>
    <p v-else-if="help" class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ help }}</p>
  </div>
</template>
<script>
export default {
  name: 'InputField',
  inheritAttrs: false,
  props: {
    id: { type: String, required: true },
    label: { type: String, default: '' },
    modelValue: { type: [String, Number], default: '' },
    type: { type: String, default: 'text' },
    multiline: { type: Boolean, default: false },
    rows: { type: Number, default: 3 },
    autocomplete: { type: String, default: 'off' },
    inputmode: { type: String, default: undefined },
    readonly: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    isRequired: { type: Boolean, default: false },
    clearable: { type: Boolean, default: false },
    min: { type: [Number, String], default: undefined },
    max: { type: [Number, String], default: undefined },
    step: { type: [Number, String], default: undefined },
    placeholder: { type: String, default: '' },
    error: { type: String, default: '' },
    help: { type: String, default: '' },
    size: { type: String, default: 'md' },
    wrapperClass: { type: String, default: 'w-full' },
  },
  emits: ['update:modelValue', 'change', 'blur', 'focus', 'clear'],
  data() { return { showPwd: false }; },
  computed: {
    inputType() {
      return this.type === 'password' ? (this.showPwd ? 'text' : 'password') : this.type;
    },
    sizeClass() {
      if (this.multiline) {
        return this.size === 'sm'
          ? 'px-2.5 py-2 text-sm'
          : this.size === 'lg'
            ? 'px-4 py-3 text-base'
            : 'px-3 py-2.5 text-sm';
      }
      return this.size === 'sm'
        ? 'px-2.5 py-1.5 text-sm'
        : this.size === 'lg'
          ? 'px-4 py-2.5 text-base'
          : 'px-3 py-2 text-sm';
    },
    borderClass() {
      return this.error
        ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-500'
        : 'focus:ring-black';
    },
    needsRightPad() {
      return !!this.$slots.suffix || this.clearable || this.type === 'password';
    },
  },
  methods: {
    onClear() {
      this.$emit('update:modelValue', '');
      this.$emit('clear');
    },
  },
}
</script>
