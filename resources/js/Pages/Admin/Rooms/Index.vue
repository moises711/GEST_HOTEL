<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    rooms: Array
});

// Lógica de filtrado
const filterStatus = ref('Todos'); // O 'Libre', 'Ocupada', 'Limpieza', 'Mantenimiento'
const filterType = ref('Todos'); // O 'Doble Estándar', 'Suite Deluxe', etc.

const roomTypes = computed(() => ['Todos', ...new Set(props.rooms.map(r => r.type))]);

const filteredRooms = computed(() => {
    return props.rooms.filter(room => {
        const statusMatch = filterStatus.value === 'Todos' || room.status === filterStatus.value;
        const typeMatch = filterType.value === 'Todos' || room.type === filterType.value;
        return statusMatch && typeMatch;
    });
});

const roomsByStatus = computed(() => {
    return filteredRooms.value.reduce((acc, room) => {
        if (!acc[room.status]) {
            acc[room.status] = [];
        }
        acc[room.status].push(room);
        return acc;
    }, {});
});

const statusConfig = {
    'Libre': { color: 'green', title: 'Disponibles' },
    'Ocupada': { color: 'red', title: 'Ocupadas' },
    'Limpieza': { color: 'blue', title: 'En Limpieza' },
    'Mantenimiento': { color: 'yellow', title: 'En Mantenimiento' },
};

</script>

<template>
    <Head title="Tablero de Habitaciones" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tablero Visual de Habitaciones</h2>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">+ Añadir Habitación</button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Filtros -->
                <div class="bg-white p-4 shadow-sm sm:rounded-lg mb-6 flex items-center space-x-4">
                    <h3 class="text-sm font-medium text-gray-700">Filtrar por:</h3>
                    <div>
                        <label for="status-filter" class="sr-only">Estado</label>
                        <select id="status-filter" v-model="filterStatus" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option>Todos</option>
                            <option v-for="(config, status) in statusConfig" :key="status">{{ status }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="type-filter" class="sr-only">Tipo</label>
                        <select id="type-filter" v-model="filterType" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                             <option v-for="type in roomTypes" :key="type">{{ type }}</option>
                        </select>
                    </div>
                </div>

                <!-- Tablero de Habitaciones -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <div v-for="(config, status) in statusConfig" :key="status" class="bg-gray-50 rounded-lg p-4">
                        <h3 :class="`text-${config.color}-800`" class="font-semibold mb-4 border-b pb-2">{{ config.title }} ({{ roomsByStatus[status]?.length || 0 }})</h3>
                        <div class="space-y-3">
                           <div v-if="!roomsByStatus[status] || roomsByStatus[status].length === 0" class="text-center text-xs text-gray-400 py-4">
                                No hay habitaciones en este estado.
                           </div>
                           <div v-for="room in roomsByStatus[status]" :key="room.id" 
                                :class="`bg-${config.color}-100 border-l-4 border-${config.color}-500`" 
                                class="p-3 rounded-md shadow-sm group relative">
                                
                                <h4 class="font-bold text-gray-800">Hab. {{ room.number }}</h4>
                                <p class="text-sm text-gray-600">{{ room.type }}</p>
                                
                                <div v-if="room.status === 'Ocupada'" class="text-xs text-red-800 mt-1">Ocupante: {{ room.occupant }}</div>
                                <div v-if="room.status === 'Mantenimiento'" class="text-xs text-yellow-800 mt-1">Nota: {{ room.notes }}</div>

                                <!-- Acciones al pasar el ratón -->
                                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex space-x-1">
                                     <button class="p-1 bg-white rounded-full text-gray-500 hover:text-gray-800 shadow-sm">
                                         <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z"></path></svg>
                                     </button>
                                     <button class="p-1 bg-white rounded-full text-gray-500 hover:text-gray-800 shadow-sm">
                                         <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                     </button>
                                </div>
                           </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
