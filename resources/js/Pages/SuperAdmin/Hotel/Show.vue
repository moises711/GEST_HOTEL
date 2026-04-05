<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import { router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const props = defineProps({ hotel: Object, plans: Array });

const getActivePlan = (hotelData) => {
    const plans = hotelData?.plans || [];
    const active = plans.find((plan) => plan?.pivot?.active);
    if (active) return active;

    return plans
        .slice()
        .sort((a, b) => {
            const aEnd = a?.pivot?.ends_at ? new Date(a.pivot.ends_at).getTime() : 0;
            const bEnd = b?.pivot?.ends_at ? new Date(b.pivot.ends_at).getTime() : 0;
            return bEnd - aEnd;
        })[0] || null;
};

const normalizeModules = (hotelData, activePlan) => {
    const modulesFromHotel = Array.isArray(hotelData?.modules) ? hotelData.modules : [];
    const modulesFromPlan = Array.isArray(activePlan?.modules) ? activePlan.modules : [];
    const sourceModules = modulesFromHotel.length ? modulesFromHotel : modulesFromPlan;

    return sourceModules.map((module) => {
        if (typeof module === 'string') {
            return { id: module, name: module, active: true };
        }

        return {
            id: module?.id ?? module?.name ?? 'module',
            name: module?.name ?? module?.id ?? 'Módulo',
            active: typeof module?.active === 'boolean' ? module.active : true,
        };
    });
};

const hotel = computed(() => {
    const hotelData = props.hotel ?? {};
    const activePlan = getActivePlan(hotelData);
    const suspended = Boolean(hotelData?.settings?.suspended);

    return {
        id: hotelData?.id ?? null,
        name: hotelData?.name ?? '',
        location: hotelData?.location ?? '',
        description: hotelData?.description ?? '',
        status: suspended ? 'Suspendido' : 'Activo',
        settings: hotelData?.settings ?? {},
        contract: {
            plan_name: activePlan?.name ?? 'Sin plan',
            start_date: activePlan?.pivot?.starts_at ?? null,
            end_date: activePlan?.pivot?.ends_at ?? 'Sin vencimiento',
            monthly_rate: Number(activePlan?.price ?? 0),
        },
        modules: normalizeModules(hotelData, activePlan),
        reports: {
            occupancy_rate_monthly: Number(hotelData?.reports?.occupancy_rate_monthly ?? 0),
            average_daily_rate: Number(hotelData?.reports?.average_daily_rate ?? 0),
            total_revenue_monthly: Number(hotelData?.reports?.total_revenue_monthly ?? 0),
        },
    };
});

// ---- LÓGICA DEL COMPONENTE ----

// Formulario para actualizar el estado de los módulos
const modulesForm = useForm({
    modules: (hotel.value.modules || []).reduce((acc, module) => {
        acc[module.id] = module.active;
        return acc;
    }, {})
});

// Formulario para cambiar plan
const planForm = useForm({
    plan_id: props.plans && props.plans.length ? props.plans[0].id : null,
    mode: 'pending', // pending or apply
});

// Edit modal & other modals controls
const showEditModal = ref(false);
const showDeactivateModal = ref(false);
const showApplyPendingModal = ref(false);
const showRenewModal = ref(false);
// loading states for actions
const renewing = ref(false);
const applyingPlan = ref(false);
const savingEdit = ref(false);
const deactivating = ref(false);
const updatingModulesLoading = ref(false);
const uiError = ref('');

const editForm = useForm({
    name: hotel.value.name || '',
    location: hotel.value.location || '',
    description: hotel.value.description || '',
    settings_json: JSON.stringify(hotel.value.settings || {}),
});

const changePlan = () => {
    uiError.value = '';
    if (!planForm.plan_id) {
        uiError.value = 'Selecciona un plan para continuar.';
        return;
    }
    const url = route('superadmin.hotels.change_plan', hotel.value.id);
    planForm.post(url, {
        onBefore: () => applyingPlan.value = true,
        onFinish: () => applyingPlan.value = false,
        onSuccess: () => {
            showApplyPendingModal.value = false;
            Inertia.reload();
        },
        onError: (errors) => {
            uiError.value = errors?.plan_id || errors?.error || 'Error al cambiar plan.';
        }
    });
};

const saveEdit = () => {
    uiError.value = '';
    let settings = null;
    try {
        settings = editForm.settings_json ? JSON.parse(editForm.settings_json) : null;
    } catch (e) {
        uiError.value = 'Settings JSON inválido';
        return;
    }

    editForm.put(route('superadmin.hotels.update', hotel.value.id), {
        data: {
            name: editForm.name,
            location: editForm.location,
            description: editForm.description,
            settings: settings,
        },
        onBefore: () => savingEdit.value = true,
        onFinish: () => savingEdit.value = false,
        onSuccess: () => {
            showEditModal.value = false;
            Inertia.reload();
        },
        onError: (errors) => {
            uiError.value = errors?.name || errors?.settings || errors?.error || 'Error al guardar cambios.';
        }
    });
};

const confirmDeactivate = () => {
    uiError.value = '';
    const url = route('superadmin.hotels.deactivate', hotel.value.id);
    // simple post
    editForm.post(url, {
        onBefore: () => deactivating.value = true,
        onFinish: () => deactivating.value = false,
        onSuccess: () => {
            showDeactivateModal.value = false;
            Inertia.reload();
        },
        onError: (errors) => {
            uiError.value = errors?.error || 'Error al desactivar hotel.';
        }
    });
};

const doRenew = () => {
    uiError.value = '';
    const url = route('superadmin.hotels.renew', hotel.value.id);
    editForm.post(url, {
        onBefore: () => renewing.value = true,
        onFinish: () => renewing.value = false,
        onSuccess: () => {
            showRenewModal.value = false;
            Inertia.reload();
        },
        onError: (errors) => {
            uiError.value = errors?.error || 'Error al renovar contrato.';
        }
    });
};

const updateModules = () => {
    uiError.value = '';
    const url = route('superadmin.hotels.modules.update', hotel.value.id);
    modulesForm.post(url, {
        onBefore: () => updatingModulesLoading.value = true,
        onFinish: () => updatingModulesLoading.value = false,
        onSuccess: () => {
            Inertia.reload();
        },
        onError: (errors) => {
            uiError.value = errors?.modules || errors?.error || 'Error al actualizar módulos.';
        }
    });
};

const viewDetailedReports = () => {
    router.get(route('superadmin.billing.index'));
};

const money = (amount) => `S/ ${Number(amount || 0).toLocaleString('es-PE')}`;

const formatDate = (value) => {
    if (!value || value === 'Sin vencimiento') return value || '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleDateString('es-ES');
};

const contractExpired = computed(() => {
    if (!hotel.value.contract.end_date || hotel.value.contract.end_date === 'Sin vencimiento') return false;
    const end = new Date(hotel.value.contract.end_date);
    if (Number.isNaN(end.getTime())) return false;
    return end < new Date();
});

const activeModulesCount = computed(() =>
    Object.values(modulesForm.modules || {}).filter(Boolean).length
);

</script>

<template>
    <Head :title="`Detalles de ${hotel.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ hotel.name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ hotel.location || 'Sin ubicación definida' }}</p>
                </div>
                <span :class="hotel.status === 'Activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full">
                    {{ hotel.status }}
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div v-if="uiError" class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ uiError }}
                </div>

                <!-- 1. Gestión del Contrato -->
                <div class="bg-gradient-to-br from-indigo-50 to-white overflow-hidden shadow-sm sm:rounded-2xl p-6 border border-indigo-100">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
                        <h3 class="text-lg font-semibold text-gray-900">Gestión del Contrato</h3>
                        <span :class="contractExpired ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'" class="px-3 py-1 rounded-full text-xs font-semibold w-fit">
                            {{ contractExpired ? 'Contrato vencido' : 'Contrato vigente' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-indigo-700">Nivel de Servicio</p>
                            <p class="mt-2 text-xl font-semibold text-indigo-900">{{ hotel.contract.plan_name }}</p>
                        </div>
                        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-amber-700">Vencimiento</p>
                            <p class="mt-2 text-xl font-semibold text-amber-900">{{ formatDate(hotel.contract.end_date) }}</p>
                        </div>
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-emerald-700">Tarifa Mensual</p>
                            <p class="mt-2 text-xl font-semibold text-emerald-900">{{ money(hotel.contract.monthly_rate) }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center justify-end gap-2">
                        <button @click.prevent="showRenewModal = true" :disabled="renewing" :class="['px-4 py-2 rounded-md border-2 text-sm', renewing ? 'bg-blue-300 border-blue-500 text-gray-700' : 'bg-blue-500 text-white border-blue-700 hover:bg-blue-600']">Renovar Contrato</button>

                        <select v-model="planForm.plan_id" class="px-3 py-2 border rounded-md text-sm">
                            <option v-for="p in props.plans" :key="p.id" :value="p.id">{{ p.name }} - {{ money(p.price) }}</option>
                        </select>
                        <select v-model="planForm.mode" class="px-3 py-2 border rounded-md text-sm">
                            <option value="pending">Marcar como pendiente</option>
                            <option value="apply">Aplicar inmediatamente</option>
                        </select>
                        <button @click.prevent="showApplyPendingModal = true" :disabled="applyingPlan" :class="['px-4 py-2 rounded-md border-2 text-sm', applyingPlan ? 'bg-yellow-200 border-yellow-400 text-gray-700' : 'bg-yellow-500 text-white border-yellow-600 hover:bg-yellow-600']">Cambiar Plan</button>

                        <button @click.prevent="showEditModal = true" :class="['px-4 py-2 rounded-md border-2 text-sm', savingEdit ? 'bg-gray-200 border-gray-300 text-gray-700' : 'bg-gray-200 text-gray-800 border-gray-300 hover:bg-gray-300']">Editar</button>
                        <button @click.prevent="showDeactivateModal = true" :disabled="deactivating" :class="['px-4 py-2 rounded-md border-2 text-sm', deactivating ? 'bg-red-200 border-red-400 text-gray-700' : 'bg-red-500 text-white border-red-600 hover:bg-red-600']">Desactivar Hotel</button>
                    </div>
                </div>

                <!-- 2. Gestión de Módulos -->
                <div class="bg-gradient-to-br from-slate-50 to-white overflow-hidden shadow-sm sm:rounded-2xl p-6 border border-slate-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Gestión de Módulos</h3>
                        <span class="text-xs px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-700 font-semibold">
                            {{ activeModulesCount }} activos
                        </span>
                    </div>
                    <form @submit.prevent="updateModules">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div
                                v-for="module in hotel.modules"
                                :key="module.id"
                                :class="[
                                    'flex items-center justify-between p-3 border rounded-xl transition-colors duration-200',
                                    modulesForm.modules[module.id]
                                        ? 'border-indigo-200 bg-indigo-50'
                                        : 'border-gray-200 bg-gray-50'
                                ]"
                            >
                                <span class="text-sm font-medium text-gray-900 uppercase tracking-wide">{{ module.name }}</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="modulesForm.modules[module.id]" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <button type="submit" :disabled="updatingModulesLoading || !modulesForm.isDirty" :class="['px-4 py-2 rounded-md border-2', updatingModulesLoading ? 'bg-indigo-300 border-indigo-400 text-gray-700' : 'bg-indigo-600 text-white border-indigo-700 hover:bg-indigo-700']">Guardar Cambios de Módulos</button>
                        </div>
                    </form>
                </div>

                <!-- 3. Reportes y Estadísticas -->
                <div class="bg-gradient-to-br from-violet-50 to-white overflow-hidden shadow-sm sm:rounded-2xl p-6 border border-violet-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Reportes y Estadísticas (Mensual)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                         <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-emerald-700">Ingresos Totales</p>
                            <p class="mt-2 text-2xl font-semibold text-emerald-900">{{ money(hotel.reports.total_revenue_monthly) }}</p>
                        </div>
                        <div class="rounded-2xl border border-sky-100 bg-sky-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-sky-700">Tasa de Ocupación</p>
                            <p class="mt-2 text-2xl font-semibold text-sky-900">{{ hotel.reports.occupancy_rate_monthly }}%</p>
                        </div>
                        <div class="rounded-2xl border border-violet-100 bg-violet-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-violet-700">Tarifa Diaria Promedio (ADR)</p>
                            <p class="mt-2 text-2xl font-semibold text-violet-900">{{ money(hotel.reports.average_daily_rate) }}</p>
                        </div>
                    </div>
                     <div class="mt-6 flex justify-end">
                        <button @click="viewDetailedReports" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Ver Reportes Detallados</button>
                    </div>
                </div>

                 <!-- 4. Zona de Peligro -->
                <div class="bg-red-50 border border-red-200 overflow-hidden shadow-sm sm:rounded-2xl p-6">
                    <h3 class="text-lg font-medium text-red-900 mb-2">Zona de Peligro</h3>
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-red-800">Desactivar este hotel revocará el acceso a todos sus administradores y detendrá la sincronización de datos.</p>
                        <button @click.prevent="showDeactivateModal = true" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Desactivar Hotel</button>
                    </div>
                </div>

                <!-- Modales -->
                <Modal :show="showEditModal" @close="showEditModal = false" maxWidth="lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Editar Hotel</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nombre</label>
                                <input v-model="editForm.name" type="text" class="mt-1 block w-full rounded-md border-gray-300" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ubicación</label>
                                <input v-model="editForm.location" type="text" class="mt-1 block w-full rounded-md border-gray-300" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Descripción</label>
                                <textarea v-model="editForm.description" class="mt-1 block w-full rounded-md border-gray-300" rows="3"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Settings (JSON)</label>
                                <textarea v-model="editForm.settings_json" class="mt-1 block w-full rounded-md border-gray-300" rows="4" placeholder='{"max_users":10}'></textarea>
                            </div>
                                <div class="flex justify-end space-x-2">
                                <button @click="showEditModal = false" class="px-4 py-2 bg-white border rounded-md">Cancelar</button>
                                <button @click.prevent="saveEdit" :disabled="savingEdit" :class="['px-4 py-2 rounded-md border-2', savingEdit ? 'bg-indigo-300 border-indigo-400 text-gray-700' : 'bg-indigo-600 text-white border-indigo-700 hover:bg-indigo-700']">Guardar</button>
                            </div>
                        </div>
                    </div>
                </Modal>

                <Modal :show="showDeactivateModal" @close="showDeactivateModal = false" maxWidth="md">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-red-700 mb-4">Confirmar desactivación</h3>
                        <p class="text-sm text-gray-700 mb-4">¿Estás seguro? Esta acción revocará el acceso a todos los administradores del hotel.</p>
                        <div class="flex justify-end space-x-2">
                            <button @click="showDeactivateModal = false" class="px-4 py-2 bg-white border rounded-md">Cancelar</button>
                            <button @click.prevent="confirmDeactivate" class="px-4 py-2 bg-red-600 text-white rounded-md">Desactivar</button>
                        </div>
                    </div>
                </Modal>

                <Modal :show="showApplyPendingModal" @close="showApplyPendingModal = false" maxWidth="md">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Aplicar cambio de plan</h3>
                        <p class="text-sm text-gray-700 mb-4">Vas a cambiar el plan. ¿Deseas aplicarlo inmediatamente o marcarlo como pendiente?</p>
                        <div class="flex items-center gap-2 mb-4">
                            <select v-model="planForm.plan_id" class="px-3 py-2 border rounded-md">
                                <option v-for="p in props.plans" :key="p.id" :value="p.id">{{ p.name }} - ${{ p.price }}</option>
                            </select>
                            <select v-model="planForm.mode" class="px-3 py-2 border rounded-md">
                                <option value="pending">Marcar como pendiente</option>
                                <option value="apply">Aplicar inmediatamente</option>
                            </select>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button @click="showApplyPendingModal = false" class="px-4 py-2 bg-white border rounded-md">Cancelar</button>
                               <button @click.prevent="changePlan" :disabled="applyingPlan" :class="['px-4 py-2 rounded-md border-2', applyingPlan ? 'bg-yellow-200 border-yellow-400 text-gray-700' : 'bg-yellow-500 text-white border-yellow-600 hover:bg-yellow-600']">Confirmar</button>
                        </div>
                    </div>
                </Modal>

                <Modal :show="showRenewModal" @close="showRenewModal = false" maxWidth="md">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Renovar contrato</h3>
                        <p class="text-sm text-gray-700 mb-4">Renovar contrato extenderá la fecha de vencimiento un mes más. ¿Deseas continuar?</p>
                        <div class="flex justify-end space-x-2">
                            <button @click="showRenewModal = false" class="px-4 py-2 bg-white border rounded-md">Cancelar</button>
                            <button @click.prevent="doRenew" class="px-4 py-2 bg-blue-600 text-white rounded-md">Renovar</button>
                        </div>
                    </div>
                </Modal>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
