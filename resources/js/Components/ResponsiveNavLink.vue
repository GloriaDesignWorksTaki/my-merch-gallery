<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
  href: string;
  active?: boolean;
}>();

const classes = computed(() =>
  props.active
    ? 'glass-panel block w-full rounded-2xl px-4 py-3 text-start text-base font-medium text-sky-800 transition duration-150 ease-in-out'
    : 'block w-full rounded-2xl px-4 py-3 text-start text-base font-medium text-slate-600 transition duration-150 ease-in-out hover:bg-white/30 hover:text-slate-900',
);
</script>

<template>
  <Link :href="href" :class="classes">
    <slot />
  </Link>
</template>
