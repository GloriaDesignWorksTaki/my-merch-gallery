<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
  summary: {
    bands: number;
    merchItems: number;
    posts: number;
  };
  recentBands: { id: number; name: string; slug: string }[];
  recentMerchItems: { id: number; name: string; slug: string; band?: { name: string; slug: string } | null }[];
  recentPosts: { id: number; body: string; band?: { name: string; slug: string } | null; cover_image?: { image_path: string } | null }[];
  profileHints: {
    bioMissing: boolean;
    avatarMissing: boolean;
  };
}>();
</script>

<template>
  <Head title="マイページ" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between gap-4">
        <div>
          <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">Workspace</p>
          <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-800">マイページ</h2>
        </div>
        <div class="glass-panel rounded-full px-4 py-2 text-sm text-slate-600">
          MVP control center
        </div>
      </div>
    </template>

    <div class="mx-auto max-w-4xl space-y-6">
      <section class="grid gap-5 md:grid-cols-3">
        <div class="glass-surface p-6">
          <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Bands</p>
          <p class="mt-4 text-3xl font-semibold text-slate-800">{{ summary.bands }}</p>
          <p class="mt-2 text-sm text-slate-500">登録済みバンド</p>
        </div>
        <div class="glass-surface p-6">
          <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Merch</p>
          <p class="mt-4 text-3xl font-semibold text-slate-800">{{ summary.merchItems }}</p>
          <p class="mt-2 text-sm text-slate-500">登録済みマーチ</p>
        </div>
        <div class="glass-surface p-6">
          <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Posts</p>
          <p class="mt-4 text-3xl font-semibold text-slate-800">{{ summary.posts }}</p>
          <p class="mt-2 text-sm text-slate-500">あなたの投稿数</p>
        </div>
      </section>

      <section class="glass-surface p-6">
        <div class="flex items-center justify-between gap-4">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Quick flow</p>
            <h3 class="mt-2 text-xl font-semibold text-slate-800">MVP の登録導線</h3>
          </div>
          <div class="text-sm text-slate-500">soft glass layout</div>
        </div>
        <div class="mt-6 grid gap-4 md:grid-cols-3">
          <Link :href="route('bands.create')" class="glass-panel rounded-2xl p-4 hover:bg-white/55">
            <p class="text-sm font-medium text-slate-800">1. バンド登録</p>
            <p class="mt-2 text-sm text-slate-500">メジャーもインディーズもここから追加。</p>
          </Link>
          <Link :href="route('merch-items.create')" class="glass-panel rounded-2xl p-4 hover:bg-white/55">
            <p class="text-sm font-medium text-slate-800">2. マーチ登録</p>
            <p class="mt-2 text-sm text-slate-500">カテゴリと時期ラベルを紐づける。</p>
          </Link>
          <Link :href="route('posts.create')" class="glass-panel rounded-2xl p-4 hover:bg-white/55">
            <p class="text-sm font-medium text-slate-800">3. 投稿作成</p>
            <p class="mt-2 text-sm text-slate-500">記録と会話をここから開始。</p>
          </Link>
        </div>
      </section>

      <section class="grid gap-6 xl:grid-cols-3">
        <div class="glass-surface p-6">
          <div class="flex items-center justify-between gap-4">
            <h3 class="text-lg font-semibold text-slate-800">最近登録したバンド</h3>
            <Link :href="route('bands.index')" class="glass-link text-sm font-medium">一覧へ</Link>
          </div>
          <div class="mt-4 space-y-3">
            <Link v-for="band in recentBands" :key="band.id" :href="route('bands.show', band.slug)" class="glass-panel block rounded-2xl px-4 py-4 hover:bg-white/55">
              <p class="font-medium text-slate-800">{{ band.name }}</p>
            </Link>
            <p v-if="recentBands.length === 0" class="text-sm text-slate-500">まだバンド登録がありません。</p>
          </div>
        </div>

        <div class="glass-surface p-6">
          <div class="flex items-center justify-between gap-4">
            <h3 class="text-lg font-semibold text-slate-800">最近登録したマーチ</h3>
            <Link :href="route('merch-items.index')" class="glass-link text-sm font-medium">一覧へ</Link>
          </div>
          <div class="mt-4 space-y-3">
            <Link v-for="item in recentMerchItems" :key="item.id" :href="route('merch-items.show', item.slug)" class="glass-panel block rounded-2xl px-4 py-4 hover:bg-white/55">
              <p class="font-medium text-slate-800">{{ item.name }}</p>
              <p v-if="item.band" class="mt-1 text-sm text-slate-500">{{ item.band.name }}</p>
            </Link>
            <p v-if="recentMerchItems.length === 0" class="text-sm text-slate-500">まだマーチ登録がありません。</p>
          </div>
        </div>

        <div class="glass-surface p-6">
          <div class="flex items-center justify-between gap-4">
            <h3 class="text-lg font-semibold text-slate-800">プロフィール整備</h3>
            <Link :href="route('profile.edit')" class="glass-link text-sm font-medium">設定へ</Link>
          </div>
          <div class="mt-4 space-y-3 text-sm text-slate-600">
            <p :class="profileHints.bioMissing ? 'text-slate-800' : 'text-slate-500'">{{ profileHints.bioMissing ? '自己紹介が未設定です。' : '自己紹介は設定済みです。' }}</p>
            <p :class="profileHints.avatarMissing ? 'text-slate-800' : 'text-slate-500'">{{ profileHints.avatarMissing ? 'プロフィール画像が未設定です。' : 'プロフィール画像は設定済みです。' }}</p>
          </div>
          <div class="mt-6">
            <h4 class="text-sm font-medium text-slate-800">最近の投稿</h4>
            <div class="mt-3 space-y-3">
              <Link v-for="post in recentPosts" :key="post.id" :href="route('posts.show', post.id)" class="glass-panel block rounded-2xl px-4 py-4 hover:bg-white/55">
                <p class="line-clamp-2 text-slate-800">{{ post.body }}</p>
                <p v-if="post.band" class="mt-2 text-sm text-slate-500">{{ post.band.name }}</p>
              </Link>
              <p v-if="recentPosts.length === 0" class="text-sm text-slate-500">まだ投稿がありません。</p>
            </div>
          </div>
        </div>
      </section>
    </div>
  </AuthenticatedLayout>
</template>
