<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// Datos de ejemplo que vendrían del backend
const summary = {
    total_revenue_monthly: 21500,
    active_hotels: 4,
    contracts_expiring_soon: 1,
};

</script>

<template>
    <Head title="SuperAdmin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard del Super Administrador</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Sección de Resumen -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900">Ingresos Mensuales Totales</h3>
                        <p class="mt-1 text-3xl font-semibold text-gray-700">€{{ summary.total_revenue_monthly.toLocaleString() }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900">Hoteles Activos</h3>
                        <p class="mt-1 text-3xl font-semibold text-gray-700">{{ summary.active_hotels }}</p>
                    </div>
                    <div class="bg-yellow-100 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-yellow-900">Contratos por Vencer</h3>
                        <p class="mt-1 text-3xl font-semibold text-yellow-800">{{ summary.contracts_expiring_soon }}</p>
                        <p class="text-sm text-yellow-700">En los próximos 90 días</p>
                    </div>
                </div>

                <!-- Sección de Acciones Principales -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Acciones Principales</h3>
                        <div class="flex flex-wrap gap-4">
                            <Link
                                :href="route('superadmin.hotels.index')"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Gestionar Hoteles y Contratos
                            </Link>
                            <Link
                                :href="route('superadmin.admins.index')"
                                class="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Gestionar Administradores
                            </Link>
                            <Link
                                :href="route('rooms.index')"
                                class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Ver todas las Habitaciones
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
