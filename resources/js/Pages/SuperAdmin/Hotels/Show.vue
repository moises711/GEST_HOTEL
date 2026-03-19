<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import { ref } from 'vue';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

// --- Datos de Ejemplo (reemplazar con datos reales de la API) ---
const hotel = ref({
  name: 'Hotel Paraíso Azul',
  owner: 'Juan Pérez',
  email: 'juan.perez@hotel.com',
  registered_at: '2023-01-15',
  plan: 'Premium',
  expires_at: '2024-12-31',
  auto_renew: true,
  modules: [
    { name: 'Reservas', active: true },
    { name: 'Facturación', active: true },
    { name: 'Reportes', active: true },
    { name: 'Gestión de Eventos', active: false },
  ],
  admins: [
    { name: 'Admin Principal', email: 'admin1@hotel.com' },
    { name: 'Gerente Nocturno', email: 'admin2@hotel.com' },
  ]
});

const chartData = {
  labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
  datasets: [
    {
      label: 'Ocupación (%)',
      backgroundColor: '#3B82F6',
      data: [65, 59, 80, 81, 56, 70]
    }
  ]
};

const chartOptions = { responsive: true, maintainAspectRatio: false };

</script>

<template>
    <Head :title="`Detalles de ${hotel.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ hotel.name }}</h2>
        </template>

        <div class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Columna Principal -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- 1. Suscripción y Plan -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Suscripción y Plan</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Plan Actual</p>
                                <p class="text-xl font-semibold text-blue-600">{{ hotel.plan }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Vencimiento</p>
                                <p class="text-xl font-semibold">{{ hotel.expires_at }}</p>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t flex justify-between items-center">
                            <div class="flex items-center">
                                <input type="checkbox" v-model="hotel.auto_renew" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="auto_renew" class="ml-2 block text-sm text-gray-900">Renovación Automática</label>
                            </div>
                            <div>
                                <button class="px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 mr-2">Cambiar Plan</button>
                                <button class="px-4 py-2 text-sm text-red-600 border border-red-300 rounded-md hover:bg-red-50">Cancelar Suscripción</button>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Módulos Activos -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Módulos Contratados</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div v-for="mod in hotel.modules" :key="mod.name" class="flex items-center justify-between p-3 rounded-lg" :class="mod.active ? 'bg-green-50' : 'bg-gray-100'">
                                <span class="font-medium" :class="mod.active ? 'text-green-800' : 'text-gray-600'">{{ mod.name }}</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="mod.active" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                     <!-- Reporte de Uso -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Reporte de Uso (Últimos 6 Meses)</h3>
                        <div class="h-64">
                            <Bar :data="chartData" :options="chartOptions" />
                        </div>
                    </div>
                </div>

                <!-- Columna Lateral -->
                <div class="space-y-6">
                    <!-- 3. Información de Contacto -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Información del Hotel</h3>
                        <div class="space-y-2 text-sm">
                            <p><span class="font-semibold">Propietario:</span> {{ hotel.owner }}</p>
                            <p><span class="font-semibold">Email:</span> {{ hotel.email }}</p>
                            <p><span class="font-semibold">Miembro desde:</span> {{ hotel.registered_at }}</p>
                        </div>
                    </div>
                    
                    <!-- 4. Administradores -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                         <h3 class="text-lg font-medium text-gray-900 mb-4">Administradores</h3>
                         <ul class="space-y-3">
                             <li v-for="admin in hotel.admins" :key="admin.email" class="flex justify-between items-center text-sm">
                                 <span>{{ admin.name }} <span class="text-gray-500">({{ admin.email }})</span></span>
                                 <button class-><svg class="w-4 h-4 text-gray-500 hover:text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z" clip-rule="evenodd"></path></svg></button>
                             </li>
                         </ul>
                         <button class="mt-4 w-full px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-900">+ Invitar Administrador</button>
                    </div>

                    <!-- 5. Zona de Peligro -->
                    <div class="bg-red-50 border border-red-200 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-red-900 mb-2">Zona de Peligro</h3>
                        <div class="space-y-3">
                            <button class="w-full px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">Suspender Hotel</button>
                            <button class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Eliminar Hotel</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>