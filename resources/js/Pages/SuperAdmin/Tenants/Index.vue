<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

</script>

<template>
    <Head title="Control de Tenants" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Control de Tenants</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Crear nuevo tenant -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900">Crear Nuevo Tenant</h3>
                            <!-- (Formulario para crear un tenant) -->
                        </div>

                        <!-- Lista de tenants -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Tenants Registrados</h3>
                            <!-- (Tabla de tenants) -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
