<script setup lang="ts">
import AppButton from '@/Components/parts/AppButton.vue';
import ApplicationLogo from '@/Components/parts/ApplicationLogo.vue';
import LocaleSwitcher from '@/Components/parts/LocaleSwitcher.vue';
import { footerMenuIcons, sidebarCtaIcons, sidebarNavIcons } from '@/constants/sidebarIcons';
import type {
  FooterMenuItem,
  FooterMenuPlacement,
  SidebarCtaAction,
  SidebarCtaIcon,
  SidebarNavSection,
} from '@/types/sidebar';
import { IconChevronDown } from '@tabler/icons-vue';
import { fallbackVisitAuthLoginModal, fallbackVisitAuthRegisterModal } from '@/utils/authModalFallback';
import { Link } from '@inertiajs/vue3';
import { computed, inject, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const openAuthLogin = inject('openAuthLogin', fallbackVisitAuthLoginModal) as () => void;
const openAuthRegister = inject('openAuthRegister', fallbackVisitAuthRegisterModal) as () => void;

const props = withDefaults(defineProps<{
  homeHref: string;
  mobileActionLabel: string;
  mobileActionHref?: string | null;
  /** ログイン後の Manage など：ヘッダーをリンクではなくアカウント系ドロップダウンにする */
  mobileHeaderMenuItems?: FooterMenuItem[];
  mobileAuthModal?: boolean;
  primarySections: SidebarNavSection[];
  ctaLabel: string;
  ctaHref?: string | null;
  ctaAuthModal?: boolean;
  /** 未指定時はログインモーダル→login、リンクCTA→bandRegister */
  ctaIcon?: SidebarCtaIcon;
  ctaActions?: SidebarCtaAction[];
  footerTitle: string;
  footerSubtitle?: string;
  footerMeta?: string;
  footerAvatarUrl?: string | null;
  footerAvatarFocusX?: number;
  footerAvatarFocusY?: number;
  footerAvatarZoom?: number;
  footerMenuItems?: FooterMenuItem[];
  footerMenuPlacement?: FooterMenuPlacement;
  showFooter?: boolean;
  showMobile?: boolean;
  showDesktop?: boolean;
}>(), {
  mobileActionHref: null,
  mobileHeaderMenuItems: undefined,
  mobileAuthModal: false,
  ctaHref: null,
  ctaAuthModal: false,
  ctaIcon: undefined,
  footerAvatarUrl: null,
  footerAvatarFocusX: 50,
  footerAvatarFocusY: 50,
  footerAvatarZoom: 1,
  footerMenuPlacement: 'top-start',
  showFooter: true,
  showMobile: true,
  showDesktop: true,
});

const footerAvatarStyle = computed(() => ({
  objectPosition: `${props.footerAvatarFocusX}% ${props.footerAvatarFocusY}%`,
  transform: `scale(${props.footerAvatarZoom})`,
}));

const menuOpen = ref(false);
const mobileMenuOpen = ref(false);
const footerRoot = ref<HTMLButtonElement | null>(null);
const mobileHeaderRoot = ref<HTMLButtonElement | null>(null);
const panelRef = ref<HTMLElement | null>(null);
const mobilePanelRef = ref<HTMLElement | null>(null);
const dropdownStyle = ref<Record<string, string>>({});
const mobileDropdownStyle = ref<Record<string, string>>({});

const PANEL_MAX_WIDTH_PX = 16 * 16;
const PANEL_MARGIN_PX = 8;

function clampPanelLeft(leftPx: number, panelWidth: number): number {
  let left = leftPx;
  if (left < PANEL_MARGIN_PX) {
    left = PANEL_MARGIN_PX;
  }
  if (left + panelWidth > window.innerWidth - PANEL_MARGIN_PX) {
    left = window.innerWidth - PANEL_MARGIN_PX - panelWidth;
  }
  return left;
}

function computeDropdownStyle(el: HTMLElement, placement: FooterMenuPlacement): Record<string, string> {
  const r = el.getBoundingClientRect();
  const innerH = window.innerHeight;
  const maxW = Math.min(window.innerWidth - 2 * PANEL_MARGIN_PX, PANEL_MAX_WIDTH_PX);

  const vertical = placement.startsWith('bottom') ? 'bottom' : 'top';
  const horizontal = placement.endsWith('end') ? 'end' : 'start';

  let leftPx: number;
  if (horizontal === 'start') {
    leftPx = clampPanelLeft(r.left, maxW);
  } else {
    leftPx = clampPanelLeft(r.right - maxW, maxW);
  }

  const base: Record<string, string> = {
    position: 'fixed',
    left: `${leftPx}px`,
    width: `${maxW}px`,
    overflowY: 'auto',
    overflowX: 'hidden',
  };

  if (vertical === 'bottom') {
    const spaceBelow = Math.max(0, innerH - r.bottom - PANEL_MARGIN_PX * 2);
    const maxH = Math.min(320, spaceBelow);
    return {
      ...base,
      top: `${r.bottom + PANEL_MARGIN_PX}px`,
      bottom: 'auto',
      maxHeight: `${Math.max(1, maxH)}px`,
    };
  }
  const spaceAbove = Math.max(0, r.top - PANEL_MARGIN_PX);
  const maxH = Math.min(320, spaceAbove);
  return {
    ...base,
    top: 'auto',
    bottom: `${innerH - r.top + PANEL_MARGIN_PX}px`,
    maxHeight: `${Math.max(1, maxH)}px`,
  };
}

function updateDropdownPosition() {
  const el = footerRoot.value;
  if (!el) {
    return;
  }
  dropdownStyle.value = computeDropdownStyle(el, props.footerMenuPlacement);
}

function updateMobileDropdownPosition() {
  const el = mobileHeaderRoot.value;
  if (!el) {
    return;
  }
  mobileDropdownStyle.value = computeDropdownStyle(el, 'bottom-end');
}

function onViewportChange() {
  if (menuOpen.value) {
    updateDropdownPosition();
  }
  if (mobileMenuOpen.value) {
    updateMobileDropdownPosition();
  }
}

function toggleMenu() {
  menuOpen.value = !menuOpen.value;
  if (menuOpen.value) {
    mobileMenuOpen.value = false;
  }
}

function closeMenu() {
  menuOpen.value = false;
}

function toggleMobileMenu() {
  mobileMenuOpen.value = !mobileMenuOpen.value;
  if (mobileMenuOpen.value) {
    menuOpen.value = false;
  }
}

function closeMobileMenu() {
  mobileMenuOpen.value = false;
}

function onDocClick(e: MouseEvent) {
  if (!menuOpen.value && !mobileMenuOpen.value) {
    return;
  }
  const t = e.target;
  if (!(t instanceof Node)) {
    return;
  }
  if (menuOpen.value) {
    if (footerRoot.value?.contains(t) || panelRef.value?.contains(t)) {
      return;
    }
    menuOpen.value = false;
  }
  if (mobileMenuOpen.value) {
    if (mobileHeaderRoot.value?.contains(t) || mobilePanelRef.value?.contains(t)) {
      return;
    }
    mobileMenuOpen.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', onDocClick);
  window.addEventListener('resize', onViewportChange);
  window.addEventListener('scroll', onViewportChange, true);
});
onBeforeUnmount(() => {
  document.removeEventListener('click', onDocClick);
  window.removeEventListener('resize', onViewportChange);
  window.removeEventListener('scroll', onViewportChange, true);
});

watch(menuOpen, async (v) => {
  if (v) {
    await nextTick();
    updateDropdownPosition();
  }
});

watch(mobileMenuOpen, async (v) => {
  if (v) {
    await nextTick();
    updateMobileDropdownPosition();
  }
});

watch(
  () => props.footerMenuPlacement,
  async () => {
    if (!menuOpen.value) {
      return;
    }
    await nextTick();
    updateDropdownPosition();
  },
);

const resolvedPrimaryCtaIcon = computed((): SidebarCtaIcon => {
  if (props.ctaIcon) {
    return props.ctaIcon;
  }
  if (props.ctaAuthModal) {
    return 'login';
  }
  return 'bandRegister';
});

function resolvedActionIcon(action: SidebarCtaAction): SidebarCtaIcon {
  if (action.icon) {
    return action.icon;
  }
  if (action.useRegisterModal) {
    return 'signup';
  }
  return 'merchRegister';
}
</script>

<template>
  <header v-if="showMobile" class="app-mobile-header">
    <div class="mx-auto flex w-full max-w-[680px] items-center justify-between gap-3 px-4 py-3 sm:px-6">
      <Link :href="homeHref" class="app-mobile-header-logo shrink-0">
        <ApplicationLogo class="h-full w-full object-contain" />
      </Link>
      <div class="flex min-w-0 shrink items-center gap-2">
        <LocaleSwitcher justify="end" class="max-w-[min(100%,14rem)]" />
        <button
          v-if="mobileAuthModal"
          type="button"
          class="app-mobile-header-cta shrink-0"
          @click="openAuthLogin"
        >
          {{ mobileActionLabel }}
        </button>
        <button
          v-else-if="mobileHeaderMenuItems?.length"
          ref="mobileHeaderRoot"
          type="button"
          class="app-mobile-header-account shrink-0"
          :aria-expanded="mobileMenuOpen"
          :aria-label="t('layout.sidebar.footerMenuAria')"
          aria-haspopup="menu"
          @click.stop="toggleMobileMenu"
        >
          <div
            class="app-sidebar-avatar-ring flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-2xl text-xs font-semibold"
          >
            <img
              v-if="footerAvatarUrl"
              :src="footerAvatarUrl"
              alt=""
              class="h-full w-full object-cover"
              :style="footerAvatarStyle"
            />
            <span v-else>{{ footerTitle.slice(0, 1) }}</span>
          </div>
          <IconChevronDown
            :size="18"
            class="shrink-0 text-slate-400 transition-transform duration-200"
            :class="mobileMenuOpen ? 'rotate-180' : ''"
            aria-hidden="true"
          />
        </button>
        <Link
          v-else-if="mobileActionHref"
          :href="mobileActionHref"
          class="app-mobile-header-cta shrink-0"
        >
          {{ mobileActionLabel }}
        </Link>
      </div>
    </div>
  </header>

  <Teleport v-if="showMobile" to="body">
    <div
      v-if="mobileMenuOpen && mobileHeaderMenuItems?.length"
      ref="mobilePanelRef"
      class="app-sidebar-dropdown"
      :style="mobileDropdownStyle"
      role="menu"
    >
      <Link
        v-for="item in mobileHeaderMenuItems"
        :key="item.label"
        :href="item.href"
        :method="item.method"
        :as="item.as"
        role="menuitem"
        class="flex min-h-[2.75rem] w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-left text-sm font-medium leading-snug transition"
        :class="
          item.danger
            ? 'text-rose-700 hover:bg-rose-50 focus-visible:bg-rose-50 focus-visible:outline-none dark:text-rose-300 dark:hover:bg-rose-950/60 dark:focus-visible:bg-rose-950/60'
            : 'app-sidebar-dropdown-link'
        "
        @click="closeMobileMenu"
      >
        <component
          v-if="item.icon"
          :is="footerMenuIcons[item.icon]"
          class="h-5 w-5 shrink-0"
          :class="item.danger ? 'text-rose-600' : 'text-slate-500'"
          aria-hidden="true"
        />
        <span class="min-w-0 flex-1">{{ item.label }}</span>
      </Link>
    </div>
  </Teleport>

  <aside v-if="showDesktop" class="sticky top-0 hidden h-screen w-[290px] shrink-0 px-4 py-5 lg:block">
    <div class="flex h-full flex-col justify-between">
      <div>
        <Link
          :href="homeHref"
          class="flex h-14 w-14 items-center justify-center rounded-full p-2 transition hover:bg-white/35 theme-light:hover:bg-slate-200/90 theme-primary:hover:bg-white/35 dark:hover:bg-slate-800/50"
        >
          <ApplicationLogo class="h-full w-full object-contain" />
        </Link>

        <div class="app-sidebar-nav-card mt-4">
          <section
            v-for="section in primarySections"
            :key="section.title"
            :class="section === primarySections[0] ? '' : 'app-sidebar-inner-section'"
          >
            <p class="app-sidebar-section-title px-3 text-[11px] font-semibold uppercase tracking-[0.28em]">{{ section.title }}</p>
            <nav class="mt-3 space-y-1.5" :class="section.scrollable ? 'max-h-56 overflow-y-auto pr-1' : ''">
              <Link
                v-for="item in section.items"
                :key="item.label"
                :href="item.href"
                :method="item.method"
                :as="item.as"
                class="flex items-center gap-3 rounded-2xl px-4 py-3 font-medium transition"
                :class="[
                  section.compact ? 'py-2.5 text-base' : 'py-2.5 text-base',
                  item.active ? 'app-sidebar-nav-link-active' : 'app-sidebar-nav-link-idle',
                ]"
              >
                <component
                  v-if="item.icon"
                  :is="sidebarNavIcons[item.icon]"
                  class="h-5 w-5 shrink-0 text-current"
                  aria-hidden="true"
                />
                <span class="min-w-0 flex-1 leading-snug">{{ item.label }}</span>
              </Link>
            </nav>
          </section>

          <div class="mt-5 space-y-3">
            <AppButton
              v-if="ctaAuthModal"
              variant="white"
              size="lg"
              radius="md"
              extra-class="w-full gap-2"
              native-type="button"
              @click="openAuthLogin"
            >
              <component :is="sidebarCtaIcons[resolvedPrimaryCtaIcon]" class="h-5 w-5 shrink-0" aria-hidden="true" />
              {{ ctaLabel }}
            </AppButton>
            <AppButton
              v-else-if="ctaHref"
              :href="ctaHref"
              variant="white"
              size="lg"
              radius="md"
              extra-class="w-full gap-2"
            >
              <component :is="sidebarCtaIcons[resolvedPrimaryCtaIcon]" class="h-5 w-5 shrink-0" aria-hidden="true" />
              {{ ctaLabel }}
            </AppButton>
            <template v-for="action in ctaActions ?? []" :key="action.label">
              <AppButton
                v-if="action.useRegisterModal"
                variant="signup"
                size="lg"
                radius="md"
                extra-class="w-full gap-2"
                native-type="button"
                @click="openAuthRegister"
              >
                <component :is="sidebarCtaIcons[resolvedActionIcon(action)]" class="h-5 w-5 shrink-0" aria-hidden="true" />
                {{ action.label }}
              </AppButton>
              <AppButton
                v-else
                :href="action.href"
                variant="signup"
                size="lg"
                radius="md"
                extra-class="w-full gap-2"
              >
                <component :is="sidebarCtaIcons[resolvedActionIcon(action)]" class="h-5 w-5 shrink-0" aria-hidden="true" />
                {{ action.label }}
              </AppButton>
            </template>
          </div>
        </div>
      </div>

      <button
        v-if="showFooter && footerMenuItems?.length"
        ref="footerRoot"
        type="button"
        class="app-sidebar-footer-btn"
        :aria-expanded="menuOpen"
        :aria-label="t('layout.sidebar.footerMenuAria')"
        aria-haspopup="menu"
        @click.stop="toggleMenu"
      >
        <div class="flex items-center gap-3">
          <div class="app-sidebar-avatar-ring flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl text-sm font-semibold">
            <img
              v-if="footerAvatarUrl"
              :src="footerAvatarUrl"
              alt=""
              class="h-full w-full object-cover"
              :style="footerAvatarStyle"
            />
            <span v-else>{{ footerTitle.slice(0, 1) }}</span>
          </div>
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-semibold text-slate-800">{{ footerTitle }}</p>
            <p v-if="footerSubtitle" class="truncate text-sm text-slate-500">{{ footerSubtitle }}</p>
          </div>
          <IconChevronDown
            :size="20"
            class="shrink-0 text-slate-400 transition-transform duration-200"
            :class="menuOpen ? 'rotate-180' : ''"
            aria-hidden="true"
          />
        </div>
        <p v-if="footerMeta" class="mt-3 text-sm text-slate-500">{{ footerMeta }}</p>
      </button>

      <div v-else-if="showFooter" class="app-sidebar-footer-static">
        <div class="flex items-center gap-3">
          <div class="app-sidebar-avatar-ring flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl text-sm font-semibold">
            <img
              v-if="footerAvatarUrl"
              :src="footerAvatarUrl"
              alt=""
              class="h-full w-full object-cover"
              :style="footerAvatarStyle"
            />
            <span v-else>{{ footerTitle.slice(0, 1) }}</span>
          </div>
          <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-slate-800">{{ footerTitle }}</p>
            <p v-if="footerSubtitle" class="truncate text-sm text-slate-500">{{ footerSubtitle }}</p>
          </div>
        </div>
        <p v-if="footerMeta" class="mt-3 text-sm text-slate-500">{{ footerMeta }}</p>
      </div>
    </div>

    <Teleport to="body">
      <div
        v-if="menuOpen && footerMenuItems?.length"
        ref="panelRef"
        class="app-sidebar-dropdown"
        :style="dropdownStyle"
        role="menu"
      >
        <Link
          v-for="item in footerMenuItems"
          :key="item.label"
          :href="item.href"
          :method="item.method"
          :as="item.as"
          role="menuitem"
          class="flex min-h-[2.75rem] w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-left text-sm font-medium leading-snug transition"
          :class="
            item.danger
              ? 'text-rose-700 hover:bg-rose-50 focus-visible:bg-rose-50 focus-visible:outline-none dark:text-rose-300 dark:hover:bg-rose-950/60 dark:focus-visible:bg-rose-950/60'
              : 'app-sidebar-dropdown-link'
          "
          @click="closeMenu"
        >
          <component
            v-if="item.icon"
            :is="footerMenuIcons[item.icon]"
            class="h-5 w-5 shrink-0"
            :class="item.danger ? 'text-rose-600' : 'text-slate-500'"
            aria-hidden="true"
          />
          <span class="min-w-0 flex-1">{{ item.label }}</span>
        </Link>
      </div>
    </Teleport>
  </aside>
</template>
