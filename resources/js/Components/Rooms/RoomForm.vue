<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
  show: { type: Boolean, default: false },
  action: { type: String, default: null },
  initial: { type: Object, default: () => ({ number: '', beds: 1, services: '', price_per_night: '' }) },
});

const emit = defineEmits(['close','saved']);

const form = useForm({
  number: props.initial.number || '',
  beds: props.initial.beds || 1,
  services: props.initial.services || '',
  price_per_night: props.initial.price_per_night || '',
});

watch(() => props.initial, (v) => {
  form.reset();
  form.number = v.number || '';
  form.beds = v.beds || 1;
  form.services = v.services || '';
  form.price_per_night = v.price_per_night || '';
});

const close = () => emit('close');

const submit = () => {
  if (!props.action) return;
  form.post(props.action, {
    onSuccess: () => {
      emit('saved');
      close();
    }
  });
};
</script>

<template>
  <Modal :show="show" @close="close" maxWidth="md">
    <div class="p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">{{ initial.id ? 'Editar Habitación' : 'Nueva Habitación' }}</h3>

      <div class="space-y-4">
        <div v-if="form.errors.error" class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
          {{ form.errors.error }}
        </div>

        <div>
          <InputLabel for="number">Número</InputLabel>
          <TextInput id="number" v-model="form.number" class="mt-1 block w-full" />
          <p v-if="form.errors.number" class="mt-1 text-xs text-red-600">{{ form.errors.number }}</p>
        </div>

        <div>
          <InputLabel for="beds">Camas</InputLabel>
          <TextInput id="beds" type="number" min="1" max="10" v-model="form.beds" class="mt-1 block w-full" />
          <p v-if="form.errors.beds" class="mt-1 text-xs text-red-600">{{ form.errors.beds }}</p>
        </div>

        <div>
          <InputLabel for="price_per_night">Costo por noche</InputLabel>
          <TextInput id="price_per_night" type="number" min="0" step="0.01" v-model="form.price_per_night" class="mt-1 block w-full" />
          <p v-if="form.errors.price_per_night" class="mt-1 text-xs text-red-600">{{ form.errors.price_per_night }}</p>
        </div>

        <div>
          <InputLabel for="services">Servicios incluidos</InputLabel>
          <textarea id="services" v-model="form.services" rows="3" class="mt-1 block w-full border-gray-300 rounded-md" placeholder="WiFi, TV, desayuno, aire acondicionado..."></textarea>
          <p v-if="form.errors.services" class="mt-1 text-xs text-red-600">{{ form.errors.services }}</p>
        </div>

        <div class="flex justify-end space-x-2">
          <button @click="close" type="button" class="px-4 py-2 bg-white border rounded-md">Cancelar</button>
          <PrimaryButton @click.prevent="submit">Guardar</PrimaryButton>
        </div>
      </div>
    </div>
  </Modal>
</template>
