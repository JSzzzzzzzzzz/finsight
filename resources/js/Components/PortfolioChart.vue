<script setup>
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'
import { Line } from 'vue-chartjs'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const props = defineProps({
  data: Array
});

const chartData = {
  labels: props.data.map(i => i.date),
  datasets: [
    {
      label: 'Portfolio Value (RM)',
      data: props.data.map(i => i.value),

      borderColor: '#3b82f6',
      backgroundColor: 'rgba(59,130,246,0.15)',

      fill: true,
      tension: 0.4,

      pointRadius: 4,
      pointHoverRadius: 6,

      borderWidth: 2
    }
  ]
};

const options = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false // Hide label to look cleaner
    }
  },
  scales: {
    y: {
      ticks: {
        color: '#9CA3AF',
        callback: value => 'RM ' + value.toFixed(2)
      },
      grid: {
        color: 'rgba(255,255,255,0.05)'
      }
    },
    x: {
      grid: {
        display: false
      }
    }
  }
}
</script>

<template>
  <div class="h-64 w-full">
    <Line :data="chartData" :options="options" />
  </div>
</template>