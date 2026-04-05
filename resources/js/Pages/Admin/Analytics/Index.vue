<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ analytics: Object, planConfig: Object });
const plan = computed(() => props.planConfig || { tier: 'basico', plan_name: 'Básico' });

const planTheme = computed(() => {
    if (plan.value.tier === 'alto_empresarial') {
        return {
            wrapper: 'from-red-50 to-white border-red-300',
            badge: 'text-red-700 bg-red-100',
            card: 'border-red-200',
        };
    }
    if (plan.value.tier === 'intermedio') {
        return {
            wrapper: 'from-yellow-50 to-white border-yellow-300',
            badge: 'text-yellow-700 bg-yellow-100',
            card: 'border-yellow-200',
        };
    }
    return {
        wrapper: 'from-green-50 to-white border-green-300',
        badge: 'text-green-700 bg-green-100',
        card: 'border-green-200',
    };
});
</script>

<template>
    <Head title="Analítica" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Analítica Avanzada</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div :class="['bg-gradient-to-r p-6 shadow-sm sm:rounded-lg border-l-4', planTheme.wrapper]">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Analítica</h3>
                            <p class="text-sm text-gray-600">Panel de analítica y KPIs avanzados (integraciones externas).</p>
                        </div>
                        <div class="text-sm px-3 py-1 rounded-md" :class="[planTheme.badge, plan.tier === 'alto_empresarial' ? 'animate-pulse' : '']">{{ plan.plan_name }}</div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div :class="['bg-white p-4 rounded-lg shadow border', planTheme.card]">
                            <div class="text-sm text-gray-500">Ocupación Semana</div>
                            <div class="text-2xl font-semibold mt-2">0%</div>
                        </div>
                        <div :class="['bg-white p-4 rounded-lg shadow border', planTheme.card]">
                            <div class="text-sm text-gray-500">ADR</div>
                            <div class="text-2xl font-semibold mt-2">€0.00</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
