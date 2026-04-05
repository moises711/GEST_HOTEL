<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement);

const props = defineProps({
        metrics: { type: Object, default: () => ({ total_month: 0, top_plan: 'Sin datos', renewal_rate: 0 }) },
        invoices: { type: Array, default: () => [] },
        monthly: { type: Array, default: () => [] },
});

const chartData = computed(() => ({
        labels: (props.monthly || []).map(item => item.label),
        datasets: [
                {
                        label: 'Ingresos por mes',
                        backgroundColor: '#818CF8',
                        borderColor: '#4F46E5',
                        data: (props.monthly || []).map(item => Number(item.amount || 0)),
                        tension: 0.3,
                },
        ],
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false
};

const money = (value) => `S/ ${Number(value || 0).toLocaleString('es-PE')}`;

const getStatusClass = (status) => {
  switch (status) {
    case 'Pagado': return 'bg-green-100 text-green-800';
    case 'Pendiente': return 'bg-yellow-100 text-yellow-800';
    case 'Vencido': return 'bg-red-100 text-red-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};
</script>

<template>
    <Head title="Ingresos y Facturación" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ingresos y Facturación</h2>
        </template>

        <div class="space-y-6">
            <!-- Métricas de Ingresos -->
             <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900">Ingresos Totales (Mes)</h3>
                    <p class="mt-1 text-3xl font-semibold text-gray-700">{{ money(props.metrics?.total_month) }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900">Planes Más Vendidos</h3>
                    <p class="mt-1 text-2xl font-semibold text-gray-700">{{ props.metrics?.top_plan || 'Sin datos' }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900">Tasa de Renovación</h3>
                    <p class="mt-1 text-3xl font-semibold text-gray-700">{{ props.metrics?.renewal_rate ?? 0 }}%</p>
                </div>
            </div>

            <!-- Gráfico y Historial de Pagos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Gráfico de Ingresos por Hotel -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Ingresos por Hotel</h3>
                    <div class="h-80">
                        <Line :data="chartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Historial de Pagos -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Historial de Pagos</h3>
                    <ul class="divide-y divide-gray-200">
                        <li v-for="invoice in props.invoices" :key="invoice.id" class="py-3 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ invoice.hotel }}</p>
                                <p class="text-sm text-gray-500">{{ invoice.id }} - {{ invoice.date }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-800">{{ money(invoice.amount) }}</p>
                                <span :class="getStatusClass(invoice.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                    {{ invoice.status }}
                                </span>
                            </div>
                        </li>
                        <li v-if="!props.invoices || props.invoices.length === 0" class="py-3 text-sm text-gray-500">No hay facturación registrada.</li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
