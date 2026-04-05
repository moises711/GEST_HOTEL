<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    admin: {
        type: Object,
        default: () => ({
            id: 1, // Ejemplo
            name: 'Admin Uno',
            email: 'admin1@example.com',
            start_date: '2023-01-01',
            expiration_date: '2024-01-01',
        }),
    },
});

const form = useForm({
    name: props.admin.name,
    email: props.admin.email,
    password: '', // La contraseña se deja en blanco por seguridad
    password_confirmation: '',
    start_date: props.admin.start_date,
    expiration_date: props.admin.expiration_date,
});

const submit = () => {
    // Lógica para enviar el formulario de actualización.
    // Se conectará al backend cuando esté listo.
    alert('Funcionalidad de edición pendiente de conexión con el backend.');
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
                            <div>
                                <InputLabel for="name" value="Nombre" />
                                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus autocomplete="name" />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="email" value="Email" />
                                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autocomplete="username" />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="password" value="Nueva Contraseña (Opcional)" />
                                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" autocomplete="new-password" />
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="password_confirmation" value="Confirmar Nueva Contraseña" />
                                <TextInput id="password_confirmation" type="password" class="mt-1 block w-full" v-model="form.password_confirmation" autocomplete="new-password" />
                                <InputError class="mt-2" :message="form.errors.password_confirmation" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="start_date" value="Fecha de Inicio" />
                                <TextInput id="start_date" type="date" class="mt-1 block w-full" v-model="form.start_date" required />
                                <InputError class="mt-2" :message="form.errors.start_date" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="expiration_date" value="Fecha de Expiración" />
                                <TextInput id="expiration_date" type="date" class="mt-1 block w-full" v-model="form.expiration_date" required />
                                <InputError class="mt-2" :message="form.errors.expiration_date" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Actualizar Administrador
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
