<script setup lang="ts">
import { buildAppFieldClass } from '@/lib/appFieldClasses';
import type { FieldRadius, FieldSize, FieldVariant } from '@/lib/appFieldClasses';
import { computed, onMounted, ref, useAttrs } from 'vue';

defineOptions({ inheritAttrs: false });

const props = withDefaults(
  defineProps<{
    variant?: FieldVariant;
    size?: FieldSize;
    radius?: FieldRadius;
    extraClass?: string;
    disabled?: boolean;
  }>(),
  {
    variant: 'default',
    size: 'md',
    radius: 'md',
    disabled: false,
  },
);

const model = defineModel<string | number | boolean | Array<string | number>>({ required: true });

const attrs = useAttrs();
const selectRef = ref<HTMLSelectElement | null>(null);

const combinedClass = computed(() =>
  buildAppFieldClass({
    variant: props.variant,
    size: props.size,
    radius: props.radius,
    extraClass: props.extraClass,
  }),
);

const mergedClass = computed(() => [combinedClass.value, attrs.class].filter(Boolean));

const shouldAutofocus = computed(() => {
  if (!('autofocus' in attrs)) {
    return false;
  }
  const v = attrs.autofocus;
  return v !== false && v !== 'false' && v !== 0 && v !== '0';
});

const controlAttrs = computed(() => {
  const { class: _, autofocus: _a, ...rest } = attrs as Record<string, unknown>;
  return rest;
});

onMounted(() => {
  if (shouldAutofocus.value) {
    selectRef.value?.focus();
  }
});

defineExpose({ focus: () => selectRef.value?.focus() });
</script>

<template>
  <select
    ref="selectRef"
    v-model="model"
    :disabled="disabled"
    :class="mergedClass"
    v-bind="controlAttrs"
  >
    <slot />
  </select>
</template>
