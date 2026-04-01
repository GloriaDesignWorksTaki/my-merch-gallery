<script setup lang="ts">
import CompactPagination from '@/Components/CompactPagination.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

type MerchRow = {
  id: number;
  name: string;
  slug: string;
  release_year?: number | null;
  is_official: boolean;
  band: { name: string; slug: string };
  category?: { name: string } | null;
  cover_image?: { image_path: string; alt_text?: string | null } | null;
};

type PaginationLink = {
  url: string | null;
  label: string;
  active: boolean;
};

const props = defineProps<{
  merchItems: {
    data: MerchRow[];
    links: PaginationLink[];
    current_page: number;
  };
  filters: {
    search?: string | null;
    band?: number | null;
    category?: number | null;
    sort?: string | null;
  };
  bands: { id: number; name: string }[];
  categories: { id: number; name: string }[];
}>();

const filterForm = reactive({
  search: props.filters.search ?? '',
  band: props.filters.band ?? '',
  category: props.filters.category ?? '',
  sort: props.filters.sort ?? 'newest',
});

const applyFilters = () => {
  router.get(route('merch-items.index'), {
    search: filterForm.search || undefined,
    band: filterForm.band || undefined,
    category: filterForm.category || undefined,
    sort: filterForm.sort !== 'newest' ? filterForm.sort : undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};
</script>

<template>
  <PublicLayout>
    <Head title="マーチ一覧" />

    <div class="flex items-center justify-between gap-4 px-1">
      <div>
        <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">Archive</p>
        <h1 class="mt-2 text-2xl font-semibold text-slate-800">マーチ</h1>
      </div>
    </div>

    <section class="glass-surface mt-7 p-5">
      <div class="grid gap-4 md:grid-cols-[minmax(0,1.6fr)_repeat(3,minmax(0,1fr))]">
        <input
          v-model="filterForm.search"
          type="text"
          class="rounded-2xl"
          placeholder="アイテム名で検索"
          @keyup.enter="applyFilters"
        />
        <select v-model="filterForm.band" class="rounded-2xl" @change="applyFilters">
          <option value="">全バンド</option>
          <option v-for="band in bands" :key="band.id" :value="band.id">{{ band.name }}</option>
        </select>
        <select v-model="filterForm.category" class="rounded-2xl" @change="applyFilters">
          <option value="">全カテゴリ</option>
          <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
        </select>
        <select v-model="filterForm.sort" class="rounded-2xl" @change="applyFilters">
          <option value="newest">新着順</option>
          <option value="oldest">古い順</option>
          <option value="name">名前順</option>
        </select>
      </div>
    </section>

    <ul class="mt-7 space-y-5">
      <li v-for="item in merchItems.data" :key="item.id" class="glass-surface p-5">
        <Link
          :href="route('merch-items.show', {
            merchItem: item.slug,
            page: merchItems.current_page,
            search: filters.search || undefined,
            band: filters.band || undefined,
            category: filters.category || undefined,
            sort: filters.sort && filters.sort !== 'newest' ? filters.sort : undefined,
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
                  No Image
                </div>
              </div>

              <div class="min-w-0">
                <p class="text-lg font-medium text-slate-800">{{ item.name }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ item.band.name }}</p>
                <p class="mt-2 text-sm text-slate-500">
                  <span v-if="item.category">{{ item.category.name }}</span>
                  <span v-if="item.release_year"> · {{ item.release_year }}</span>
                  <span v-if="item.is_official"> · 公式</span>
                </p>
              </div>
            </div>
            <span class="glass-link shrink-0 text-sm font-medium">詳細へ</span>
          </div>
        </Link>
      </li>
    </ul>

    <CompactPagination :links="merchItems.links" />

    <p v-if="merchItems.data.length === 0" class="mt-4 text-slate-500">まだマーチがありません。</p>
  </PublicLayout>
</template>
