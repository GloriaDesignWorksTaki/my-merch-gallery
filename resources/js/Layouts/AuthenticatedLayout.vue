<script setup lang="ts">
import { computed } from 'vue';
import AppSidebar from '@/Components/AppSidebar.vue';
import StatusBanner from '@/Components/StatusBanner.vue';
import { Link, usePage } from '@inertiajs/vue3';
import type { User } from '@/types';

const page = usePage<{ auth: { user: User | null }; flash?: { status?: string | null }; ui?: { stats?: { bands?: number; merchItems?: number; posts?: number } } }>();
const authUser = page.props.auth.user;
const status = computed(() => page.props.flash?.status ?? null);
const stats = computed(() => page.props.ui?.stats ?? {});

if (!authUser) {
  throw new Error('AuthenticatedLayout requires a logged-in user.');
}

const browseNavItems = computed(() => [
  { label: 'ホーム', href: route('home'), active: route().current('home') },
  { label: 'マイページ', href: route('dashboard'), active: route().current('dashboard') },
  { label: 'バンド一覧', href: route('bands.index'), active: route().current('bands.index') || route().current('bands.show') },
  { label: 'マーチ一覧', href: route('merch-items.index'), active: route().current('merch-items.index') || route().current('merch-items.show') },
  { label: '投稿一覧', href: route('posts.index'), active: route().current('posts.index') || route().current('posts.show') },
]);

const manageNavItems = computed(() => [
  { label: 'バンド登録', href: route('bands.create'), active: route().current('bands.create') || route().current('bands.edit') },
  { label: 'マーチ登録', href: route('merch-items.create'), active: route().current('merch-items.create') || route().current('merch-items.edit') },
  { label: '投稿作成', href: route('posts.create'), active: route().current('posts.create') || route().current('posts.edit') },
  { label: 'プロフィール設定', href: route('profile.edit'), active: route().current('profile.edit') },
]);

const sidebarSections = computed(() => [
  {
    title: 'Browse',
    items: browseNavItems.value,
  },
  {
    title: 'Manage',
    items: manageNavItems.value,
    compact: true,
  },
]);
</script>

<template>
  <div class="min-h-screen text-slate-700">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
      <div class="absolute -left-12 top-12 h-72 w-72 rounded-full bg-sky-400/28 blur-3xl" />
      <div class="absolute right-0 top-20 h-80 w-80 rounded-full bg-white/55 blur-3xl" />
      <div class="absolute bottom-0 left-1/4 h-[28rem] w-[28rem] rounded-full bg-cyan-300/18 blur-3xl" />
    </div>

    <AppSidebar
      :home-href="route('home')"
      mobile-title="MY PAGE"
      mobile-action-label="Manage"
      :mobile-action-href="route('posts.create')"
      :primary-sections="sidebarSections"
      cta-label="投稿する"
      :cta-href="route('posts.create')"
      :footer-title="authUser.name"
      :footer-subtitle="`@${authUser.username}`"
      :footer-meta="authUser.email"
      :show-desktop="false"
    />

    <div class="mx-auto flex min-h-screen max-w-[1440px] justify-center gap-4 px-0 md:px-5 xl:gap-6">
      <AppSidebar
        :home-href="route('home')"
        mobile-title="MY PAGE"
        mobile-action-label="Manage"
        :mobile-action-href="route('posts.create')"
        :primary-sections="sidebarSections"
        cta-label="投稿する"
        :cta-href="route('posts.create')"
        :footer-title="authUser.name"
        :footer-subtitle="`@${authUser.username}`"
        :footer-meta="authUser.email"
        :show-mobile="false"
      />

      <main class="min-h-screen w-full max-w-[680px] border-x border-white/30 pb-24 md:pb-10">
        <div v-if="$slots.header" class="px-4 pt-5 sm:px-6 sm:pt-7">
          <div class="glass-panel rounded-3xl px-6 py-5">
            <slot name="header" />
          </div>
        </div>
        <div class="px-4 py-5 sm:px-6 sm:py-7">
          <StatusBanner v-if="status" :status="status" class="mb-4" />
          <slot />
        </div>
      </main>

      <aside class="sticky top-0 hidden h-screen w-[340px] shrink-0 px-2 py-5 xl:block">
        <div class="space-y-5">
          <section class="rounded-[2rem] border border-white/40 bg-white/35 p-6 backdrop-blur-xl">
            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-sky-700/70">Account</p>
            <h2 class="mt-3 text-2xl font-bold text-slate-800">Your Space</h2>
            <div class="mt-4 grid grid-cols-3 gap-3 text-center">
              <div class="glass-panel rounded-2xl px-3 py-4">
                <p class="text-xs text-slate-500">Bands</p>
                <p class="mt-2 text-lg font-semibold text-slate-800">{{ stats.bands ?? 0 }}</p>
              </div>
              <div class="glass-panel rounded-2xl px-3 py-4">
                <p class="text-xs text-slate-500">Merch</p>
                <p class="mt-2 text-lg font-semibold text-slate-800">{{ stats.merchItems ?? 0 }}</p>
              </div>
              <div class="glass-panel rounded-2xl px-3 py-4">
                <p class="text-xs text-slate-500">Posts</p>
                <p class="mt-2 text-lg font-semibold text-slate-800">{{ stats.posts ?? 0 }}</p>
              </div>
            </div>
            <div class="mt-4 space-y-3 text-sm text-slate-600">
              <p>閲覧と登録を同じレイアウト内で行き来できるようにしています。</p>
              <p>次の一手を右カラムからすぐ選べるように改善しています。</p>
            </div>
            <div class="mt-5 flex flex-wrap gap-2">
              <Link :href="route('profile.edit')" class="glass-link text-sm font-medium">プロフィール設定</Link>
              <Link :href="route('logout')" method="post" as="button" class="glass-link text-sm font-medium">ログアウト</Link>
            </div>
          </section>
        </div>
      </aside>
    </div>

    <nav class="fixed inset-x-0 bottom-0 z-30 px-3 pb-3 md:hidden">
      <div class="mx-auto grid max-w-md grid-cols-4 rounded-[1.75rem] border border-white/40 bg-white/55 shadow-[0_10px_35px_rgba(148,163,184,0.18)] backdrop-blur-2xl">
        <Link
          v-for="item in browseNavItems.slice(0, 4)"
          :key="item.label"
          :href="item.href"
          class="flex flex-col items-center justify-center px-2 py-3 text-[11px] font-semibold transition"
          :class="item.active ? 'text-slate-900' : 'text-slate-500'"
        >
          {{ item.label }}
        </Link>
      </div>
    </nav>
  </div>
</template>
