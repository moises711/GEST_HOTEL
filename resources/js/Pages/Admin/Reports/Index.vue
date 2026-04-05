<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ reports: Array, planConfig: Object });
const plan = computed(() => props.planConfig || { tier: 'basico', plan_name: 'Básico' });

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
    <Head title="Reportes" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reportes</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div :class="['bg-gradient-to-r p-6 shadow-sm sm:rounded-lg border-l-4', planTheme.wrapper]">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm text-gray-600">Reportes de ocupación, revenue management y rendimiento.</p>
                        <span class="text-xs px-3 py-1 rounded-full" :class="planTheme.badge">
                            {{ plan.plan_name }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div :class="['p-4 rounded-lg border bg-gray-50 transition hover:shadow-sm', planTheme.card]">
                            <p class="text-xs text-gray-500">Ocupación semanal</p>
                            <p class="text-lg font-semibold text-gray-800">Disponible</p>
                        </div>
                        <div :class="['p-4 rounded-lg border bg-gray-50 transition hover:shadow-sm', planTheme.card, plan.tier === 'alto_empresarial' ? 'animate-pulse' : '']">
                            <p class="text-xs text-gray-500">Indicadores avanzados</p>
                            <p class="text-lg font-semibold text-gray-800">{{ plan.tier === 'alto_empresarial' ? 'Habilitado' : 'Intermedio' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
