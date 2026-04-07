<script setup lang="ts">
import AppButton from '@/Components/parts/AppButton.vue';
import ApplicationLogo from '@/Components/parts/ApplicationLogo.vue';
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
  mobileTitle: string;
  mobileActionLabel: string;
  mobileActionHref?: string | null;
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
const footerRoot = ref<HTMLButtonElement | null>(null);
const panelRef = ref<HTMLElement | null>(null);
const dropdownStyle = ref<Record<string, string>>({});

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

function updateDropdownPosition() {
  const el = footerRoot.value;
  if (!el) {
    return;
  }
  const r = el.getBoundingClientRect();
  const innerH = window.innerHeight;
  const maxW = Math.min(window.innerWidth - 2 * PANEL_MARGIN_PX, PANEL_MAX_WIDTH_PX);

  const vertical = props.footerMenuPlacement.startsWith('bottom') ? 'bottom' : 'top';
  const horizontal = props.footerMenuPlacement.endsWith('end') ? 'end' : 'start';

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
    dropdownStyle.value = {
      ...base,
      top: `${r.bottom + PANEL_MARGIN_PX}px`,
      bottom: 'auto',
      maxHeight: `${Math.max(1, maxH)}px`,
    };
  } else {
    const spaceAbove = Math.max(0, r.top - PANEL_MARGIN_PX);
    const maxH = Math.min(320, spaceAbove);
    dropdownStyle.value = {
      ...base,
      top: 'auto',
      bottom: `${innerH - r.top + PANEL_MARGIN_PX}px`,
      maxHeight: `${Math.max(1, maxH)}px`,
    };
  }
}

function onViewportChange() {
  if (menuOpen.value) {
    updateDropdownPosition();
  }
}

function toggleMenu() {
  menuOpen.value = !menuOpen.value;
}

function closeMenu() {
  menuOpen.value = false;
}

function onDocClick(e: MouseEvent) {
  if (!menuOpen.value) {
    return;
  }
  const t = e.target;
  if (!(t instanceof Node)) {
    return;
  }
  if (footerRoot.value?.contains(t) || panelRef.value?.contains(t)) {
    return;
  }
  menuOpen.value = false;
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
  <header v-if="showMobile" class="sticky top-0 z-30 border-b border-white/30 bg-white/25 backdrop-blur-2xl md:hidden">
    <div class="mx-auto flex max-w-md items-center justify-between px-4 py-3">
      <Link :href="homeHref" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/40 bg-white/40 p-1.5">
        <ApplicationLogo class="h-full w-full object-contain" />
      </Link>
      <div class="text-base font-semibold tracking-[0.18em] text-slate-800">{{ mobileTitle }}</div>
      <button
        v-if="mobileAuthModal"
        type="button"
        class="rounded-full border border-white/50 bg-white/40 px-4 py-1.5 text-sm font-semibold text-slate-700"
        @click="openAuthLogin"
      >
        {{ mobileActionLabel }}
      </button>
      <Link
        v-else-if="mobileActionHref"
        :href="mobileActionHref"
        class="rounded-full border border-white/50 bg-white/40 px-4 py-1.5 text-sm font-semibold text-slate-700"
      >
        {{ mobileActionLabel }}
      </Link>
      <div v-else class="h-10 min-w-[5.5rem] shrink-0" aria-hidden="true" />
    </div>
  </header>

  <aside v-if="showDesktop" class="sticky top-0 hidden h-screen w-[290px] shrink-0 px-4 py-5 lg:block">
    <div class="flex h-full flex-col justify-between">
      <div>
        <Link :href="homeHref" class="flex h-14 w-14 items-center justify-center rounded-full p-2 transition hover:bg-white/35">
          <ApplicationLogo class="h-full w-full object-contain" />
        </Link>

        <div class="mt-4 rounded-[2rem] border border-white/40 bg-white/32 p-4 shadow-[0_20px_60px_rgba(148,163,184,0.12)] backdrop-blur-xl">
          <section
            v-for="section in primarySections"
            :key="section.title"
            :class="section === primarySections[0] ? '' : 'mt-6 rounded-3xl border border-white/35 bg-white/35 p-3'"
          >
            <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.28em] text-sky-700/70">{{ section.title }}</p>
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
                  item.active ? 'bg-white/70 text-slate-900 shadow-sm' : 'text-slate-700 hover:bg-white/45',
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
        class="rounded-[2rem] border border-white/40 bg-white/35 p-5 text-left backdrop-blur-xl transition hover:bg-white/45 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/50"
        :aria-expanded="menuOpen"
        :aria-label="t('layout.sidebar.footerMenuAria')"
        aria-haspopup="menu"
        @click.stop="toggleMenu"
      >
        <div class="flex items-center gap-3">
          <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-white/50 bg-white/55 text-sm font-semibold text-slate-500">
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

      <div v-else-if="showFooter" class="rounded-[2rem] border border-white/40 bg-white/35 p-5 backdrop-blur-xl">
        <div class="flex items-center gap-3">
          <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-white/50 bg-white/55 text-sm font-semibold text-slate-500">
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
        class="z-[200] flex flex-col gap-0.5 rounded-2xl border border-white/40 bg-white/95 p-1.5 text-sm shadow-xl backdrop-blur-md"
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
              ? 'text-rose-700 hover:bg-rose-50 focus-visible:bg-rose-50 focus-visible:outline-none'
              : 'text-slate-700 hover:bg-slate-100/90 focus-visible:bg-slate-100/90 focus-visible:outline-none'
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
