<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import type { AuthUser } from '@/types';
import { notificationTargetUrl } from '@/utils/notificationTarget';

const { t, locale } = useI18n();

const page = usePage<{
  auth: { user: AuthUser | null };
  inbox?: {
    unreadCount: number;
  };
}>();

const open = ref(false);
const root = ref<HTMLElement | null>(null);
const triggerBtn = ref<HTMLButtonElement | null>(null);
const panelRef = ref<HTMLElement | null>(null);

const dropdownStyle = ref<Record<string, string>>({});

const PANEL_MAX_WIDTH_PX = 22 * 16;
const PANEL_MARGIN_PX = 8;

function updateDropdownPosition() {
  const btn = triggerBtn.value;
  if (!btn) {
    return;
  }
  const r = btn.getBoundingClientRect();
  const maxW = Math.min(window.innerWidth - 2 * PANEL_MARGIN_PX, PANEL_MAX_WIDTH_PX);
  let left = r.right - maxW;
  if (left < PANEL_MARGIN_PX) {
    left = PANEL_MARGIN_PX;
  }
  if (left + maxW > window.innerWidth - PANEL_MARGIN_PX) {
    left = window.innerWidth - PANEL_MARGIN_PX - maxW;
  }
  dropdownStyle.value = {
    position: 'fixed',
    top: `${r.bottom + PANEL_MARGIN_PX}px`,
    left: `${left}px`,
    width: `${maxW}px`,
  };
}

function onViewportChange() {
  if (open.value) {
    updateDropdownPosition();
  }
}

const inbox = computed(() => page.props.inbox ?? { unreadCount: 0 });

const recent = ref<Array<{ id: string; read: boolean; created_at: string | null; data: Record<string, unknown> }>>([]);
const recentLoading = ref(false);

async function loadRecent() {
  if (!page.props.auth.user) {
    return;
  }
  recentLoading.value = true;
  try {
    const { data } = await axios.get<{ recent: typeof recent.value }>(route('notifications.dropdown'));
    recent.value = data.recent ?? [];
  } finally {
    recentLoading.value = false;
  }
}

function toggle() {
  open.value = !open.value;
}

function onDocClick(e: MouseEvent) {
  if (!open.value) {
    return;
  }
  const t = e.target;
  if (!(t instanceof Node)) {
    return;
  }
  if (root.value?.contains(t) || panelRef.value?.contains(t)) {
    return;
  }
  open.value = false;
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

watch(open, async (v) => {
  if (v) {
    await nextTick();
    updateDropdownPosition();
    await loadRecent();
  }
});

function goNotifications() {
  open.value = false;
  router.visit(route('notifications.index'));
}

function openNotification(row: { id: string; data: Record<string, unknown> }) {
  const url = notificationTargetUrl(row.data);
  router.post(
    route('notifications.read', row.id),
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        open.value = false;
        router.visit(url);
      },
    },
  );
}

function markAllRead() {
  router.post(route('notifications.read-all'), {}, { preserveScroll: true, onSuccess: () => { open.value = false; } });
}

function formatAt(iso: string | null | undefined): string {
  if (!iso) {
    return '';
  }
  try {
    return new Date(iso).toLocaleString(locale.value === 'ja' ? 'ja-JP' : 'en-US', {
      dateStyle: 'short',
      timeStyle: 'short',
    });
  } catch {
    return iso;
  }
}

function summaryLine(data: Record<string, unknown>): string {
  const type = data.type;
  const name = typeof data.actor_name === 'string' ? data.actor_name : '';
  const merch = typeof data.merch_item_name === 'string' ? data.merch_item_name : '';
  if (type === 'merch_item_liked') {
    return t('notifications.summary.merchItemLiked', { name, merch });
  }
  if (type === 'merch_item_commented') {
    return t('notifications.summary.merchCommented', { name, merch });
  }
  if (type === 'merch_comment_replied') {
    return t('notifications.summary.merchCommentReplied', { name, merch });
  }
  if (type === 'merch_comment_liked') {
    return t('notifications.summary.merchCommentLiked', { name, merch });
  }
  return t('notifications.summary.fallback');
}
</script>

<template>
  <div v-if="page.props.auth.user" ref="root" class="relative">
    <button
      ref="triggerBtn"
      type="button"
      class="relative flex h-10 w-10 items-center justify-center rounded-full border border-white/40 bg-white/35 text-slate-600 transition hover:bg-white/55"
      :aria-expanded="open"
      :aria-label="t('notifications.bellAria')"
      @click.stop="toggle"
    >
      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.102V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"
        />
      </svg>
      <span
        v-if="inbox.unreadCount > 0"
        class="absolute -right-0.5 -top-0.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white"
      >
        {{ inbox.unreadCount > 99 ? '99+' : inbox.unreadCount }}
      </span>
    </button>

    <Teleport to="body">
      <div
        v-if="open"
        ref="panelRef"
        class="z-[200] overflow-hidden rounded-2xl border border-white/40 bg-white/95 py-2 text-sm shadow-xl backdrop-blur-md"
        :style="dropdownStyle"
        role="dialog"
      >
        <div class="flex items-center justify-between border-b border-white/30 px-3 pb-2">
          <span class="font-semibold text-slate-800">{{ t('notifications.dropdownTitle') }}</span>
          <button
            v-if="inbox.unreadCount > 0"
            type="button"
            class="text-xs font-medium text-sky-700 hover:underline"
            @click="markAllRead"
          >
            {{ t('notifications.markAllRead') }}
          </button>
        </div>
        <ul v-if="recent.length" class="max-h-80 overflow-y-auto">
          <li v-for="n in recent" :key="n.id">
            <button
              type="button"
              class="flex w-full flex-col gap-0.5 px-3 py-2.5 text-left transition hover:bg-white/60"
              :class="n.read ? 'opacity-75' : 'bg-sky-50/50'"
              @click="openNotification(n)"
            >
              <span class="text-xs leading-snug text-slate-700">{{ summaryLine(n.data) }}</span>
              <span v-if="n.created_at" class="text-[11px] text-slate-400">{{ formatAt(n.created_at) }}</span>
            </button>
          </li>
        </ul>
        <p v-else-if="recentLoading" class="px-3 py-6 text-center text-xs text-slate-500">{{ t('common.loading') }}</p>
        <p v-else class="px-3 py-6 text-center text-xs text-slate-500">{{ t('notifications.emptyRecent') }}</p>
        <div class="border-t border-white/30 px-3 pt-2">
          <button
            type="button"
            class="w-full rounded-xl py-2 text-center text-xs font-semibold text-sky-700 hover:bg-white/50"
            @click="goNotifications"
          >
            {{ t('notifications.viewAll') }}
          </button>
        </div>
      </div>
    </Teleport>
  </div>
</template>
