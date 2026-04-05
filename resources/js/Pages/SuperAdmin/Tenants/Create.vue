<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';

const uiError = ref('');

const form = useForm({
  name: '',
  domain: '',
  database: '',
  admin_name: '',
  admin_email: '',
  admin_password: '',
  admin_password_confirmation: '',
  // optional advanced fields
  modules_comma: '',
  settings_json: '',
});

const closeModal = () => {
  router.get(route('superadmin.hotels.index'));
};

const submit = () => {
  uiError.value = '';
  // prepare modules array
  const modules = (form.modules_comma || '').split(',').map(s => s.trim()).filter(Boolean);

  // try parse settings JSON
  let settings = null;
  try {
    settings = form.settings_json ? JSON.parse(form.settings_json) : null;
  } catch (e) {
    uiError.value = 'Settings JSON inválido';
    return;
  }

  form.post(route('superadmin.hotels.store'), {
    data: {
      name: form.name,
      location: form.domain,
      database: form.database,
      admin_name: form.admin_name,
      admin_email: form.admin_email,
      admin_password: form.admin_password,
      admin_password_confirmation: form.admin_password_confirmation,
      modules: modules,
      settings: settings,
    }
    ,
    onSuccess: () => {
      closeModal();
    },
    onError: (errors) => {
      uiError.value = errors?.general || errors?.name || errors?.admin_email || 'No se pudo crear la instancia.';
    }
  });
};
</script>

<template>
  <Head title="Crear Instancia" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Crear Nueva Instancia</h2>
    </template>

    <Modal :show="true" @close="closeModal" maxWidth="2xl">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Crear Nueva Instancia</h3>
          <button @click="closeModal" class="text-gray-500 hover:text-gray-700 text-xl leading-none">×</button>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div v-if="uiError" class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
            {{ uiError }}
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Nombre (Hotel)</label>
            <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Dominio (opcional)</label>
            <input v-model="form.domain" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Nombre de Base de Datos (opcional)</label>
            <input v-model="form.database" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
            <p class="text-xs text-gray-500 mt-1">Si lo dejas vacío se generará automáticamente (hotel_{id}).</p>
          </div>

          <hr class="my-4" />

          <h3 class="text-base font-medium">Credenciales del Administrador</h3>

          <div>
            <label class="block text-sm font-medium text-gray-700">Nombre</label>
            <input v-model="form.admin_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Correo</label>
            <input v-model="form.admin_email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Contraseña</label>
              <input v-model="form.admin_password" type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
              <input v-model="form.admin_password_confirmation" type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Módulos (separados por coma, opcional)</label>
            <input v-model="form.modules_comma" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="reservations,checkin,billing" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Settings (JSON opcional)</label>
            <textarea v-model="form.settings_json" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" rows="4" placeholder='{"max_users":10}'></textarea>
          </div>

          <div class="pt-2 flex justify-end">
            <Link :href="route('superadmin.hotels.index')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md mr-3 border-2 border-gray-300">Cancelar</Link>
            <button type="submit" :disabled="form.processing" :class="['px-4 py-2 rounded-md border-2', form.processing ? 'bg-blue-300 border-blue-400 text-gray-700' : 'bg-blue-600 text-white border-blue-700 hover:bg-blue-700']">Crear Instancia</button>
          </div>
        </form>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>
