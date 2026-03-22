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

const data = {
  labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
  datasets: [
    {
      label: 'Portfolio Value (RM)',
      backgroundColor: 'rgba(59, 130, 246, 0.2)', // Blue with transparency
      borderColor: '#3b82f6', // Solid Blue
      pointBackgroundColor: '#3b82f6',
      borderWidth: 2,
      fill: true, // Makes it look fancy
      tension: 0.4, // Makes lines smooth/curved
      data: [12000, 12150, 12100, 12300, 12250, 12400, 12450]
    }
  ]
}

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
      grid: {
        display: true,
        borderDash: [5, 5]
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
    <Line :data="data" :options="options" />
  </div>
</template>