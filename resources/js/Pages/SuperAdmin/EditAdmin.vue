<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
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

const closeModal = () => {
    router.get(route('superadmin.admins.index'));
};

const submit = () => {
    form
        .transform((data) => ({
            _method: 'put',
            name: data.name,
            email: data.email,
            password: data.password,
            password_confirmation: data.password_confirmation,
        }))
        .post(route('superadmin.admins.update', props.admin.id), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset('password', 'password_confirmation');
                closeModal();
            },
        });
};
</script>

<template>
    <Head title="Editar Administrador" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Administrador
            </h2>
        </template>

        <Modal :show="true" @close="closeModal" maxWidth="2xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Editar Administrador</h3>
                    <button @click="closeModal" class="text-gray-500 hover:text-gray-700 text-xl leading-none">×</button>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                        <input v-model="form.name" id="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required autocomplete="name">
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                        <input v-model="form.email" id="email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required autocomplete="username">
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Nueva Contraseña (Opcional)</label>
                            <input v-model="form.password" id="password" type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" autocomplete="new-password">
                            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Nueva Contraseña</label>
                            <input v-model="form.password_confirmation" id="password_confirmation" type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" autocomplete="new-password">
                            <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ form.errors.password_confirmation }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Plan actual</label>
                            <input :value="props.admin.plan_name || 'Sin plan'" type="text" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-700" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Duración del plan (meses)</label>
                            <input :value="props.admin.duration_months || 1" type="text" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-700" readonly>
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <Link :href="route('superadmin.admins.index')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 mr-3">Cancelar</Link>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-500 text-white rounded-md hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
