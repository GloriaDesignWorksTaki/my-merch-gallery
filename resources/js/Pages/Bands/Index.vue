<script setup lang="ts">
import LikeToggleInline from '@/Components/parts/LikeToggleInline.vue';
import CompactPagination from '@/Components/parts/CompactPagination.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import type { PaginatedList } from '@/types/inertia';
import SeoHead from '@/Components/seo/SeoHead.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

type BandRow = {
  id: number;
  name: string;
  slug: string;
  image_path?: string | null;
  country?: { name: string } | null;
  merch_items_count?: number;
  likes_count?: number;
  liked?: boolean;
};

const props = defineProps<{
  bands: PaginatedList<BandRow> & { current_page: number };
  selectedLetter?: string;
  availableLetters?: string[];
}>();

const normalizedBandName = (name: string) => name.trim().replace(/^the\s+/i, '');

const groupedBands = computed(() => {
  const groups = new Map<string, BandRow[]>();

  props.bands.data.forEach((band) => {
    const initial = normalizedBandName(band.name).charAt(0).toUpperCase();
    const key = /^[A-Z]$/.test(initial) ? initial : '#';

    if (!groups.has(key)) {
      groups.set(key, []);
    }

    groups.get(key)?.push(band);
  });

  return [...groups.entries()].map(([letter, items]) => ({
    letter,
    items,
  }));
});

const letterLinks = computed(() => {
  const letters = props.availableLetters ?? [];

  return letters.map((letter) => ({
    letter,
    href: route('bands.index', letter === props.selectedLetter ? {} : { letter }),
    active: letter === props.selectedLetter,
  }));
});
</script>

<template>
  <PublicLayout>
    <SeoHead page="bandsIndex" />

    <div class="flex items-center justify-between gap-4 px-1">
      <div>
        <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('bands.catalogEyebrow') }}</p>
        <h1 class="mt-2 text-2xl font-semibold text-slate-800">{{ t('bands.listPageHead') }}</h1>
      </div>
    </div>
    <div class="mt-7 space-y-7">
      <nav class="flex flex-wrap items-center gap-2 px-1">
        <Link
          :href="route('bands.index')"
          class="rounded-full px-3 py-1.5 text-sm transition"
          :class="!selectedLetter ? 'bg-sky-600 text-white' : 'glass-link text-slate-700 hover:bg-white/55'"
        >
          {{ t('bands.letterAll') }}
        </Link>
        <Link
          v-for="item in letterLinks"
          :key="item.letter"
          :href="item.href"
          class="rounded-full px-3 py-1.5 text-sm transition"
          :class="item.active ? 'bg-sky-600 text-white' : 'glass-link text-slate-700 hover:bg-white/55'"
        >
          {{ item.letter }}
        </Link>
      </nav>

      <section v-for="group in groupedBands" :key="group.letter" class="glass-surface overflow-hidden p-4 md:p-5">
        <div class="px-1 py-2">
          <p class="text-xs font-semibold uppercase tracking-[0.35em] text-sky-600/70">
            {{ group.letter }}
          </p>
        </div>

        <ul class="divide-y divide-white/30">
          <li v-for="b in group.items" :key="b.id">
            <div class="flex items-center gap-2 px-4 py-5 sm:gap-4 md:px-5">
              <Link
                :href="route('bands.show', { band: b.slug, page: bands.current_page, letter: selectedLetter || undefined })"
                class="flex min-w-0 flex-1 items-center gap-3 sm:gap-4 transition hover:opacity-90"
              >
                <span
                  v-if="b.image_path"
                  class="relative shrink-0 overflow-hidden rounded-2xl border border-white/40 bg-white/40"
                >
                  <img :src="`/storage/${b.image_path}`" alt="" class="h-12 w-12 object-cover sm:h-14 sm:w-14" />
                </span>
                <p class="min-w-0 flex-1 truncate text-sm font-medium text-slate-800 sm:text-base">
                  {{ b.name }}<template v-if="b.country"><span class="font-normal text-slate-500"> · {{ b.country.name }}</span></template>
                </p>
                <span
                  v-if="b.merch_items_count !== undefined"
                  class="shrink-0 whitespace-nowrap text-xs text-slate-500 tabular-nums sm:text-sm"
                >{{ t('bands.merchCount', { count: b.merch_items_count }) }}</span>
              </Link>
              <LikeToggleInline
                :likes-count="b.likes_count ?? 0"
                :liked="Boolean(b.liked)"
                :feature-label="t('bands.featureLike')"
                :toggle-href="route('bands.like.toggle', b.slug)"
                variant="compact"
              />
            </div>
          </li>
        </ul>
      </section>
    </div>
    <CompactPagination :links="bands.links" />
    <p v-if="bands.data.length === 0" class="mt-4 text-slate-500">{{ t('bands.empty') }}</p>
  </PublicLayout>
</template>
