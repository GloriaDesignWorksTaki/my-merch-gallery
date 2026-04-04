<script setup lang="ts">
import SeoHead from '@/Components/seo/SeoHead.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CompactPagination from '@/Components/parts/CompactPagination.vue';
import type { PaginatedList } from '@/types/inertia';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { notificationTargetUrl } from '@/utils/notificationTarget';

const { t } = useI18n();

type Row = {
  id: string;
  read_at: string | null;
  created_at: string;
  data: Record<string, unknown>;
};

const props = defineProps<{
  notifications: PaginatedList<Row>;
}>();

function summaryLine(data: Record<string, unknown>): string {
  const type = data.type;
  const name = typeof data.actor_name === 'string' ? data.actor_name : '';
  const merch = typeof data.merch_item_name === 'string' ? data.merch_item_name : '';
  if (type === 'merch_item_liked') {
    return t('notifications.summary.merchItemLiked', { name, merch });
  }
  if (type === 'merch_item_commented') {
    return t('notifications.summary.merchCommented', { name, merch });
  }
  if (type === 'merch_comment_replied') {
    return t('notifications.summary.merchCommentReplied', { name, merch });
  }
  if (type === 'merch_comment_liked') {
    return t('notifications.summary.merchCommentLiked', { name, merch });
  }
  return t('notifications.summary.fallback');
}

function openRow(row: Row) {
  const url = notificationTargetUrl(row.data);
  router.post(
    route('notifications.read', row.id),
    {},
    {
      preserveScroll: true,
      onSuccess: () => router.visit(url),
    },
  );
}

function markAll() {
  router.post(route('notifications.read-all'), {}, { preserveScroll: true });
}
</script>

<template>
  <SeoHead page="notifications" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
          <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('notifications.eyebrow') }}</p>
          <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-800">{{ t('notifications.pageTitle') }}</h2>
        </div>
        <button
          v-if="notifications.data.some((n) => n.read_at === null)"
          type="button"
          class="glass-link text-sm font-medium"
          @click="markAll"
        >
          {{ t('notifications.markAllRead') }}
        </button>
      </div>
    </template>

    <div class="mx-auto max-w-2xl">
      <ul v-if="notifications.data.length" class="space-y-2">
        <li v-for="n in notifications.data" :key="n.id">
          <button
            type="button"
            class="glass-surface w-full px-4 py-4 text-left transition hover:bg-white/40"
            :class="n.read_at === null ? 'border-l-4 border-sky-400' : ''"
            @click="openRow(n)"
          >
            <p class="text-sm text-slate-800">{{ summaryLine(n.data) }}</p>
            <p v-if="typeof n.data.body_preview === 'string' && n.data.body_preview" class="mt-1 line-clamp-2 text-xs text-slate-500">
              {{ n.data.body_preview }}
            </p>
            <p class="mt-2 text-[11px] text-slate-400">{{ n.created_at }}</p>
          </button>
        </li>
      </ul>
      <p v-else class="glass-surface p-8 text-center text-sm text-slate-500">{{ t('notifications.emptyPage') }}</p>
      <CompactPagination v-if="notifications.data.length" class="mt-6" :links="notifications.links" />
    </div>
  </AuthenticatedLayout>
</template>
