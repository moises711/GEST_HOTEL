<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    plans: {
        type: Array,
        default: () => [],
    },
});

const getButtonClass = (planName) => {
    const normalized = (planName || '').toString().toLowerCase();
    if (normalized.includes('básico') || normalized.includes('basico')) {
        return 'bg-gray-200 text-gray-800 hover:bg-gray-300';
    }
    if (normalized.includes('intermedio') || normalized.includes('pro')) {
        return 'bg-yellow-500 text-white hover:bg-yellow-600';
    }
    if (normalized.includes('alto') || normalized.includes('empresarial') || normalized.includes('premium')) {
        return 'bg-red-500 text-white hover:bg-red-600';
    }
    return 'bg-indigo-500 text-white hover:bg-indigo-600';
};

const usePlanInHotels = (planId) => {
    router.get(route('superadmin.hotels.index'), { plan_id: planId });
};
</script>

<template>
    <Head title="Planes y Niveles" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Planes y Niveles</h2>
        </template>

        <div class="space-y-6">

            <div class="flex justify-end">
             <button @click="router.get(route('superadmin.hotels.create'))" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                    + Crear Nuevo Plan
                </button>
            </div>

            <!-- Tarjetas de Planes -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div v-for="plan in props.plans" :key="plan.id" class="bg-white rounded-lg shadow-lg p-6 flex flex-col transform hover:-translate-y-2 transition-transform duration-300">
                    <h3 class="text-2xl font-bold text-center text-gray-800">{{ plan.name }}</h3>
              <p class="text-4xl font-extrabold text-center my-4">${{ plan.price }}<span class="text-base font-medium text-gray-500">/mes</span></p>
                    
                    <ul class="space-y-3 text-sm text-gray-600 flex-grow">
                <li v-for="feature in (plan.features || [])" :key="feature" class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>{{ feature }}</span>
                        </li>
                <li v-if="!plan.features || plan.features.length === 0" class="text-gray-500">Sin características definidas</li>
                    </ul>

              <button @click="usePlanInHotels(plan.id)" :class="getButtonClass(plan.name)" class="w-full mt-6 py-2 px-4 rounded-lg font-semibold transition-colors">
                        Seleccionar Plan
                    </button>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
