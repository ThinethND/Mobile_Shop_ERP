<template>
    <div class="space-y-3">
        <div v-for="(day, index) in days" :key="day.name"
            class="rounded-lg border border-zinc-200 bg-white/70 dark:border-zinc-700/50 dark:bg-zinc-900/60 shadow-sm hover:shadow-md transition-all p-4">
            <!-- Main Row -->
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <!-- Left: Checkbox + Day Label -->
                <div class="flex items-center gap-3">
                    <CheckboxField v-model="day.enabled"
                        class="h-4 w-4 text-[#38bdf8] border-gray-300 rounded focus:ring-[#38bdf8] cursor-pointer" />
                    <h4 class="text-[15px] font-semibold text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                        {{ day.name }}
                    </h4>
                </div>

                <!-- Right: Time Pickers -->
                <div class="flex flex-col sm:items-end sm:text-right w-full md:w-auto gap-2">
                    <template v-if="day.enabled">
                        <!-- First Time Row -->
                        <div class="flex flex-wrap items-center justify-end gap-2 sm:gap-3">
                            <select v-model="day.intervals[0].open"
                                class="w-28 sm:w-32 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 shadow-sm focus:border-[#38bdf8] focus:ring focus:ring-[#38bdf8]/30 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                                <option v-for="time in timeOptions" :key="time" :value="time">
                                    {{ time }}
                                </option>
                            </select>

                            <span class="text-zinc-400">—</span>

                            <select v-model="day.intervals[0].close"
                                class="w-28 sm:w-32 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 shadow-sm focus:border-[#38bdf8] focus:ring focus:ring-[#38bdf8]/30 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                                <option v-for="time in timeOptions" :key="time" :value="time">
                                    {{ time }}
                                </option>
                            </select>

                            <!-- Add Button -->
                            <button v-if="day.intervals.length < 2" type="button"
                                class="ml-1 sm:ml-2 inline-flex items-center justify-center rounded-md px-3 py-1.5 text-lg text-[#38bdf8] transition dark:border-[#38bdf8]/30 dark:bg-[#38bdf8]/10 dark:hover:bg-[#38bdf8]/20 cursor-pointer"
                                @click="addInterval(index)">
                                +
                            </button>

                            <!-- Remove First Interval -->
                            <button v-if="day.intervals.length > 1" type="button"
                                class="ml-1 sm:ml-2 inline-flex items-center justify-center text-lg rounded-md px-3 py-1.5 text-[#38bdf8] transition dark:hover:bg-[#38bdf8]/20 cursor-pointer"
                                @click="removeInterval(index, 0)">
                                ✕
                            </button>
                        </div>

                        <!-- Second Time Row -->
                        <div v-if="day.intervals.length > 1"
                            class="flex flex-wrap items-center justify-end gap-2 sm:gap-3">
                            <select v-model="day.intervals[1].open"
                                class="w-28 sm:w-32 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 shadow-sm focus:border-[#38bdf8] focus:ring focus:ring-[#38bdf8]/30 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                                <option v-for="time in timeOptions" :key="time" :value="time">
                                    {{ time }}
                                </option>
                            </select>

                            <span class="text-zinc-400">—</span>

                            <select v-model="day.intervals[1].close"
                                class="w-28 sm:w-32 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 shadow-sm focus:border-[#38bdf8] focus:ring focus:ring-[#38bdf8]/30 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                                <option v-for="time in timeOptions" :key="time" :value="time">
                                    {{ time }}
                                </option>
                            </select>

                            <button type="button"
                                class="ml-1 sm:ml-2 inline-flex items-center text-lg justify-center rounded-md px-3 py-1.5 text-[#38bdf8] transition  dark:hover:bg-[#38bdf8]/20 cursor-pointer"
                                @click="removeInterval(index, 1)">
                                ✕
                            </button>
                        </div>
                    </template>

                    <!-- Closed -->
                    <template v-else>
                        <p class="text-sm text-zinc-400 italic text-center sm:text-right">
                            Closed
                        </p>
                    </template>
                </div>
            </div>

            <!-- Copy to All Days -->
            <div v-if="index === 0 && day.enabled && day.intervals.length"
                class="mt-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 rounded-md bg-[#38bdf8]/10 px-3 py-2 text-sm text-[#38bdf8] dark:bg-[#38bdf8]/20 dark:text-[#38bdf8]">
                <span>💡 Copy these opening hours to all days?</span>
                <button class="font-medium underline hover:text-[#38bdf8]/90 cursor-pointer" type="button"
                    @click="copyToAll">
                    Yes, copy
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, watch, defineEmits, defineProps, onMounted } from 'vue'
import CheckboxField from '@/Backend/components/CheckboxField.vue'

const emit = defineEmits(['update:modelValue'])
const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  }
})

const defaultDays = [
  { name: 'Monday', enabled: true, intervals: [{ open: '09:00', close: '18:00' }] },
  { name: 'Tuesday', enabled: true, intervals: [{ open: '09:00', close: '18:00' }] },
  { name: 'Wednesday', enabled: true, intervals: [{ open: '09:00', close: '18:00' }] },
  { name: 'Thursday', enabled: true, intervals: [{ open: '09:00', close: '18:00' }] },
  { name: 'Friday', enabled: true, intervals: [{ open: '09:00', close: '18:00' }] },
  { name: 'Saturday', enabled: false, intervals: [{ open: '09:00', close: '18:00' }] },
  { name: 'Sunday', enabled: false, intervals: [{ open: '09:00', close: '18:00' }] },
]

// ✅ create reactive copy
const days = reactive(JSON.parse(JSON.stringify(defaultDays)))

// helper
function deepEqual(a, b) {
  return JSON.stringify(a) === JSON.stringify(b)
}

// ✅ safely rebuild full reactive days from props
function rebuildDays(saved) {
  if (!Array.isArray(saved) || !saved.length) return
  const parsed = defaultDays.map((def, i) => {
    const savedDay = saved.find(d => d.name === def.name)
    return savedDay
      ? {
          name: def.name,
          enabled: savedDay.enabled ?? false,
          intervals: savedDay.intervals?.length
            ? JSON.parse(JSON.stringify(savedDay.intervals))
            : [{ open: '09:00', close: '18:00' }],
        }
      : def
  })
  // replace contents without breaking reactivity
  days.splice(0, days.length, ...parsed)
}

// 1️⃣ On mount — initialize from backend (edit mode)
onMounted(() => {
  if (props.modelValue?.length) {
    rebuildDays(props.modelValue)
  }
})

// 2️⃣ Parent → child update
watch(
  () => props.modelValue,
  (newVal) => {
    if (!deepEqual(newVal, days)) rebuildDays(newVal)
  },
  { deep: true }
)

// 3️⃣ Child → parent update
watch(
  () => days,
  (val) => {
    if (!deepEqual(val, props.modelValue))
      emit('update:modelValue', JSON.parse(JSON.stringify(val)))
  },
  { deep: true }
)

// time options
const timeOptions = []
for (let h = 0; h < 24; h++) {
  for (const m of ['00', '15', '30', '45']) {
    timeOptions.push(`${String(h).padStart(2, '0')}:${m}`)
  }
}

// interval logic
const addInterval = (i) => {
  const list = days[i].intervals
  if (list.length < 2) list.push({ open: '09:00', close: '18:00' })
}
const removeInterval = (i, j) => {
  const list = days[i].intervals
  if (list.length === 1) return
  if (j === 0 && list.length > 1) {
    list[0] = { ...list[1] }
    list.splice(1, 1)
  } else {
    list.splice(j, 1)
  }
}
const copyToAll = () => {
  const base = days[0]
  days.forEach((d, i) => {
    if (i > 0) {
      d.enabled = base.enabled
      d.intervals = JSON.parse(JSON.stringify(base.intervals))
    }
  })
}
</script>




<style scoped>
select {
    min-width: 110px;
}
</style>
