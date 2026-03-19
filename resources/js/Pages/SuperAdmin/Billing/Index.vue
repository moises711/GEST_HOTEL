<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

</script>

<template>
    <Head title="Ingresos y Facturación" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ingresos y Facturación</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Aquí irá el gráfico de ingresos -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900">Ingresos por Mes</h3>
                            <!-- (Componente de gráfico) -->
                        </div>

                        <!-- Ingresos por hotel -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Ingresos por Hotel</h3>
                            <!-- (Tabla de ingresos por hotel) -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
