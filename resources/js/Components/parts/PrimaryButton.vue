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
  <AppButton variant="primary" size="md" radius="full" :native-type="props.type">
    <slot />
  </AppButton>
</template>
