<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const plans = [
  {
    name: 'Básico',
    price: '$49',
    features: [
      'Hasta 50 habitaciones',
      'Módulo de Reservas',
      'Módulo de Habitaciones',
      'Soporte por email'
    ],
    buttonClass: 'bg-gray-200 text-gray-800 hover:bg-gray-300'
  },
  {
    name: 'Pro',
    price: '$99',
    features: [
      'Hasta 200 habitaciones',
      'Módulos Básico +',
      'Módulo de Facturación',
      'Soporte prioritario'
    ],
    buttonClass: 'bg-blue-500 text-white hover:bg-blue-600'
  },
  {
    name: 'Premium',
    price: '$199',
    features: [
      'Habitaciones ilimitadas',
      'Módulos Pro +',
      'Módulo de Reportes',
      'Módulo de Usuarios',
      'Soporte 24/7'
    ],
    buttonClass: 'bg-indigo-500 text-white hover:bg-indigo-600'
  }
];
</script>

<template>
    <Head title="Planes y Niveles" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Planes y Niveles</h2>
        </template>

        <div class="space-y-6">

            <div class="flex justify-end">
                 <button class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                    + Crear Nuevo Plan
                </button>
            </div>

            <!-- Tarjetas de Planes -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div v-for="plan in plans" :key="plan.name" class="bg-white rounded-lg shadow-lg p-6 flex flex-col transform hover:-translate-y-2 transition-transform duration-300">
                    <h3 class="text-2xl font-bold text-center text-gray-800">{{ plan.name }}</h3>
                    <p class="text-4xl font-extrabold text-center my-4">{{ plan.price }}<span class="text-base font-medium text-gray-500">/mes</span></p>
                    
                    <ul class="space-y-3 text-sm text-gray-600 flex-grow">
                        <li v-for="feature in plan.features" :key="feature" class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>{{ feature }}</span>
                        </li>
                    </ul>

                    <button :class="plan.buttonClass" class="w-full mt-6 py-2 px-4 rounded-lg font-semibold transition-colors">
                        Seleccionar Plan
                    </button>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
