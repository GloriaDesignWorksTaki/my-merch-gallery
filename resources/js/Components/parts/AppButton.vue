<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * アプリ共通ボタン
 * variant: 見た目・意味付け
 * size: 高さ・文字サイズ・余白
 * radius: 角丸の強さ（角丸 vs ピル型）
 */
const props = withDefaults(
  defineProps<{
    /** 役割・カラー系（見た目の type に相当） */
    variant?: 'primary' | 'secondary' | 'white' | 'danger' | 'signup' | 'muted';
    size?: 'sm' | 'md' | 'lg';
    /** md = 角丸（2xl）、full = ピル型 */
    radius?: 'md' | 'full';
    /** ネイティブ button の type */
    nativeType?: 'button' | 'submit' | 'reset';
    /** 指定時は Inertia の Link として描画 */
    href?: string;
    disabled?: boolean;
    /** 追加クラス（w-full 等） */
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
      return 'glass-panel border-white/50 bg-white/55 text-slate-700 shadow-sm hover:bg-white/72 active:bg-white/78';
    case 'white':
      return 'border border-white/50 bg-white/70 text-sky-700 shadow-sm backdrop-blur-xl hover:bg-white/80 active:bg-white/85';
    case 'danger':
      return 'border border-transparent bg-red-600 uppercase tracking-widest text-white hover:bg-red-500 focus:ring-red-500/70 active:bg-red-700';
    case 'signup':
      return 'border border-sky-300/80 bg-sky-500 text-white shadow-sm hover:bg-sky-600 active:bg-sky-700';
    case 'muted':
      return 'border border-slate-200/80 bg-white/60 text-slate-600 hover:bg-white active:bg-white';
    case 'primary':
    default:
      return 'glass-panel-strong text-sky-800 hover:bg-white/70 active:bg-white/75';
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

/** danger は従来 UI に合わせて角だけ固定（小さめ） */
const radiusOverride = computed(() => {
  if (props.variant === 'danger') {
    return 'rounded-md';
  }
  return radiusClass.value;
});

const combinedClass = computed(() =>
  [
    'inline-flex items-center justify-center font-semibold transition duration-150 ease-in-out',
    'focus:outline-none focus:ring-2 focus:ring-sky-200/70 focus:ring-offset-0',
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
