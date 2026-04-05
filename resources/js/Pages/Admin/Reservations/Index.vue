<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({ reservations: Array });
</script>

<template>
    <Head title="Reservas" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reservas</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <p class="text-sm text-gray-600">Listado de reservas recientes.</p>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Huésped</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hab.</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Check-in</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Check-out</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="r in props.reservations" :key="r.id">
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ r.id }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ r.guest ?? r.name ?? '—' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ r.room ?? r.room_number ?? '—' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ r.checkin ?? r.start_date ?? '—' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ r.checkout ?? r.end_date ?? '—' }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="{
                                                'bg-green-100 text-green-800': (r.status || '').toLowerCase().includes('libre') || (r.status || '').toLowerCase().includes('available'),
                                                'bg-red-100 text-red-800': (r.status || '').toLowerCase().includes('ocup') || (r.status || '').toLowerCase().includes('occupied') || (r.status || '').toLowerCase().includes('checked in'),
                                                'bg-yellow-100 text-yellow-800': (r.status || '').toLowerCase().includes('limpieza') || (r.status || '').toLowerCase().includes('clean'),
                                                'bg-blue-100 text-blue-800': (r.status || '').toLowerCase().includes('reserv') || (r.status || '').toLowerCase().includes('reserved')
                                            }">
                                            {{ r.status ?? '—' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="!props.reservations || props.reservations.length === 0">
                                    <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-400">No hay reservas.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
