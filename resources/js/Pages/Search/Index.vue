<script setup lang="ts">
import SeoHead from '@/Components/seo/SeoHead.vue';
import CompactPagination from '@/Components/parts/CompactPagination.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import type { PaginatedList } from '@/types/inertia';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

type BandRow = {
  id: number;
  name: string;
  slug: string;
  country?: { name: string } | null;
  merch_items_count?: number;
};

type MerchRow = {
  id: number;
  name: string;
  slug: string;
  release_year?: number | null;
  size_note?: string | null;
  is_official: boolean;
  band: { name: string; slug: string };
  category?: { name: string } | null;
  cover_image?: { image_path: string; alt_text?: string | null } | null;
};

const props = defineProps<{
  q: string;
  tab: 'bands' | 'merch';
  counts: { bands: number; merch: number };
  bands: (PaginatedList<BandRow> & { current_page: number; links: { url: string | null; label: string; active: boolean }[] }) | null;
  merchItems: (PaginatedList<MerchRow> & { current_page: number; links: { url: string | null; label: string; active: boolean }[] }) | null;
}>();

const tabItems = computed(() => [
  { id: 'bands' as const, label: t('search.tabs.bands'), count: props.counts.bands },
  { id: 'merch' as const, label: t('search.tabs.merch'), count: props.counts.merch },
]);

const pageTitle = computed(() =>
  props.q ? t('search.title.withQuery', { q: props.q }) : t('search.title.default'),
);

const seoPage = computed(() => (props.q ? 'searchWithQuery' : 'search'));
const seoParams = computed(() => (props.q ? { q: props.q } : {}));
</script>

<template>
  <PublicLayout>
    <SeoHead :page="seoPage" :params="seoParams" />
    <div class="px-1">
      <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('search.eyebrow') }}</p>
      <h1 class="mt-2 text-2xl font-semibold text-slate-800">{{ pageTitle }}</h1>
    </div>
    <div v-if="!q" class="glass-surface mt-7 p-8 text-center">
      <p class="text-slate-600">{{ t('search.hint') }}</p>
    </div>
    <template v-else>
      <nav class="mt-7 flex flex-wrap gap-2 border-b border-white/30 pb-1" :aria-label="t('search.categoryNav')">
        <Link
          v-for="item in tabItems"
          :key="item.id"
          :href="route('search', { q, tab: item.id })"
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
          <ul v-if="bands.data.length" class="divide-y divide-white/30 rounded-[1.25rem] border border-white/35 bg-white/25 overflow-hidden">
            <li v-for="b in bands.data" :key="b.id">
              <Link
                :href="route('bands.show', { band: b.slug, q, tab: 'bands', page: bands.current_page })"
                class="flex items-center justify-between px-4 py-5 transition hover:bg-white/35 md:px-5"
              >
                <div>
                  <p class="font-medium text-slate-800">{{ b.name }}</p>
                  <p v-if="b.country" class="mt-1 text-sm text-slate-500">{{ b.country.name }}</p>
                </div>
                <span v-if="b.merch_items_count !== undefined" class="text-sm text-slate-500">{{
                  t('search.merchCount', { count: b.merch_items_count })
                }}</span>
              </Link>
            </li>
          </ul>
          <p v-else class="text-slate-500">{{ t('search.empty.bands') }}</p>
          <CompactPagination v-if="bands.data.length" class="mt-6" :links="bands.links" />
        </template>
        <template v-if="tab === 'merch' && merchItems">
          <ul v-if="merchItems.data.length" class="space-y-5">
            <li v-for="item in merchItems.data" :key="item.id" class="glass-surface p-5">
              <Link
                :href="route('merch-items.show', {
                  merchItem: item.slug,
                  q,
                  tab: 'merch',
                  page: merchItems.current_page,
                })"
                class="block hover:opacity-90"
              >
                <div class="flex items-center justify-between gap-4">
                  <div class="flex min-w-0 items-center gap-4">
                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl border border-white/40 bg-white/45">
                      <img
                        v-if="item.cover_image"
                        :src="`/storage/${item.cover_image.image_path}`"
                        :alt="item.cover_image.alt_text || item.name"
                        class="h-full w-full object-cover"
                      />
                      <div v-else class="flex h-full w-full items-center justify-center text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                        {{ t('search.noImage') }}
                      </div>
                    </div>
                    <div class="min-w-0">
                      <p class="text-lg font-medium text-slate-800">{{ item.name }}</p>
                      <p class="mt-1 text-sm text-slate-500">{{ item.band.name }}</p>
                      <p class="mt-2 text-sm text-slate-500">
                        <span v-if="item.category">{{ item.category.name }}</span>
                        <span v-if="item.release_year"> · {{ item.release_year }}</span>
                        <span v-if="item.size_note"> · {{ t('pages.merch.sizeNoteShort', { note: item.size_note }) }}</span>
                        <span v-if="item.is_official"> · {{ t('search.official') }}</span>
                      </p>
                    </div>
                  </div>
                  <span class="glass-link shrink-0 text-sm font-medium">{{ t('search.detail') }}</span>
                </div>
              </Link>
            </li>
          </ul>
          <p v-else class="text-slate-500">{{ t('search.empty.merch') }}</p>
          <CompactPagination v-if="merchItems.data.length" class="mt-6" :links="merchItems.links" />
        </template>
      </div>
    </template>
  </PublicLayout>
</template>
