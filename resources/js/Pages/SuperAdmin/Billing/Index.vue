<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement);

const chartData = {
  labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio'],
  datasets: [
    {
      label: 'Ingresos por Hotel',
      backgroundColor: '#818CF8',
      borderColor: '#4F46E5',
      data: [65, 59, 80, 81, 56, 55, 40],
      tension: 0.3
    }
  ]
};

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false
};

const invoices = [
    { id: 'INV-001', hotel: 'Hotel Paraíso Azul', amount: '$500.00', date: '2024-07-20', status: 'Pagado' },
    { id: 'INV-002', hotel: 'Montaña Mágica Lodge', amount: '$250.00', date: '2024-07-18', status: 'Pagado' },
    { id: 'INV-003', hotel: 'Playa del Sol Resort', amount: '$350.00', date: '2024-07-15', status: 'Pendiente' },
    { id: 'INV-004', hotel: 'Hotel Estelar', amount: '$500.00', date: '2024-07-12', status: 'Vencido' },
];

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
                    <p class="mt-1 text-3xl font-semibold text-gray-700">$15,250</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900">Planes Más Vendidos</h3>
                    <p class="mt-1 text-2xl font-semibold text-gray-700">Premium</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900">Tasa de Renovación</h3>
                    <p class="mt-1 text-3xl font-semibold text-gray-700">92%</p>
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
                        <li v-for="invoice in invoices" :key="invoice.id" class="py-3 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ invoice.hotel }}</p>
                                <p class="text-sm text-gray-500">{{ invoice.id }} - {{ invoice.date }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-800">{{ invoice.amount }}</p>
                                <span :class="getStatusClass(invoice.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                    {{ invoice.status }}
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
