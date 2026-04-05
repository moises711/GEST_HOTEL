<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue';
import { Chart } from 'chart.js/auto';

const props = defineProps({
  chartData: { type: Object, required: true },
  chartOptions: { type: Object, required: false, default: () => ({}) },
  type: { type: String, default: 'bar' }
});

const canvas = ref(null);
let chartInstance = null;

onMounted(() => {
  if (!canvas.value) return;
  const ctx = canvas.value.getContext('2d');
  chartInstance = new Chart(ctx, {
    type: props.type,
    data: props.chartData,
    options: props.chartOptions,
  });
});

onBeforeUnmount(() => {
  if (chartInstance) {
    chartInstance.destroy();
    chartInstance = null;
  }
});
</script>

<template>
  <div class="w-full h-full">
    <canvas ref="canvas" class="w-full h-full"></canvas>
  </div>
</template>
