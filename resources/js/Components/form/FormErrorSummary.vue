<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
  errors: Record<string, string | undefined>;
}>();

const { t } = useI18n();

const messages = computed(() =>
  [...new Set(Object.values(props.errors).filter((message): message is string => Boolean(message)))],
);
</script>

<template>
  <div v-if="messages.length" class="glass-panel rounded-3xl border-rose-200/70 bg-rose-50/70 p-4">
    <p class="text-sm font-semibold text-rose-700">{{ t('form.reviewInput') }}</p>
    <ul class="mt-2 space-y-1 text-sm text-rose-700">
      <li v-for="message in messages" :key="message">{{ message }}</li>
    </ul>
  </div>
</template>
