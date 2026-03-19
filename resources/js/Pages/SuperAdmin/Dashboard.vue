<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

// Métricas de ejemplo
const totalHotels = ref(120);
const activeHotels = ref(110);
const suspendedHotels = ref(10);
const monthlyRevenue = ref(12500);
const annualRevenue = ref(150000);
const newHotelsThisMonth = ref(8);

</script>

<template>
    <Head title="Super Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Resumen Global</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total de Hoteles -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900">Total de Hoteles</h3>
                        <p class="mt-1 text-3xl font-semibold text-indigo-600">{{ totalHotels }}</p>
                    </div>

                    <!-- Hoteles Activos / Suspendidos -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900">Estado de Hoteles</h3>
                        <p class="mt-1 text-xl font-semibold text-green-600">Activos: {{ activeHotels }}</p>
                        <p class="mt-1 text-xl font-semibold text-red-600">Suspendidos: {{ suspendedHotels }}</p>
                    </div>

                    <!-- Ingresos -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900">Ingresos</h3>
                        <p class="mt-1 text-xl font-semibold text-blue-600">Mensual: ${{ monthlyRevenue }}</p>
                        <p class="mt-1 text-xl font-semibold text-gray-600">Anual: ${{ annualRevenue }}</p>
                    </div>

                    <!-- Nuevos Hoteles -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900">Nuevos Hoteles (Mes)</h3>
                        <p class="mt-1 text-3xl font-semibold text-yellow-500">{{ newHotelsThisMonth }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
