<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
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

                            <div class="flex items-center justify-end mt-4">
                                <Link :href="route('superadmin.admins.index')" class="mr-3 text-sm text-gray-600 hover:text-gray-900">Cancelar</Link>
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
