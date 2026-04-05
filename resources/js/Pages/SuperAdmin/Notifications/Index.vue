<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const notifications = {
  vencimiento: [
    { hotel: 'Playa del Sol Resort', message: 'La suscripción vence en 3 días.', time: 'hace 1 hora' },
    { hotel: 'Montaña Mágica Lodge', message: 'La suscripción vence en 1 día.', time: 'hace 5 horas' },
  ],
  pago: [
    { hotel: 'Hotel Estelar', message: 'El pago automático falló.', time: 'hace 2 días' }
  ]
};
</script>

<template>
    <Head title="Centro de Notificaciones" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Centro de Notificaciones</h2>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Alertas de Vencimiento -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-yellow-800 bg-yellow-100 p-3 rounded-lg">Alertas de Vencimiento</h3>
                    <ul class="mt-4 space-y-4">
                        <li v-for="(notif, index) in notifications.vencimiento" :key="index" class="p-4 border-l-4 border-yellow-400 bg-yellow-50 rounded-r-lg">
                            <p class="font-semibold text-yellow-900">{{ notif.hotel }}</p>
                            <p class="text-sm text-yellow-700">{{ notif.message }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ notif.time }}</p>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Problemas de Pago -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-red-800 bg-red-100 p-3 rounded-lg">Problemas de Pago</h3>
                    <ul class="mt-4 space-y-4">
                        <li v-for="(notif, index) in notifications.pago" :key="index" class="p-4 border-l-4 border-red-400 bg-red-50 rounded-r-lg">
                           <p class="font-semibold text-red-900">{{ notif.hotel }}</p>
                            <p class="text-sm text-red-700">{{ notif.message }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ notif.time }}</p>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
