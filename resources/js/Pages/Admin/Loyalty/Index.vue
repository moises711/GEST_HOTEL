<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ loyaltyPrograms: Array, planConfig: Object });
const plan = computed(() => props.planConfig || { tier: 'basico', plan_name: 'Básico' });
const programs = computed(() => props.loyaltyPrograms || []);

const planTheme = computed(() => {
    if (plan.value.tier === 'alto_empresarial') {
        return {
            wrapper: 'from-red-50 to-white border-red-300',
            badge: 'bg-red-100 text-red-700',
            card: 'border-red-200',
        };
    }
    if (plan.value.tier === 'intermedio') {
        return {
            wrapper: 'from-yellow-50 to-white border-yellow-300',
            badge: 'bg-yellow-100 text-yellow-700',
            card: 'border-yellow-200',
        };
    }
    return {
        wrapper: 'from-green-50 to-white border-green-300',
        badge: 'bg-green-100 text-green-700',
        card: 'border-green-200',
    };
});
</script>

<template>
    <Head title="Fidelización" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Fidelización de Clientes</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div :class="['bg-gradient-to-r p-6 shadow-sm sm:rounded-lg border-l-4', planTheme.wrapper]">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm text-gray-600">Programas de puntos, promociones y segmentación de clientes.</p>
                        <span class="text-xs px-3 py-1 rounded-full"
                              :class="[planTheme.badge, plan.tier === 'alto_empresarial' ? 'animate-pulse' : '']">
                            {{ plan.plan_name }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div :class="['p-4 rounded-lg border bg-gray-50 transition hover:shadow-sm', planTheme.card]">
                            <p class="text-xs text-gray-500">Programas activos</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ programs.length }}</p>
                        </div>
                        <div :class="['p-4 rounded-lg border bg-gray-50 transition hover:shadow-sm', planTheme.card]">
                            <p class="text-xs text-gray-500">Segmentación avanzada</p>
                            <p class="text-lg font-semibold text-gray-900">{{ plan.tier === 'alto_empresarial' ? 'Completa' : 'Estándar' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
