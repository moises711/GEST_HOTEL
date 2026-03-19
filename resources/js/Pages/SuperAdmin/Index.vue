<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// En el futuro, esto vendrá como prop desde el controlador
const admins = [
  {
    id: 1,
    name: 'Juan Perez',
    email: 'juan.perez@hotelx.com',
    start_date: '2023-01-15',
    expiration_date: '2025-01-15',
    status: 'Activo'
  },
  {
    id: 2,
    name: 'Ana Gomez',
    email: 'ana.gomez@hotely.com',
    start_date: '2022-11-01',
    expiration_date: '2024-11-01',
    status: 'Activo'
  },
  {
    id: 3,
    name: 'Carlos Ruiz',
    email: 'carlos.ruiz@hotelz.com',
    start_date: '2023-05-20',
    expiration_date: '2024-05-20',
    status: 'Expirado'
  },
];

</script>

<template>
    <Head title="Gestión de Administradores" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Administradores</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-end mb-4">
                        <Link
                            :href="route('superadmin.admins.create')"
                            class="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            Crear Nuevo Administrador
                        </Link>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Alta</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Expiración</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="admin in admins" :key="admin.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ admin.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ admin.email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ admin.start_date }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ admin.expiration_date }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="admin.status === 'Activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ admin.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('superadmin.admins.edit', admin.id)" class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</Link>
                                        <a href="#" @click.prevent="alert('Funcionalidad de eliminar pendiente.')" class="text-red-600 hover:text-red-900">Eliminar</a>
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
