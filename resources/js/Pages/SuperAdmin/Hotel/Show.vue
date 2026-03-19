<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// ---- DATOS DE EJEMPLO ----
// En el futuro, el prop `hotel` vendrá del controlador con toda esta información.
const props = {
    hotel: {
        id: 1,
        name: 'Hotel Sol y Mar',
        status: 'Activo',
        contract: {
            plan_name: 'Premium',
            start_date: '2024-01-01',
            end_date: '2024-12-31',
            monthly_rate: 4500,
        },
        modules: [
            { id: 'reservations', name: 'Gestión de Reservas', active: true },
            { id: 'checkin', name: 'Check-in/Check-out Digital', active: true },
            { id: 'billing', name: 'Facturación y Pagos', active: true },
            { id: 'reports', name: 'Módulo de Reportes Avanzados', active: true },
            { id: 'housekeeping', name: 'Gestión de Limpieza', active: false },
            { id: 'channel', name: 'Channel Manager', active: false },
        ],
        reports: {
            occupancy_rate_monthly: 85.2,
            average_daily_rate: 150.75,
            total_revenue_monthly: 55200,
        }
    }
};

// ---- LÓGICA DEL COMPONENTE ----

// Formulario para actualizar el estado de los módulos
const modulesForm = useForm({
    modules: ref(props.hotel.modules).value.reduce((acc, module) => {
        acc[module.id] = module.active;
        return acc;
    }, {})
});

const updateModules = () => {
    // Lógica para enviar el formulario de módulos al servidor
    // modulesForm.put(route('superadmin.hotels.modules.update', props.hotel.id));
    alert('Simulando actualización de módulos...\nDatos: ' + JSON.stringify(modulesForm.data()));
};

</script>

<template>
    <Head :title="`Detalles de ${hotel.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ hotel.name }}</h2>
                <span :class="hotel.status === 'Activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full">
                    {{ hotel.status }}
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- 1. Gestión del Contrato -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Gestión del Contrato</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Nivel de Servicio</p>
                            <p class="mt-1 text-xl font-semibold text-indigo-600">{{ hotel.contract.plan_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Vencimiento</p>
                            <p class="mt-1 text-lg text-gray-900">{{ hotel.contract.end_date }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Tarifa Mensual</p>
                            <p class="mt-1 text-lg text-gray-900">€{{ hotel.contract.monthly_rate.toLocaleString() }}</p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Renovar Contrato</button>
                        <button class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cambiar Plan</button>
                    </div>
                </div>

                <!-- 2. Gestión de Módulos -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Gestión de Módulos</h3>
                    <form @submit.prevent="updateModules">
                        <div class="space-y-4">
                            <div v-for="module in hotel.modules" :key="module.id" class="flex items-center justify-between p-3 border rounded-lg">
                                <span class="text-sm font-medium text-gray-900">{{ module.name }}</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="modulesForm.modules[module.id]" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <button type="submit" :disabled="!modulesForm.isDirty" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:bg-gray-300">Guardar Cambios de Módulos</button>
                        </div>
                    </form>
                </div>

                <!-- 3. Reportes y Estadísticas -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Reportes y Estadísticas (Mensual)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                         <div>
                            <p class="text-sm font-medium text-gray-500">Ingresos Totales</p>
                            <p class="mt-1 text-2xl font-semibold text-gray-800">€{{ hotel.reports.total_revenue_monthly.toLocaleString() }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Tasa de Ocupación</p>
                            <p class="mt-1 text-2xl font-semibold text-gray-800">{{ hotel.reports.occupancy_rate_monthly }}%</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Tarifa Diaria Promedio (ADR)</p>
                            <p class="mt-1 text-2xl font-semibold text-gray-800">€{{ hotel.reports.average_daily_rate.toLocaleString() }}</p>
                        </div>
                    </div>
                     <div class="mt-6 flex justify-end">
                        <button class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Ver Reportes Detallados</button>
                    </div>
                </div>

                 <!-- 4. Zona de Peligro -->
                <div class="bg-red-50 border border-red-200 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-red-900 mb-2">Zona de Peligro</h3>
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-red-800">Desactivar este hotel revocará el acceso a todos sus administradores y detendrá la sincronización de datos.</p>
                        <button class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Desactivar Hotel</button>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
