<script setup lang="ts">
import SeoHead from '@/Components/seo/SeoHead.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CompactPagination from '@/Components/parts/CompactPagination.vue';
import type { CoverImageJson } from '@/types/uploadAssets';
import type { PaginatedList } from '@/types/inertia';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

type BandLikeRow = {
  id: number;
  created_at: string;
  band: { id: number; name: string; slug: string };
};

type MerchNested = {
  id: number;
  name: string;
  slug: string;
  band: { name: string; slug: string };
  cover_image?: CoverImageJson | null;
};

type MerchLikeRow = {
  id: number;
  created_at: string;
  merch_item?: MerchNested;
  merchItem?: MerchNested;
};

const props = defineProps<{
  tab: 'bands' | 'merch';
  counts: { bands: number; merch: number };
  bands: (PaginatedList<BandLikeRow> & { current_page: number }) | null;
  merchItems: (PaginatedList<MerchLikeRow> & { current_page: number }) | null;
}>();

const { t, locale } = useI18n();

const tabItems = computed(() => [
  { id: 'bands' as const, label: t('search.tabs.bands'), count: props.counts.bands },
  { id: 'merch' as const, label: t('search.tabs.merch'), count: props.counts.merch },
]);

function likedAtLabel(iso: string) {
  try {
    return new Date(iso).toLocaleString(locale.value === 'ja' ? 'ja-JP' : 'en-US', {
      dateStyle: 'medium',
      timeStyle: 'short',
    });
  } catch {
    return iso;
  }
}

function merchFromRow(row: MerchLikeRow): MerchNested | null {
  return row.merch_item ?? row.merchItem ?? null;
}

const merchTabRows = computed(() => {
  const data = props.merchItems?.data ?? [];
  return data
    .map((row) => ({ row, merch: merchFromRow(row) }))
    .filter((x): x is { row: MerchLikeRow; merch: MerchNested } => x.merch !== null);
});
</script>

<template>
  <SeoHead page="dashboardLikes" />
  <AuthenticatedLayout>
    <template #header>
      <div>
        <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('dashboard.likesEyebrow') }}</p>
        <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-800">{{ t('dashboard.likesTitle') }}</h2>
        <p class="mt-2 text-sm text-slate-600">{{ t('dashboard.likesHint') }}</p>
      </div>
    </template>

    <div class="mx-auto max-w-4xl">
      <nav class="flex flex-wrap gap-2 border-b border-white/30 pb-1" :aria-label="t('search.categoryNav')">
        <Link
          v-for="item in tabItems"
          :key="item.id"
          :href="route('dashboard.likes', { tab: item.id })"
          class="relative rounded-t-2xl px-4 py-3 text-sm font-semibold transition"
          :class="
            tab === item.id
              ? 'bg-white/70 text-slate-900 shadow-sm'
              : 'text-slate-600 hover:bg-white/40'
          "
          preserve-scroll
        >
          {{ item.label }}
          <span
            class="ml-1.5 rounded-full px-2 py-0.5 text-xs font-medium"
            :class="tab === item.id ? 'bg-sky-100 text-sky-800' : 'bg-white/50 text-slate-500'"
          >
            {{ item.count }}
          </span>
        </Link>
      </nav>

      <div class="mt-6">
        <template v-if="tab === 'bands' && bands">
          <ul v-if="bands.data.length" class="space-y-3">
            <li v-for="row in bands.data" :key="row.id" class="glass-surface p-4">
              <Link
                :href="route('bands.show', { band: row.band.slug, page: bands.current_page, tab: 'bands' })"
                class="flex flex-col gap-2 hover:opacity-90 sm:flex-row sm:items-center sm:justify-between"
              >
                <div>
                  <p class="font-medium text-slate-800">{{ row.band.name }}</p>
                  <p class="mt-1 text-xs text-slate-500">{{ likedAtLabel(row.created_at) }}</p>
                </div>
                <span class="text-sm text-sky-700">{{ t('merch.detailLink') }}</span>
              </Link>
            </li>
          </ul>
          <p v-else class="text-sm text-slate-500">{{ t('dashboard.likesEmptyBands') }}</p>
          <CompactPagination v-if="bands.data.length" class="mt-6" :links="bands.links" />
        </template>

        <template v-if="tab === 'merch' && merchItems">
          <ul v-if="merchTabRows.length" class="space-y-3">
            <li v-for="{ row, merch } in merchTabRows" :key="row.id" class="glass-surface p-4">
              <Link
                :href="route('merch-items.show', {
                  merchItem: merch.slug,
                  page: merchItems.current_page,
                  tab: 'merch',
                })"
                class="flex flex-col gap-3 hover:opacity-90 sm:flex-row sm:items-center sm:justify-between"
              >
                <div class="flex min-w-0 items-center gap-4">
                  <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl border border-white/40 bg-white/45">
                    <img
                      v-if="merch.cover_image"
                      :src="merch.cover_image.image_url"
                      :alt="merch.cover_image.alt_text || merch.name"
                      class="h-full w-full object-cover"
                    />
                    <div
                      v-else
                      class="flex h-full w-full items-center justify-center text-[10px] font-semibold uppercase tracking-[0.15em] text-slate-400"
                    >
                      {{ t('search.noImage') }}
                    </div>
                  </div>
                  <div class="min-w-0">
                    <p class="font-medium text-slate-800">{{ merch.name }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ merch.band.name }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ likedAtLabel(row.created_at) }}</p>
                  </div>
                </div>
                <span class="shrink-0 text-sm text-sky-700">{{ t('merch.detailLink') }}</span>
              </Link>
            </li>
          </ul>
          <p v-else class="text-sm text-slate-500">{{ t('dashboard.likesEmptyMerch') }}</p>
          <CompactPagination v-if="merchItems.data.length" class="mt-6" :links="merchItems.links" />
        </template>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
