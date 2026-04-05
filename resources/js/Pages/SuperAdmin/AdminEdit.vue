<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// --- DATOS DE EJEMPLO ---
// En una aplicación real, estos datos vendrían como props del controlador.
const admin = ref({
    id: 1,
    name: 'Admin Uno',
    email: 'admin1@hotel.com',
    start_date: '2024-01-01',
    expiration_date: '2024-12-31',
});

const form = useForm({
    name: admin.value.name,
    email: admin.value.email,
    password: '', // La contraseña se deja en blanco por seguridad
    start_date: admin.value.start_date,
    expiration_date: admin.value.expiration_date,
});

const submit = () => {
    // Lógica para enviar el formulario. Por ahora solo una alerta.
    alert('Enviando formulario para actualizar al admin ' + admin.value.id);
    // form.put(route('superadmin.update', admin.value.id));
};
</script>

<template>
    <Head title="Editar Administrador" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Administrador: {{ admin.name }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Nombre -->
                                <div>
                                    <label for="name" class="block font-medium text-sm text-gray-700">Nombre Completo</label>
                                    <input id="name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" v-model="form.name" required autofocus />
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block font-medium text-sm text-gray-700">Correo Electrónico</label>
                                    <input id="email" type="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" v-model="form.email" required />
                                </div>

                                <!-- Password -->
                                <div>
                                    <label for="password" class="block font-medium text-sm text-gray-700">Nueva Contraseña (Opcional)</label>
                                    <input id="password" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" v-model="form.password" />
                                    <p class="text-sm text-gray-500 mt-1">Dejar en blanco para no cambiar la contraseña.</p>
                                </div>

                                <div></div> <!-- Espacio en blanco para alinear -->

                                <!-- Fecha de Inicio -->
                                <div>
                                    <label for="start_date" class="block font-medium text-sm text-gray-700">Fecha de Inicio</label>
                                    <input id="start_date" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" v-model="form.start_date" required />
                                </div>

                                <!-- Fecha de Expiración -->
                                <div>
                                    <label for="expiration_date" class="block font-medium text-sm text-gray-700">Fecha de Expiración</label>
                                    <input id="expiration_date" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" v-model="form.expiration_date" required />
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('superadmin.admins')" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</Link>
                                <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50" :disabled="form.processing">
                                    Actualizar Administrador
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
