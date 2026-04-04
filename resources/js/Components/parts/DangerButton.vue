<script setup lang="ts">
import AppButton from '@/Components/parts/AppButton.vue';

const props = withDefaults(
  defineProps<{
    type?: 'button' | 'submit' | 'reset';
  }>(),
  {
    type: 'button',
  },
);
</script>

<template>
  <AppButton variant="danger" size="sm" radius="md" :native-type="props.type">
    <slot />
  </AppButton>
</template>
