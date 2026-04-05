<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  href: { type: [String, Object], required: true },
  active: { type: Boolean, default: false },
  tone: { type: String, default: 'indigo' },
  disabled: { type: Boolean, default: false },
  pulse: { type: Boolean, default: false },
});

const toneClasses = computed(() => {
  const map = {
    blue: 'border-blue-300 bg-blue-500/30 text-blue-50',
    sky: 'border-sky-300 bg-sky-500/30 text-sky-50',
    green: 'border-emerald-300 bg-emerald-500/30 text-emerald-50',
    yellow: 'border-amber-300 bg-amber-500/30 text-amber-50',
    red: 'border-rose-300 bg-rose-500/30 text-rose-50',
    indigo: 'border-indigo-300 bg-indigo-500/30 text-indigo-50',
  };

  return map[props.tone] || map.indigo;
});

const inactiveToneClasses = computed(() => {
  const map = {
    blue: 'text-blue-100 border-transparent hover:bg-blue-500/20 hover:border-blue-200',
    sky: 'text-sky-100 border-transparent hover:bg-sky-500/20 hover:border-sky-200',
    green: 'text-emerald-100 border-transparent hover:bg-emerald-500/20 hover:border-emerald-200',
    yellow: 'text-amber-100 border-transparent hover:bg-amber-500/20 hover:border-amber-200',
    red: 'text-rose-100 border-transparent hover:bg-rose-500/20 hover:border-rose-200',
    indigo: 'text-indigo-100 border-transparent hover:bg-indigo-500/20 hover:border-indigo-200',
  };

  return map[props.tone] || map.indigo;
});

const classes = computed(() => {
  return [
    'block px-4 py-2 rounded-md text-sm flex items-center gap-3 border-l-4 transition-all duration-200',
    props.disabled
      ? 'text-gray-500 border-transparent bg-gray-900/20 cursor-not-allowed opacity-70'
      : (props.active ? toneClasses.value : inactiveToneClasses.value),
    props.pulse && props.active ? 'animate-pulse' : '',
  ].join(' ');
});
</script>

<template>
  <div v-if="disabled" :class="classes">
    <slot name="icon" />
    <span class="flex-1"> <slot /> </span>
  </div>
  <Link v-else :href="href" :class="classes">
    <slot name="icon" />
    <span class="flex-1"> <slot /> </span>
  </Link>
</template>
