<script setup lang="ts">
import AppTextarea from '@/Components/form/AppTextarea.vue';
import { ref } from 'vue';

const model = defineModel<string>({ required: true });

const appTextareaRef = ref<{ focus: () => void } | null>(null);

defineExpose({ focus: () => appTextareaRef.value?.focus() });
</script>

<template>
  <AppTextarea ref="appTextareaRef" v-model="model" v-bind="$attrs" />
</template>
