<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const hotels = [
  { id: 1, name: 'Hotel Paraíso Azul', owner: 'Juan Pérez', email: 'juan.perez@hotel.com', status: 'activo', plan: 'Premium', expires_at: '2024-12-31' },
  { id: 2, name: 'Montaña Mágica Lodge', owner: 'Ana Gómez', email: 'ana.gomez@hotel.com', status: 'activo', plan: 'Básico', expires_at: '2024-11-15' },
  { id: 3, name: 'Playa del Sol Resort', owner: 'Carlos Ruíz', email: 'carlos.ruiz@hotel.com', status: 'suspendido', plan: 'Pro', expires_at: '2024-10-01' },
  { id: 4, name: 'Hotel Estelar', owner: 'Laura Fernandez', email: 'laura.fernandez@hotel.com', status: 'vencido', plan: 'Premium', expires_at: '2024-09-20' },
];

const getStatusClass = (status) => {
  switch (status) {
    case 'activo': return 'bg-green-100 text-green-800';
    case 'suspendido': return 'bg-yellow-100 text-yellow-800';
    case 'vencido': return 'bg-red-100 text-red-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};
</script>

<template>
    <Head title="Gestión de Hoteles" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Hoteles</h2>
        </template>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Listado de Hoteles</h3>
                    <Link :href="route('superadmin.hotels.create')" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                        + Nuevo Hotel
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
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
                            <tr v-for="hotel in hotels" :key="hotel.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ hotel.name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ hotel.owner }}</div>
                                    <div class="text-sm text-gray-500">{{ hotel.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatusClass(hotel.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ hotel.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ hotel.plan }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ hotel.expires_at }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <Link :href="route('superadmin.hotels.show', hotel.id)" class="text-blue-600 hover:text-blue-900">Ver</Link>
                                    <Link :href="route('superadmin.hotels.edit', hotel.id)" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                    <button class="text-red-600 hover:text-red-900">Suspender</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
