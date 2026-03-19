<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// En el futuro, esto vendrá como prop desde el controlador
const hotels = [
  {
    id: 1,
    name: 'Hotel Sol y Mar',
    plan: 'Premium',
    expiration_date: '2024-12-31',
    revenue_monthly: 4500,
    status: 'Activo'
  },
  {
    id: 2,
    name: 'Posada del Viajero',
    plan: 'Básico',
    expiration_date: '2025-03-15',
    revenue_monthly: 1200,
    status: 'Activo'
  },
  {
    id: 3,
    name: 'Grand Palace Hotel',
    plan: 'Enterprise',
    expiration_date: '2026-08-01',
    revenue_monthly: 15000,
    status: 'Activo'
  },
  {
    id: 4,
    name: 'Hostal La Montaña',
    plan: 'Básico',
    expiration_date: '2024-07-20',
    revenue_monthly: 800,
    status: 'Contrato por vencer'
  },
    {
    id: 5,
    name: 'Resort Paraíso Perdido',
    plan: 'Premium',
    expiration_date: '2023-10-31',
    revenue_monthly: 3200,
    status: 'Inactivo'
  },
];

</script>

<template>
    <Head title="Gestión de Hoteles" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Hoteles y Contratos</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hotel</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nivel de Servicio</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimiento Contrato</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ingresos (Mensual)</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="hotel in hotels" :key="hotel.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ hotel.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ hotel.plan }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ hotel.expiration_date }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">€{{ hotel.revenue_monthly.toLocaleString() }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'bg-green-100 text-green-800': hotel.status === 'Activo',
                                            'bg-yellow-100 text-yellow-800': hotel.status === 'Contrato por vencer',
                                            'bg-red-100 text-red-800': hotel.status === 'Inactivo'
                                        }" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ hotel.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('superadmin.hotels.show', hotel.id)" class="text-indigo-600 hover:text-indigo-900">Ver Detalles</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
