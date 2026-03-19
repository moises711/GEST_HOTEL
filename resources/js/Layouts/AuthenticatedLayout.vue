<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingSidebar = ref(false);
</script>

<template>
    <div class="flex h-screen bg-gray-200 font-sans">
        <!-- Sidebar -->
        <aside
            :class="{ '-translate-x-full': !showingSidebar }"
            class="w-64 bg-gray-900 text-white absolute inset-y-0 left-0 transform md:relative md:translate-x-0 transition duration-300 ease-in-out z-30"
        >
            <!-- Logo -->
            <div class="flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-sm sticky top-0">
                <Link :href="route('dashboard')">
                    <ApplicationLogo class="block h-10 w-auto fill-current text-white" />
                </Link>
            </div>

            <!-- Navigation Links -->
            <nav class="py-4 px-2 space-y-1">
                <NavLink :href="route('dashboard')" :active="route().current('dashboard')" variant="sidebar">
                    Resumen
                </NavLink>
                <NavLink :href="route('superadmin.hotels.index')" :active="route().current('superadmin.hotels.index') || route().current('superadmin.hotels.show')" variant="sidebar">
                    Hoteles
                </NavLink>
                <NavLink :href="route('superadmin.billing.index')" :active="route().current('superadmin.billing.index')" variant="sidebar">
                    Facturación
                </NavLink>
                <NavLink :href="route('superadmin.subscriptions.index')" :active="route().current('superadmin.subscriptions.index')" variant="sidebar">
                    Suscripciones
                </NavLink>
                <NavLink :href="route('superadmin.modules.index')" :active="route().current('superadmin.modules.index')" variant="sidebar">
                    Módulos
                </NavLink>
                <NavLink :href="route('superadmin.plans.index')" :active="route().current('superadmin.plans.index')" variant="sidebar">
                    Planes
                </NavLink>
                <NavLink :href="route('superadmin.tenants.index')" :active="route().current('superadmin.tenants.index')" variant="sidebar">
                    Usuarios
                </NavLink>
                <NavLink :href="route('superadmin.auditlog.index')" :active="route().current('superadmin.auditlog.index')" variant="sidebar">
                    Auditoría
                </NavLink>
                <NavLink :href="route('superadmin.notifications.index')" :active="route().current('superadmin.notifications.index')" variant="sidebar">
                    Notificaciones
                </NavLink>
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
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="container mx-auto">
                    <slot />
                </div>
            </main>
        </div>
        
        <!-- Sidebar overlay for mobile -->
        <div v-if="showingSidebar" @click="showingSidebar = false" class="fixed inset-0 bg-black opacity-50 z-20 md:hidden"></div>
    </div>
</template>
