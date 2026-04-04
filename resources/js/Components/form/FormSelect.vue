<script setup lang="ts">
import AppSelect from '@/Components/form/AppSelect.vue';
import { ref } from 'vue';

withDefaults(
  defineProps<{
    disabled?: boolean;
  }>(),
  {
    disabled: false,
  },
);

const model = defineModel<string | number | boolean | Array<string | number>>({ required: true });

const appSelectRef = ref<{ focus: () => void } | null>(null);

defineExpose({ focus: () => appSelectRef.value?.focus() });
</script>

<template>
  <AppSelect ref="appSelectRef" v-model="model" :disabled="disabled" v-bind="$attrs">
    <slot />
  </AppSelect>
</template>
