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

/** ネイティブ `autofocus` と onMounted の focus を両方付けるとブラウザ警告になるため、属性は DOM に渡さない */
const shouldAutofocus = computed(() => {
  if (!('autofocus' in attrs)) {
    return false;
  }
  const v = attrs.autofocus;
  return v !== false && v !== 'false' && v !== 0 && v !== '0';
});

const inputAttrs = computed(() => {
  const { class: _, autofocus: _a, ...rest } = attrs as Record<string, unknown>;
  return rest;
});

onMounted(() => {
  if (shouldAutofocus.value) {
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
