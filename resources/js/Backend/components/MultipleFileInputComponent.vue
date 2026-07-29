<template>
  <div :class="parentCls">
    <div
      class="rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-700/60 dark:bg-zinc-900"
      @dragover.prevent="dragOver = true"
      @dragleave.prevent="dragOver = false"
      @drop.prevent="onDrop"
      :class="dragOver ? 'ring-2 ring-[#38bdf8]/60' : ''"
    >
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
        <div
          v-if="canAddMore"
          class="group relative flex aspect-square w-full cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50 transition hover:border-[#38bdf8] hover:bg-white dark:border-zinc-700 dark:bg-zinc-900"
          @click="openPicker"
          role="button"
          tabindex="0"
          aria-label="Upload images"
        >
          <div class="flex flex-col items-center justify-center text-zinc-400 transition group-hover:text-[#38bdf8]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="mt-1 text-[11px]">Add</span>
          </div>
        </div>

        <div
          v-for="(u, i) in initialLocal"
          :key="'init-' + i + '-' + u"
          class="relative aspect-square w-full overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900"
        >
          <img :src="u" alt="Image" class="h-full w-full object-cover" />
          <button
            type="button"
            class="absolute right-1.5 top-1.5 inline-flex h-6 w-6 cursor-pointer items-center justify-center rounded-full bg-[#38bdf8] text-white shadow"
            @click.stop="removeInitial(i)"
            aria-label="Remove image"
            title="Remove"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 8.586l4.95-4.95 1.414 1.414L11.414 10l4.95 4.95-1.414 1.414L10 11.414l-4.95 4.95-1.414-1.414L8.586 10l-4.95-4.95L5.05 3.636 10 8.586z" clip-rule="evenodd"/>
            </svg>
          </button>
        </div>

        <div
          v-for="(u, i) in previewUrls"
          :key="'new-' + i + '-' + u"
          class="relative aspect-square w-full overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900"
        >
          <img :src="u" alt="Image" class="h-full w-full object-cover" />
          <button
            type="button"
            class="absolute right-1.5 top-1.5 inline-flex h-6 w-6 cursor-pointer items-center justify-center rounded-full bg-[#38bdf8] text-white shadow"
            @click.stop="removeNew(i)"
            aria-label="Remove image"
            title="Remove"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 8.586l4.95-4.95 1.414 1.414L11.414 10l4.95 4.95-1.414 1.414L10 11.414l-4.95 4.95-1.414-1.414L8.586 10l-4.95-4.95L5.05 3.636 10 8.586z" clip-rule="evenodd"/>
            </svg>
          </button>
        </div>
      </div>

      <div v-if="rejections.length" class="mt-3 space-y-1">
        <div v-for="(r, i) in rejections" :key="i" class="rounded-md bg-rose-50 px-2.5 py-1.5 text-xs text-rose-700 ring-1 ring-rose-200">
          {{ r }}
        </div>
      </div>
    </div>

    <!-- Hidden input -->
    <input
      ref="fileInput"
      type="file"
      class="sr-only"
      :id="id"
      multiple
      :required="isRequired"
      :accept="accept"
      @change="onChange"
    />
  </div>
</template>

<script>
export default {
  name: 'MultipleFileInputComponent',
  emits: ['update:modelValue', 'change', 'clear', 'invalid', 'remove-initial'],
  props: {
    id: { type: String, required: true },
    accept: { type: String, default: 'image/*' },
    initialUrls: { type: [String, Array], default: '' },
    modelValue: { type: Array, default: () => [] },
    maxFiles: { type: Number, default: 12 },
    maxSizeMB: { type: Number, default: 10 },
    parentCls: { type: String, default: '' },
    isRequired: { type: Boolean, default: false },
  },
  data() {
    return {
      dragOver: false,
      filesList: [],   
      objectUrls: [],  
      previewUrls: [], 
      rejections: [],
      initialLocal: [], 
    };
  },
  computed: {
    totalCount() {
      return this.initialLocal.length + this.filesList.length;
    },
    canAddMore() {
      return this.totalCount < this.maxFiles;
    },
  },
  mounted() {
    this.hydrateInitial();
    this.syncFromModel(this.modelValue);
  },
  beforeUnmount() {
    this.cleanupObjectUrls();
  },
  watch: {
    modelValue(val) { this.syncFromModel(val); },
    initialUrls() { this.hydrateInitial(); },
  },
  methods: {
    hydrateInitial() {
      const arr = Array.isArray(this.initialUrls) ? this.initialUrls : (this.initialUrls ? [this.initialUrls] : []);
      this.initialLocal = arr.filter(Boolean);
    },

    openPicker() { this.$refs.fileInput?.click(); },
    onChange(e) { this.addFiles(e.target.files); this.dragOver = false; },
    onDrop(e) { this.addFiles(e.dataTransfer?.files); this.dragOver = false; },

    addFiles(fileList) {
      if (!fileList || !fileList.length) return;
      const incoming = Array.from(fileList);
      const { valid, errors } = this.validateFiles(incoming);

      const availableSlots = Math.max(0, this.maxFiles - this.totalCount);
      const trimmed = valid.slice(0, availableSlots);
      if (valid.length > trimmed.length) errors.push(`You can select up to ${this.maxFiles} images.`);

      this.rejections = errors;
      if (errors.length) this.$emit('invalid', errors);
      if (!trimmed.length) return;

      this.filesList = [...this.filesList, ...trimmed];
      this.rebuildPreviews();
      this.emitModel();
      this.$emit('change', this.filesList);
      if (this.$refs.fileInput) this.$refs.fileInput.value = '';
    },

    removeInitial(index) {
      const removed = this.initialLocal.splice(index, 1)[0];
      this.$emit('remove-initial', removed, index);
    },
    removeNew(index) {
      this.filesList.splice(index, 1);
      this.rebuildPreviews();
      this.emitModel();
    },

    emitModel() { this.$emit('update:modelValue', this.filesList); },

    rebuildPreviews() {
      this.cleanupObjectUrls();
      this.previewUrls = this.filesList.map(f => this.makeUrl(f));
    },
    makeUrl(file) {
      const url = URL.createObjectURL(file);
      this.objectUrls.push(url);
      return url;
    },
    cleanupObjectUrls() {
      for (const u of this.objectUrls) URL.revokeObjectURL(u);
      this.objectUrls = [];
    },

    syncFromModel(val) {
      const arr = Array.isArray(val) ? val : (val ? [val] : []);
      this.filesList = arr.filter(f => f && typeof f === 'object' && 'name' in f);
      const allowedForNew = Math.max(0, this.maxFiles - this.initialLocal.length);
      this.filesList = this.filesList.slice(0, allowedForNew);
      this.rebuildPreviews();
    },

    validateFiles(files) {
      const maxBytes = this.maxSizeMB * 1024 * 1024;
      const acceptList = (this.accept || '').split(',').map(s => s.trim()).filter(Boolean);
      const out = [];
      const errs = [];

      const matchesAccept = (file) => {
        if (!acceptList.length) return true;
        const { type, name } = file;
        const lower = (name || '').toLowerCase();
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

      for (const f of files) {
        if (f.size > maxBytes) { errs.push(`${f.name} is larger than ${this.maxSizeMB}MB.`); continue; }
        if (!matchesAccept(f)) { errs.push(`${f.name} is not an accepted type.`); continue; }
        out.push(f);
      }
      return { valid: out, errors: errs };
    },
  },
};
</script>

<style scoped>
:focus-visible { outline: none; box-shadow: 0 0 0 2px rgba(56,189,248,.5); }
</style>
