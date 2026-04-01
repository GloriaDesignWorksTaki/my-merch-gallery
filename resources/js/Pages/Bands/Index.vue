<script setup lang="ts">
import CompactPagination from '@/Components/CompactPagination.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

type BandRow = {
  id: number;
  name: string;
  slug: string;
  country?: { name: string } | null;
  merch_items_count?: number;
};

type PaginationLink = {
  url: string | null;
  label: string;
  active: boolean;
};

const props = defineProps<{
  bands: {
    data: BandRow[];
    links: PaginationLink[];
    current_page: number;
  };
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
    <Head title="バンド一覧" />

    <div class="flex items-center justify-between gap-4 px-1">
      <div>
        <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">Catalog</p>
        <h1 class="mt-2 text-2xl font-semibold text-slate-800">バンド</h1>
      </div>
    </div>
    <div class="mt-7 space-y-7">
      <nav class="flex flex-wrap items-center gap-2 px-1">
        <Link
          :href="route('bands.index')"
          class="rounded-full px-3 py-1.5 text-sm transition"
          :class="!selectedLetter ? 'bg-sky-600 text-white' : 'glass-link text-slate-700 hover:bg-white/55'"
        >
          All
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
            <Link :href="route('bands.show', { band: b.slug, page: bands.current_page, letter: selectedLetter || undefined })" class="flex items-center justify-between rounded-2xl px-4 py-5 transition hover:bg-white/35 md:px-5">
              <div>
                <p class="font-medium text-slate-800">{{ b.name }}</p>
                <p v-if="b.country" class="mt-1 text-sm text-slate-500">{{ b.country.name }}</p>
              </div>
              <span v-if="b.merch_items_count !== undefined" class="text-sm text-slate-500">マーチ {{ b.merch_items_count }}</span>
            </Link>
          </li>
        </ul>
      </section>
    </div>
    <CompactPagination :links="bands.links" />
    <p v-if="bands.data.length === 0" class="mt-4 text-slate-500">まだバンドがありません。</p>
  </PublicLayout>
</template>
