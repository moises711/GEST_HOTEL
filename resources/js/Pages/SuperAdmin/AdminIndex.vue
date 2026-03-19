<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

// --- DATOS DE EJEMPLO ---
// Estos datos serán reemplazados por los que vengan de la base de datos más adelante.
const admins = ref([
  { id: 1, name: 'Admin Uno', email: 'admin1@hotel.com', start_date: '2024-01-01', expiration_date: '2024-12-31' },
  { id: 2, name: 'Admin Dos', email: 'admin2@hotel.com', start_date: '2023-05-15', expiration_date: '2024-05-14' },
  { id: 3, name: 'Admin Tres', email: 'admin3@hotel.com', start_date: '2024-03-01', expiration_date: '2025-02-28' },
]);
</script>

<template>
    <Head title="Gestionar Administradores" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Administradores</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-4 flex justify-end">
                            <Link :href="route('superadmin.create')" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                                Crear Nuevo Admin
                            </Link>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border">
                                <thead class="bg-gray-200">
                                    <tr>
                                        <th class="py-3 px-4 border-b text-left">Nombre</th>
                                        <th class="py-3 px-4 border-b text-left">Email</th>
                                        <th class="py-3 px-4 border-b text-left">Fecha de Inicio</th>
                                        <th class="py-3 px-4 border-b text-left">Fecha de Expiración</th>
                                        <th class="py-3 px-4 border-b text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="admin in admins" :key="admin.id" class="hover:bg-gray-50">
                                        <td class="py-2 px-4 border-b">{{ admin.name }}</td>
                                        <td class="py-2 px-4 border-b">{{ admin.email }}</td>
                                        <td class="py-2 px-4 border-b">{{ admin.start_date }}</td>
                                        <td class="py-2 px-4 border-b">{{ admin.expiration_date }}</td>
                                        <td class="py-2 px-4 border-b text-center">
                                            <Link :href="route('superadmin.edit', admin.id)" class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</Link>
                                            <button @click="() => alert('Función de eliminar pendiente')" class="text-red-600 hover:text-red-900">Eliminar</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
