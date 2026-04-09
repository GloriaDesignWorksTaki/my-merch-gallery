<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CompactPagination from '@/Components/parts/CompactPagination.vue';
import PrimaryButton from '@/Components/parts/PrimaryButton.vue';
import type { PaginatedList } from '@/types/inertia';
import SeoHead from '@/Components/seo/SeoHead.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();

type Row = {
  id: number;
  status: string;
  created_at: string;
  payload: Record<string, unknown>;
  reviewer_note: string | null;
  band: { id: number; name: string; slug: string };
  user: { id: number; name: string; username: string };
  reviewer: { id: number; name: string; username: string } | null;
};

defineProps<{
  requests: PaginatedList<Row>;
  filters: { status: string };
}>();

const rejectNotes = ref<Record<number, string>>({});

function approve(id: number) {
  router.post(route('admin.band-edit-requests.approve', id));
}

function reject(id: number) {
  router.post(route('admin.band-edit-requests.reject', id), {
    reviewer_note: rejectNotes.value[id] ?? '',
  });
}

function formatAt(iso: string) {
  try {
    return new Date(iso).toLocaleString(locale.value === 'ja' ? 'ja-JP' : 'en-US', {
      dateStyle: 'medium',
      timeStyle: 'short',
    });
  } catch {
    return iso;
  }
}

const statusTabs = [
  { key: 'pending', labelKey: 'pages.admin.bandRequestsFilterPending' },
  { key: 'approved', labelKey: 'pages.admin.bandRequestsFilterApproved' },
  { key: 'rejected', labelKey: 'pages.admin.bandRequestsFilterRejected' },
  { key: 'all', labelKey: 'pages.admin.bandRequestsFilterAll' },
] as const;
</script>

<template>
  <SeoHead page="adminBandEditRequests" />

  <AuthenticatedLayout>
    <template #header>
      <div>
        <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('pages.admin.eyebrow') }}</p>
        <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-800">{{ t('pages.admin.bandRequestsTitle') }}</h2>
        <div class="mt-4 flex flex-wrap gap-2">
          <Link
            v-for="tab in statusTabs"
            :key="tab.key"
            :href="route('admin.band-edit-requests.index', { status: tab.key })"
            class="rounded-full px-4 py-2 text-sm font-medium transition"
            :class="
              filters.status === tab.key
                ? 'bg-sky-600 text-white shadow-sm'
                : 'glass-panel text-slate-700 hover:bg-white/55'
            "
          >
            {{ t(tab.labelKey) }}
          </Link>
        </div>
      </div>
    </template>

    <div class="mx-auto max-w-5xl space-y-4">
      <Link :href="route('admin.dashboard')" class="glass-link text-sm font-medium">{{ t('pages.admin.title') }}</Link>

      <ul v-if="requests.data.length" class="space-y-4">
        <li v-for="req in requests.data" :key="req.id" class="glass-surface p-5 text-sm">
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="font-semibold text-slate-800">
                <Link :href="route('bands.show', req.band.slug)" class="text-sky-700 hover:underline">{{ req.band.name }}</Link>
              </p>
              <p class="mt-1 text-slate-600">
                {{ t('pages.admin.bandRequestsSubmittedBy') }}:
                <Link :href="route('users.show', req.user.id)" class="font-medium text-sky-700 hover:underline">@{{ req.user.username }}</Link>
              </p>
              <p class="mt-1 text-xs text-slate-500">{{ t('pages.admin.bandRequestsAt') }}: {{ formatAt(req.created_at) }}</p>
              <p v-if="req.reviewer_note" class="mt-2 text-slate-600">{{ req.reviewer_note }}</p>
            </div>
            <span class="shrink-0 rounded-full bg-white/60 px-3 py-1 text-xs font-medium text-slate-600">{{ req.status }}</span>
          </div>

          <div v-if="req.status === 'pending'" class="mt-4 flex flex-col gap-3 border-t border-white/40 pt-4 sm:flex-row sm:items-end">
            <div class="min-w-0 flex-1">
              <label class="text-xs font-medium text-slate-600">{{ t('pages.admin.bandRequestsRejectNote') }}</label>
              <textarea
                v-model="rejectNotes[req.id]"
                rows="2"
                class="mt-1 w-full rounded-2xl border border-white/40 bg-white/50 px-3 py-2 text-base text-slate-800 shadow-sm md:text-sm"
              />
            </div>
            <div class="flex flex-wrap gap-2">
              <PrimaryButton type="button" class="bg-emerald-600 hover:bg-emerald-700" @click="approve(req.id)">
                {{ t('pages.admin.bandRequestsApprove') }}
              </PrimaryButton>
              <button
                type="button"
                class="glass-panel rounded-full px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50/60"
                @click="reject(req.id)"
              >
                {{ t('pages.admin.bandRequestsReject') }}
              </button>
            </div>
          </div>
        </li>
      </ul>
      <p v-else class="glass-surface p-8 text-center text-slate-600">{{ t('pages.admin.bandRequestsEmpty') }}</p>

      <CompactPagination :links="requests.links" />
    </div>
  </AuthenticatedLayout>
</template>
