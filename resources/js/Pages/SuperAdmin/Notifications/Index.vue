<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

</script>

<template>
    <Head title="Notificaciones" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notificaciones</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Alertas de vencimiento -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900">Alertas de Vencimiento</h3>
                            <!-- (Lista de notificaciones de vencimiento) -->
                        </div>

                        <!-- Problemas de pago -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Problemas de Pago</h3>
                            <!-- (Lista de notificaciones de problemas de pago) -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
