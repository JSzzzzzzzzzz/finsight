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

// const data = {
//   labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
//   datasets: [
//     {
//       label: 'Portfolio Value (RM)',
//       backgroundColor: 'rgba(59, 130, 246, 0.2)', // Blue with transparency
//       borderColor: '#3b82f6', // Solid Blue
//       pointBackgroundColor: '#3b82f6',
//       borderWidth: 2,
//       fill: true, // Makes it look fancy
//       tension: 0.4, // Makes lines smooth/curved
//       data: [props.data.map(i => i.value)]
//     }
//   ]
// }

const chartData = {
  labels: props.data.map(i => i.date),
  datasets: [
    {
      label: 'Portfolio Value (RM)',
      data: props.data.map(i => i.value),
      backgroundColor: 'rgba(59, 130, 246, 0.2)',
      borderColor: '#3b82f6',
      pointBackgroundColor: '#3b82f6',
      borderWidth: 2,
      fill: true,
      tension: 0.4,
      pointRadius: 5
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
        font: {
          size: 12
        },
        callback: function (value) {
          return 'RM ' + value;
        }
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