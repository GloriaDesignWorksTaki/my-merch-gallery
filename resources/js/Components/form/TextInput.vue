<script setup lang="ts">
import AppInput from '@/Components/form/AppInput.vue';
import { ref } from 'vue';

const model = defineModel<string>({ required: true });

const appInputRef = ref<{ focus: () => void } | null>(null);

defineExpose({ focus: () => appInputRef.value?.focus() });
</script>

<template>
  <AppInput ref="appInputRef" v-model="model" v-bind="$attrs" />
</template>
