<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

// Datos de ejemplo
const hotels = [
  { id: 1, name: 'Hotel Paraíso', owner: 'Juan Pérez', email: 'juan@hotelparaiso.com', status: 'activo', plan: 'Premium', expires_at: '2024-12-31' },
  { id: 2, name: 'Resort Montaña', owner: 'Ana Gómez', email: 'ana@resortmontana.com', status: 'suspendido', plan: 'Básico', expires_at: '2024-11-20' },
  { id: 3, name: 'Hotel Central', owner: 'Luis Torres', email: 'luis@hotelcentral.com', status: 'vencido', plan: 'Pro', expires_at: '2024-10-15' },
];

</script>

<template>
    <Head title="Gestión de Hoteles" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Hoteles</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre del Hotel</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Propietario</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimiento</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="hotel in hotels" :key="hotel.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ hotel.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ hotel.owner }} / {{ hotel.email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'bg-green-100 text-green-800': hotel.status === 'activo',
                                            'bg-red-100 text-red-800': hotel.status === 'suspendido',
                                            'bg-yellow-100 text-yellow-800': hotel.status === 'vencido',
                                        }" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ hotel.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ hotel.plan }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ hotel.expires_at }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="#" class="text-indigo-600 hover:text-indigo-900">Ver</a>
                                        <a href="#" class="ml-4 text-indigo-600 hover:text-indigo-900">Editar</a>
                                        <a href="#" class="ml-4 text-red-600 hover:text-red-900">Suspender</a>
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
