<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ planConfig: Object });
const plan = computed(() => props.planConfig || { plan_name: 'Plan', tier: 'alto_empresarial' });
</script>

<template>
  <Head title="Modo oscuro" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Modo oscuro</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-gray-900 text-gray-100 p-6 shadow-sm sm:rounded-lg border border-gray-700">
          <div class="flex items-center justify-between mb-3">
            <p class="text-sm text-gray-300">Configuración de visualización oscura para usuarios del hotel.</p>
            <span class="text-xs px-3 py-1 rounded-full bg-gray-800 text-gray-200 border border-gray-600">{{ plan.plan_name }}</span>
          </div>
          <p class="text-sm text-gray-400">Módulo premium para mejorar lectura nocturna y confort visual.</p>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
