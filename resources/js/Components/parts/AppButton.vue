<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    variant?: 'primary' | 'secondary' | 'white' | 'danger' | 'signup' | 'muted';
    size?: 'sm' | 'md' | 'lg';
    radius?: 'md' | 'full';
    nativeType?: 'button' | 'submit' | 'reset';
    href?: string;
    disabled?: boolean;
    extraClass?: string;
  }>(),
  {
    variant: 'primary',
    size: 'md',
    radius: 'md',
    nativeType: 'button',
  },
);

const variantClass = computed(() => {
  switch (props.variant) {
    case 'secondary':
      return 'glass-panel border-white/50 bg-white/55 text-slate-700 shadow-sm hover:bg-white/72 active:bg-white/78 theme-light:border-slate-300 theme-light:bg-white theme-light:text-slate-800 theme-light:hover:bg-slate-50 theme-light:hover:border-slate-400 theme-light:active:bg-slate-100 dark:border-slate-600 dark:bg-slate-800/95 dark:text-slate-100 dark:hover:bg-slate-700 dark:active:bg-slate-700';
    case 'white':
      return 'border border-white/50 bg-white/70 text-sky-700 shadow-sm backdrop-blur-xl hover:bg-white/80 active:bg-white/85 theme-light:border-slate-300 theme-light:bg-white theme-light:text-sky-800 theme-light:hover:border-sky-400 theme-light:hover:bg-sky-50 theme-light:active:bg-sky-100/80 dark:border-slate-500 dark:bg-slate-800 dark:text-sky-300 dark:hover:bg-slate-700 dark:active:bg-slate-700';
    case 'danger':
      return 'border border-transparent bg-red-600 uppercase tracking-widest text-white hover:bg-red-500 focus:ring-red-500/70 active:bg-red-700';
    case 'signup':
      return 'border border-sky-300/80 bg-sky-500 text-white shadow-sm hover:bg-sky-600 active:bg-sky-700';
    case 'muted':
      return 'border border-slate-200/80 bg-white/60 text-slate-600 hover:bg-white active:bg-white theme-light:border-slate-300 theme-light:bg-slate-50 theme-light:text-slate-700 theme-light:hover:bg-slate-100 theme-light:active:bg-slate-200/80 dark:border-slate-600 dark:bg-slate-800/90 dark:text-slate-300 dark:hover:bg-slate-700 dark:active:bg-slate-700';
    case 'primary':
    default:
      return 'glass-panel-strong text-sky-800 hover:bg-white/70 active:bg-white/75 theme-light:text-sky-900 theme-light:hover:bg-sky-50 theme-light:active:bg-sky-100/90 dark:text-sky-200 dark:hover:bg-slate-800/85 dark:active:bg-slate-800';
  }
});

const sizeClass = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'min-h-9 px-4 py-2 text-xs';
    case 'lg':
      return 'min-h-[3rem] px-5 py-3.5 text-base font-bold';
    case 'md':
    default:
      return 'min-h-11 px-5 py-2.5 text-sm';
  }
});

const radiusClass = computed(() => (props.radius === 'full' ? 'rounded-full' : 'rounded-2xl'));

const radiusOverride = computed(() => {
  if (props.variant === 'danger') {
    return 'rounded-md';
  }
  return radiusClass.value;
});

const combinedClass = computed(() =>
  [
    'inline-flex items-center justify-center font-semibold transition duration-150 ease-in-out',
    'focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500/35 focus:ring-offset-0 theme-primary:focus-visible:ring-sky-200/70',
    'disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-60',
    variantClass.value,
    sizeClass.value,
    radiusOverride.value,
    props.extraClass ?? '',
  ]
    .filter(Boolean)
    .join(' '),
);
</script>

<template>
  <Link
    v-if="href"
    :href="href"
    :class="combinedClass"
  >
    <slot />
  </Link>
  <button
    v-else
    :type="nativeType"
    :disabled="disabled"
    :class="combinedClass"
  >
    <slot />
  </button>
</template>
