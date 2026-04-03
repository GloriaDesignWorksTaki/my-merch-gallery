<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
  href: string;
  active?: boolean;
}>();

const classes = computed(() =>
  props.active
    ? 'inline-flex items-center rounded-full border border-white/50 bg-white/55 px-3 py-2 text-sm font-medium leading-5 text-sky-800 backdrop-blur-xl transition duration-150 ease-in-out'
    : 'inline-flex items-center rounded-full border border-transparent px-3 py-2 text-sm font-medium leading-5 text-slate-600 transition duration-150 ease-in-out hover:border-white/30 hover:bg-white/35 hover:text-slate-900',
);
</script>

<template>
  <Link :href="href" :class="classes">
    <slot />
  </Link>
</template>
