<script setup lang="ts">
import { computed } from 'vue';
import AppSidebar from '@/Components/container/AppSidebar.vue';
import LikesHistoryShortcut from '@/Components/parts/LikesHistoryShortcut.vue';
import NotificationBell from '@/Components/parts/NotificationBell.vue';
import RightPaneSearch from '@/Components/container/RightPaneSearch.vue';
import StatusBanner from '@/Components/container/StatusBanner.vue';
import { Link, usePage } from '@inertiajs/vue3';
import type { AuthUser } from '@/types';
import { sidebarNavIcons } from '@/constants/sidebarIcons';
import type { FooterMenuItem } from '@/types/sidebar';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const page = usePage<{
  auth: { user: AuthUser | null };
  flash?: { status?: string | null };
  inbox?: { unreadCount: number };
}>();
const authUser = page.props.auth.user;
const status = computed(() => page.props.flash?.status ?? null);

if (!authUser) {
  throw new Error('AuthenticatedLayout requires a logged-in user.');
}

const browseNavItems = computed(() => [
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
    items: browseNavItems.value,
    scrollable: false,
  },
]);

const footerMenuItems = computed((): FooterMenuItem[] => {
  const items: FooterMenuItem[] = [{ label: t('layout.nav.profile'), href: route('profile.edit'), icon: 'profile' }];
  if (authUser.role === 'admin' || authUser.role === 'owner') {
    items.push({ label: t('layout.nav.adminDashboard'), href: route('admin.dashboard'), icon: 'admin' });
  }
  items.push({
    label: t('layout.nav.logout'),
    href: route('logout', undefined, false),
    method: 'post',
    as: 'button',
    danger: true,
    icon: 'logout',
  });

  return items;
});

const mobileHeaderMenuItems = computed((): FooterMenuItem[] => {
  const items: FooterMenuItem[] = [
    { label: t('layout.nav.bandRegister'), href: route('bands.create'), icon: 'bandRegister' },
    { label: t('layout.nav.merchRegister'), href: route('merch-items.create'), icon: 'merchRegister' },
    { label: t('layout.nav.profile'), href: route('profile.edit'), icon: 'profile' },
  ];
  if (authUser.role === 'admin' || authUser.role === 'owner') {
    items.push({ label: t('layout.nav.adminDashboard'), href: route('admin.dashboard'), icon: 'admin' });
  }
  items.push({
    label: t('layout.nav.logout'),
    href: route('logout', undefined, false),
    method: 'post',
    as: 'button',
    danger: true,
    icon: 'logout',
  });
  return items;
});

const sidebarProps = computed(() => ({
  homeHref: route('home'),
  mobileActionLabel: t('layout.mobile.manage'),
  mobileActionHref: null,
  mobileHeaderMenuItems: mobileHeaderMenuItems.value,
  primarySections: sidebarSections.value,
  ctaLabel: t('layout.nav.bandRegister'),
  ctaHref: route('bands.create'),
  ctaIcon: 'bandRegister' as const,
  ctaActions: [{ label: t('layout.nav.merchRegister'), href: route('merch-items.create'), icon: 'merchRegister' as const }],
  footerTitle: authUser.name,
  footerSubtitle: `@${authUser.username}`,
  footerAvatarUrl: authUser.avatar_url ?? null,
  footerAvatarFocusX: authUser.avatar_focus_x ?? 50,
  footerAvatarFocusY: authUser.avatar_focus_y ?? 50,
  footerAvatarZoom: authUser.avatar_zoom ?? 1,
  footerMenuItems: footerMenuItems.value,
  footerMenuPlacement: 'top-start' as const,
}));
</script>

<template>
  <div class="app-chrome min-h-screen text-slate-700">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
      <div class="app-shell-blob-a" />
      <div class="app-shell-blob-b" />
      <div class="app-shell-blob-c" />
    </div>

    <AppSidebar v-bind="sidebarProps" :show-desktop="false" />

    <div class="mx-auto flex w-full max-w-[680px] items-center gap-2 px-4 py-3 sm:px-6 xl:hidden">
      <div class="flex shrink-0 items-center gap-2">
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
        <div v-if="$slots.header" class="px-4 pt-3 sm:px-6 sm:pt-5">
          <div class="glass-panel rounded-2xl px-4 py-3.5 sm:px-5 sm:py-4">
            <slot name="header" />
          </div>
        </div>
        <div class="px-4 pb-4 pt-3 sm:px-6 sm:pb-5 sm:pt-5">
          <StatusBanner v-if="status" :status="status" class="mb-4" />
          <slot />
        </div>
      </main>

      <aside class="sticky top-0 hidden h-screen w-[340px] shrink-0 px-2 py-5 xl:flex xl:flex-col">
        <div class="flex h-full min-h-0 flex-col gap-5">
          <div class="min-h-0 flex-1 space-y-5 overflow-y-auto">
            <div class="flex items-center justify-end gap-2">
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
      <div class="app-mobile-nav mx-auto grid max-w-md grid-cols-4 rounded-[1.75rem]">
        <Link
          v-for="item in browseNavItems.slice(0, 4)"
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
  </div>
</template>
