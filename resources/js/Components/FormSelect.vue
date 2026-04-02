<script setup lang="ts">
import { onMounted, ref } from 'vue';

const props = withDefaults(
  defineProps<{
    disabled?: boolean;
  }>(),
  {
    disabled: false,
  },
);

const model = defineModel<string | number | boolean | Array<string | number>>({ required: true });

const select = ref<HTMLSelectElement | null>(null);

onMounted(() => {
  if (select.value?.hasAttribute('autofocus')) {
    select.value?.focus();
  }
});

defineExpose({ focus: () => select.value?.focus() });
</script>

<template>
  <select
    ref="select"
    v-model="model"
    :disabled="props.disabled"
    class="glass-panel w-full rounded-2xl border-white/50 bg-white/45 px-4 py-3 text-slate-800 shadow-[0_10px_30px_rgba(125,166,214,0.12)] focus:border-sky-300/70 focus:ring-sky-200/60"
  >
    <slot />
  </select>
</template>
