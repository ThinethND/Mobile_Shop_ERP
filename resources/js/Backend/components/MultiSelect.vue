<template>
    <div class="w-full" :class="wrapperClass" ref="root">
        <label
            v-if="label"
            :for="id"
            class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200"
        >
            {{ label }} <sup v-if="required" class="hover">*</sup>
        </label>

        <div
            :id="id"
            class="relative rounded-md border bg-white dark:bg-zinc-900"
            :class="[
                disabled ? 'cursor-not-allowed opacity-60' : 'cursor-text',
                error
                    ? 'border-rose-500 focus-within:ring-rose-500'
                    : 'border-zinc-300 dark:border-zinc-700',
                'focus-within:ring-2',
            ]"
            @mousedown.prevent="focusInput"
            @click="open = true"
        >
            <div class="flex flex-wrap items-center gap-1.5 px-2.5 py-1.5">
                <button
                    v-for="(opt, i) in selectedOptions"
                    :key="getVal(opt) + '-chip'"
                    type="button"
                    class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-xs font-medium text-rose-500 ring-1 ring-emerald-600/20 ring-inset dark:bg-emerald-900/20 dark:text-emerald-200"
                    @click.stop="remove(getVal(opt))"
                    :title="`Remove ${getLbl(opt)}`"
                >
                   <span v-if="getImg(opt)" class="mr-1 h-4 w-4 rounded-full overflow-hidden border border-white/50">
  <img :src="getImg(opt)" class="h-full w-full object-cover" />
</span>
{{ getLbl(opt) }}

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="ml-1 h-3.5 w-3.5"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 8.586l4.95-4.95 1.414 1.414L11.414 10l4.95 4.95-1.414 1.414L10 11.414l-4.95 4.95-1.414-1.414L8.586 10l-4.95-4.95L5.05 3.636 10 8.586z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>

                <input
                    ref="input"
                    type="text"
                    v-model="query"
                    :placeholder="selectedOptions.length ? '' : placeholder"
                    :disabled="disabled"
                    class="min-w-[6rem] flex-1 border-0 bg-transparent px-1 py-1 text-sm text-zinc-900 placeholder-zinc-400 outline-none dark:text-zinc-100"
                    @keydown.down.prevent="move(1)"
                    @keydown.up.prevent="move(-1)"
                    @keydown.enter.prevent="selectActive()"
                    @keydown.esc.prevent="close"
                    @keydown.delete="onBackspace"
                />
            </div>

            <transition name="fade" appear>
                <div
                    v-if="open"
                    class="absolute z-30 mt-1 max-h-64 w-full overflow-auto rounded-md border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                >
                    <div
                        class="sticky top-0 bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                    >
                        {{ headerText }}
                    </div>

                    <ul
                        class="divide-y divide-zinc-100 dark:divide-zinc-800"
                        role="listbox"
                        aria-multiselectable="true"
                    >
                        <li
                            v-for="(opt, idx) in filtered"
                            :key="getVal(opt)"
                            :id="`opt-${id}-${getVal(opt)}`"
                            role="option"
                            :aria-selected="isSelected(getVal(opt))"
                            class="cursor-pointer px-3 py-2 text-sm"
                            :class="[
                                idx === activeIndex
                                    ? 'custom-bg-primary text-white'
                                    : 'hover:bg-zinc-100 dark:hover:bg-zinc-800',
                                isSelected(getVal(opt)) && idx !== activeIndex
                                    ? 'font-semibold text-zinc-800 dark:text-zinc-200'
                                    : '',
                            ]"
                            @mousedown.prevent
                            @click="toggle(opt)"
                            @mouseenter="activeIndex = idx"
                        >
                          <div class="flex items-center gap-2">
  <div class="h-5 w-5 rounded-full overflow-hidden border border-zinc-200 bg-zinc-50 flex items-center justify-center">
    <img v-if="getImg(opt)" :src="getImg(opt)" class="h-full w-full object-cover" />
  </div>
  <span>{{ getLbl(opt) }}</span>
</div>

                        </li>

                        <li
                            v-if="!filtered.length"
                            class="px-3 py-3 text-sm text-zinc-500"
                        >
                            No results
                        </li>
                    </ul>
                </div>
            </transition>
        </div>

        <p v-if="error" class="mt-1 text-sm text-rose-600">{{ error }}</p>
    </div>
</template>

<script>
export default {
    name: 'MultiSelect',
    emits: ['update:modelValue', 'change'],
    props: {
        id: { type: String, required: true },
        label: { type: String, default: '' },
        placeholder: { type: String, default: 'Pick items' },
        options: { type: Array, default: () => [] },
        modelValue: { type: Array, default: () => [] },
        required: { type: Boolean, default: false },
        disabled: { type: Boolean, default: false },
        error: { type: String, default: '' },
        wrapperClass: { type: String, default: 'w-full' },
        valueKey: { type: String, default: 'id' },
        labelKey: { type: String, default: 'name' },
        maxSelected: { type: Number, default: Infinity },
        imageKey: { type: String, default: '' }, // e.g. "image_url"

    },
    data() {
        return {
            open: false,
            query: '',
            activeIndex: 0,
        };
    },
    computed: {
        getVal() {
            return (o) => o?.[this.valueKey] ?? o?.value ?? o;
        },
        getLbl() {
            return (o) =>
                o?.[this.labelKey] ?? o?.label ?? o?.title ?? String(o);
        },

        getImg() {
  return (o) => (this.imageKey ? (o?.[this.imageKey] ?? null) : null);
},


        selectedOptions() {
            const set = new Set(this.modelValue ?? []);
            return (this.options || []).filter((o) => set.has(this.getVal(o)));
        },
        filtered() {
            const q = (this.query || '').trim().toLowerCase();
            const base = this.options || [];
            if (!q) return base;
            return base.filter((o) => this.getLbl(o).toLowerCase().includes(q));
        },
        headerText() {
            return this.selectedOptions.length
                ? 'Selected'
                : this.query.trim()
                  ? 'Results'
                  : this.label || 'Options';
        },
    },
    watch: {
        open(val) {
            if (val) {
                this.$nextTick(() => this.$refs.input?.focus());
                document.addEventListener('pointerdown', this.onOutside, {
                    capture: true,
                });
            } else {
                document.removeEventListener('pointerdown', this.onOutside, {
                    capture: true,
                });
            }
        },
    },
    methods: {
        focusInput() {
            this.$refs.input?.focus();
        },
        onOutside(e) {
            if (!this.$refs.root?.contains(e.target)) this.close();
        },
        close() {
            this.open = false;
            this.activeIndex = 0;
        },

        isSelected(val) {
            return (this.modelValue || []).includes(val);
        },

        move(dir) {
            if (!this.open) this.open = true;
            const len = this.filtered.length;
            if (!len) return;
            this.activeIndex = (this.activeIndex + dir + len) % len;
        },
        selectActive() {
            const opt = this.filtered[this.activeIndex];
            if (opt) this.toggle(opt);
        },
        onBackspace() {
            if ((this.query || '').trim()) return;
            const arr = this.modelValue || [];
            if (arr.length) this.remove(arr[arr.length - 1]);
        },
        toggle(opt) {
            if (this.disabled) return;
            const val = this.getVal(opt);
            const arr = new Set(this.modelValue || []);
            if (arr.has(val)) arr.delete(val);
            else {
                if ((this.modelValue?.length || 0) >= this.maxSelected) return;
                arr.add(val);
            }
            const next = Array.from(arr);
            this.$emit('update:modelValue', next);
            this.$emit('change', next);
            this.query = '';
            this.activeIndex = 0;
            this.$nextTick(() => this.$refs.input?.focus());
        },
        remove(val) {
            const next = (this.modelValue || []).filter((v) => v !== val);
            this.$emit('update:modelValue', next);
            this.$emit('change', next);
        },
    },
    beforeUnmount() {
        document.removeEventListener('pointerdown', this.onOutside, {
            capture: true,
        });
    },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.12s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
.custom-bg-primary {
    color: #38bdf8;
}
</style>
