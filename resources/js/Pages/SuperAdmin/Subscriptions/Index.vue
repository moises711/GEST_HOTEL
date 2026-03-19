<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const subscriptions = [
  { id: 1, hotel: 'Hotel Paraíso Azul', plan: 'Premium', expires_at: '2024-08-15', days_left: 20, status: 'por_vencer' },
  { id: 2, hotel: 'Montaña Mágica Lodge', plan: 'Básico', expires_at: '2024-07-30', days_left: 4, status: 'por_vencer' },
  { id: 3, hotel: 'Playa del Sol Resort', plan: 'Pro', expires_at: '2024-07-25', days_left: 0, status: 'vencido' },
  { id: 4, hotel: 'Hotel Estelar', plan: 'Premium', expires_at: '2024-09-10', days_left: 45, status: 'activo' },
];

const getStatusPillInfo = (status, days_left) => {
  if (status === 'vencido') {
    return { text: 'Vencido', class: 'bg-red-100 text-red-800' };
  }
  if (status === 'por_vencer') {
    return { text: `Vence en ${days_left} días`, class: 'bg-yellow-100 text-yellow-800' };
  }
  return { text: `Activo - ${days_left} días restantes`, class: 'bg-green-100 text-green-800' };
};
</script>

<template>
    <Head title="Control de Suscripciones" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Control de Suscripciones</h2>
        </template>

        <div class="space-y-6">

            <!-- Alertas de Suscripciones -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Hoteles por Vencer -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-yellow-800">Hoteles por Vencer</h3>
                    <p class="mt-1 text-3xl font-semibold text-yellow-900">2</p>
                </div>
                <!-- Hoteles Vencidos -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-red-800">Hoteles Vencidos</h3>
                    <p class="mt-1 text-3xl font-semibold text-red-900">1</p>
                </div>
            </div>

            <!-- Tabla de Suscripciones -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Estado de Suscripciones</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hotel</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Vencimiento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="sub in subscriptions" :key="sub.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ sub.hotel }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sub.plan }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sub.expires_at }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusPillInfo(sub.status, sub.days_left).class" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ getStatusPillInfo(sub.status, sub.days_left).text }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900">Renovar</button>
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
