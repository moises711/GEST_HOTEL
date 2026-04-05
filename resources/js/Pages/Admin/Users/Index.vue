<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ planConfig: Object });
const plan = computed(() => props.planConfig || { plan_name: 'Plan', tier: 'basico' });
</script>

<template>
  <Head title="Usuarios" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Usuarios</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow-sm sm:rounded-lg border border-gray-200">
          <div class="flex items-center justify-between mb-3">
            <p class="text-sm text-gray-600">Administración de usuarios del hotel.</p>
            <span class="text-xs px-3 py-1 rounded-full bg-indigo-100 text-indigo-700">{{ plan.plan_name }}</span>
          </div>
          <p class="text-sm text-gray-500">Módulo habilitado por plan. Aquí puedes gestionar cuentas y permisos internos.</p>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
