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
  }>(),
  {
    variant: 'default',
    size: 'md',
    radius: 'md',
  },
);

const attrs = useAttrs();
const textareaRef = ref<HTMLTextAreaElement | null>(null);

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
    textareaRef.value?.focus();
  }
});

defineExpose({ focus: () => textareaRef.value?.focus() });
</script>

<template>
  <textarea
    ref="textareaRef"
    v-model="model"
    :class="mergedClass"
    v-bind="controlAttrs"
  />
</template>
