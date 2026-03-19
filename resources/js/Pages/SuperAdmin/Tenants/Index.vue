<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const tenants = ref([
  { id: 'tenant_paraíso_azul', hotel: 'Hotel Paraíso Azul', db_name: 'db_hotel_1', status: 'active' },
  { id: 'tenant_montaña_mágica', hotel: 'Montaña Mágica Lodge', db_name: 'db_hotel_2', status: 'active' },
  { id: 'tenant_playa_sol', hotel: 'Playa del Sol Resort', db_name: 'db_hotel_3', status: 'inactive' },
  { id: 'tenant_estelar', hotel: 'Hotel Estelar', db_name: 'db_hotel_4', status: 'active' },
]);

// --- KPIs ---
const kpis = computed(() => ({
    total: tenants.value.length,
    active: tenants.value.filter(t => t.status === 'active').length,
    inactive: tenants.value.filter(t => t.status === 'inactive').length
}));

</script>

<template>
    <Head title="Control de Usuarios" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Control de Usuarios</h2>
        </template>

        <!-- Tarjetas de Información -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-sm font-medium text-gray-500">Total de Usuarios</h3>
                <p class="text-3xl font-semibold text-gray-900">{{ kpis.total }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-sm font-medium text-gray-500">Usuarios Activos</h3>
                <p class="text-3xl font-semibold text-green-600">{{ kpis.active }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-sm font-medium text-gray-500">Usuarios Inactivos</h3>
                <p class="text-3xl font-semibold text-red-600">{{ kpis.inactive }}</p>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Gestión de Instancias de Hotel</h3>
                    <button class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                        + Crear Usuario
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID de Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hotel Asociado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre DB</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="tenant in tenants" :key="tenant.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">{{ tenant.id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ tenant.hotel }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">{{ tenant.db_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="tenant.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ tenant.status === 'active' ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <button class="text-indigo-600 hover:text-indigo-900">Actualizar</button>
                                    <button class="text-red-600 hover:text-red-900">Ejecutar Migraciones</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
