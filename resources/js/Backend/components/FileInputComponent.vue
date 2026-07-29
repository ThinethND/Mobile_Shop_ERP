<template>
  <div :class="parentCls">
    <div
      class="group relative flex h-28 w-28 cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50 transition hover:border-[#38bdf8] hover:bg-white dark:border-zinc-700 dark:bg-zinc-900"
      :class="dragOver ? 'ring-2 ring-[#38bdf8]/70' : ''"
      @click="openPicker"
      @dragover.prevent="dragOver = true"
      @dragleave.prevent="dragOver = false"
      @drop.prevent="onDrop"
      aria-label="Upload file"
      role="button"
      tabindex="0"
    >
      <img
        v-if="currentPreview"
        :src="currentPreview"
        alt="Preview"
        class="h-full w-full object-cover"
      />

      <!-- PLACEHOLDER -->
      <div v-else class="flex flex-col items-center justify-center text-zinc-400 transition group-hover:text-[#38bdf8]">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V6a2 2 0 0 1 2-2h8.5M7 20.25h10a2 2 0 0 0 2-2V7.75m-8.5-3.5L19 8"/>
        </svg>
        <span class="mt-1 text-[11px]">Upload</span>
      </div>

      <div
        v-if="currentPreview"
        class="pointer-events-none absolute inset-0 hidden items-end justify-between bg-gradient-to-t from-black/30 p-1.5 group-hover:flex"
      >
        <button
          type="button"
          class="pointer-events-auto inline-flex items-center justify-center rounded-full bg-white/95 p-1.5 text-zinc-700 shadow hover:bg-white cursor-pointer"
          @click.stop="openPicker"
          aria-label="Change image"
          title="Change"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h2l1.2-1.8A2 2 0 0 1 10.8 4h2.4a2 2 0 0 1 1.6.8L16 7h2a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3v-6a3 3 0 0 1 3-3z"/>
            <circle cx="12" cy="13" r="3.5"/>
          </svg>
        </button>

        <button
          type="button"
          class="pointer-events-auto inline-flex items-center justify-center rounded-full bg-white/95 p-1.5 text-rose-600 shadow hover:bg-white cursor-pointer"
          @click.stop="clearAll"
          aria-label="Clear image"
          title="Clear"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M10 8.586l4.95-4.95 1.414 1.414L11.414 10l4.95 4.95-1.414 1.414L10 11.414l-4.95 4.95-1.414-1.414L8.586 10l-4.95-4.95L5.05 3.636 10 8.586z" clip-rule="evenodd"/>
          </svg>
        </button>
      </div>
    </div>

    <div v-if="rejections.length" class="mt-2 space-y-1">
      <div v-for="(r, i) in rejections" :key="i" class="rounded-md bg-rose-50 px-2.5 py-1.5 text-xs text-rose-700 ring-1 ring-rose-200">
        {{ r }}
      </div>
    </div>

    <input
      ref="fileInput"
      type="file"
      class="sr-only"
      :id="id"
      :required="isRequired"
      :accept="accept"
      @change="onChange"
    />
  </div>
</template>

<script>
export default {
  name: 'FileInputComponent',
  emits: ['update:modelValue', 'change', 'clear', 'invalid'],
  props: {
    id: { type: String, required: true },
    accept: { type: String, default: 'image/*' },
    initialUrls: { type: [String, Array], default: '' },
    prvImage: { type: String, default: '' },
    modelValue: { default: null },
    maxSizeMB: { type: Number, default: 10 },
    parentCls: { type: String, default: '' },
    isRequired: { type: Boolean, default: false },
  },
  data() {
    return { dragOver: false, file: null, objectUrl: null, previewUrl: '', rejections: [] };
  },
  computed: {
    _initialUrl() {
      if (Array.isArray(this.initialUrls) && this.initialUrls.length) return this.initialUrls[0];
      if (typeof this.initialUrls === 'string' && this.initialUrls) return this.initialUrls;
      if (this.prvImage) return this.prvImage;
      return '';
    },
    currentPreview() { return this.previewUrl || this._initialUrl || ''; },
  },
  mounted() { this.syncFromModel(this.modelValue); },
  beforeUnmount() { this.cleanupObjectUrl(); },
  watch: { modelValue(val) { this.syncFromModel(val); } },
  methods: {
    openPicker() { this.$refs.fileInput?.click(); },
    onChange(e) {
      const list = e.target.files;
      if (!list || !list.length) return;
      this.setFile(list[list.length - 1]);
      this.$emit('change', this.file);
      this.dragOver = false;
    },
    onDrop(e) {
      const dt = e.dataTransfer;
      if (dt && dt.files && dt.files.length) {
        this.setFile(dt.files[dt.files.length - 1]);
        this.$emit('change', this.file);
      }
      this.dragOver = false;
    },
    clearAll() {
      const input = this.$refs.fileInput;
      if (input) input.value = '';
      this.setFile(null);
      this.rejections = [];
      this.$emit('update:modelValue', null);
      this.$emit('clear');
    },
    syncFromModel(val) {
      if (val && typeof val === 'object' && 'name' in val) this.setFile(val);
      else this.setFile(null);
    },
    setFile(file) {
      this.file = file;
      this.rebuildPreview();
      this.$emit('update:modelValue', this.file || null);
    },
    rebuildPreview() {
      this.cleanupObjectUrl();
      if (this.file) {
        const { errors } = this.validateFile(this.file);
        this.rejections = errors;
        if (errors.length) {
          this.previewUrl = '';
          this.$emit('invalid', errors);
          return;
        }
        this.objectUrl = URL.createObjectURL(this.file);
        this.previewUrl = this.objectUrl;
      } else {
        this.previewUrl = '';
      }
    },
    cleanupObjectUrl() {
      if (this.objectUrl) URL.revokeObjectURL(this.objectUrl);
      this.objectUrl = null;
    },
    validateFile(file) {
      const maxBytes = this.maxSizeMB * 1024 * 1024;
      const acceptList = (this.accept || '').split(',').map(s => s.trim()).filter(Boolean);
      const errs = [];
      if (file.size > maxBytes) errs.push(`${file.name} is larger than ${this.maxSizeMB}MB.`);
      const matchesAccept = () => {
        if (!acceptList.length) return true;
        const { type, name } = file;
        const lower = name?.toLowerCase() || '';
        for (const rule of acceptList) {
          if (rule === '*/*') return true;
          if (rule.endsWith('/*')) {
            const prefix = rule.slice(0, rule.indexOf('/'));
            if (type.startsWith(prefix + '/')) return true;
          } else if (rule.startsWith('.')) {
            if (lower.endsWith(rule.toLowerCase())) return true;
          } else {
            if (type === rule) return true;
          }
        }
        return false;
      };
      if (!matchesAccept()) errs.push(`${file.name} is not an accepted type.`);
      return { errors: errs };
    },
  },
};
</script>

<style scoped>
:focus-visible { outline: none; box-shadow: 0 0 0 2px rgba(56,189,248,.5); }
</style>
