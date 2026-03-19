<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

</script>

<template>
    <Head title="Gestión de Módulos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Módulos</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Lista de módulos -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900">Módulos del Sistema</h3>
                            <!-- (Tabla de módulos) -->
                        </div>

                        <!-- Activar/desactivar por plan -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Módulos por Plan</h3>
                            <!-- (Interfaz para asignar módulos a planes) -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
