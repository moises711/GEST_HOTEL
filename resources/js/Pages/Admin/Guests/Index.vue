<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    guests: Array,
    guestsPagination: Object,
});

const importForm = useForm({
    file: null,
});

const onFileChange = (event) => {
    importForm.file = event.target.files?.[0] ?? null;
};

const submitImport = () => {
    importForm.post(route('admin.guests.import'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            importForm.reset('file');
        },
    });
};
</script>

<template>
    <Head title="Clientes" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Clientes</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-gradient-to-r from-indigo-50 to-white p-6 shadow-sm sm:rounded-lg border-l-4 border-indigo-300">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Huéspedes registrados</h3>
                            <p class="text-sm text-gray-600">Listado paginado para consultas rápidas y carga masiva por CSV.</p>
                        </div>
                        <div class="text-sm text-indigo-700 bg-indigo-100 px-3 py-1 rounded-md">Total: {{ props.guests?.length || 0 }}</div>
                    </div>

                    <form class="mb-4 flex flex-col gap-3 rounded-md border border-gray-200 bg-white p-4 sm:flex-row sm:items-end" @submit.prevent="submitImport">
                        <div class="flex-1">
                            <label class="block text-xs font-medium uppercase tracking-wide text-gray-500">Importar CSV</label>
                            <input
                                type="file"
                                accept=".csv,text/csv,.txt"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                @change="onFileChange"
                            />
                            <p class="mt-1 text-xs text-gray-500">Columnas mínimas: first_name, last_name, document (también acepta nombre, apellido, dni).</p>
                            <p v-if="importForm.errors.file" class="mt-1 text-xs text-red-600">{{ importForm.errors.file }}</p>
                        </div>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                            :disabled="importForm.processing || !importForm.file"
                        >
                            {{ importForm.processing ? 'Importando...' : 'Importar' }}
                        </button>
                    </form>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Habitación</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="g in props.guests" :key="g.id">
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ g.id }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ (g.first_name ? g.first_name + ' ' + (g.last_name||'') : (g.name || '—')) }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ g.email ?? g.email_address ?? '—' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ g.room_label ?? g.room_number ?? g.room_id ?? '—' }}</td>
                                </tr>
                                <tr v-if="!props.guests || props.guests.length === 0">
                                    <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-400">No hay clientes registrados.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="props.guestsPagination" class="mt-4 flex items-center justify-between">
                        <div class="text-xs text-gray-500">
                            Página {{ props.guestsPagination.current_page }} · {{ props.guestsPagination.per_page }} por carga
                        </div>
                        <div class="flex items-center gap-2">
                            <Link
                                v-if="props.guestsPagination.prev_page_url"
                                :href="props.guestsPagination.prev_page_url"
                                class="rounded-md border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-50"
                            >Anterior</Link>
                            <Link
                                v-if="props.guestsPagination.next_page_url"
                                :href="props.guestsPagination.next_page_url"
                                class="rounded-md border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-50"
                            >Siguiente</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
