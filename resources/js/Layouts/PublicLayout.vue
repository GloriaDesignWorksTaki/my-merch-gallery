<script setup lang="ts">
import { computed } from 'vue';
import AppSidebar from '@/Components/AppSidebar.vue';
import StatusBanner from '@/Components/StatusBanner.vue';
import { Link, usePage } from '@inertiajs/vue3';
import type { AuthUser } from '@/types';

const page = usePage<{ auth: { user: AuthUser | null }; flash?: { status?: string | null }; ui?: { stats?: { bands?: number; merchItems?: number; posts?: number } } }>();
const user = page.props.auth.user;
const status = computed(() => page.props.flash?.status ?? null);
const stats = computed(() => page.props.ui?.stats ?? {});

const primaryNavItems = computed(() => [
  { label: 'ホーム', href: route('home'), active: route().current('home') },
  { label: 'バンド', href: route('bands.index'), active: route().current('bands.*') },
  { label: 'マーチ', href: route('merch-items.index'), active: route().current('merch-items.*') },
  { label: '投稿', href: route('posts.index'), active: route().current('posts.*') },
]);

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
]);

const accountNavItems = computed(() => [
  { label: 'プロフィール設定', href: route('profile.edit'), active: route().current('profile.edit') },
  { label: 'ログアウト', href: route('logout'), active: false, method: 'post' as const, as: 'button' as const },
]);

const guestSidebarSections = computed(() => [
  {
    title: 'Navigation',
    items: primaryNavItems.value,
  },
]);

const userSidebarSections = computed(() => [
  {
    title: 'Browse',
    items: browseNavItems.value,
    scrollable: true,
  },
  {
    title: 'Manage',
    items: manageNavItems.value,
    compact: true,
  },
  {
    title: 'Account',
    items: accountNavItems.value,
    compact: true,
  },
]);

const sidebarSections = computed(() => (user ? userSidebarSections.value : guestSidebarSections.value));

/** モバイル／デスクトップで二重に書かないよう AppSidebar へ渡す共通プロップ */
const sidebarProps = computed(() => ({
  homeHref: route('home'),
  mobileTitle: user ? 'MY PAGE' : 'GLORIA',
  mobileActionLabel: user ? 'Manage' : '登録',
  mobileActionHref: user ? route('posts.create') : route('register'),
  primarySections: sidebarSections.value,
  ctaLabel: user ? '投稿する' : 'ログイン',
  ctaHref: user ? route('posts.create') : route('login'),
  ctaActions: user ? [] : [{ label: 'サインアップ', href: route('register'), tone: 'secondary' as const }],
  footerTitle: user?.name ?? 'Gloria Design Works',
  footerSubtitle: user ? `@${user.username}` : '@GloriaDesignWKS',
  footerAvatarUrl: user?.avatar_path ? `/storage/${user.avatar_path}` : null,
  footerAvatarFocusX: user?.avatar_focus_x ?? 50,
  footerAvatarFocusY: user?.avatar_focus_y ?? 50,
  footerAvatarZoom: user?.avatar_zoom ?? 1,
  showFooter: Boolean(user),
}));
</script>

<template>
  <div class="min-h-screen text-slate-700">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
      <div class="absolute left-[-12rem] top-[-6rem] h-80 w-80 rounded-full bg-sky-500/12 blur-3xl" />
      <div class="absolute bottom-[-8rem] right-[-10rem] h-96 w-96 rounded-full bg-cyan-400/10 blur-3xl" />
    </div>

    <AppSidebar v-bind="sidebarProps" :show-desktop="false" />

    <div class="mx-auto flex min-h-screen max-w-[1440px] justify-center gap-4 px-0 md:px-5 xl:gap-6">
      <AppSidebar v-bind="sidebarProps" :show-mobile="false" />

      <main class="min-h-screen w-full min-w-0 max-w-[680px] overflow-x-hidden border-x border-white/30 pb-24 md:pb-10">
        <div class="px-4 py-5 sm:px-6 sm:py-7">
          <StatusBanner v-if="status" :status="status" class="mb-4" />
          <slot />
        </div>
      </main>

      <aside class="sticky top-0 hidden h-screen w-[340px] shrink-0 px-2 py-5 xl:block">
        <div class="space-y-5">
          <section class="rounded-[2rem] border border-white/40 bg-white/35 p-6 backdrop-blur-xl">
            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-sky-700/70">Shortcuts</p>
            <h2 class="mt-3 text-2xl font-bold text-slate-800">Quick Links</h2>
            <div class="mt-4 flex flex-col gap-2.5">
              <Link :href="route('bands.index')" class="rounded-2xl px-4 py-3 text-sm text-slate-700 transition hover:bg-white/40">バンド一覧</Link>
              <Link :href="route('merch-items.index')" class="rounded-2xl px-4 py-3 text-sm text-slate-700 transition hover:bg-white/40">マーチ一覧</Link>
              <Link :href="route('posts.index')" class="rounded-2xl px-4 py-3 text-sm text-slate-700 transition hover:bg-white/40">投稿一覧</Link>
              <Link v-if="user" :href="route('dashboard')" class="rounded-2xl px-4 py-3 text-sm text-slate-700 transition hover:bg-white/40">マイページ</Link>
            </div>
          </section>
          <section class="rounded-[2rem] border border-white/40 bg-white/35 p-6 backdrop-blur-xl">
            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-sky-700/70">Overview</p>
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
          </section>
        </div>
      </aside>
    </div>

    <nav class="fixed inset-x-0 bottom-0 z-30 px-3 pb-3 md:hidden">
      <div class="mx-auto grid max-w-md grid-cols-4 rounded-[1.75rem] border border-white/40 bg-white/55 shadow-[0_10px_35px_rgba(148,163,184,0.18)] backdrop-blur-2xl">
        <Link
          v-for="item in primaryNavItems"
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
