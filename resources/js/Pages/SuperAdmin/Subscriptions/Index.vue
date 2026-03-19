<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

</script>

<template>
    <Head title="Control de Suscripciones" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Control de Suscripciones</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Hoteles por vencer -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900">Hoteles por Vencer</h3>
                            <!-- (Tabla de hoteles por vencer) -->
                        </div>

                        <!-- Hoteles vencidos -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Hoteles Vencidos</h3>
                            <!-- (Tabla de hoteles vencidos) -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
