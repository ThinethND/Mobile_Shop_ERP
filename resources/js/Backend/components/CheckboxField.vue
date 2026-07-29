<template>
  <div :class="wrapperClass">
    <label :for="id" class="inline-flex items-center" :class="s.gap">
      <span class="relative inline-block" :class="s.boxWrap">
        <input
          :id="id"
          type="checkbox"
          class="peer appearance-none rounded border bg-white outline-none transition
          cursor-pointer
                 focus:ring-2 focus:ring-[#38bdf8] focus:ring-offset-2
                 hover:border-[#38bdf8]
                 dark:border-zinc-700 dark:bg-zinc-900
                 disabled:cursor-not-allowed disabled:opacity-60"
          :class="[s.box, borderClass]"
          :checked="checked"
          :disabled="disabled"
          @change="onToggle($event)"
          :aria-invalid="!!error"
        />

        <span
          class="pointer-events-none absolute inset-0 flex items-center justify-center
                 opacity-0 transition-opacity peer-checked:opacity-100"
        >
          <svg
            :class="s.icon"
            viewBox="0 0 20 20"
            class="fill-current text-[#38bdf8]"
            aria-hidden="true"
          >
            <path
              fill-rule="evenodd"
              d="M16.707 5.293a1 1 0 00-1.414 0L8.5 12.086l-2.793-2.793a1 1 0 10-1.414 1.414l3.5 3.5a1 1 0 001.414 0l7.5-7.5a1 1 0 000-1.414z"
              clip-rule="evenodd"
            />
          </svg>
        </span>
      </span>

      <span class="select-none">
        <span :class="s.label" class="text-zinc-900 dark:text-zinc-100">{{ label }}</span>
        <span
          v-if="description"
          class="block mt-0.5 text-zinc-500 dark:text-zinc-400"
          :class="s.desc"
        >
          {{ description }}
        </span>
      </span>
    </label>

    <p v-if="error" class="mt-1 text-sm text-rose-600">{{ error }}</p>

    <input v-if="name" type="hidden" :name="name" :value="checked ? trueValue : falseValue" />
  </div>
</template>

<script>
export default {
  name: 'SmallCheckbox',
  emits: ['update:modelValue', 'change'],
  props: {
    id: { type: String, required: true },
    label: { type: String, default: '' },
    description: { type: String, default: '' },

    modelValue: { default: false },
    value: { default: null },

    trueValue: { default: true },
    falseValue: { default: false },

    size: { type: String, default: 'sm' },
    disabled: { type: Boolean, default: false },
    error: { type: String, default: '' },
    wrapperClass: { type: String, default: 'inline-flex flex-col' },
    name: { type: String, default: '' },
  },
  computed: {
    checked() {
      if (Array.isArray(this.modelValue)) {
        return this.modelValue.includes(this.value);
      }
      return (
        this.modelValue === this.trueValue ||
        (this.trueValue === true && this.modelValue === true)
      );
    },
    borderClass() {
      return this.checked
        ? 'border-[#38bdf8] bg-white'
        : 'border-zinc-300 dark:border-zinc-700';
    },
    s() {
      if (this.size === 'xs') {
        return {
          boxWrap: 'h-4 w-4',
          box: 'h-4 w-4',
          icon: 'h-3 w-3',
          label: 'text-xs',
          desc: 'text-[11px]',
          gap: 'gap-2',
        };
      }
      return {
        boxWrap: 'h-5 w-5',
        box: 'h-5 w-5',
        icon: 'h-3.5 w-3.5',
        label: 'text-sm',
        desc: 'text-xs',
        gap: 'gap-2.5',
      };
    },
  },
  methods: {
    onToggle(evt) {
      if (this.disabled) return;

      let newValue;

      if (Array.isArray(this.modelValue)) {
        newValue = [...this.modelValue];
        const index = newValue.indexOf(this.value);

        if (evt.target.checked && index === -1) {
          newValue.push(this.value);
        } else if (!evt.target.checked && index !== -1) {
          newValue.splice(index, 1);
        }
      } else {
        newValue = evt.target.checked ? this.trueValue : this.falseValue;
      }

      this.$emit('update:modelValue', newValue);
      this.$emit('change', newValue);
    },
  },
};
</script>

