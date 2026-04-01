<script setup lang="ts">
defineProps<{
  message?: string;
}>();
</script>

<template>
  <div v-show="message">
    <p class="rounded-2xl bg-rose-100/70 px-3 py-2 text-sm text-rose-700">
      {{ message }}
    </p>
  </div>
</template>
