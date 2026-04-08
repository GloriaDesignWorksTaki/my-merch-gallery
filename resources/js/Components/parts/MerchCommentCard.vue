<script setup lang="ts">
import MerchCommentReply from '@/Components/parts/MerchCommentReply.vue';
import LikeToggleInline from '@/Components/parts/LikeToggleInline.vue';
import type { MerchCommentNode } from '@/types/merchComment';
import type { AuthUser } from '@/types';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
  comment: MerchCommentNode;
  merchSlug: string;
  authUser: AuthUser | null;
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

const replying = ref(false);
const replyForm = useForm({
  body: '',
  parent_id: null as number | null,
});

function toggleReply() {
  replying.value = !replying.value;
  if (replying.value) {
    replyForm.clearErrors();
    replyForm.body = '';
    replyForm.parent_id = props.comment.id;
  }
}

function submitReply() {
  replyForm.parent_id = props.comment.id;
  replyForm.post(route('merch-items.comments.store', props.merchSlug), {
    preserveScroll: true,
    onSuccess: () => {
      replyForm.reset('body');
      replyForm.parent_id = null;
      replying.value = false;
    },
  });
}

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
  <div :id="`comment-${comment.id}`" class="rounded-2xl border border-white/25 bg-white/20 p-4">
    <div class="flex gap-3">
      <Link
        :href="route('users.show', comment.user.id)"
        class="relative flex h-11 w-11 shrink-0 overflow-hidden rounded-2xl border border-white/40 bg-white/55 text-center text-sm font-semibold text-slate-500"
        :title="comment.user.name"
      >
        <img
          v-if="comment.user.avatar_url"
          :src="comment.user.avatar_url"
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
            <p class="font-medium text-slate-800">
              <Link :href="route('users.show', comment.user.id)" class="text-sky-700 hover:underline">{{ comment.user.name }}</Link>
            </p>
            <p class="mt-0.5 text-xs text-slate-500">
              <span>@{{ comment.user.username }}</span>
              <span class="mx-1.5 text-slate-400">·</span>
              <span>{{ formatCommentAt(comment.created_at) }}</span>
            </p>
          </div>
          <div class="flex shrink-0 items-center gap-1">
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
                class="rounded-full p-1.5 text-slate-500 hover:bg-white/40 hover:text-slate-800"
                :aria-expanded="menuOpen"
                aria-haspopup="true"
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
                role="menu"
              >
                <button
                  type="button"
                  role="menuitem"
                  class="block w-full px-4 py-2.5 text-left text-rose-600 hover:bg-rose-50/80"
                  @click="confirmDelete"
                >
                  {{ t('pages.merch.commentDelete') }}
                </button>
              </div>
            </div>
          </div>
        </div>
        <p class="mt-2 whitespace-pre-wrap text-sm text-slate-700">{{ comment.body }}</p>

        <div v-if="authUser" class="mt-3">
          <button type="button" class="text-xs font-medium text-sky-700 hover:underline" @click="toggleReply">
            {{ replying ? t('pages.merch.replyCancel') : t('pages.merch.replyButton') }}
          </button>
        </div>

        <form v-if="replying && authUser" class="mt-3 space-y-2" @submit.prevent="submitReply">
          <textarea
            v-model="replyForm.body"
            rows="3"
            required
            maxlength="5000"
            class="glass-panel block w-full rounded-2xl border-white/50 bg-white/45 px-3 py-2 text-sm text-slate-800 placeholder:text-slate-400"
            :placeholder="t('pages.merch.replyPlaceholder')"
          />
          <p v-if="replyForm.errors.body" class="text-xs text-rose-600">{{ replyForm.errors.body }}</p>
          <div class="flex justify-end gap-2">
            <button type="button" class="text-xs text-slate-600 hover:underline" @click="replying = false">
              {{ t('common.cancel') }}
            </button>
            <button
              type="submit"
              class="glass-panel rounded-full px-4 py-1.5 text-xs font-semibold text-sky-700 hover:bg-white/55 disabled:opacity-50"
              :disabled="replyForm.processing"
            >
              {{ t('pages.merch.replySubmit') }}
            </button>
          </div>
        </form>

        <ul v-if="(comment.replies?.length ?? 0) > 0" class="mt-4 space-y-3 border-l-2 border-sky-200/50 pl-4">
          <li v-for="r in comment.replies ?? []" :key="r.id">
            <MerchCommentReply
              :comment="r"
              :merch-slug="merchSlug"
              :show-owner-menu="Boolean(authUser && authUser.id === r.user.id)"
              @destroy="emit('destroy', $event)"
            />
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
