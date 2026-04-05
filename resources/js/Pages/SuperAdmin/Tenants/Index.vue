
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    tenants: { type: Array, default: () => [] },
});

const total = computed(() => props.tenants.length);
const active = computed(() => props.tenants.filter(t => t.database).length);
const inactive = computed(() => total.value - active.value);

const editTenant = (tenantId) => {
    router.get(route('superadmin.hotels.edit', tenantId));
};

const deleteTenant = (tenantId) => {
    if (!confirm('¿Seguro que deseas eliminar este hotel?')) {
        return;
    }

    router.delete(route('superadmin.hotels.destroy', tenantId), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Gestión de Hoteles" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Hoteles</h2>
        </template>

        <div class="space-y-6">
            <!-- Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Total Instancias</div>
                    <div class="mt-2 text-2xl font-bold">{{ total }}</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Activas (tienen DB)</div>
                    <div class="mt-2 text-2xl font-bold text-green-600">{{ active }}</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Inactivas</div>
                    <div class="mt-2 text-2xl font-bold text-gray-700">{{ inactive }}</div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Lista de Hoteles</h3>
                        <Link :href="route('superadmin.hotels.create')" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">+ Crear Hotel</Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dominio</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Base de Datos</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="tenant in tenants" :key="tenant.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">{{ tenant.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ tenant.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ tenant.domain ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">{{ tenant.database ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <button @click="editTenant(tenant.id)" class="text-indigo-600 hover:text-indigo-900">Editar</button>
                                        <button @click="deleteTenant(tenant.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                                    </td>
                                </tr>
                                <tr v-if="tenants.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No hay instancias registradas.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
