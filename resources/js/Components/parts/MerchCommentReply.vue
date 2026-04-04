<script setup lang="ts">
import LikeToggleInline from '@/Components/parts/LikeToggleInline.vue';
import type { MerchCommentNode } from '@/types/merchComment';
import { Link } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
  comment: MerchCommentNode;
  merchSlug: string;
  showOwnerMenu: boolean;
}>();

const emit = defineEmits<{
  destroy: [id: number];
}>();

const { t, locale } = useI18n();

const menuOpen = ref(false);
const menuRoot = ref<HTMLElement | null>(null);

function closeMenu() {
  menuOpen.value = false;
}

function onDocClick(e: MouseEvent) {
  if (!menuOpen.value || !menuRoot.value) {
    return;
  }
  if (e.target instanceof Node && !menuRoot.value.contains(e.target)) {
    closeMenu();
  }
}

onMounted(() => document.addEventListener('click', onDocClick));
onBeforeUnmount(() => document.removeEventListener('click', onDocClick));

function formatCommentAt(iso: string) {
  try {
    return new Date(iso).toLocaleString(locale.value === 'ja' ? 'ja-JP' : 'en-US', {
      dateStyle: 'medium',
      timeStyle: 'short',
    });
  } catch {
    return iso;
  }
}

const likeToggleHref = computed(() =>
  route('merch-items.comments.like.toggle', {
    merchItem: props.merchSlug,
    merchItemComment: props.comment.id,
  }),
);

function confirmDelete() {
  closeMenu();
  emit('destroy', props.comment.id);
}
</script>

<template>
  <div :id="`comment-${comment.id}`" class="rounded-xl border border-white/20 bg-white/15 p-3">
    <div class="flex gap-2.5">
      <Link
        :href="route('users.show', comment.user.id)"
        class="relative flex h-9 w-9 shrink-0 overflow-hidden rounded-xl border border-white/40 bg-white/55 text-center text-xs font-semibold text-slate-500"
        :title="comment.user.name"
      >
        <img
          v-if="comment.user.avatar_path"
          :src="`/storage/${comment.user.avatar_path}`"
          :alt="comment.user.name"
          class="h-full w-full object-cover"
          :style="{
            objectPosition: `${comment.user.avatar_focus_x ?? 50}% ${comment.user.avatar_focus_y ?? 50}%`,
            transform: `scale(${comment.user.avatar_zoom ?? 1})`,
          }"
        />
        <span v-else class="flex h-full w-full items-center justify-center">{{ comment.user.name.slice(0, 1) }}</span>
      </Link>
      <div class="min-w-0 flex-1">
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0">
            <p class="text-sm font-medium text-slate-800">
              <Link :href="route('users.show', comment.user.id)" class="text-sky-700 hover:underline">{{ comment.user.name }}</Link>
            </p>
            <p class="mt-0.5 text-[11px] text-slate-500">
              <span>@{{ comment.user.username }}</span>
              <span class="mx-1 text-slate-400">·</span>
              <span>{{ formatCommentAt(comment.created_at) }}</span>
            </p>
          </div>
          <div class="flex shrink-0 items-center gap-0.5">
            <LikeToggleInline
              :likes-count="comment.likes_count ?? 0"
              :liked="Boolean(comment.liked)"
              :feature-label="t('pages.merch.commentLikeFeature')"
              :toggle-href="likeToggleHref"
              variant="compact"
            />
            <div v-if="showOwnerMenu" ref="menuRoot" class="relative">
              <button
                type="button"
                class="rounded-full p-1 text-slate-500 hover:bg-white/40 hover:text-slate-800"
                :aria-expanded="menuOpen"
                @click.stop="menuOpen = !menuOpen"
              >
                <span class="sr-only">{{ t('pages.merch.commentMenuOpen') }}</span>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <circle cx="12" cy="6" r="1.75" />
                  <circle cx="12" cy="12" r="1.75" />
                  <circle cx="12" cy="18" r="1.75" />
                </svg>
              </button>
              <div
                v-if="menuOpen"
                class="absolute right-0 z-20 mt-1 min-w-[9rem] overflow-hidden rounded-2xl border border-white/40 bg-white/90 py-1 text-sm shadow-lg backdrop-blur-md"
              >
                <button
                  type="button"
                  class="block w-full px-4 py-2.5 text-left text-rose-600 hover:bg-rose-50/80"
                  @click="confirmDelete"
                >
                  {{ t('pages.merch.commentDelete') }}
                </button>
              </div>
            </div>
          </div>
        </div>
        <p class="mt-1.5 whitespace-pre-wrap text-sm text-slate-700">{{ comment.body }}</p>
      </div>
    </div>
  </div>
</template>
