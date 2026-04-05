<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({ hotels: Array, plans: Array });

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    hotel_id: '',
    hotel_name: '',
    hotel_location: '',
    plan_id: '',
    duration_months: 1,
    role: 'owner',
});

const closeModal = () => {
    router.get(route('superadmin.admins.index'));
};

const submit = () => {
    form.post(route('superadmin.admins.store'), {
        onSuccess: () => {
            closeModal();
        },
    });
};
const selectedPlan = computed(() => props.plans.find(p => p.id === form.plan_id));

</script>

<template>
    <Head title="Crear Administrador" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Crear Nuevo Administrador de Hotel
            </h2>
        </template>

        <Modal :show="true" @close="closeModal" maxWidth="2xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Crear Administrador</h3>
                    <button @click="closeModal" class="text-gray-500 hover:text-gray-700 text-xl leading-none">×</button>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div v-if="form.errors.general || form.errors.error" class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                        {{ form.errors.general || form.errors.error }}
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                        <input v-model="form.name" id="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required autocomplete="name">
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                        <input v-model="form.email" id="email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required autocomplete="username">
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                            <input v-model="form.password" id="password" type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required autocomplete="new-password">
                            <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                            <input v-model="form.password_confirmation" id="password_confirmation" type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required autocomplete="new-password">
                            <p v-if="form.errors.password_confirmation" class="mt-1 text-xs text-red-600">{{ form.errors.password_confirmation }}</p>
                        </div>
                    </div>

                    <div class="border-l-4 border-gray-200 pl-4 space-y-3">
                        <div>
                            <label for="hotel_name" class="block text-sm font-medium text-gray-700">Nombre del Hotel</label>
                            <input v-model="form.hotel_name" id="hotel_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="Nombre del nuevo hotel" />
                            <p v-if="form.errors.hotel_name" class="mt-1 text-xs text-red-600">{{ form.errors.hotel_name }}</p>
                        </div>
                        <div>
                            <label for="hotel_location" class="block text-sm font-medium text-gray-700">Ubicación</label>
                            <input v-model="form.hotel_location" id="hotel_location" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="Ciudad / Dirección" />
                            <p v-if="form.errors.hotel_location" class="mt-1 text-xs text-red-600">{{ form.errors.hotel_location }}</p>
                        </div>
                    </div>

                    <div>
                        <label for="plan_id" class="block text-sm font-medium text-gray-700">Plan</label>
                        <select v-model="form.plan_id" id="plan_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" required>
                            <option value="" disabled>Selecciona un plan</option>
                            <option v-for="p in props.plans" :key="p.id" :value="p.id">{{ p.name }} - ${{ p.price }}</option>
                        </select>
                        <p v-if="form.errors.plan_id" class="mt-1 text-xs text-red-600">{{ form.errors.plan_id }}</p>
                    </div>

                    <div>
                        <label for="duration_months" class="block text-sm font-medium text-gray-700">Duración del plan (meses)</label>
                        <input v-model.number="form.duration_months" id="duration_months" type="number" min="1" max="60" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" required>
                        <p v-if="form.errors.duration_months" class="mt-1 text-xs text-red-600">{{ form.errors.duration_months }}</p>
                        <p class="mt-1 text-xs text-gray-500">La fecha de expiración se calculará automáticamente desde hoy.</p>
                        <p v-if="selectedPlan" class="mt-1 text-xs text-gray-500">Duración sugerida por el plan: {{ selectedPlan.duration_months ?? 1 }} mes(es).</p>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <Link :href="route('superadmin.dashboard')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 mr-3">Cancelar</Link>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Crear Administrador</button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
