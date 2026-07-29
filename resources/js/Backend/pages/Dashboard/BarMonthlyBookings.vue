<script setup lang="ts">
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip, Legend } from 'chart.js'
ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend)

const props = defineProps<{
  labels: string[]
  data: number[]
  label?: string
  theme?: 'light' | 'dark'
}>()

const isLight = computed(() => props.theme === 'light')
const tick = computed(() => isLight.value ? '#334155' : '#ffffff99')
const grid = computed(() => isLight.value ? 'rgba(0,0,0,.06)' : 'rgba(255,255,255,.06)')

const dataCfg = computed(() => ({
  labels: props.labels,
  datasets: [{
    label: props.label ?? 'Bookings',
    data: props.data,
    backgroundColor: isLight.value ? 'rgba(56,189,248,.75)' : 'rgba(56,189,248,.7)',
    borderColor: '#38bdf8',
    borderWidth: 1,
    borderRadius: 8
  }]
}))

const options = computed(() => ({
  responsive: true, maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: isLight.value ? 'rgba(15,23,42,.95)' : 'rgba(0,0,0,.8)',
      titleColor: '#fff', bodyColor: '#fff'
    }
  },
  scales: {
    x: { ticks: { color: tick.value }, grid: { display: false } },
    y: { ticks: { color: tick.value }, grid: { color: grid.value } }
  }
}))
</script>

<template><Bar :data="dataCfg" :options="options" /></template>
