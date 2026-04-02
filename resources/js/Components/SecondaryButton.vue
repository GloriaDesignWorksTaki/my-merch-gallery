<script setup lang="ts">
withDefaults(
  defineProps<{
    type?: 'button' | 'submit' | 'reset';
  }>(),
  {
    type: 'button',
  },
);
</script>

<template>
  <button
    :type="type"
    class="glass-panel inline-flex min-h-11 items-center justify-center rounded-full border-white/50 bg-white/55 px-5 py-2.5 text-sm font-semibold text-slate-700 transition duration-150 ease-in-out hover:bg-white/72 focus:outline-none focus:ring-2 focus:ring-sky-200/70 focus:ring-offset-0 disabled:cursor-not-allowed disabled:opacity-60"
  >
    <slot />
  </button>
</template>
