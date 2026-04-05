<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watchEffect } from 'vue';

const props = defineProps({
    subscriptions: {
        type: Array,
        default: () => [],
    },
    plans: {
        type: Array,
        default: () => [],
    },
});

const getStatusPillInfo = (status, days_left) => {
  if (status === 'vencido') {
    return { text: 'Vencido', class: 'bg-red-100 text-red-800' };
  }
  if (status === 'por_vencer') {
    return { text: `Vence en ${days_left} días`, class: 'bg-yellow-100 text-yellow-800' };
  }
  return { text: `Activo - ${days_left} días restantes`, class: 'bg-green-100 text-green-800' };
};

const expiringCount = computed(() => props.subscriptions.filter((subscription) => subscription.status === 'por_vencer').length);
const expiredCount = computed(() => props.subscriptions.filter((subscription) => subscription.status === 'vencido').length);
const pendingChangesCount = computed(() => props.subscriptions.filter((subscription) => subscription.has_pending).length);

const selectedPlanByHotel = ref({});
const uiError = ref('');

watchEffect(() => {
    const next = { ...selectedPlanByHotel.value };
    for (const sub of props.subscriptions) {
        if (!next[sub.hotel_id]) {
            next[sub.hotel_id] = sub.pending_plan_id || sub.current_plan_id || props.plans[0]?.id || null;
        }
    }
    selectedPlanByHotel.value = next;
});

const changePlan = (subscription, mode) => {
    uiError.value = '';
    if (!subscription.hotel_id) {
        return;
    }

    const planId = selectedPlanByHotel.value[subscription.hotel_id];
    if (!planId) {
        uiError.value = 'Selecciona un plan primero.';
        return;
    }

    const label = mode === 'apply' ? 'aplicar ahora' : 'programar';
    if (!confirm(`¿Deseas ${label} el plan para ${subscription.hotel}?`)) {
        return;
    }

    router.post(route('superadmin.hotels.change_plan', subscription.hotel_id), {
        plan_id: planId,
        mode,
    }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Control de Suscripciones" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Control de Suscripciones</h2>
        </template>

        <div class="space-y-6">

            <!-- Alertas de Suscripciones -->
            <div v-if="uiError" class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ uiError }}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Hoteles por Vencer -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-yellow-800">Hoteles por Vencer</h3>
                    <p class="mt-1 text-3xl font-semibold text-yellow-900">{{ expiringCount }}</p>
                </div>
                <!-- Hoteles Vencidos -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-red-800">Hoteles Vencidos</h3>
                    <p class="mt-1 text-3xl font-semibold text-red-900">{{ expiredCount }}</p>
                </div>
                <!-- Cambios Pendientes -->
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-indigo-800">Cambios de Plan Pendientes</h3>
                    <p class="mt-1 text-3xl font-semibold text-indigo-900">{{ pendingChangesCount }}</p>
                </div>
            </div>

            <!-- Tabla de Suscripciones -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Estado de Suscripciones</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hotel</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Vencimiento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nuevo plan</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="sub in props.subscriptions" :key="sub.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ sub.hotel }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sub.plan }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sub.expires_at || '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusPillInfo(sub.status, sub.days_left).class" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ getStatusPillInfo(sub.status, sub.days_left).text }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <select v-model="selectedPlanByHotel[sub.hotel_id]" class="border-gray-300 rounded-md text-sm">
                                            <option :value="null" disabled>Selecciona plan</option>
                                            <option v-for="plan in props.plans" :key="plan.id" :value="plan.id">{{ plan.name }}</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="changePlan(sub, 'pending')" :disabled="!sub.hotel_id" class="text-yellow-600 hover:text-yellow-900 disabled:text-gray-400 mr-3">Programar cambio</button>
                                        <button @click="changePlan(sub, 'apply')" :disabled="!sub.hotel_id" class="text-indigo-600 hover:text-indigo-900 disabled:text-gray-400">Aplicar ahora</button>
                                    </td>
                                </tr>
                                <tr v-if="props.subscriptions.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No hay suscripciones registradas.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
