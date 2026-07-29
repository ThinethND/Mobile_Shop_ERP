<script setup lang="ts">
import { computed } from 'vue'
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale, LinearScale, PointElement, LineElement, Filler,
  Tooltip, Legend
} from 'chart.js'
import type { ChartData, ChartOptions } from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip, Legend)

const props = defineProps<{
  labels: string[]
  data: number[]
  label?: string
  theme?: 'light' | 'dark'
}>()

const isLight = computed(() => props.theme === 'light')

const chartData = computed<ChartData<'line'>>(() => ({
  labels: props.labels,
  datasets: [{
    label: props.label ?? 'Bookings',
    data: props.data,
    borderColor: '#38bdf8',
    backgroundColor(ctx) {
      const { chart } = ctx
      const area = chart.chartArea
      if (!area) return isLight.value ? 'rgba(56,189,248,.12)' : 'rgba(56,189,248,.35)'
      const g = chart.ctx.createLinearGradient(0, area.top, 0, area.bottom)
      g.addColorStop(0, isLight.value ? 'rgba(56,189,248,.22)' : 'rgba(56,189,248,.35)')
      g.addColorStop(1, 'rgba(56,189,248,0)')
      return g
    },
    tension: 0.35,
    fill: true,
    pointRadius: 2
  }]
}))

const tick = computed(() => (isLight.value ? '#334155' : '#ffffff99'))
const grid = computed(() => (isLight.value ? 'rgba(0,0,0,.06)' : 'rgba(255,255,255,.06)'))

const options = computed<ChartOptions<'line'>>(() => ({
  responsive: true,
  maintainAspectRatio: false,

  interaction: { mode: 'index', intersect: false },

  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: isLight.value ? 'rgba(15,23,42,.95)' : 'rgba(0,0,0,.8)',
      titleColor: '#fff',
      bodyColor: '#fff'
    }
  },
  scales: {
    x: { ticks: { color: tick.value }, grid: { color: grid.value } },
    y: { ticks: { color: tick.value }, grid: { color: grid.value } }
  }
}))
</script>

<template>
  <Line :data="chartData" :options="options" />
</template>
