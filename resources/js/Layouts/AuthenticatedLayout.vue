<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import SidebarLink from '@/Components/SidebarLink.vue';
import { Link } from '@inertiajs/vue3';

const showingSidebar = ref(false);

const page = usePage();
const sharedPlanConfig = computed(() => page?.props?.planConfig || null);

const flash = computed(() => page?.props?.flash || {});

const currentHotel = computed(() => {
    const user = page?.props?.auth?.user ?? null;
    return user?.hotels?.[0] ?? null;
});

const isSuperAdmin = computed(() => {
    const user = page?.props?.auth?.user ?? null;
    return Boolean(user?.is_superadmin || user?.type === 'superadmin');
});

const currentHotelPlan = computed(() => {
    const plans = currentHotel.value?.plans || [];
    const activePlan = plans.find((plan) => Boolean(plan?.pivot?.active));
    if (activePlan) {
        return activePlan;
    }

    return plans
        .slice()
        .sort((a, b) => {
            const aEnd = a?.pivot?.ends_at ? new Date(a.pivot.ends_at).getTime() : 0;
            const bEnd = b?.pivot?.ends_at ? new Date(b.pivot.ends_at).getTime() : 0;
            return bEnd - aEnd;
        })[0] || null;
});

const inferTierFromName = (name) => {
    const value = (name || '').toString().toLowerCase();
    if (value.includes('alto') || value.includes('empresarial') || value.includes('premium')) return 'alto_empresarial';
    if (value.includes('intermedio') || value.includes('pro')) return 'intermedio';
    if (value.includes('básico') || value.includes('basico')) return 'basico';
    return null;
};

const tierWeight = (tier) => {
    const levels = { basico: 1, intermedio: 2, alto_empresarial: 3 };
    return levels[tier] || 0;
};

const planName = computed(() => {
    const plan = sharedPlanConfig.value?.plan_name ?? currentHotelPlan.value?.name ?? null;
    return plan ? plan.toString().toLowerCase() : null;
});

const modules = computed(() => {
    const fromShared = sharedPlanConfig.value?.enabled_modules;
    if (Array.isArray(fromShared) && fromShared.length) {
        return fromShared;
    }

    const hotel = currentHotel.value;
    return hotel?.modules || currentHotelPlan.value?.modules || [];
});

const normalizedModules = computed(() => {
    const value = modules.value;

    if (Array.isArray(value)) {
        return value;
    }

    if (typeof value === 'string') {
        try {
            const parsed = JSON.parse(value);
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    }

    return [];
});

const tierDefaultModules = computed(() => {
    if (planTier.value === 'alto_empresarial') {
        return ['rooms', 'reservations', 'guests', 'housekeeping', 'users', 'notes', 'analytics', 'maintenance', 'customization', 'dark_mode'];
    }

    if (planTier.value === 'intermedio') {
        return ['rooms', 'reservations', 'guests', 'housekeeping', 'users', 'notes'];
    }

    return ['rooms', 'reservations', 'guests'];
});

const effectiveModules = computed(() => {
    const merged = [...normalizedModules.value, ...tierDefaultModules.value];
    return Array.from(new Set(merged));
});

const hasModule = (name) => {
    return effectiveModules.value.indexOf(name) !== -1;
};

const planTier = computed(() => {
    const candidates = [
        sharedPlanConfig.value?.tier,
        inferTierFromName(sharedPlanConfig.value?.plan_name),
        inferTierFromName(currentHotelPlan.value?.name),
    ].filter(Boolean);

    const fromModules = normalizedModules.value;
    if (fromModules.includes('maintenance') || fromModules.includes('customization') || fromModules.includes('dark_mode') || fromModules.includes('analytics')) {
        candidates.push('alto_empresarial');
    } else if (fromModules.includes('users') || fromModules.includes('notes') || fromModules.includes('housekeeping')) {
        candidates.push('intermedio');
    }

    if (!candidates.length) {
        return 'basico';
    }

    return candidates.sort((a, b) => tierWeight(b) - tierWeight(a))[0];
});

const hasTier = (tier) => {
    return tierWeight(planTier.value) >= tierWeight(tier);
};

const accent = computed(() => {
    if (isSuperAdmin.value) {
        return { bg: 'bg-slate-700', badgeBg: 'bg-slate-200', badgeText: 'text-slate-700', token: 'slate' };
    }

    if (planTier.value === 'basico') {
        return { bg: 'bg-sky-600', badgeBg: 'bg-sky-100', badgeText: 'text-sky-700', token: 'sky' };
    }
    if (planTier.value === 'intermedio') {
        return { bg: 'bg-yellow-600', badgeBg: 'bg-yellow-100', badgeText: 'text-yellow-700', token: 'yellow' };
    }
    if (planTier.value === 'alto_empresarial') {
        return { bg: 'bg-blue-700', badgeBg: 'bg-blue-100', badgeText: 'text-blue-700', token: 'blue' };
    }
    return { bg: 'bg-indigo-600', badgeBg: 'bg-indigo-100', badgeText: 'text-indigo-700', token: 'indigo' };
});

const sidebarTheme = computed(() => {
    if (accent.value.token === 'slate') {
        return {
            bg: 'bg-gradient-to-b from-slate-800 via-slate-900 to-black',
            border: 'border-slate-300/30',
            dot: 'bg-slate-200',
            top: 'bg-black/70',
            edge: 'border-l-8 border-slate-400',
        };
    }

    if (accent.value.token === 'sky') {
        return {
            bg: 'bg-gradient-to-b from-sky-500 via-sky-700 to-sky-900',
            border: 'border-sky-200/50',
            dot: 'bg-sky-200',
            top: 'bg-sky-800/80',
            edge: 'border-l-8 border-sky-300',
        };
    }
    if (accent.value.token === 'blue') {
        return {
            bg: 'bg-gradient-to-b from-blue-700 via-blue-800 to-blue-950',
            border: 'border-blue-200/50',
            dot: 'bg-blue-200',
            top: 'bg-blue-900/80',
            edge: 'border-l-8 border-blue-300',
        };
    }
    if (accent.value.token === 'yellow') {
        return {
            bg: 'bg-gradient-to-b from-amber-500 via-amber-700 to-amber-900',
            border: 'border-amber-200/50',
            dot: 'bg-amber-200',
            top: 'bg-amber-800/80',
            edge: 'border-l-8 border-amber-300',
        };
    }
    return {
        bg: 'bg-gradient-to-b from-indigo-950 to-gray-950',
        border: 'border-indigo-500/40',
        dot: 'bg-indigo-400',
        top: 'bg-indigo-900/80',
        edge: 'border-l-8 border-indigo-300',
    };
});

const enabledCount = computed(() => effectiveModules.value.length);

const isMenuOptionVisible = (option) => {
    const rules = {
        dashboard: () => true,
        rooms: () => hasModule('rooms'),
        reservations: () => hasModule('reservations'),
        checkin: () => hasModule('checkin') || hasModule('reservations'),
        guests: () => hasModule('guests'),
        users: () => hasModule('users') || hasTier('intermedio'),
        notes: () => hasModule('notes') || hasTier('intermedio'),
        maintenance: () => hasModule('maintenance') || hasTier('alto_empresarial'),
        customization: () => hasModule('customization') || hasTier('alto_empresarial'),
        darkMode: () => hasModule('dark_mode') || hasTier('alto_empresarial'),
        finances: () => hasTier('intermedio'),
        reports: () => hasTier('intermedio'),
        loyalty: () => hasTier('intermedio'),
        analytics: () => hasModule('analytics') || hasTier('alto_empresarial'),
        channelManager: () => hasTier('alto_empresarial'),
    };

    return rules[option] ? rules[option]() : false;
};
</script>

<template>
    <div class="flex h-screen bg-gray-200 font-sans">
        <!-- Sidebar -->
        <aside
            :class="[sidebarTheme.bg, sidebarTheme.edge, { '-translate-x-full': !showingSidebar }]"
            class="w-64 text-white absolute inset-y-0 left-0 transform md:relative md:translate-x-0 transition duration-300 ease-in-out z-30 border-r"
        >
            <!-- Perfil -->
                <div :class="['px-4 py-4 border-b', sidebarTheme.border]">
                    <div class="flex items-center gap-3">
                    <div :class="[accent.bg, 'h-10 w-10 rounded-full flex items-center justify-center text-white font-semibold']">{{ $page.props.auth.user.name.split(' ').map(n => n[0]).join('').slice(0,2) }}</div>
                    <div>
                        <div class="text-sm font-medium">{{ $page.props.auth.user.name }}</div>
                        <div class="text-xs text-gray-400">{{ $page.props.auth.user.email }}</div>
                        <div v-if="!($page.props.auth.user.is_superadmin || $page.props.auth.user.type === 'superadmin')" class="mt-2 text-[10px] text-gray-300 flex items-center gap-2">
                            <span :class="['inline-block w-2 h-2 rounded-full', sidebarTheme.dot, planTier === 'alto_empresarial' ? 'animate-pulse' : '']"></span>
                            <span>{{ enabledCount }} módulos activos</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="py-4 px-2 space-y-1">
                <!-- Superadmin menu -->
                <template v-if="$page.props.auth.user.is_superadmin || $page.props.auth.user.type === 'superadmin'">
                    <SidebarLink :href="route('superadmin.dashboard')" :active="route().current('superadmin.dashboard')">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6"/></svg>
                        </template>
                        panel principal
                    </SidebarLink>
                    <SidebarLink :href="route('superadmin.hotels.index')" :active="route().current('superadmin.hotels.index') || route().current('superadmin.hotels.show')">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h3v-6h4v6h3a2 2 0 002-2V7L12 3 3 7z"/></svg>
                        </template>
                        Hoteles
                    </SidebarLink>
                    <SidebarLink :href="route('superadmin.billing.index')" :active="route().current('superadmin.billing.index')">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V4m0 16v-4"/></svg>
                        </template>
                        Facturación
                    </SidebarLink>
                    <SidebarLink :href="route('superadmin.subscriptions.index')" :active="route().current('superadmin.subscriptions.index')">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                        </template>
                        Suscripciones
                    </SidebarLink>
                    <SidebarLink :href="route('superadmin.modules.index')" :active="route().current('superadmin.modules.index')">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </template>
                        Módulos
                    </SidebarLink>
                    <SidebarLink :href="route('superadmin.plans.index')" :active="route().current('superadmin.plans.index')">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2"/></svg>
                        </template>
                        Planes
                    </SidebarLink>
                    <SidebarLink :href="route('superadmin.admins.index')" :active="route().current('superadmin.admins.index')">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </template>
                        Usuarios
                    </SidebarLink>
                    <SidebarLink :href="route('superadmin.notifications.index')" :active="route().current('superadmin.notifications.index')">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </template>
                        Notificaciones
                    </SidebarLink>
                </template>

                <!-- Admin menu -->
                <template v-else-if="$page.props.auth.user.type === 'admin' || ($page.props.auth.user.roles && $page.props.auth.user.roles.some(r => r.name === 'admin'))">
                    <SidebarLink v-if="isMenuOptionVisible('dashboard')" :href="route('admin.dashboard')" :active="route().current('admin.dashboard')" :tone="accent.token" :pulse="planTier === 'alto_empresarial'">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h18M3 6h18M3 18h18"/></svg>
                        </template>
                        Panel
                    </SidebarLink>
                    <SidebarLink v-if="isMenuOptionVisible('reservations')" :href="route('admin.reservations.index')" :active="route().current('admin.reservations.index')" :tone="accent.token">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3M16 7V3M3 11h18M5 21h14a2 2 0 002-2V11H3v8a2 2 0 002 2z"/></svg>
                        </template>
                        Reservas
                    </SidebarLink>
                    <SidebarLink v-if="isMenuOptionVisible('checkin')" :href="route('admin.checkin.index')" :active="route().current('admin.checkin.index')" :tone="accent.token">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/></svg>
                        </template>
                        Check-in
                    </SidebarLink>
                    <SidebarLink v-if="isMenuOptionVisible('guests')" :href="route('admin.guests.index')" :active="route().current('admin.guests.index')" :tone="accent.token">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A12.07 12.07 0 0112 15c2.5 0 4.847.73 6.879 1.98M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </template>
                        Huéspedes
                    </SidebarLink>
                    <SidebarLink v-if="isMenuOptionVisible('finances')" :href="route('admin.finances.index')" :active="route().current('admin.finances.index')" :tone="accent.token">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2"/></svg>
                        </template>
                        Finanzas
                    </SidebarLink>
                    <SidebarLink v-if="isMenuOptionVisible('reports')" :href="route('admin.reports.index')" :active="route().current('admin.reports.index')" :tone="accent.token">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m3 6V7m3 10v-4m4 6H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2z"/></svg>
                        </template>
                        Reportes
                    </SidebarLink>
                    <SidebarLink v-if="isMenuOptionVisible('loyalty')" :href="route('admin.loyalty.index')" :active="route().current('admin.loyalty.index')" :tone="accent.token">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        </template>
                        Fidelización
                    </SidebarLink>
                    <SidebarLink v-if="isMenuOptionVisible('users')" :href="route('admin.users.index')" :active="route().current('admin.users.index')" :tone="accent.token">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </template>
                        Usuarios
                    </SidebarLink>
                    <SidebarLink v-if="isMenuOptionVisible('notes')" :href="route('admin.notes.index')" :active="route().current('admin.notes.index')" :tone="accent.token">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z"/></svg>
                        </template>
                        Notas
                    </SidebarLink>
                    <SidebarLink v-if="isMenuOptionVisible('rooms')" :href="route('admin.rooms.index')" :active="route().current('admin.rooms.index')" :tone="accent.token">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7"/></svg>
                        </template>
                        Habitaciones
                    </SidebarLink>
                    <SidebarLink v-if="isMenuOptionVisible('maintenance')" :href="route('admin.maintenance.index')" :active="route().current('admin.maintenance.index')" :tone="accent.token" :pulse="planTier === 'alto_empresarial'">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317a1 1 0 011.35-.936l7.5 3.5a1 1 0 01.325 1.618l-7.5 8.5a1 1 0 01-1.654-.274l-2.5-5a1 1 0 01.18-1.12l2.299-2.288-2.18-4z"/></svg>
                        </template>
                        Mantenimiento
                    </SidebarLink>
                    <SidebarLink v-if="isMenuOptionVisible('customization')" :href="route('admin.customization.index')" :active="route().current('admin.customization.index')" :tone="accent.token" :pulse="planTier === 'alto_empresarial'">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 16v-2m8-6h-2M6 12H4m11.314 5.314l-1.414-1.414M8.1 8.1 6.686 6.686m8.628 0L13.9 8.1M8.1 15.9l-1.414 1.414"/></svg>
                        </template>
                        Personalización
                    </SidebarLink>
                    <SidebarLink v-if="isMenuOptionVisible('darkMode')" :href="route('admin.dark-mode.index')" :active="route().current('admin.dark-mode.index')" :tone="accent.token" :pulse="planTier === 'alto_empresarial'">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3c0 .32.02.64.05.95A7 7 0 0021 12.79z"/></svg>
                        </template>
                        Modo oscuro
                    </SidebarLink>
                    <SidebarLink v-if="isMenuOptionVisible('analytics')" :href="route('admin.analytics.index')" :active="route().current('admin.analytics.index')" :tone="accent.token" :pulse="planTier === 'alto_empresarial'">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18"/></svg>
                        </template>
                        Analytics
                    </SidebarLink>
                    <SidebarLink v-if="isMenuOptionVisible('channelManager')" :href="route('admin.channel-manager.index')" :active="route().current('admin.channel-manager.index')" :tone="accent.token" :pulse="planTier === 'alto_empresarial'">
                        <template #icon>
                            <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h8m-8 4h6m4 8H6a2 2 0 01-2-2V7a2 2 0 012-2h3l2-2h2l2 2h3a2 2 0 012 2v12a2 2 0 01-2 2z"/></svg>
                        </template>
                        Channel Manager
                    </SidebarLink>
                </template>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="flex justify-between items-center p-4 bg-white border-b border-gray-200">
                <button @click="showingSidebar = !showingSidebar" class="md:hidden text-gray-600 hover:text-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                
                <div class="flex-1">
                    <!-- Page Heading -->
                    <header class="w-full" v-if="$slots.header">
                        <slot name="header" />
                    </header>
                </div>

                <!-- Settings Dropdown -->
                <div class="relative">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition duration-150 ease-in-out">
                                <div>{{ $page.props.auth.user.name }}</div>
                                <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')"> Perfil </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">
                                Cerrar Sesión
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>

                <!-- Visible logout button (desktop) -->
                <div class="hidden md:flex items-center ms-4">
                    <Link :href="route('logout')" method="post" class="text-sm text-gray-600 hover:text-gray-800">
                        Cerrar Sesión
                    </Link>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="container mx-auto">
                    <div v-if="flash.success || flash.error" class="fixed top-6 right-6 z-50 w-96">
                        <div v-if="flash.success" class="mb-2 px-4 py-3 rounded shadow bg-green-50 border border-green-200 text-green-800">{{ flash.success }}</div>
                        <div v-if="flash.error" class="px-4 py-3 rounded shadow bg-red-50 border border-red-200 text-red-800">{{ flash.error }}</div>
                    </div>
                    <slot />
                </div>
            </main>
        </div>
        
        <!-- Sidebar overlay for mobile -->
        <div v-if="showingSidebar" @click="showingSidebar = false" class="fixed inset-0 bg-black opacity-50 z-20 md:hidden"></div>
    </div>
</template>
