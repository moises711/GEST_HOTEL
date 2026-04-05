<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ admins: Object, filters: Object });

const q = ref(props.filters?.q || '');
const computedAdmins = computed(() => props.admins.data ?? []);

const search = () => {
    router.get(route('superadmin.admins.index'), { q: q.value }, { preserveState: true, replace: true });
};

const deleteAdmin = (adminId) => {
    if (!confirm('¿Seguro que deseas eliminar este administrador? Esta acción no se puede deshacer.')) {
        return;
    }

    router.delete(route('superadmin.admins.destroy', adminId), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Gestionar Administradores" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Administradores</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-4 flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <input v-model="q" @keyup.enter="search" placeholder="Buscar administradores..." class="rounded-md border-gray-300 px-3 py-2" />
                            </div>
                            <div>
                                <Link :href="route('superadmin.admins.create')" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                                    Crear Nuevo Admin
                                </Link>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border">
                                <thead class="bg-gray-200">
                                    <tr>
                                        <th class="py-3 px-4 border-b text-left">Nombre</th>
                                        <th class="py-3 px-4 border-b text-left">Email</th>
                                        <th class="py-3 px-4 border-b text-left">Hotel</th>
                                        <th class="py-3 px-4 border-b text-left">Plan / Vencimiento</th>
                                        <th class="py-3 px-4 border-b text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="admin in computedAdmins" :key="admin.id" class="hover:bg-gray-50">
                                        <td class="py-2 px-4 border-b">{{ admin.name }}</td>
                                        <td class="py-2 px-4 border-b">{{ admin.email }}</td>
                                        <td class="py-2 px-4 border-b">{{ admin.hotel ? admin.hotel.name : '—' }}</td>
                                        <td class="py-2 px-4 border-b">
                                            <div v-if="admin.plan">{{ admin.plan.name }}<div class="text-xs text-gray-500">{{ admin.plan.ends_at }}</div></div>
                                            <div v-else class="text-xs text-gray-500">Sin plan</div>
                                        </td>
                                        <td class="py-2 px-4 border-b text-center">
                                            <Link :href="route('superadmin.admins.edit', admin.id)" class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</Link>
                                            <button @click="deleteAdmin(admin.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                                        </td>
                                    </tr>
                                    <tr v-if="(props.admins?.data || []).length === 0">
                                        <td colspan="5" class="py-4 text-center text-sm text-gray-500">No hay administradores.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
