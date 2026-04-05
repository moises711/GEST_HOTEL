<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    admin: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.admin.name,
    email: props.admin.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form
        .transform((data) => ({
            _method: 'put',
            name: data.name,
            email: data.email,
            password: data.password,
            password_confirmation: data.password_confirmation,
        }))
        .post(route('superadmin.admins.update', props.admin.id));
};
</script>

<template>
    <Head title="Editar Administrador" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Administrador: {{ props.admin.name }}</h2>
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
                                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block font-medium text-sm text-gray-700">Correo Electrónico</label>
                                    <input id="email" type="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" v-model="form.email" required />
                                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                                </div>

                                <!-- Password -->
                                <div>
                                    <label for="password" class="block font-medium text-sm text-gray-700">Nueva Contraseña (Opcional)</label>
                                    <input id="password" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" v-model="form.password" />
                                    <p class="text-sm text-gray-500 mt-1">Dejar en blanco para no cambiar la contraseña.</p>
                                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Confirmar Contraseña</label>
                                    <input id="password_confirmation" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" v-model="form.password_confirmation" />
                                    <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ form.errors.password_confirmation }}</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('superadmin.admins.index')" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</Link>
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
