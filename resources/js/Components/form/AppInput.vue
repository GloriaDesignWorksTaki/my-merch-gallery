<script setup lang="ts">
import { buildAppFieldClass } from '@/lib/appFieldClasses';
import type { FieldRadius, FieldSize, FieldVariant } from '@/lib/appFieldClasses';
import { computed, onMounted, ref, useAttrs } from 'vue';

defineOptions({ inheritAttrs: false });

const model = defineModel<string>({ required: true });

const props = withDefaults(
  defineProps<{
    variant?: FieldVariant;
    size?: FieldSize;
    radius?: FieldRadius;
    extraClass?: string;
    /** input の type（text, email, password, search …） */
    inputType?: string;
  }>(),
  {
    variant: 'default',
    size: 'md',
    radius: 'md',
    inputType: 'text',
  },
);

const attrs = useAttrs();
const inputRef = ref<HTMLInputElement | null>(null);

const combinedClass = computed(() =>
  buildAppFieldClass({
    variant: props.variant,
    size: props.size,
    radius: props.radius,
    extraClass: props.extraClass,
  }),
);

const mergedClass = computed(() => [combinedClass.value, attrs.class].filter(Boolean));

const inputAttrs = computed(() => {
  const { class: _, ...rest } = attrs as Record<string, unknown>;
  return rest;
});

onMounted(() => {
  if (inputRef.value?.hasAttribute('autofocus')) {
    inputRef.value?.focus();
  }
});

defineExpose({ focus: () => inputRef.value?.focus() });
</script>

<template>
  <input
    ref="inputRef"
    v-model="model"
    :type="inputType"
    :class="mergedClass"
    v-bind="inputAttrs"
  />
</template>
