<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import RoomCard from '@/Components/Rooms/RoomCard.vue';
import RoomForm from '@/Components/Rooms/RoomForm.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    rooms: Array
});
const page = usePage();
const planTier = computed(() => page?.props?.planConfig?.tier || 'basico');
const hasIntermedio = computed(() => ['intermedio', 'alto_empresarial'].includes(planTier.value));
const hasAlto = computed(() => planTier.value === 'alto_empresarial');

// Lógica de filtrado
const filterStatus = ref('Todos'); // O 'Libre', 'Ocupada', 'Limpieza', 'Mantenimiento'
const filterType = ref('Todos'); // O 'Doble Estándar', 'Suite Deluxe', etc.

// Modal / creación
const showCreate = ref(false);
const editingRoom = ref(null);

const openCreate = () => { editingRoom.value = null; showCreate.value = true; };
const openEdit = (room) => { editingRoom.value = room; showCreate.value = true; };

const selectedRoomForGuest = ref(null);

const guestForm = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    document: '',
    room_id: '',
    stay_nights: 1,
    discount_percent: 0,
});

const readyForm = useForm({});

const markRoomReady = (room) => {
    readyForm.post(route('admin.rooms.ready', room.id), {
        preserveScroll: true,
    });
};

const goToCheckout = () => {
    window.location.href = route('admin.checkin.index');
};

const selectRoomForGuest = (room) => {
    selectedRoomForGuest.value = room;
    guestForm.room_id = room.id;
};

const closeGuestModal = () => {
    selectedRoomForGuest.value = null;
    guestForm.reset();
    guestForm.clearErrors();
};

const submitGuestFromRoom = () => {
    guestForm.post(route('admin.guests.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeGuestModal();
        },
    });
};

const afterSaved = () => {
    // opcional: refrescar con Inertia.visit o emitir evento desde backend
    // aquí asumimos que la página será actualizada por Inertia automáticamente tras la respuesta
};

const roomTypes = computed(() => ['Todos', ...new Set((props.rooms || []).map(r => r.type))]);

const filteredRooms = computed(() => {
    return (props.rooms || []).filter(room => {
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

const roomSummary = computed(() => {
    const source = props.rooms || [];
    return {
        total: source.length,
        libres: source.filter((r) => r.status === 'Libre').length,
        ocupadas: source.filter((r) => r.status === 'Ocupada').length,
        limpieza: source.filter((r) => r.status === 'Limpieza').length,
    };
});

const statusConfig = {
    'Libre': { color: 'green', title: 'Disponibles', textClass: 'text-green-800', bgClass: 'bg-green-100', borderClass: 'border-green-500' },
    'Ocupada': { color: 'red', title: 'Ocupadas', textClass: 'text-red-800', bgClass: 'bg-red-100', borderClass: 'border-red-500' },
    'Limpieza': { color: 'blue', title: 'En Limpieza', textClass: 'text-blue-800', bgClass: 'bg-blue-100', borderClass: 'border-blue-500' },
    'Mantenimiento': { color: 'yellow', title: 'En Mantenimiento', textClass: 'text-yellow-800', bgClass: 'bg-yellow-100', borderClass: 'border-yellow-500' },
};

</script>

<template>
    <Head title="Tablero de Habitaciones" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tablero Visual de Habitaciones</h2>
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
                    <div class="ml-auto">
                        <button @click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">+ Añadir Habitación</button>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                    <div class="rounded-xl border border-gray-200 bg-white p-3">
                        <p class="text-xs text-gray-500">Total</p>
                        <p class="text-xl font-semibold text-gray-900">{{ roomSummary.total }}</p>
                    </div>
                    <div class="rounded-xl border border-green-200 bg-green-50 p-3">
                        <p class="text-xs text-green-700">Libres</p>
                        <p class="text-xl font-semibold text-green-900">{{ roomSummary.libres }}</p>
                    </div>
                    <div class="rounded-xl border border-red-200 bg-red-50 p-3">
                        <p class="text-xs text-red-700">Ocupadas</p>
                        <p class="text-xl font-semibold text-red-900">{{ roomSummary.ocupadas }}</p>
                    </div>
                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-3">
                        <p class="text-xs text-blue-700">En limpieza</p>
                        <p class="text-xl font-semibold text-blue-900">{{ roomSummary.limpieza }}</p>
                    </div>
                </div>

                <!-- Tablero de Habitaciones -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="(config, status) in statusConfig" :key="status" class="bg-gray-50 rounded-lg p-4">
                        <h3 :class="config.textClass + ' font-semibold mb-4 border-b pb-2'">{{ config.title }} ({{ roomsByStatus[status]?.length || 0 }})</h3>
                        <div class="space-y-3">
                           <div v-if="!roomsByStatus[status] || roomsByStatus[status].length === 0" class="text-center text-xs text-gray-400 py-4">
                                No hay habitaciones en este estado.
                           </div>
                           <div class="space-y-3">
                                      <RoomCard
                                          v-for="room in roomsByStatus[status]"
                                          :key="room.id"
                                          :room="room"
                                          :status-config="statusConfig"
                                          @edit="openEdit"
                                          @registerGuest="selectRoomForGuest"
                                          @checkout="goToCheckout"
                                          @markReady="markRoomReady"
                                      />
                           </div>
                        </div>
                    </div>
                </div>

                <Modal :show="!!selectedRoomForGuest" @close="closeGuestModal" maxWidth="2xl">
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-gray-800 mb-4">
                            Registrar huésped para habitación {{ selectedRoomForGuest?.number }}
                        </h3>

                        <div v-if="guestForm.errors.general" class="mb-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                            {{ guestForm.errors.general }}
                        </div>

                        <form @submit.prevent="submitGuestFromRoom" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">
                            <input type="hidden" v-model="guestForm.room_id" />

                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Nombre *</label>
                                <input v-model="guestForm.first_name" type="text" class="w-full rounded-md border-gray-300 text-sm" required>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Apellido *</label>
                                <input v-model="guestForm.last_name" type="text" class="w-full rounded-md border-gray-300 text-sm" required>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Documento *</label>
                                <input v-model="guestForm.document" type="text" class="w-full rounded-md border-gray-300 text-sm" required>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Email</label>
                                <input v-model="guestForm.email" type="email" class="w-full rounded-md border-gray-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Teléfono</label>
                                <input v-model="guestForm.phone" type="text" class="w-full rounded-md border-gray-300 text-sm">
                            </div>
                            <div v-if="hasIntermedio">
                                <label class="block text-xs text-gray-600 mb-1">Noches *</label>
                                <input v-model.number="guestForm.stay_nights" min="1" max="365" type="number" class="w-full rounded-md border-gray-300 text-sm" required>
                            </div>
                            <div v-if="hasAlto">
                                <label class="block text-xs text-gray-600 mb-1">Descuento (%)</label>
                                <input v-model.number="guestForm.discount_percent" min="0" max="50" type="number" class="w-full rounded-md border-gray-300 text-sm">
                            </div>

                            <div class="md:col-span-2 lg:col-span-6 flex justify-end gap-2 mt-2">
                                <button type="button" @click="closeGuestModal" class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-50">Cancelar</button>
                                <button type="submit" :disabled="guestForm.processing" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 disabled:opacity-60">
                                    {{ guestForm.processing ? 'Guardando...' : 'Registrar huésped' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </Modal>

                <RoomForm :show="showCreate" :action="route('admin.rooms.store')" :initial="editingRoom || {}" @close="showCreate = false" @saved="afterSaved" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
