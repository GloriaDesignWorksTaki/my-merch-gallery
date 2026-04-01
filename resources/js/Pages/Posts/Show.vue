<script setup lang="ts">
import CompactPagination from '@/Components/CompactPagination.vue';
import PageContextBar from '@/Components/PageContextBar.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

type CommentRow = {
  id: number;
  body: string;
  user: { name: string; username: string };
};

type PaginationLink = {
  url: string | null;
  label: string;
  active: boolean;
};

type PostShow = {
  id: number;
  body: string;
  visibility: string;
  published_at?: string | null;
  user: { id: number; name: string; username: string };
  band: { id: number; name: string; slug: string };
  merch_item?: { id: number; name: string; slug: string; category?: { name: string } } | null;
  images: { image_path: string }[];
};

defineProps<{
  post: PostShow;
  comments: {
    data: CommentRow[];
    links: PaginationLink[];
  };
  canEdit: boolean;
  returnTo: string;
  relatedPosts: {
    id: number;
    body: string;
    user: { username: string };
    cover_image?: { image_path: string } | null;
  }[];
  relatedMerchItems: {
    id: number;
    name: string;
    slug: string;
    release_year?: number | null;
    is_official: boolean;
    category?: { name: string } | null;
    cover_image?: { image_path: string; alt_text?: string | null } | null;
  }[];
}>();
</script>

<template>
  <PublicLayout>
    <Head :title="`投稿 #${post.id}`" />

    <PageContextBar
      :crumbs="[
        { label: '投稿一覧', href: returnTo },
        { label: post.band.name, href: route('bands.show', post.band.slug) },
        { label: `投稿 #${post.id}` },
      ]"
      :actions="canEdit ? [{ label: '編集する', href: route('posts.edit', { post: post.id, return_to: returnTo }) }] : []"
    />

    <article class="glass-surface mt-4 p-6">
      <div class="flex flex-wrap items-start justify-between gap-6">
        <div>
          <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">Community Note</p>
          <p class="mt-2 text-sm text-slate-500">
            <Link :href="route('users.show', post.user.id)" class="hover:underline">@{{ post.user.username }}</Link>
            ·
            <Link :href="route('bands.show', post.band.slug)" class="glass-link font-medium">{{ post.band.name }}</Link>
            <template v-if="post.merch_item">
              ·
              <Link :href="route('merch-items.show', post.merch_item.slug)" class="glass-link font-medium">{{ post.merch_item.name }}</Link>
            </template>
            <span> · {{ post.visibility === 'public' ? '公開' : post.visibility === 'unlisted' ? '限定公開' : '非公開' }}</span>
          </p>
        </div>
        <Link v-if="post.merch_item" :href="route('merch-items.show', post.merch_item.slug)" class="glass-panel rounded-full px-4 py-2 text-sm font-medium text-sky-700 hover:bg-white/55">
          関連マーチを見る
        </Link>
      </div>
      <p class="mt-6 whitespace-pre-wrap text-slate-800">{{ post.body }}</p>

      <div v-if="post.images.length" class="mt-6 grid gap-3 sm:grid-cols-2">
        <img v-for="(img, i) in post.images" :key="i" :src="`/storage/${img.image_path}`" alt="" class="max-h-64 w-full rounded-md object-cover" />
      </div>
    </article>

    <section class="mt-8">
      <div class="flex items-center justify-between gap-4">
        <h2 class="text-lg font-medium text-slate-800">同じバンドの投稿</h2>
        <Link :href="route('bands.show', post.band.slug)" class="glass-link text-sm font-medium">バンド詳細へ</Link>
      </div>
      <ul v-if="relatedPosts.length" class="mt-3 space-y-3">
        <li v-for="relatedPost in relatedPosts" :key="relatedPost.id" class="glass-surface p-4">
          <Link :href="route('posts.show', relatedPost.id)" class="flex items-start gap-4">
            <div class="min-w-0 flex-1">
              <p class="line-clamp-2 text-slate-800">{{ relatedPost.body }}</p>
              <p class="mt-2 text-sm text-slate-500">@{{ relatedPost.user.username }}</p>
            </div>
            <div v-if="relatedPost.cover_image" class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl border border-white/40 bg-white/45">
              <img :src="`/storage/${relatedPost.cover_image.image_path}`" alt="" class="h-full w-full object-cover" />
            </div>
          </Link>
        </li>
      </ul>
      <div v-else class="glass-surface mt-3 p-6">
        <p class="text-sm text-slate-500">このバンドの関連投稿はまだありません。</p>
      </div>
    </section>

    <section class="mt-8">
      <div class="flex items-center justify-between gap-4">
        <h2 class="text-lg font-medium text-slate-800">関連マーチ</h2>
        <Link :href="route('merch-items.index')" class="glass-link text-sm font-medium">マーチ一覧へ</Link>
      </div>
      <ul v-if="relatedMerchItems.length" class="mt-3 space-y-3">
        <li v-for="item in relatedMerchItems" :key="item.id" class="glass-surface p-4">
          <Link :href="route('merch-items.show', item.slug)" class="flex items-center gap-4">
            <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl border border-white/40 bg-white/45">
              <img v-if="item.cover_image" :src="`/storage/${item.cover_image.image_path}`" :alt="item.cover_image.alt_text || item.name" class="h-full w-full object-cover" />
            </div>
            <div>
              <p class="font-medium text-slate-800">{{ item.name }}</p>
              <p class="mt-1 text-sm text-slate-500">
                <span v-if="item.category">{{ item.category.name }}</span>
                <span v-if="item.release_year"> · {{ item.release_year }}</span>
                <span v-if="item.is_official"> · 公式</span>
              </p>
            </div>
          </Link>
        </li>
      </ul>
      <div v-else class="glass-surface mt-3 p-6">
        <p class="text-sm text-slate-500">このバンドの関連マーチはまだありません。</p>
      </div>
    </section>

    <section class="mt-8">
      <h2 class="text-lg font-medium text-slate-800">コメント</h2>
      <ul class="mt-3 space-y-3">
        <li v-for="c in comments.data" :key="c.id" class="glass-panel rounded-2xl p-4 text-sm">
          <p class="font-medium text-slate-800">@{{ c.user.username }}</p>
          <p class="mt-1 text-slate-600">{{ c.body }}</p>
        </li>
      </ul>
      <CompactPagination :links="comments.links" />
      <p v-if="comments.data.length === 0" class="mt-3 text-sm text-slate-500">コメントはまだありません。</p>
    </section>
  </PublicLayout>
</template>
