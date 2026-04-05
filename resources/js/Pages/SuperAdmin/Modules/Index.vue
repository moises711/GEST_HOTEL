<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const modules = ref([
  { id: 1, name: 'Reservas', active: true },
  { id: 2, name: 'Habitaciones', active: true },
  { id: 3, name: 'Facturación', active: true },
  { id: 4, name: 'Reportes', active: false },
  { id: 5, name: 'Usuarios', active: true },
]);

const plans = [
    { name: 'Básico', modules: ['Reservas', 'Habitaciones'] },
    { name: 'Pro', modules: ['Reservas', 'Habitaciones', 'Facturación'] },
    { name: 'Premium', modules: ['Reservas', 'Habitaciones', 'Facturación', 'Reportes', 'Usuarios'] },
];

</script>

<template>
    <Head title="Gestión de Módulos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Módulos</h2>
        </template>

        <div class="space-y-6">
            <!-- Lista de Módulos del Sistema -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Módulos del Sistema</h3>
                        <button class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                           + Crear Módulo
                        </button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div v-for="mod in modules" :key="mod.id" class="p-4 border rounded-lg flex items-center justify-between" :class="mod.active ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200'">
                            <span class="font-medium" :class="mod.active ? 'text-green-800' : 'text-gray-600'">{{ mod.name }}</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="mod.active" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 dark:bg-gray-700 peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Módulos por Plan -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Módulos por Plan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div v-for="plan in plans" :key="plan.name" class="p-4 border rounded-lg">
                            <h4 class="font-bold text-lg text-blue-800 mb-3">{{ plan.name }}</h4>
                            <ul class="space-y-2">
                                <li v-for="modName in plan.modules" :key="modName" class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    <span>{{ modName }}</span>
                                </li>
                            </ul>
                            <button class="mt-4 text-sm text-blue-600 hover:underline">Editar Módulos del Plan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
