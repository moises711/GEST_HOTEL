<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ guests: Array, notifications: Array, planConfig: Object });

const plan = computed(() => props.planConfig || { tier: 'basico', plan_name: 'Básico' });
const guests = computed(() => props.guests || []);
const notifications = computed(() => props.notifications || []);

const outForm = useForm({ guest_id: null });

const doCheckOut = (guestId) => {
    outForm.guest_id = guestId;
    outForm.post(route('admin.checkin.checkout'), { preserveScroll: true });
};

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
    <Head title="Check-in / Check-out" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Check-in / Check-out</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div :class="['bg-gradient-to-r p-6 shadow-sm sm:rounded-lg border-l-4', planTheme.wrapper]">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm text-gray-600">La entrada se registra automáticamente al crear el huésped. Desde este panel solo se marca la salida.</p>
                        <span class="text-xs px-3 py-1 rounded-full" :class="[planTheme.badge, plan.tier === 'alto_empresarial' ? 'animate-pulse' : '']">
                            {{ plan.plan_name }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div :class="['p-4 rounded-lg border bg-gray-50 transition hover:shadow-sm', planTheme.card]">
                            <p class="text-xs text-gray-500">Llegadas hoy</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ guests.length }}</p>
                        </div>
                        <div v-if="plan.tier !== 'basico'" :class="['p-4 rounded-lg border bg-gray-50 transition hover:shadow-sm', planTheme.card]">
                            <p class="text-xs text-gray-500">Cambios rápidos de estado</p>
                            <p class="text-lg font-semibold text-gray-900">Habilitado</p>
                        </div>
                        <div v-if="plan.tier === 'alto_empresarial'" :class="['p-4 rounded-lg border bg-gray-50 transition hover:shadow-sm animate-pulse', planTheme.card]">
                            <p class="text-xs text-gray-500">Alertas inteligentes</p>
                            <p class="text-lg font-semibold text-gray-900">Activas</p>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <h3 class="text-sm font-semibold text-gray-800 mb-3">Huéspedes del hotel</h3>
                            <div class="space-y-2 max-h-96 overflow-auto">
                                <div v-for="guest in guests" :key="guest.id" class="flex items-center justify-between border rounded-md px-3 py-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ guest.name }}</p>
                                        <p class="text-xs text-gray-500">Habitación: {{ guest.room_number || 'Sin asignar' }}</p>
                                        <p class="text-xs" :class="guest.status === 'ingresado' ? 'text-emerald-700' : (guest.status === 'limpieza' ? 'text-blue-700' : 'text-gray-500')">
                                            Estado: {{ guest.status }}
                                        </p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click="doCheckOut(guest.id)" :disabled="guest.status !== 'ingresado' || outForm.processing" class="px-2 py-1 text-xs rounded bg-rose-600 text-white disabled:opacity-50">Salida</button>
                                    </div>
                                </div>
                                <div v-if="guests.length === 0" class="text-xs text-gray-500">No hay huéspedes para gestionar.</div>
                            </div>
                        </div>

                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <h3 class="text-sm font-semibold text-gray-800 mb-3">Notificaciones</h3>
                            <div class="space-y-2 max-h-96 overflow-auto">
                                <div v-for="n in notifications" :key="n.id" class="rounded-md border px-3 py-2"
                                     :class="n.action === 'housekeeping' ? 'bg-blue-50 border-blue-200' : (n.action === 'checkout' ? 'bg-orange-50 border-orange-200' : 'bg-emerald-50 border-emerald-200')">
                                    <p class="text-sm text-gray-800">{{ n.message }}</p>
                                    <p class="text-[11px] text-gray-500">{{ n.happened_at }}</p>
                                </div>
                                <div v-if="notifications.length === 0" class="text-xs text-gray-500">Sin notificaciones aún.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
