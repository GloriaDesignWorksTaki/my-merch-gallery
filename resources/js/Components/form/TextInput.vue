<script setup lang="ts">
import AppInput from '@/Components/form/AppInput.vue';
import { ref } from 'vue';

/**
 * 互換ラッパー。見た目の指定は AppInput の variant / size / radius で行う。
 */
const model = defineModel<string>({ required: true });

const appInputRef = ref<{ focus: () => void } | null>(null);

defineExpose({ focus: () => appInputRef.value?.focus() });
</script>

<template>
  <AppInput ref="appInputRef" v-model="model" v-bind="$attrs" />
</template>
