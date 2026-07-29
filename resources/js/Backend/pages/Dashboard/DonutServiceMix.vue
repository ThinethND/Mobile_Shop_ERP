<script setup lang="ts">
import { computed } from 'vue'
import { Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
import type { ChartData, ChartOptions } from 'chart.js'

ChartJS.register(ArcElement, Tooltip, Legend)

const props = withDefaults(defineProps<{
  labels: string[]
  data: number[]
  theme?: 'light' | 'dark'
}>(), {
  theme: 'light'
})

const isLight = computed(() => props.theme === 'light')

const dataCfg = computed<ChartData<'doughnut'>>(() => ({
  labels: props.labels,
  datasets: [{
    data: props.data,
    backgroundColor: ['#38bdf8', '#7dd3fc', '#bae6fd', '#e0f2fe', '#f0f9ff'],
    borderWidth: 0
  }]
}))

const options = computed<ChartOptions<'doughnut'>>(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'bottom',
      labels: { color: isLight.value ? '#334155' : '#fff', boxWidth: 12 }
    },
    tooltip: {
      backgroundColor: isLight.value ? 'rgba(15,23,42,.95)' : 'rgba(0,0,0,.8)',
      titleColor: '#fff',
      bodyColor: '#fff'
    }
  },
  cutout: '70%'
}))
</script>

<template>
  <Doughnut :data="dataCfg" :options="options" />
</template>
