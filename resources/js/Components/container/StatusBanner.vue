<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
  status?: string | null;
}>();

const { t } = useI18n();

const STATUS_TO_I18N: Record<string, string> = {
  'band-created': 'flash.bandCreated',
  'band-updated': 'flash.bandUpdated',
  'merch-item-created': 'flash.merchItemCreated',
  'merch-item-updated': 'flash.merchItemUpdated',
  'post-created': 'flash.postCreated',
  'post-updated': 'flash.postUpdated',
};

const display = computed(() => {
  const s = props.status;
  if (!s) {
    return '';
  }
  const key = STATUS_TO_I18N[s];
  return key ? t(key) : s;
});
</script>

<template>
  <div v-if="status" class="glass-panel-strong rounded-3xl px-5 py-4 text-sm font-medium text-sky-900">
    {{ display }}
  </div>
</template>
