<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const auditLog = ref([
  { id: 1, event: 'Plan Cambiado', subject: 'Hotel Paraíso Azul', user: 'Super Admin', time: '2024-07-21 10:00 AM', details: { from: 'Pro', to: 'Premium' } },
  { id: 2, event: 'Hotel Suspendido', subject: 'Playa del Sol Resort', user: 'Super Admin', time: '2024-07-21 09:30 AM', details: { reason: 'Falta de pago' } },
  { id: 3, event: 'Admin Invitado', subject: 'Hotel Estelar', user: 'juan.perez@hotel.com', time: '2024-07-20 03:20 PM', details: { invited_email: 'nuevo.gerente@hotel.com' } },
  { id: 4, event: 'Módulo Activado', subject: 'Montaña Mágica Lodge', user: 'Super Admin', time: '2024-07-20 11:00 AM', details: { module: 'Reportes' } },
  { id: 5, event: 'Login Fallido', subject: 'sistema', user: 'unknown', time: '2024-07-20 09:00 AM', details: { ip_address: '192.168.1.100' } },
]);

// --- KPIs ---
const securityEvents = ['Hotel Suspendido', 'Hotel Eliminado', 'Login Fallido'];
const kpis = computed(() => ({
    total: auditLog.value.length,
    security: auditLog.value.filter(log => securityEvents.includes(log.event)).length,
    uniqueUsers: new Set(auditLog.value.map(log => log.user)).size
}));

const getEventClass = (event) => {
  switch (event) {
    case 'Plan Cambiado': return 'bg-blue-100 text-blue-800';
    case 'Hotel Suspendido': return 'bg-yellow-100 text-yellow-800';
    case 'Hotel Eliminado': return 'bg-red-100 text-red-800';
    case 'Login Fallido': return 'bg-red-100 text-red-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};

</script>

<template>
    <Head title="Registro de Auditoría" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Registro de Auditoría</h2>
        </template>

        <!-- Tarjetas de Información -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-sm font-medium text-gray-500">Total de Eventos</h3>
                <p class="text-3xl font-semibold text-gray-900">{{ kpis.total }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-sm font-medium text-gray-500">Eventos de Seguridad</h3>
                <p class="text-3xl font-semibold text-red-600">{{ kpis.security }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-sm font-medium text-gray-500">Usuarios Únicos</h3>
                <p class="text-3xl font-semibold text-blue-600">{{ kpis.uniqueUsers }}</p>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Cronología de Eventos del Sistema</h3>
                    <!-- Aquí se podrían añadir filtros -->
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Evento</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sujeto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Realizado por</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Detalles</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="log in auditLog" :key="log.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                     <span :class="getEventClass(log.event)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ log.event }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">{{ log.subject }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ log.user }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ log.time }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">{{ log.details }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
