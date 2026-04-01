<script setup lang="ts">
import CompactPagination from '@/Components/CompactPagination.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

type PostRow = {
  id: number;
  body: string;
  visibility: string;
  user: { name: string; username: string };
  band: { name: string; slug: string };
  merch_item?: { name: string } | null;
  cover_image?: { image_path: string } | null;
};

type PaginationLink = {
  url: string | null;
  label: string;
  active: boolean;
};

const props = defineProps<{
  posts: {
    data: PostRow[];
    links: PaginationLink[];
    current_page: number;
  };
  filters: {
    search?: string | null;
    band?: number | null;
    visibility?: string | null;
    sort?: string | null;
  };
  bands: { id: number; name: string }[];
}>();

const filterForm = reactive({
  search: props.filters.search ?? '',
  band: props.filters.band ?? '',
  visibility: props.filters.visibility ?? '',
  sort: props.filters.sort ?? 'newest',
});

const applyFilters = () => {
  router.get(route('posts.index'), {
    search: filterForm.search || undefined,
    band: filterForm.band || undefined,
    visibility: filterForm.visibility || undefined,
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
    <Head title="投稿一覧" />

    <div class="flex items-center justify-between gap-4 px-1">
      <div>
        <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">Community</p>
        <h1 class="mt-2 text-2xl font-semibold text-slate-800">投稿</h1>
      </div>
      <Link :href="route('posts.create')" class="glass-panel rounded-full px-4 py-2 text-sm font-medium text-sky-700 hover:bg-white/55">投稿作成</Link>
    </div>
    <section class="glass-surface mt-7 p-5">
      <div class="grid gap-4 md:grid-cols-[minmax(0,1.6fr)_repeat(3,minmax(0,1fr))]">
        <input
          v-model="filterForm.search"
          type="text"
          class="rounded-2xl"
          placeholder="本文で検索"
          @keyup.enter="applyFilters"
        />
        <select v-model="filterForm.band" class="rounded-2xl" @change="applyFilters">
          <option value="">全バンド</option>
          <option v-for="band in bands" :key="band.id" :value="band.id">{{ band.name }}</option>
        </select>
        <select v-model="filterForm.visibility" class="rounded-2xl" @change="applyFilters">
          <option value="">全公開範囲</option>
          <option value="public">公開</option>
          <option value="unlisted">限定公開</option>
        </select>
        <select v-model="filterForm.sort" class="rounded-2xl" @change="applyFilters">
          <option value="newest">新着順</option>
          <option value="oldest">古い順</option>
        </select>
      </div>
    </section>
    <ul class="mt-7 space-y-5">
      <li v-for="p in posts.data" :key="p.id" class="glass-surface p-5">
        <Link
          :href="route('posts.show', {
            post: p.id,
            page: posts.current_page,
            search: filters.search || undefined,
            band: filters.band || undefined,
            visibility: filters.visibility || undefined,
            sort: filters.sort && filters.sort !== 'newest' ? filters.sort : undefined,
          })"
          class="block hover:opacity-90"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
              <p class="line-clamp-3 text-slate-800">{{ p.body }}</p>
              <p class="mt-3 text-sm text-slate-500">
                @{{ p.user.username }} · {{ p.band.name }}
                <span v-if="p.merch_item"> · {{ p.merch_item.name }}</span>
                <span> · {{ p.visibility === 'public' ? '公開' : '限定公開' }}</span>
              </p>
            </div>
            <div v-if="p.cover_image" class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl border border-white/40 bg-white/45">
              <img :src="`/storage/${p.cover_image.image_path}`" alt="" class="h-full w-full object-cover" />
            </div>
          </div>
        </Link>
      </li>
    </ul>
    <CompactPagination :links="posts.links" />
    <p v-if="posts.data.length === 0" class="mt-4 text-slate-500">まだ投稿がありません。</p>
  </PublicLayout>
</template>
