<script setup lang="ts">
import AppSidebar from '@/Components/container/AppSidebar.vue';
import LoginRequiredModal from '@/Components/modules/LoginRequiredModal.vue';
import RightPaneSearch from '@/Components/container/RightPaneSearch.vue';
import StatusBanner from '@/Components/container/StatusBanner.vue';
import type { AuthUser } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, provide, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const page = usePage<{ auth: { user: AuthUser | null }; flash?: { status?: string | null }; ui?: { stats?: { bands?: number; merchItems?: number; posts?: number } } }>();
const user = page.props.auth.user;
const status = computed(() => page.props.flash?.status ?? null);
const stats = computed(() => page.props.ui?.stats ?? {});

const primaryNavItems = computed(() => [
  { label: t('layout.nav.home'), href: route('home'), active: route().current('home') },
  { label: t('layout.nav.bandsList'), href: route('bands.index'), active: route().current('bands.*') },
  { label: t('layout.nav.merchList'), href: route('merch-items.index'), active: route().current('merch-items.*') },
  { label: t('layout.nav.postsList'), href: route('posts.index'), active: route().current('posts.*') },
]);

const browseNavItems = computed(() => [
  { label: t('layout.nav.home'), href: route('home'), active: route().current('home') },
  { label: t('layout.nav.dashboard'), href: route('dashboard'), active: route().current('dashboard') },
  { label: t('layout.nav.bandsIndex'), href: route('bands.index'), active: route().current('bands.index') || route().current('bands.show') },
  { label: t('layout.nav.merchIndex'), href: route('merch-items.index'), active: route().current('merch-items.index') || route().current('merch-items.show') },
  { label: t('layout.nav.postsIndex'), href: route('posts.index'), active: route().current('posts.index') || route().current('posts.show') },
]);

const manageNavItems = computed(() => [
  { label: t('layout.nav.bandRegister'), href: route('bands.create'), active: route().current('bands.create') || route().current('bands.edit') },
  { label: t('layout.nav.merchRegister'), href: route('merch-items.create'), active: route().current('merch-items.create') || route().current('merch-items.edit') },
  { label: t('layout.nav.postCreate'), href: route('posts.create'), active: route().current('posts.create') || route().current('posts.edit') },
]);

const accountNavItems = computed(() => [
  { label: t('layout.nav.profile'), href: route('profile.edit'), active: route().current('profile.edit') },
  { label: t('layout.nav.logout'), href: route('logout'), active: false, method: 'post' as const, as: 'button' as const },
]);

const guestSidebarSections = computed(() => [
  {
    title: t('layout.sidebar.navigation'),
    items: primaryNavItems.value,
  },
]);

const userSidebarSections = computed(() => [
  {
    title: t('layout.sidebar.browse'),
    items: browseNavItems.value,
    scrollable: true,
  },
  {
    title: t('layout.sidebar.manage'),
    items: manageNavItems.value,
    compact: true,
  },
  {
    title: t('layout.sidebar.account'),
    items: accountNavItems.value,
    compact: true,
  },
]);

const sidebarSections = computed(() => (user ? userSidebarSections.value : guestSidebarSections.value));

const loginRequiredOpen = ref(false);
const loginRequiredFeature = ref('');

function openLoginRequired(feature?: string) {
  loginRequiredFeature.value = feature ?? t('layout.loginRequired.defaultFeature');
  loginRequiredOpen.value = true;
}

function closeLoginRequired() {
  loginRequiredOpen.value = false;
}

provide('openLoginRequired', openLoginRequired);

/** モバイル／デスクトップで二重に書かないよう AppSidebar へ渡す共通プロップ */
const sidebarProps = computed(() => ({
  homeHref: route('home'),
  mobileTitle: user ? t('layout.mobile.myPage') : t('layout.mobile.gloria'),
  mobileActionLabel: user ? t('layout.mobile.manage') : t('layout.mobile.register'),
  mobileActionHref: user ? route('posts.create') : route('register'),
  primarySections: sidebarSections.value,
  ctaLabel: user ? t('layout.mobile.post') : t('layout.mobile.login'),
  ctaHref: user ? route('posts.create') : route('login'),
  ctaActions: user ? [] : [{ label: t('layout.mobile.signup'), href: route('register') }],
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

    <div class="mx-auto max-w-[1440px] px-4 pb-3 pt-2 xl:hidden md:px-5">
      <RightPaneSearch variant="compact" />
    </div>

    <div class="mx-auto flex min-h-screen max-w-[1440px] justify-center gap-4 px-0 md:px-5 xl:gap-6">
      <AppSidebar v-bind="sidebarProps" :show-mobile="false" />

      <main class="min-h-screen w-full min-w-0 max-w-[680px] overflow-x-hidden border-x border-white/30 pb-24 md:pb-10">
        <div class="px-4 py-5 sm:px-6 sm:py-7">
          <StatusBanner v-if="status" :status="status" class="mb-4" />
          <slot />
        </div>
      </main>

      <aside class="sticky top-0 hidden h-screen w-[340px] shrink-0 px-2 py-5 xl:flex xl:flex-col">
        <div class="flex h-full min-h-0 flex-col gap-5">
          <div class="min-h-0 flex-1 space-y-5 overflow-y-auto">
          <RightPaneSearch variant="panel" />
          <section class="rounded-[2rem] border border-white/40 bg-white/35 p-6 backdrop-blur-xl">
            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-sky-700/70">{{ t('layout.public.shortcutsEyebrow') }}</p>
            <h2 class="mt-3 text-2xl font-bold text-slate-800">{{ t('layout.public.quickLinksTitle') }}</h2>
            <div class="mt-4 flex flex-col gap-2.5">
              <Link :href="route('bands.index')" class="rounded-2xl px-4 py-3 text-sm text-slate-700 transition hover:bg-white/40">{{ t('layout.public.linkBands') }}</Link>
              <Link :href="route('merch-items.index')" class="rounded-2xl px-4 py-3 text-sm text-slate-700 transition hover:bg-white/40">{{ t('layout.public.linkMerch') }}</Link>
              <Link :href="route('posts.index')" class="rounded-2xl px-4 py-3 text-sm text-slate-700 transition hover:bg-white/40">{{ t('layout.public.linkPosts') }}</Link>
              <Link v-if="user" :href="route('dashboard')" class="rounded-2xl px-4 py-3 text-sm text-slate-700 transition hover:bg-white/40">{{ t('layout.public.linkDashboard') }}</Link>
            </div>
          </section>
          <section class="rounded-[2rem] border border-white/40 bg-white/35 p-6 backdrop-blur-xl">
            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-sky-700/70">{{ t('layout.public.overviewEyebrow') }}</p>
            <div class="mt-4 grid grid-cols-3 gap-3 text-center">
              <div class="glass-panel rounded-2xl px-3 py-4">
                <p class="text-xs text-slate-500">{{ t('layout.overview.statBands') }}</p>
                <p class="mt-2 text-lg font-semibold text-slate-800">{{ stats.bands ?? 0 }}</p>
              </div>
              <div class="glass-panel rounded-2xl px-3 py-4">
                <p class="text-xs text-slate-500">{{ t('layout.overview.statMerch') }}</p>
                <p class="mt-2 text-lg font-semibold text-slate-800">{{ stats.merchItems ?? 0 }}</p>
              </div>
              <div class="glass-panel rounded-2xl px-3 py-4">
                <p class="text-xs text-slate-500">{{ t('layout.overview.statPosts') }}</p>
                <p class="mt-2 text-lg font-semibold text-slate-800">{{ stats.posts ?? 0 }}</p>
              </div>
            </div>
          </section>
          </div>
          <p class="shrink-0 pt-1 text-center text-[11px] leading-relaxed text-slate-500">
            {{ t('layout.copyright', { year: new Date().getFullYear() }) }}
          </p>
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

    <LoginRequiredModal
      :show="loginRequiredOpen"
      :feature="loginRequiredFeature"
      @close="closeLoginRequired"
    />
  </div>
</template>
