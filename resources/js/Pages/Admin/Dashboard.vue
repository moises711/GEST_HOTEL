<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    kpis: Object,
    alerts: Array,
    activeModules: Object,
    planConfig: Object,
});

// Placeholder para un futuro gráfico
const chartUrl = `https://placehold.co/600x250/E9EBFB/4F46E5?text=Gráfico+de+Ocupación+(%25)`;

const kpis = props.kpis || { occupancy_rate: 0, daily_revenue: 0, monthly_revenue: 0, next_checkins: 0 };
const alerts = props.alerts || [];
const activeModules = props.activeModules || { plan: 'Básico', modules: [] };
const planConfig = props.planConfig || { tier: 'basico', plan_name: 'Básico', features: [] };

const planTone = computed(() => {
    if (planConfig.tier === 'alto_empresarial') {
        return {
            wrapper: 'from-red-50 to-white border-red-300',
            badge: 'bg-red-100 text-red-700',
            card: 'border-red-100 hover:border-red-300',
            mini: 'bg-red-100 text-red-700',
        };
    }
    if (planConfig.tier === 'intermedio') {
        return {
            wrapper: 'from-yellow-50 to-white border-yellow-300',
            badge: 'bg-yellow-100 text-yellow-700',
            card: 'border-yellow-100 hover:border-yellow-300',
            mini: 'bg-yellow-100 text-yellow-700',
        };
    }
    return {
        wrapper: 'from-green-50 to-white border-green-300',
        badge: 'bg-green-100 text-green-700',
        card: 'border-green-100 hover:border-green-300',
        mini: 'bg-green-100 text-green-700',
    };
});

const tierClass = computed(() => {
    return planTone.value.wrapper;
});

const kpiCards = computed(() => {
    const base = [
        { key: 'occupancy_rate', title: 'Ocupación Actual', value: `${kpis.occupancy_rate}%` },
        { key: 'next_checkins', title: 'Próximos Check-ins (24h)', value: `${kpis.next_checkins}` },
    ];

    if (planConfig.tier !== 'basico') {
        base.push(
            { key: 'daily_revenue', title: 'Ingresos del Día', value: `€${Number(kpis.daily_revenue || 0).toLocaleString()}` },
            { key: 'monthly_revenue', title: 'Ingresos del Mes', value: `€${Number(kpis.monthly_revenue || 0).toLocaleString()}` },
        );
    }

    if (planConfig.tier === 'alto_empresarial') {
        base.push({ key: 'automation', title: 'Automatizaciones activas', value: 'Ligera' });
    }

    return base;
});

const kpiCardAction = (key) => {
    const actions = {
        occupancy_rate: { label: 'Ver habitaciones', route: route('admin.rooms.index') },
        next_checkins: { label: 'Gestionar check-in/out', route: route('admin.checkin.index') },
        daily_revenue: { label: 'Ver finanzas', route: route('admin.finances.index') },
        monthly_revenue: { label: 'Ver reportes', route: route('admin.reports.index') },
        automation: { label: 'Ver analíticas', route: route('admin.analytics.index') },
    };

    return actions[key] || null;
};

const kpiCardDecor = (key) => {
    const styles = {
        occupancy_rate: {
            tone: 'from-sky-50 to-white border-sky-200',
            iconWrap: 'bg-sky-100 text-sky-700',
            iconPath: 'M3 13h4l3-8 4 14 3-6h4',
        },
        next_checkins: {
            tone: 'from-violet-50 to-white border-violet-200',
            iconWrap: 'bg-violet-100 text-violet-700',
            iconPath: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7H3v12a2 2 0 002 2z',
        },
        daily_revenue: {
            tone: 'from-emerald-50 to-white border-emerald-200',
            iconWrap: 'bg-emerald-100 text-emerald-700',
            iconPath: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V4m0 16v-4',
        },
        monthly_revenue: {
            tone: 'from-amber-50 to-white border-amber-200',
            iconWrap: 'bg-amber-100 text-amber-700',
            iconPath: 'M3 3v18h18M7 14l3-3 3 2 4-5',
        },
        automation: {
            tone: 'from-rose-50 to-white border-rose-200',
            iconWrap: 'bg-rose-100 text-rose-700',
            iconPath: 'M10.325 4.317a1 1 0 011.35-.936l7.5 3.5a1 1 0 01.325 1.618l-7.5 8.5a1 1 0 01-1.654-.274l-2.5-5a1 1 0 01.18-1.12l2.299-2.288-2.18-4z',
        },
    };

    return styles[key] || {
        tone: 'from-gray-50 to-white border-gray-200',
        iconWrap: 'bg-gray-100 text-gray-700',
        iconPath: 'M12 6v12M6 12h12',
    };
};

const didacticCards = computed(() => {
    const modulesCount = (planConfig.enabled_modules || []).length;
    const featuresCount = (planConfig.features || []).length;
    const maxUsers = planConfig.max_users === null || typeof planConfig.max_users === 'undefined'
        ? 'Ilimitado'
        : planConfig.max_users;

    return [
        { key: 'users', title: 'Capacidad de usuarios', value: maxUsers, subtitle: 'Límite operativo del plan' },
        { key: 'modules', title: 'Módulos activos', value: modulesCount, subtitle: 'Disponibles actualmente' },
        { key: 'features', title: 'Funciones premium', value: featuresCount, subtitle: 'Mejoras habilitadas por plan' },
    ];
});

const didacticCardAction = (key) => {
    const actions = {
        users: { label: 'Ir a usuarios', route: route('admin.users.index') },
        modules: { label: 'Ir a panel', route: route('admin.dashboard') },
        features: { label: 'Ir a personalización', route: route('admin.customization.index') },
    };

    return actions[key] || null;
};

const didacticCardDecor = (key) => {
    const styles = {
        users: {
            tone: 'from-cyan-50 to-white border-cyan-200',
            iconWrap: 'bg-cyan-100 text-cyan-700',
            iconPath: 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 7a4 4 0 11-8 0 4 4 0 018 0z',
        },
        modules: {
            tone: 'from-indigo-50 to-white border-indigo-200',
            iconWrap: 'bg-indigo-100 text-indigo-700',
            iconPath: 'M4 6h16M4 12h16M4 18h16',
        },
        features: {
            tone: 'from-fuchsia-50 to-white border-fuchsia-200',
            iconWrap: 'bg-fuchsia-100 text-fuchsia-700',
            iconPath: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.18 3.63a1 1 0 00.95.69h3.815c.969 0 1.371 1.24.588 1.81l-3.087 2.244a1 1 0 00-.364 1.118l1.18 3.63c.3.921-.755 1.688-1.54 1.118l-3.086-2.244a1 1 0 00-1.176 0l-3.087 2.244c-.784.57-1.838-.197-1.539-1.118l1.18-3.63a1 1 0 00-.364-1.118L2.516 9.057c-.783-.57-.38-1.81.588-1.81h3.815a1 1 0 00.95-.69l1.18-3.63z',
        },
    };

    return styles[key] || {
        tone: 'from-gray-50 to-white border-gray-200',
        iconWrap: 'bg-gray-100 text-gray-700',
        iconPath: 'M12 6v12M6 12h12',
    };
};

const occupancyProgress = computed(() => Math.max(0, Math.min(100, Number(kpis.occupancy_rate || 0))));

</script>

<template>
    <Head title="Dashboard del Hotel" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Principal del Hotel</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- 1. KPIs Principales -->
                <div :class="[
                    'grid grid-cols-1 md:grid-cols-2 gap-6',
                    planConfig.tier === 'alto_empresarial'
                        ? 'lg:grid-cols-3'
                        : (planConfig.tier !== 'basico' ? 'lg:grid-cols-4' : 'lg:grid-cols-2')
                ]">
                    <div v-for="card in kpiCards" :key="card.key" :class="['group bg-gradient-to-br border p-6 shadow-sm sm:rounded-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-lg', kpiCardDecor(card.key).tone]">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">{{ card.title }}</h3>
                                <p class="mt-1 text-4xl font-semibold text-gray-900 tracking-tight">{{ card.value }}</p>
                            </div>
                            <span :class="['h-10 w-10 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110', kpiCardDecor(card.key).iconWrap]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="kpiCardDecor(card.key).iconPath" />
                                </svg>
                            </span>
                        </div>
                        <div v-if="kpiCardAction(card.key)" class="mt-4">
                            <Link
                                :href="kpiCardAction(card.key).route"
                                class="inline-flex items-center text-xs font-semibold text-gray-700 hover:text-gray-900"
                            >
                                {{ kpiCardAction(card.key).label }}
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div v-for="item in didacticCards" :key="item.title" :class="['group bg-gradient-to-br border p-5 shadow-sm sm:rounded-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-lg', didacticCardDecor(item.key).tone]">
                        <div class="flex items-center justify-between">
                            <p class="text-xs uppercase tracking-wide text-gray-500">{{ item.title }}</p>
                            <span :class="['h-8 w-8 rounded-lg flex items-center justify-center transition-transform duration-300 group-hover:scale-110', didacticCardDecor(item.key).iconWrap]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="didacticCardDecor(item.key).iconPath" />
                                </svg>
                            </span>
                        </div>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ item.value }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ item.subtitle }}</p>
                        <div v-if="didacticCardAction(item.key)" class="mt-3">
                            <Link
                                :href="didacticCardAction(item.key).route"
                                class="inline-flex items-center text-xs font-semibold text-gray-700 hover:text-gray-900"
                            >
                                {{ didacticCardAction(item.key).label }}
                            </Link>
                        </div>
                    </div>
                </div>

                <div :class="['bg-white border p-5 shadow-sm sm:rounded-lg transition-all duration-300 hover:shadow-md', planTone.card]">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-gray-700">Progreso de ocupación</h3>
                        <span class="text-sm font-bold text-gray-900">{{ occupancyProgress }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                        <div
                            :class="[
                                'h-3 rounded-full transition-all duration-700',
                                planConfig.tier === 'alto_empresarial' ? 'bg-red-500 animate-pulse' : (planConfig.tier === 'intermedio' ? 'bg-yellow-500' : 'bg-green-500')
                            ]"
                            :style="{ width: occupancyProgress + '%' }"
                        ></div>
                    </div>
                </div>

                <!-- Gráfico (Placeholder) y Alertas -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Rendimiento de Ocupación</h3>
                        <img v-if="planConfig.has_weekly_charts" :src="chartUrl" alt="Gráfico de Ocupación" class="w-full h-auto rounded-lg transition-opacity duration-300" />
                        <div v-else class="grid grid-cols-7 gap-2">
                           <div v-for="d in ['L','M','X','J','V','S','D']" :key="d" :class="['h-14 rounded-lg flex items-center justify-center text-xs font-semibold', planTone.mini]">{{ d }}</div>
                        </div>
                    </div>
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Alertas y Notificaciones</h3>
                        <div class="space-y-3">
                            <div v-for="alert in alerts" :key="alert.id" class="p-3 rounded-md"
                                :class="{
                                    'bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800': alert.type === 'warning',
                                    'bg-red-100 border-l-4 border-red-500 text-red-800': alert.type === 'error',
                                    'bg-blue-100 border-l-4 border-blue-500 text-blue-800': alert.type === 'info'
                                }">
                                <p class="text-sm">{{ alert.message }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
