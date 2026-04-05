<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({ hotels: Object, filters: Object, plans: Array });

const getStatusClass = (status) => {
    switch (status) {
        case 'activo': return 'bg-green-100 text-green-800';
        case 'suspendido': return 'bg-yellow-100 text-yellow-800';
        case 'vencido': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

import { ref, computed } from 'vue';
const q = ref(props.filters?.q || '');
const planFilter = ref(props.filters?.plan_id || '');

const computedHotels = computed(() => props.hotels.data ?? []);

const search = () => {
    router.get(route('superadmin.hotels.index'), {
        q: q.value,
        plan_id: planFilter.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const suspendHotel = (hotelId) => {
    if (!confirm('¿Deseas suspender este hotel?')) {
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

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Listado de Hoteles</h3>
                    <Link :href="route('superadmin.hotels.create')" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                        + Nuevo Hotel
                    </Link>
                </div>

                <div class="mb-4 flex items-center gap-3">
                    <input v-model="q" @keyup.enter="search" placeholder="Buscar hoteles..." class="block w-1/3 rounded-md border-gray-300 shadow-sm px-3 py-2" />
                    <select v-model="planFilter" @change="search" class="rounded-md border-gray-300 px-3 py-2">
                        <option value="">Todos los planes</option>
                        <option v-for="p in props.plans" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
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
                            <tr v-for="hotel in computedHotels" :key="hotel.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ hotel.name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ hotel.owner }}</div>
                                    <div class="text-sm text-gray-500">{{ hotel.owner_email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatusClass(hotel.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ hotel.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ hotel.plan }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ hotel.expires_at }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <Link :href="route('superadmin.hotels.show', hotel.id)" class="text-blue-600 hover:text-blue-900">Ver</Link>
                                    <Link :href="route('superadmin.hotels.edit', hotel.id)" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                    <button @click="suspendHotel(hotel.id)" class="text-red-600 hover:text-red-900">Suspender</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
