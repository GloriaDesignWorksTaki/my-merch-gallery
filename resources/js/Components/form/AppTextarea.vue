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

const controlAttrs = computed(() => {
  const { class: _, ...rest } = attrs as Record<string, unknown>;
  return rest;
});

onMounted(() => {
  if (textareaRef.value?.hasAttribute('autofocus')) {
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
