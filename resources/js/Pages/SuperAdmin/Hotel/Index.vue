<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ hotels: Object });
const list = computed(() => props.hotels?.data ?? []);

const suspendHotel = (hotelId) => {
    if (!confirm('¿Suspender hotel?')) {
        return;
    }

    router.post(route('superadmin.hotels.deactivate', hotelId), {}, {
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

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre del Hotel</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Propietario</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimiento</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="hotel in list" :key="hotel.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ hotel.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ hotel.owner }} / {{ hotel.owner_email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'bg-green-100 text-green-800': hotel.status === 'activo',
                                            'bg-red-100 text-red-800': hotel.status === 'suspendido',
                                            'bg-yellow-100 text-yellow-800': hotel.status === 'vencido',
                                        }" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ hotel.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ hotel.plan }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ hotel.expires_at }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('superadmin.hotels.show', hotel.id)" class="text-indigo-600 hover:text-indigo-900">Ver</Link>
                                        <Link :href="route('superadmin.hotels.edit', hotel.id)" class="ml-4 text-indigo-600 hover:text-indigo-900">Editar</Link>
                                        <button @click="suspendHotel(hotel.id)" class="ml-4 text-red-600 hover:text-red-900">Suspender</button>
                                    </td>
                                </tr>
                                <tr v-if="list.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No hay hoteles registrados.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
