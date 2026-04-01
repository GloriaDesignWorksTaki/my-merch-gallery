<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  errors: Record<string, string | undefined>;
}>();

const messages = computed(() =>
  [...new Set(Object.values(props.errors).filter((message): message is string => Boolean(message)))],
);
</script>

<template>
  <div v-if="messages.length" class="glass-panel rounded-3xl border-rose-200/70 bg-rose-50/70 p-4">
    <p class="text-sm font-semibold text-rose-700">入力内容を確認してください。</p>
    <ul class="mt-2 space-y-1 text-sm text-rose-700">
      <li v-for="message in messages" :key="message">{{ message }}</li>
    </ul>
  </div>
</template>
