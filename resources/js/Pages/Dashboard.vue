<script setup lang="ts">
/**
 * ダッシュボードページ
 * @param summary サマリ
 * @param recentBands 最近登録したバンド
 * @param recentMerchItems 最近登録したマーチ
 * @param recentPosts 最近の投稿
 * @param profileHints プロフィールヒント
*/

// import
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

// マルチリンガル対応用テキスト用関数
const { t } = useI18n();

// プロパティ定義
defineProps<{
  summary: {
    bands: number;
    merchItems: number;
    posts: number;
  };
  // 最近登録したバンド
  recentBands: { id: number; name: string; slug: string }[];
  // 最近登録したマーチ
  recentMerchItems: { id: number; name: string; slug: string; band?: { name: string; slug: string } | null; cover_image?: { image_path: string; alt_text?: string | null } | null }[];
  // 最近の投稿
  recentPosts: { id: number; body: string; band?: { name: string; slug: string } | null; cover_image?: { image_path: string } | null }[];
  // プロフィールヒント
  profileHints: {
    bioMissing: boolean;
    avatarMissing: boolean;
  };
}>();

</script>

<template>
  <Head :title="t('dashboard.title')" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between gap-4">
        <div>
          <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('dashboard.eyebrow') }}</p>
          <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-800">{{ t('dashboard.title') }}</h2>
        </div>
        <div class="glass-panel rounded-full px-4 py-2 text-sm text-slate-600">
          {{ t('dashboard.badge') }}
        </div>
      </div>
    </template>

    <div class="mx-auto max-w-4xl space-y-6">
      <section class="space-y-6">
        <div class="glass-surface p-6">
          <div class="flex items-center justify-between gap-4">
            <h3 class="text-lg font-semibold text-slate-800">{{ t('dashboard.recentBands') }}</h3>
            <Link :href="route('bands.index')" class="glass-link text-sm font-medium">{{ t('dashboard.toList') }}</Link>
          </div>
          <div class="mt-4 space-y-3">
            <Link v-for="band in recentBands" :key="band.id" :href="route('bands.show', band.slug)" class="glass-panel block rounded-2xl px-4 py-4 hover:bg-white/55">
              <p class="font-medium text-slate-800">{{ band.name }}</p>
            </Link>
            <p v-if="recentBands.length === 0" class="text-sm text-slate-500">{{ t('dashboard.emptyBands') }}</p>
          </div>
        </div>

        <div class="glass-surface p-6">
          <div class="flex items-center justify-between gap-4">
            <h3 class="text-lg font-semibold text-slate-800">{{ t('dashboard.recentMerch') }}</h3>
            <Link :href="route('merch-items.index')" class="glass-link text-sm font-medium">{{ t('dashboard.toList') }}</Link>
          </div>
          <div class="mt-4 space-y-3">
            <Link v-for="item in recentMerchItems" :key="item.id" :href="route('merch-items.show', item.slug)" class="glass-panel flex items-center gap-4 rounded-2xl px-4 py-4 hover:bg-white/55">
              <div class="h-14 w-14 shrink-0 overflow-hidden rounded-2xl border border-white/40 bg-white/45">
                <img v-if="item.cover_image" :src="`/storage/${item.cover_image.image_path}`" :alt="item.cover_image.alt_text || item.name" class="h-full w-full object-cover" />
              </div>
              <div class="min-w-0">
                <p class="font-medium text-slate-800">{{ item.name }}</p>
                <p v-if="item.band" class="mt-1 text-sm text-slate-500">{{ item.band.name }}</p>
              </div>
            </Link>
            <p v-if="recentMerchItems.length === 0" class="text-sm text-slate-500">{{ t('dashboard.emptyMerch') }}</p>
          </div>
        </div>

        <div class="glass-surface p-6">
          <div class="flex items-center justify-between gap-4">
            <h3 class="text-lg font-semibold text-slate-800">{{ t('dashboard.profileSection') }}</h3>
            <Link :href="route('profile.edit')" class="glass-link text-sm font-medium">{{ t('dashboard.toSettings') }}</Link>
          </div>
          <div class="mt-4 space-y-3 text-sm text-slate-600">
            <p :class="profileHints.bioMissing ? 'text-slate-800' : 'text-slate-500'">{{ profileHints.bioMissing ? t('dashboard.bioUnset') : t('dashboard.bioSet') }}</p>
            <p :class="profileHints.avatarMissing ? 'text-slate-800' : 'text-slate-500'">{{ profileHints.avatarMissing ? t('dashboard.avatarUnset') : t('dashboard.avatarSet') }}</p>
          </div>
          <div class="mt-6">
            <h4 class="text-sm font-medium text-slate-800">{{ t('dashboard.recentPosts') }}</h4>
            <div class="mt-3 space-y-3">
              <Link v-for="post in recentPosts" :key="post.id" :href="route('posts.show', post.id)" class="glass-panel block rounded-2xl px-4 py-4 hover:bg-white/55">
                <p class="line-clamp-2 text-slate-800">{{ post.body }}</p>
                <p v-if="post.band" class="mt-2 text-sm text-slate-500">{{ post.band.name }}</p>
              </Link>
              <p v-if="recentPosts.length === 0" class="text-sm text-slate-500">{{ t('dashboard.emptyPosts') }}</p>
            </div>
          </div>
        </div>
      </section>
    </div>
  </AuthenticatedLayout>
</template>
