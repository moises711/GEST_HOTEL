<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    hotel: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head :title="`Detalle de ${props.hotel?.name || 'Hotel'}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detalle de Hotel</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6 border">
                    <p class="text-sm text-gray-600 mb-4">
                        Esta vista legacy fue reemplazada por la pantalla principal de detalle de hotel.
                    </p>
                    <div class="space-y-2 text-sm text-gray-700">
                        <p><span class="font-medium">Hotel:</span> {{ props.hotel?.name || '—' }}</p>
                        <p><span class="font-medium">Ubicación:</span> {{ props.hotel?.location || '—' }}</p>
                    </div>
                    <div class="mt-6">
                        <Link :href="route('superadmin.hotels.index')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Volver a hoteles
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>