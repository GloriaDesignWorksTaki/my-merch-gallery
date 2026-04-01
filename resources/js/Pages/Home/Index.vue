<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
  featured: {
    bands: { id: number; name: string; slug: string }[];
    merchItems: { id: number; name: string; slug: string; band?: { name: string; slug: string } | null; cover_image?: { image_path: string; alt_text?: string | null } | null }[];
    posts: { id: number; body: string; user?: { username: string } | null; band?: { name: string; slug: string } | null; cover_image?: { image_path: string } | null }[];
  };
}>();
</script>

<template>
  <PublicLayout>
    <Head title="ホーム" />

    <section class="glass-surface px-8 py-12">
      <p class="text-sm uppercase tracking-[0.35em] text-sky-600/70">Show Your Merch!</p>
      <h1 class="mt-4 text-4xl font-semibold tracking-tight text-slate-800">My Merch Gallery</h1>
      <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600">バンド、マーチ、投稿を1つの流れで記録できるコレクションアーカイブです。最近の追加を追いながら、気になるアイテムや投稿へそのまま回遊できます。</p>
      <ul class="mt-8 flex flex-wrap gap-4 text-sm">
        <li><Link :href="route('bands.index')" class="glass-panel inline-flex rounded-full px-5 py-2.5 font-medium text-sky-700 hover:bg-white/55">バンド一覧へ</Link></li>
        <li><Link :href="route('posts.index')" class="glass-panel inline-flex rounded-full px-5 py-2.5 font-medium text-sky-700 hover:bg-white/55">投稿一覧へ</Link></li>
        <li><Link :href="route('merch-items.index')" class="glass-panel inline-flex rounded-full px-5 py-2.5 font-medium text-sky-700 hover:bg-white/55">マーチ一覧へ</Link></li>
      </ul>
    </section>

    <section class="mt-8">
      <div class="grid gap-6">
        <div class="glass-surface p-6">
          <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold text-slate-800">最近追加されたマーチ</h2>
            <Link :href="route('merch-items.index')" class="glass-link text-sm font-medium">もっと見る</Link>
          </div>
          <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <Link v-for="item in featured.merchItems" :key="item.id" :href="route('merch-items.show', item.slug)" class="glass-panel flex items-center gap-4 rounded-2xl p-4 hover:bg-white/55">
              <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl border border-white/40 bg-white/45">
                <img v-if="item.cover_image" :src="`/storage/${item.cover_image.image_path}`" :alt="item.cover_image.alt_text || item.name" class="h-full w-full object-cover" />
              </div>
              <div>
                <p class="font-medium text-slate-800">{{ item.name }}</p>
                <p v-if="item.band" class="mt-1 text-sm text-slate-500">{{ item.band.name }}</p>
              </div>
            </Link>
          </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
          <div class="glass-surface p-6">
            <div class="flex items-center justify-between gap-4">
              <h2 class="text-xl font-semibold text-slate-800">注目バンド</h2>
              <Link :href="route('bands.index')" class="glass-link text-sm font-medium">一覧へ</Link>
            </div>
            <div class="mt-4 space-y-3">
              <Link v-for="band in featured.bands" :key="band.id" :href="route('bands.show', band.slug)" class="glass-panel block rounded-2xl px-4 py-4 font-medium text-slate-800 hover:bg-white/55">
                {{ band.name }}
              </Link>
            </div>
          </div>

          <div class="glass-surface p-6">
            <div class="flex items-center justify-between gap-4">
              <h2 class="text-xl font-semibold text-slate-800">最近の投稿</h2>
              <Link :href="route('posts.index')" class="glass-link text-sm font-medium">一覧へ</Link>
            </div>
            <div class="mt-4 space-y-3">
              <Link v-for="post in featured.posts" :key="post.id" :href="route('posts.show', post.id)" class="glass-panel block rounded-2xl px-4 py-4 hover:bg-white/55">
                <p class="line-clamp-2 text-slate-800">{{ post.body }}</p>
                <p class="mt-2 text-sm text-slate-500">
                  <span v-if="post.user">@{{ post.user.username }}</span>
                  <span v-if="post.band"> · {{ post.band.name }}</span>
                </p>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>
