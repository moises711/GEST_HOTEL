<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    start_date: '',
    expiration_date: '',
});

const submit = () => {
    // Aquí, en el futuro, se enviarán los datos al servidor.
    // form.post(route('superadmin.admins.store'));
    alert('Simulando la creación del nuevo administrador...\nDatos: ' + JSON.stringify(form.data()));
};
</script>

<template>
    <Head title="Crear Administrador" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Crear Nuevo Administrador de Hotel
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                                <input v-model="form.name" id="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required autofocus autocomplete="name">
                            </div>

                            <!-- Email Address -->
                            <div class="mt-4">
                                <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                                <input v-model="form.email" id="email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required autocomplete="username">
                            </div>

                            <!-- Password -->
                            <div class="mt-4">
                                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                                <input v-model="form.password" id="password" type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required autocomplete="new-password">
                            </div>

                            <!-- Confirm Password -->
                            <div class="mt-4">
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                                <input v-model="form.password_confirmation" id="password_confirmation" type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required autocomplete="new-password">
                            </div>

                            <!-- Start Date -->
                            <div class="mt-4">
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                                <input v-model="form.start_date" id="start_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            </div>

                            <!-- Expiration Date -->
                            <div class="mt-4">
                                <label for="expiration_date" class="block text-sm font-medium text-gray-700">Fecha de Expiración</label>
                                <input v-model="form.expiration_date" id="expiration_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            </div>

                            <!-- Buttons -->
                            <div class="mt-6 flex justify-end">
                                <Link :href="route('superadmin.dashboard')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 mr-3">
                                    Cancelar
                                </Link>
                                <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Crear Administrador
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
