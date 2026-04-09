<script setup lang="ts">
import AppSidebar from '@/Components/container/AppSidebar.vue';
import AuthModals from '@/Components/modules/AuthModals.vue';
import LoginRequiredModal from '@/Components/modules/LoginRequiredModal.vue';
import LikesHistoryShortcut from '@/Components/parts/LikesHistoryShortcut.vue';
import NotificationBell from '@/Components/parts/NotificationBell.vue';
import RightPaneSearch from '@/Components/container/RightPaneSearch.vue';
import StatusBanner from '@/Components/container/StatusBanner.vue';
import type { AuthUser } from '@/types';
import { sidebarNavIcons } from '@/constants/sidebarIcons';
import type { FooterMenuItem } from '@/types/sidebar';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, provide, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const page = usePage<{
  auth: { user: AuthUser | null };
  flash?: { status?: string | null };
  inbox?: { unreadCount: number; recent: unknown[] };
}>();
const user = page.props.auth.user;
const status = computed(() => page.props.flash?.status ?? null);

const browseNavItemsBase = computed(() => [
  { label: t('layout.nav.home'), href: route('home'), active: route().current('home'), icon: 'home' as const },
  {
    label: t('layout.nav.bandsIndex'),
    href: route('bands.index'),
    active: route().current('bands.index') || route().current('bands.show'),
    icon: 'bands' as const,
  },
  {
    label: t('layout.nav.merchIndex'),
    href: route('merch-items.index'),
    active: route().current('merch-items.index') || route().current('merch-items.show'),
    icon: 'merch' as const,
  },
]);

const loggedInBrowseNavItems = computed(() => [
  ...browseNavItemsBase.value,
  {
    label: t('layout.nav.dashboard'),
    href: route('dashboard'),
    active: route().current('dashboard') && !route().current('dashboard.likes'),
    icon: 'dashboard' as const,
  },
]);

const sidebarSections = computed(() => [
  {
    title: t('layout.sidebar.browse'),
    items: user ? loggedInBrowseNavItems.value : browseNavItemsBase.value,
    scrollable: false,
  },
]);

const mobileBottomNavItems = computed(() => (user ? loggedInBrowseNavItems.value : browseNavItemsBase.value));

const loginRequiredOpen = ref(false);
const loginRequiredFeature = ref('');
const showAuthLogin = ref(false);
const showAuthRegister = ref(false);

function openLoginRequired(feature?: string) {
  loginRequiredFeature.value = feature ?? t('layout.loginRequired.defaultFeature');
  loginRequiredOpen.value = true;
}

function closeLoginRequired() {
  loginRequiredOpen.value = false;
}

function openAuthLogin() {
  showAuthRegister.value = false;
  showAuthLogin.value = true;
}

function openAuthRegister() {
  showAuthLogin.value = false;
  showAuthRegister.value = true;
}

function parseAuthQuery(url: string): 'login' | 'register' | null {
  try {
    const resolved = url.startsWith('http')
      ? url
      : `${window.location.origin}${url.startsWith('/') ? '' : '/'}${url}`;
    const u = new URL(resolved);
    const a = u.searchParams.get('auth');
    if (a === 'login' || a === 'register') {
      return a;
    }
  } catch {
    /* ignore invalid URL */
  }
  return null;
}

function pathWithoutAuthQuery(url: string): string | null {
  try {
    const resolved = url.startsWith('http')
      ? url
      : `${window.location.origin}${url.startsWith('/') ? '' : '/'}${url}`;
    const u = new URL(resolved);
    if (!u.searchParams.has('auth')) {
      return null;
    }
    u.searchParams.delete('auth');
    return u.pathname + (u.search || '') + u.hash;
  } catch {
    return null;
  }
}

watch(
  () => page.url,
  () => {
    const auth = parseAuthQuery(page.url);
    if (auth === 'login') {
      openAuthLogin();
      const next = pathWithoutAuthQuery(page.url);
      if (next !== null) {
        router.visit(next, { replace: true, preserveState: true, preserveScroll: true });
      }
    } else if (auth === 'register') {
      openAuthRegister();
      const next = pathWithoutAuthQuery(page.url);
      if (next !== null) {
        router.visit(next, { replace: true, preserveState: true, preserveScroll: true });
      }
    }
  },
  { immediate: true },
);

provide('openLoginRequired', openLoginRequired);
provide('openAuthLogin', openAuthLogin);
provide('openAuthRegister', openAuthRegister);

const sidebarProps = computed(() => ({
  homeHref: route('home'),
  mobileActionLabel: user ? t('layout.mobile.manage') : t('layout.mobile.login'),
  mobileActionHref: null,
  mobileHeaderMenuItems: user
    ? (() => {
        const items: FooterMenuItem[] = [
          { label: t('layout.nav.bandRegister'), href: route('bands.create'), icon: 'bandRegister' },
          { label: t('layout.nav.merchRegister'), href: route('merch-items.create'), icon: 'merchRegister' },
          { label: t('layout.nav.profile'), href: route('profile.edit'), icon: 'profile' },
        ];
        if (user.role === 'admin' || user.role === 'owner') {
          items.push({ label: t('layout.nav.adminDashboard'), href: route('admin.dashboard'), icon: 'admin' });
        }
        items.push({
          label: t('layout.nav.logout'),
          href: route('logout'),
          method: 'post',
          as: 'button',
          danger: true,
          icon: 'logout',
        });
        return items;
      })()
    : undefined,
  mobileAuthModal: !user,
  primarySections: sidebarSections.value,
  ctaLabel: user ? t('layout.nav.bandRegister') : t('layout.mobile.login'),
  ctaHref: user ? route('bands.create') : null,
  ctaAuthModal: !user,
  ctaIcon: user ? ('bandRegister' as const) : ('login' as const),
  ctaActions: user
    ? [{ label: t('layout.nav.merchRegister'), href: route('merch-items.create'), icon: 'merchRegister' as const }]
    : [{ label: t('layout.mobile.signup'), useRegisterModal: true, icon: 'signup' as const }],
  footerTitle: user?.name ?? 'Gloria Design Works',
  footerSubtitle: user ? `@${user.username}` : '@GloriaDesignWKS',
  footerAvatarUrl: user?.avatar_url ?? null,
  footerAvatarFocusX: user?.avatar_focus_x ?? 50,
  footerAvatarFocusY: user?.avatar_focus_y ?? 50,
  footerAvatarZoom: user?.avatar_zoom ?? 1,
  showFooter: Boolean(user),
  footerMenuItems: user
    ? (() => {
        const items: FooterMenuItem[] = [
          { label: t('layout.nav.profile'), href: route('profile.edit'), icon: 'profile' },
        ];
        if (user.role === 'admin' || user.role === 'owner') {
          items.push({ label: t('layout.nav.adminDashboard'), href: route('admin.dashboard'), icon: 'admin' });
        }
        items.push({
          label: t('layout.nav.logout'),
          href: route('logout'),
          method: 'post',
          as: 'button',
          danger: true,
          icon: 'logout',
        });

        return items;
      })()
    : undefined,
  footerMenuPlacement: 'top-start' as const,
}));
</script>

<template>
  <div class="app-chrome min-h-screen text-slate-700">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
      <div class="app-shell-blob-p1" />
      <div class="app-shell-blob-p2" />
    </div>

    <AppSidebar v-bind="sidebarProps" :show-desktop="false" />

    <div class="mx-auto flex w-full max-w-[680px] items-center gap-2 px-4 py-3 sm:px-6 xl:hidden">
      <div v-if="user" class="flex shrink-0 items-center gap-2">
        <LikesHistoryShortcut />
        <NotificationBell />
      </div>
      <div class="min-w-0 flex-1">
        <RightPaneSearch variant="compact" />
      </div>
    </div>

    <div class="mx-auto flex min-h-screen max-w-[1440px] justify-center gap-4 px-0 md:px-5 xl:gap-6">
      <AppSidebar v-bind="sidebarProps" :show-mobile="false" />

      <main class="app-main-column-border min-h-screen w-full min-w-0 max-w-[680px] overflow-x-hidden border-x pb-24 md:pb-10">
        <div class="px-4 pb-5 pt-3 sm:px-6 sm:pb-7 sm:pt-7">
          <StatusBanner v-if="status" :status="status" class="mb-4" />
          <slot />
        </div>
      </main>

      <aside class="sticky top-0 hidden h-screen w-[340px] shrink-0 px-2 py-5 xl:flex xl:flex-col">
        <div class="flex h-full min-h-0 flex-col gap-5">
          <div class="min-h-0 flex-1 space-y-5 overflow-y-auto">
            <div v-if="user" class="flex items-center justify-end gap-2">
              <LikesHistoryShortcut />
              <NotificationBell />
            </div>
            <RightPaneSearch variant="panel" />
          </div>
          <p class="shrink-0 pt-1 text-center text-[11px] leading-relaxed text-slate-500 theme-light:text-slate-600 dark:text-slate-300">
            {{ t('layout.copyright', { year: new Date().getFullYear() }) }}
          </p>
        </div>
      </aside>
    </div>

    <nav class="fixed inset-x-0 bottom-0 z-30 px-3 pb-3 md:hidden">
      <div
        class="app-mobile-nav mx-auto grid rounded-[1.75rem]"
        :class="user ? 'max-w-lg grid-cols-4' : 'max-w-md grid-cols-3'"
      >
        <Link
          v-for="item in mobileBottomNavItems"
          :key="item.label"
          :href="item.href"
          class="flex flex-col items-center justify-center gap-0.5 rounded-2xl px-2 py-2.5 text-[11px] font-semibold transition"
          :class="
            item.active
              ? 'text-slate-900 theme-light:bg-slate-200/70'
              : 'text-slate-500 theme-light:hover:bg-slate-200/60 theme-light:hover:text-slate-800 theme-light:active:bg-slate-300/50 theme-primary:hover:bg-white/25 dark:hover:bg-slate-800/35'
          "
        >
          <component
            v-if="item.icon"
            :is="sidebarNavIcons[item.icon]"
            class="h-6 w-6"
            :class="
              item.active ? 'text-sky-800 theme-light:text-sky-900' : 'text-slate-400 theme-light:text-slate-600'
            "
            aria-hidden="true"
          />
          <span class="max-w-[4.5rem] truncate text-center leading-tight">{{ item.label }}</span>
        </Link>
      </div>
    </nav>

    <AuthModals v-model:show-login="showAuthLogin" v-model:show-register="showAuthRegister" />

    <LoginRequiredModal
      :show="loginRequiredOpen"
      :feature="loginRequiredFeature"
      @close="closeLoginRequired"
    />
  </div>
</template>
