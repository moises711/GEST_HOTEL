<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ChartBar from '@/Components/ChartBar.vue';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const availableYears = [2026, 2025, 2024];
const selectedYear = ref(2026);

const chartData = ref({
    labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
    datasets: [
        {
            label: 'Ingresos Mensuales',
            backgroundColor: '#3B82F6',
            data: [40, 20, 12, 39, 10, 40, 39, 80, 40, 20, 12, 11]
        }
    ]
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false }
    }
};

const page = usePage();

const metrics = computed(() => page?.props?.value?.metrics || { total_hotels: 0, total_revenue: 0, new_hotels: 0, suspended: 0 });
const recent = computed(() => page?.props?.value?.recent || []);

const totalRevenue = computed(() => metrics.value.total_revenue || 0);
const averageRevenue = computed(() => Math.round((totalRevenue.value || 0) / (chartData.value.labels.length || 12)));

function exportCsv() {
    const rows = [ ['Mes', 'Ingresos'], ...chartData.value.labels.map((m, i) => [m, chartData.value.datasets[0].data[i]]) ];
    const csv = rows.map(r => r.join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `ingresos_${selectedYear.value}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}
</script>

<template>
    <Head title="Resumen Global" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Resumen Global</h2>
        </template>

        <!-- Bienvenida -->
        <div class="mb-6">
            <div class="bg-gradient-to-r from-indigo-600 to-teal-500 text-white rounded-lg p-6 shadow-md flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-white/20 flex items-center justify-center text-xl font-bold">{{ $page.props.auth.user.name.split(' ').map(n => n[0]).join('').slice(0,2) }}</div>
                    <div>
                        <div class="text-lg font-semibold">{{ $page.props.auth.user.name }}</div>
                        <div class="text-sm opacity-90">You're logged in!</div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('profile.edit')" class="bg-white/20 hover:bg-white/30 text-white px-3 py-1 rounded">Perfil</Link>
                    <Link :href="route('logout')" method="post" class="bg-white text-indigo-700 px-3 py-1 rounded font-medium">Cerrar Sesión</Link>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Métricas Principales -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total de Hoteles -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 transform hover:scale-105 transition-transform duration-300">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-500 text-white rounded-full">
                            <!-- Icono: Edificio/Hotel -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h6m-6 4h6m-6 4h6"></path></svg>
                        </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total de Hoteles</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ metrics.total_hotels || metrics.total_hotels === 0 ? metrics.total_hotels : '-' }}</p>
                                </div>
                    </div>
                </div>

                <!-- Ingresos Anuales -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 transform hover:scale-105 transition-transform duration-300">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-500 text-white rounded-full">
                            <!-- Icono: Dinero -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Ingresos Anuales</p>
                            <p class="text-2xl font-bold text-gray-900">${{ metrics.total_revenue || 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Nuevos Hoteles (Mes) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 transform hover:scale-105 transition-transform duration-300">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-500 text-white rounded-full">
                           <!-- Icono: Nuevo/Estrella -->
                           <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Nuevos Hoteles (Mes)</p>
                            <p class="text-2xl font-bold text-gray-900">{{ metrics.new_hotels ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Hoteles Suspendidos -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 transform hover:scale-105 transition-transform duration-300">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-500 text-white rounded-full">
                            <!-- Icono: Alerta/Peligro -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Hoteles Suspendidos</p>
                            <p class="text-2xl font-bold text-gray-900">{{ metrics.suspended ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controles rápidos: año, total y exportar -->
            <div class="bg-white shadow-sm sm:rounded-lg p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <label class="text-sm text-gray-600">Año:</label>
                    <select v-model="selectedYear" class="border rounded px-2 py-1 text-sm">
                        <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>

                <div class="flex items-center gap-6">
                    <div class="text-sm text-gray-500">
                        <div>Total (año):</div>
                        <div class="text-lg font-bold text-gray-900">{{ totalRevenue }}</div>
                    </div>
                    <div class="text-sm text-gray-500">
                        <div>Promedio / mes:</div>
                        <div class="text-lg font-bold text-gray-900">{{ averageRevenue }}</div>
                    </div>
                </div>

                <div class="flex items-center">
                    <button @click="exportCsv" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">Exportar CSV</button>
                </div>
            </div>

            <!-- Gráfico de Ingresos y Tabla de Hoteles Recientes -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Gráfico de Ingresos -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Ingresos Mensuales</h3>
                    <div class="h-96">
                        <ChartBar :chartData="chartData" :chartOptions="chartOptions" />
                    </div>
                </div>

                <!-- Hoteles Recientes -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Hoteles Recientes</h3>
                    <ul class="divide-y divide-gray-200">
                        <li v-for="r in recent" :key="r.id" class="py-3 flex items-center justify-between">
                            <div class="truncate">
                                <p class="font-medium text-gray-900">{{ r.name }}</p>
                                <p class="text-sm text-gray-500">{{ r.created_at }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold" :class="r.status === 'Activo' ? 'text-green-800 bg-green-200' : (r.status === 'Pendiente' ? 'text-yellow-800 bg-yellow-200' : 'text-red-800 bg-red-200')">{{ r.status }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
