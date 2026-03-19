<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

// --- DATOS DE EJEMPLO PARA UN HOTEL "PREMIUM" ---
const kpis = {
    occupancy_rate: 78,
    daily_revenue: 8540,
    monthly_revenue: 195200,
    next_checkins: 12,
};

const alerts = [
    { id: 1, type: 'warning', message: 'Reserva #582 pendiente de confirmación.' },
    { id: 2, type: 'error', message: 'Incidencia reportada en la Habitación 301 (fuga de agua).' },
    { id: 3, type: 'info', message: 'Check-in VIP para Sr. García a las 15:00.' },
];

const activeModules = {
    plan: 'Premium',
    modules: [
        { name: 'Reservas', active: true, route: '#' },
        { name: 'Check-in/Out', active: true, route: '#' },
        { name: 'Clientes', active: true, route: '#' },
        { name: 'Finanzas', active: true, route: '#' },
        { name: 'Reportes Avanzados', active: true, route: '#' },
        { name: 'Fidelización de Clientes', active: true, route: '#' },
        { name: 'Channel Manager', active: false, route: '#' },
        { name: 'Analítica Avanzada', active: false, route: '#' },
    ]
};

// Placeholder para un futuro gráfico
// Por ahora, solo es una imagen estática para la maqueta.
const chartUrl = `https://placehold.co/600x250/E9EBFB/4F46E5?text=Gráfico+de+Ocupación+(%25)`;

</script>

<template>
    <Head title="Dashboard del Hotel" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Principal del Hotel</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- 1. KPIs Principales -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500">Ocupación Actual</h3>
                        <p class="mt-1 text-3xl font-semibold text-gray-900">{{ kpis.occupancy_rate }}%</p>
                    </div>
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500">Ingresos del Día</h3>
                        <p class="mt-1 text-3xl font-semibold text-gray-900">€{{ kpis.daily_revenue.toLocaleString() }}</p>
                    </div>
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500">Ingresos del Mes</h3>
                        <p class="mt-1 text-3xl font-semibold text-gray-900">€{{ kpis.monthly_revenue.toLocaleString() }}</p>
                    </div>
                    <div class="bg-blue-50 p-6 shadow-sm sm:rounded-lg border border-blue-200">
                        <h3 class="text-sm font-medium text-blue-800">Próximos Check-ins (24h)</h3>
                        <p class="mt-1 text-3xl font-semibold text-blue-900">{{ kpis.next_checkins }}</p>
                    </div>
                </div>

                <!-- Gráfico (Placeholder) y Alertas -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white p-6 shadow-sm sm:rounded-lg">
                         <h3 class="text-lg font-medium text-gray-900 mb-4">Rendimiento de Ocupación Mensual</h3>
                         <img :src="chartUrl" alt="Gráfico de Ocupación" class="w-full h-auto rounded-lg">
                    </div>
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Alertas y Notificaciones</h3>
                        <div class="space-y-3">
                            <div v-for="alert in alerts" :key="alert.id" class="p-3 rounded-md"
                                :class="{
                                    'bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800': alert.type === 'warning',
                                    'bg-red-100 border-l-4 border-red-500 text-red-800': alert.type === 'error',
                                    'bg-blue-100 border-l-4 border-blue-500 text-blue-800': alert.type === 'info'
                                }">
                                <p class="text-sm">{{ alert.message }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Módulos del Plan -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Módulos de su Plan: <span class="text-indigo-600 font-semibold">{{ activeModules.plan }}</span></h3>
                    <p class="text-sm text-gray-600 mb-4">Estos son los módulos disponibles para su nivel de servicio. Los módulos inactivos pueden ser habilitados por un superadministrador.</p>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <Link v-for="module in activeModules.modules" :key="module.name" :href="module.route" 
                            :class="['p-4 rounded-lg text-center transition-all duration-150', 
                            module.active ? 'bg-green-100 text-green-900 hover:bg-green-200' : 'bg-gray-100 text-gray-500 cursor-not-allowed']"
                            :disabled="!module.active"
                            :is="module.active ? 'Link' : 'div'">
                            <span class="font-semibold text-sm">{{ module.name }}</span>
                            <span v-if="!module.active" class="text-xs block">(No incluido)</span>
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
