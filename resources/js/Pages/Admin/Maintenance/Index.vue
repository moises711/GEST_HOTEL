<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ planConfig: Object });
const plan = computed(() => props.planConfig || { plan_name: 'Plan', tier: 'alto_empresarial' });
</script>

<template>
  <Head title="Mantenimiento" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mantenimiento</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow-sm sm:rounded-lg border border-red-200">
          <div class="flex items-center justify-between mb-3">
            <p class="text-sm text-gray-600">Gestión de incidencias y mantenimiento de habitaciones.</p>
            <span class="text-xs px-3 py-1 rounded-full bg-red-100 text-red-700">{{ plan.plan_name }}</span>
          </div>
          <p class="text-sm text-gray-500">Módulo premium para control preventivo y correctivo.</p>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
