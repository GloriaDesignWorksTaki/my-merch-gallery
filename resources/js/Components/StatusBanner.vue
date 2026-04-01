<script setup lang="ts">
defineProps<{
  status?: string | null;
}>();

const statusLabels: Record<string, string> = {
  'band-created': 'バンドを登録しました。',
  'band-updated': 'バンド情報を更新しました。',
  'merch-item-created': 'マーチを登録しました。',
  'merch-item-updated': 'マーチ情報を更新しました。',
  'post-created': '投稿を公開しました。',
  'post-updated': '投稿を更新しました。',
};
</script>

<template>
  <div v-if="status" class="glass-panel-strong rounded-3xl px-5 py-4 text-sm font-medium text-sky-900">
    {{ statusLabels[status] ?? status }}
  </div>
</template>
