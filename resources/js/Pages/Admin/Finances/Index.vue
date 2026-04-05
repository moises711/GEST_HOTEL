<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    invoices: Array,
    planConfig: Object,
    stats: Object,
    dailyPayments: Array,
});

const plan = computed(() => props.planConfig || { tier: 'basico', plan_name: 'Básico' });
const stats = computed(() => props.stats || { daily_total: 0, monthly_total: 0, daily_count: 0 });
const dailyPayments = computed(() => props.dailyPayments || []);

const money = (amount) => `S/ ${Number(amount || 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const planTheme = computed(() => {
    if (plan.value.tier === 'alto_empresarial') {
        return {
            wrapper: 'from-red-50 to-white border-red-300',
            badge: 'text-red-700 bg-red-100',
            card: 'border-red-200',
            note: 'text-red-700',
        };
    }
    if (plan.value.tier === 'intermedio') {
        return {
            wrapper: 'from-yellow-50 to-white border-yellow-300',
            badge: 'text-yellow-700 bg-yellow-100',
            card: 'border-yellow-200',
            note: 'text-yellow-700',
        };
    }
    return {
        wrapper: 'from-green-50 to-white border-green-300',
        badge: 'text-green-700 bg-green-100',
        card: 'border-green-200',
        note: 'text-green-700',
    };
});
</script>

<template>
    <Head title="Finanzas" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Finanzas</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div :class="['bg-gradient-to-r p-6 shadow-sm sm:rounded-lg border-l-4', planTheme.wrapper]">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Finanzas</h3>
                            <p class="text-sm text-gray-600">Gestión de facturas, pagos y reportes financieros del hotel.</p>
                        </div>
                        <div class="text-sm px-3 py-1 rounded-md"
                             :class="[planTheme.badge, plan.tier === 'alto_empresarial' ? 'animate-pulse' : '']">
                             {{ plan.plan_name }}
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div :class="['bg-white p-4 rounded-lg shadow border', planTheme.card]">
                            <div class="text-sm text-gray-500">Ingresos Hoy</div>
                            <div class="text-2xl font-semibold mt-2">{{ money(stats.daily_total) }}</div>
                        </div>
                        <div :class="['bg-white p-4 rounded-lg shadow border', planTheme.card]">
                            <div class="text-sm text-gray-500">Ingresos Mensuales</div>
                            <div class="text-2xl font-semibold mt-2">{{ money(stats.monthly_total) }}</div>
                        </div>
                        <div :class="['bg-white p-4 rounded-lg shadow border', planTheme.card]">
                            <div class="text-sm text-gray-500">Pagos de Hoy</div>
                            <div class="text-2xl font-semibold mt-2">{{ stats.daily_count }}</div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-lg border bg-white p-4">
                        <h4 class="text-sm font-semibold text-gray-800 mb-3">Vista de pagos del día</h4>
                        <div v-if="dailyPayments.length === 0" class="text-sm text-gray-500">
                            No hay pagos registrados para hoy.
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-gray-500 border-b">
                                        <th class="py-2 pr-3">Huésped</th>
                                        <th class="py-2 pr-3">Habitación</th>
                                        <th class="py-2 pr-3">Noches</th>
                                        <th class="py-2 pr-3">Fecha</th>
                                        <th class="py-2 text-right">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="payment in dailyPayments" :key="payment.id" class="border-b last:border-b-0">
                                        <td class="py-2 pr-3 text-gray-800">{{ payment.guest_name }}</td>
                                        <td class="py-2 pr-3 text-gray-700">{{ payment.room_number }}</td>
                                        <td class="py-2 pr-3 text-gray-700">{{ payment.nights }}</td>
                                        <td class="py-2 pr-3 text-gray-700">{{ payment.date }}</td>
                                        <td class="py-2 text-right font-semibold text-gray-900">{{ money(payment.amount) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <p v-if="plan.tier === 'basico'" :class="['mt-4 text-xs', planTheme.note]">Vista financiera esencial con indicadores básicos.</p>
                    <p v-if="plan.tier === 'intermedio'" :class="['mt-4 text-xs', planTheme.note]">Incluye vista de KPIs operativos y navegación financiera rápida.</p>
                    <p v-if="plan.tier === 'alto_empresarial'" :class="['mt-4 text-xs', planTheme.note]">Incluye alertas inteligentes y soporte de análisis financiero avanzado.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
