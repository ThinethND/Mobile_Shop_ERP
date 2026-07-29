<template>
  <div :class="className" ref="root">
    <label :for="id" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
      {{ label }}
      <!-- <sup v-if="isRequired" class="text-rose-600">*</sup> -->
    </label>

    <div
      :id="id"
      class="relative rounded-md border"
      :class="[
        disabled ? 'cursor-not-allowed opacity-60' : 'cursor-text',
        'focus-within:ring-2'
      ]"
      :aria-invalid="!!error"
      :aria-expanded="open ? 'true' : 'false'"
      role="combobox"
      :aria-controls="`list-${id}`"
      :aria-activedescendant="activeOptionId"
      tabindex="0"
      @mousedown.self.prevent="focusInput"
      @click.self="open = true"
      @keydown.down.prevent="move(1)"
      @keydown.up.prevent="move(-1)"
      @keydown.enter.prevent="selectActive()"
      @keydown.esc.prevent="close"
    >
      <div class="flex  items-center  px-3">
        <input
          ref="search"
          type="text"
          v-model="query"
          :placeholder="inputPlaceholder"
          :disabled="disabled"
          class="flex-1 border-0 bg-transparent px-0 py-0 text-sm text-zinc-900 placeholder-zinc-400 outline-none dark:text-zinc-100 h-10"
          @click.stop="toggleFromInput"
          @input="open = true"
        />
<!-- <span class="pl-2 text-zinc-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform"
               :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 10.939l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.25 8.27a.75.75 0 01-.02-1.06z"
                  clip-rule="evenodd" />
          </svg>
        </span> -->      
      </div>

      <transition name="fade" appear>
        <div
          v-if="open"
          class="absolute z-30 max-h-64 w-full overflow-auto rounded-md border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
          :class="placement === 'down' ? 'mt-1' : 'bottom-full mb-1'"
          :id="`list-${id}`"
          role="listbox"
        >
          <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
            <li
              v-for="(opt, idx) in filtered"
              :key="getVal(opt)"
              :id="`opt-${id}-${getVal(opt)}`"
              class="cursor-pointer px-3 py-2 text-sm"
              role="option"
              :aria-selected="isSelected(getVal(opt))"
              :class="[
                idx === activeIndex ? 'custom-bg-primary text-white' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800',
                isSelected(getVal(opt)) && idx !== activeIndex ? 'font-semibold text-zinc-800 dark:text-zinc-200' : ''
              ]"
              @mousedown.prevent
              @click="choose(opt)"
              @mouseenter="activeIndex = idx"
            >
              {{ getLbl(opt) }}
            </li>

            <li v-if="!filtered.length" class="px-3 py-3 text-sm text-zinc-500">
              No results
            </li>
          </ul>
        </div>
      </transition>
    </div>

    <p v-if="error" class="mt-1 text-sm text-rose-600">{{ error }}</p>

    <select
      v-if="useHiddenSelect"
      :name="name"
      class="hidden"
      :required="isRequired"
      :disabled="disabled"
      :value="displayValue"
    >
      <option
        v-for="opt in normalizedOptions"
        :key="'hid-' + getVal(opt)"
        :value="getVal(opt)"
        :selected="isSelected(getVal(opt))"
      >
        {{ getLbl(opt) }}
      </option>
    </select>
  </div>
</template>

<script>
export default {
  name: 'SelectInputComponentVue',
  emits: ['update:modelValue', 'update:model-value', 'change'],
  props: {
    id: { type: String, required: true },
    className: { type: String, default: 'w-full' },
    label: { type: String, required: true },
    error: { type: String, default: '' },
    isRequired: { type: Boolean, default: true },
    disabled: { type: Boolean, default: false },
    options: { type: Array, default: () => [] },
    valueKey: { type: String, default: 'id' },
    labelKey: { type: String, default: 'name' },
    modelValue: { default: '' },
    placeholder: { type: String, default: 'Select Option' },
    showPlaceholder: { type: Boolean, default: true },
    name: { type: String, default: '' },
    useHiddenSelect: { type: Boolean, default: false },
  },
  data() {
    return {
      open: false,
      query: '',
      activeIndex: 0,
      placement: 'down',      // 'down' | 'up'
      dropdownMaxHeight: 256, // px; matches max-h-64 (16 * 16)
    };
  },
  computed: {
    getVal() { return (o) => o?.[this.valueKey] ?? o?.value ?? o; },
    getLbl() { return (o) => o?.[this.labelKey] ?? o?.label ?? o?.title ?? String(o); },
    normalizedOptions() { return Array.isArray(this.options) ? this.options : []; },
    filtered() {
      const q = (this.query || '').trim().toLowerCase();
      const base = this.normalizedOptions;
      if (!q) return base;
      return base.filter(o => this.getLbl(o).toLowerCase().includes(q));
    },
    displayValue: {
      get() { return this.modelValue ?? ''; },
      set(val) { this.emitValue(val); },
    },
    selectedText() {
      if (this.displayValue === '') return '';
      const found = this.normalizedOptions.find(o => this.getVal(o) === this.displayValue);
      return found ? this.getLbl(found) : String(this.displayValue ?? '');
    },
    inputPlaceholder() {
      return (this.selectedText && !this.query)
        ? this.selectedText
        : (this.showPlaceholder ? this.placeholder : '');
    },
    displayText() { return this.selectedText || (this.showPlaceholder ? this.placeholder : ''); },
    activeOptionId() {
      const opt = this.filtered[this.activeIndex];
      return opt ? `opt-${this.id}-${this.getVal(opt)}` : null;
    },
  },
  watch: {
    open(val) {
      if (val) {
        this.$nextTick(() => {
          this.$refs.search?.focus();
          this.recomputePlacement();
        });
        document.addEventListener('pointerdown', this.onOutside, { capture: true });

        window.addEventListener('resize', this.recomputePlacement, { passive: true });
        window.addEventListener('scroll', this.recomputePlacement, { passive: true, capture: true });

        const idx = this.filtered.findIndex(o => this.getVal(o) === this.displayValue);
        this.activeIndex = idx >= 0 ? idx : 0;
      } else {
        document.removeEventListener('pointerdown', this.onOutside, { capture: true });
        window.removeEventListener('resize', this.recomputePlacement);
        window.removeEventListener('scroll', this.recomputePlacement, { capture: true });
      }
    },
  },
  methods: {
    focusInput() { this.$refs.search?.focus(); },
    toggleFromInput() { this.open ? this.close() : (this.open = true); },
    emitValue(val) {
      this.$emit('update:modelValue', val);
      this.$emit('update:model-value', val);
      this.$emit('change', val);
    },
    close() {
      this.open = false;
      this.activeIndex = 0;
      this.query = '';
    },
    onOutside(e) {
      if (!this.$refs.root?.contains(e.target)) this.close();
    },
    isSelected(val) { return this.displayValue === val; },
    move(dir) {
      if (!this.open) this.open = true;
      const len = this.filtered.length;
      if (!len) return;
      this.activeIndex = (this.activeIndex + dir + len) % len;
      this.$nextTick(() => {
        const id = this.activeOptionId;
        if (!id) return;
        const el = document.getElementById(id);
        el?.scrollIntoView({ block: 'nearest' });
      });
    },
    selectActive() {
      const opt = this.filtered[this.activeIndex];
      if (opt) this.choose(opt);
    },
    choose(opt) {
      if (this.disabled) return;
      const val = this.getVal(opt);
      this.emitValue(val);
      this.close();
    },
    recomputePlacement() {
      this.$nextTick(() => {
        const root = this.$refs.root;
        if (!root) return;

        const rect = root.getBoundingClientRect();
        const viewportH = window.innerHeight || document.documentElement.clientHeight;
        const spaceAbove = rect.top;
        const spaceBelow = viewportH - rect.bottom;
        const need = this.dropdownMaxHeight;

        this.placement = (spaceBelow < Math.min(need, spaceAbove) && spaceAbove > spaceBelow)
          ? 'up'
          : 'down';
      });
    },
  },
  beforeUnmount() {
    document.removeEventListener('pointerdown', this.onOutside, { capture: true });
    window.removeEventListener('resize', this.recomputePlacement);
    window.removeEventListener('scroll', this.recomputePlacement, { capture: true });
  },
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .12s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

input::placeholder { color: #000; }

.custom-bg-primary {
color: #38bdf8; }
</style>
