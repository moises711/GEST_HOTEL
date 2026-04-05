<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  room: Object,
  statusConfig: Object,
});

const emit = defineEmits(['edit', 'registerGuest', 'checkout', 'markReady']);

const onEdit = () => emit('edit', props.room);
const onRegisterGuest = () => emit('registerGuest', props.room);
const onCheckout = () => emit('checkout', props.room);
const onMarkReady = () => emit('markReady', props.room);
</script>

<template>
  <div class="p-3 rounded-xl shadow-sm bg-white border-l-4" :class="statusConfig[room.status]?.borderClass || 'border-gray-200'">
    <div class="flex justify-between items-start">
      <div>
        <h4 class="font-bold text-gray-800">Hab. {{ room.number }}</h4>
        <p class="text-sm text-gray-600">{{ room.type }}</p>
        <div class="mt-1 flex flex-wrap gap-1">
          <span v-if="room.beds" class="text-[11px] px-2 py-0.5 rounded bg-gray-100 text-gray-700">{{ room.beds }} camas</span>
          <span v-if="room.price_per_night" class="text-[11px] px-2 py-0.5 rounded bg-indigo-50 text-indigo-700">S/ {{ Number(room.price_per_night).toLocaleString('es-PE') }}</span>
        </div>
        <div v-if="room.occupant" class="text-xs text-gray-700 mt-1">Ocupante: {{ room.occupant }}</div>
        <div v-if="room.services" class="text-xs text-gray-600 mt-1 line-clamp-2">Servicios: {{ room.services }}</div>
        <div v-else-if="room.notes" class="text-xs text-gray-600 mt-1">{{ room.notes }}</div>
      </div>

      <div class="flex flex-col items-end space-y-2">
        <span :class="statusConfig[room.status]?.textClass + ' text-xs font-medium px-2 py-1 rounded'" :style="{background: 'transparent'}">{{ room.status }}</span>
        <button v-if="room.status === 'Libre'" @click="onRegisterGuest" class="text-[11px] px-2 py-1 rounded bg-emerald-100 text-emerald-700 hover:bg-emerald-200">
            Registrar huésped
        </button>
        <button v-else-if="room.status === 'Ocupada'" @click="onCheckout" class="text-[11px] px-2 py-1 rounded bg-rose-100 text-rose-700 hover:bg-rose-200">
            Marcar salida
        </button>
        <button v-else-if="room.status === 'Limpieza'" @click="onMarkReady" class="text-[11px] px-2 py-1 rounded bg-blue-100 text-blue-700 hover:bg-blue-200">
            Marcar lista
        </button>
        <button v-else-if="room.status === 'Mantenimiento'" @click="onMarkReady" class="text-[11px] px-2 py-1 rounded bg-emerald-100 text-emerald-700 hover:bg-emerald-200">
          Marcar libre
        </button>
        <div class="flex items-center space-x-1">
          <button @click="onEdit" class="p-1 bg-indigo-50 rounded text-indigo-600 hover:bg-indigo-100">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z"></path></svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
