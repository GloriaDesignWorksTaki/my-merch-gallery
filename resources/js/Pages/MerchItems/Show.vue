<script setup lang="ts">
import MerchCommentCard from '@/Components/parts/MerchCommentCard.vue';
import CompactPagination from '@/Components/parts/CompactPagination.vue';
import LikeToggleInline from '@/Components/parts/LikeToggleInline.vue';
import GuestGateLink from '@/Components/parts/GuestGateLink.vue';
import PageContextBar from '@/Components/container/PageContextBar.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import type { CoverImageJson, MerchImageJson } from '@/types/uploadAssets';
import type { MerchCommentNode } from '@/types/merchComment';
import type { PaginatedList } from '@/types/inertia';
import SeoHead from '@/Components/seo/SeoHead.vue';
import type { AuthUser } from '@/types';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { IconUser } from '@tabler/icons-vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const page = usePage<{ auth: { user: AuthUser | null } }>();

type MerchShow = {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  release_year: number | null;
  size_note?: string | null;
  is_official: boolean;
  band: { id: number; name: string; slug: string };
  category: { name: string; slug: string };
  images: MerchImageJson[];
  creator?: {
    id: number;
    name: string;
    username: string;
    avatar_path?: string | null;
    avatar_url?: string | null;
    avatar_focus_x?: number | null;
    avatar_focus_y?: number | null;
    avatar_zoom?: number | null;
  } | null;
  likes_count?: number;
  liked?: boolean;
};

const props = defineProps<{
  merchItem: MerchShow;
  canEdit: boolean;
  returnTo: string;
  relatedMerchItems: {
    id: number;
    name: string;
    slug: string;
    release_year?: number | null;
    is_official: boolean;
    cover_image?: CoverImageJson | null;
    likes_count?: number;
    liked?: boolean;
  }[];
  comments: PaginatedList<MerchCommentNode> & { current_page: number };
}>();

const commentForm = useForm({
  body: '',
  parent_id: null as number | null,
});

function submitComment() {
  commentForm.parent_id = null;
  commentForm.post(route('merch-items.comments.store', props.merchItem.slug), {
    preserveScroll: true,
    onSuccess: () => commentForm.reset('body'),
  });
}

function destroyComment(commentId: number) {
  if (!window.confirm(t('pages.merch.confirmDeleteComment'))) {
    return;
  }
  router.delete(
    route('merch-items.comments.destroy', { merchItem: props.merchItem.slug, merchItemComment: commentId }),
    { preserveScroll: true },
  );
}

const authUser = computed(() => page.props.auth?.user ?? null);

function isOwnComment(userId: number): boolean {
  const u = authUser.value;
  return u !== null && u.id === userId;
}

function creatorAvatarStyle(c: NonNullable<MerchShow['creator']>) {
  return {
    objectPosition: `${c.avatar_focus_x ?? 50}% ${c.avatar_focus_y ?? 50}%`,
    transform: `scale(${c.avatar_zoom ?? 1})`,
  };
}
</script>

<template>
  <PublicLayout>
    <SeoHead page="merchShow" :params="{ name: merchItem.name, bandName: merchItem.band.name }" />

    <PageContextBar
      :crumbs="[
        { label: t('pages.merch.listCrumb'), href: returnTo },
        { label: merchItem.band.name, href: route('bands.show', merchItem.band.slug) },
        { label: merchItem.name },
      ]"
      :actions="canEdit ? [{ label: t('pages.merch.editAction'), href: route('merch-items.edit', { merchItem: merchItem.slug, return_to: returnTo }) }] : []"
    />

    <article class="glass-surface mt-4 p-6">
      <div class="flex flex-wrap items-start justify-between gap-6">
        <div>
          <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('pages.merch.detailEyebrow') }}</p>
          <h1 class="mt-2 text-3xl font-semibold text-slate-800">{{ merchItem.name }}</h1>
          <p class="mt-3 text-sm text-slate-500">
            {{ merchItem.band.name }} · {{ merchItem.category?.name ?? '' }}
            <span v-if="merchItem.release_year"> · {{ merchItem.release_year }}</span>
            <span v-if="merchItem.is_official"> · {{ t('search.official') }}</span>
            <span v-if="merchItem.size_note"> · {{ t('pages.merch.sizeNoteShort', { note: merchItem.size_note }) }}</span>
          </p>
          <div v-if="merchItem.creator" class="mt-4">
            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-sky-700/80">
              {{ t('pages.merch.registeredByTitle') }}
            </p>
            <Link
              :href="route('users.show', merchItem.creator.id)"
              class="mt-2 flex max-w-md items-center gap-3 rounded-2xl border border-white/35 bg-white/30 p-3 pr-4 transition hover:border-white/50 hover:bg-white/45"
            >
              <div
                class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-white/45 bg-white/55 text-slate-400"
              >
                <img
                  v-if="merchItem.creator.avatar_url"
                  :src="merchItem.creator.avatar_url"
                  :alt="merchItem.creator.name"
                  class="h-full w-full object-cover"
                  :style="creatorAvatarStyle(merchItem.creator)"
                />
                <IconUser v-else :size="26" stroke="1.75" aria-hidden="true" />
              </div>
              <div class="min-w-0 flex-1">
                <p class="truncate text-base font-semibold text-slate-800">{{ merchItem.creator.name }}</p>
                <p class="truncate text-sm text-slate-500">@{{ merchItem.creator.username }}</p>
              </div>
            </Link>
          </div>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-3">
          <LikeToggleInline
            :likes-count="merchItem.likes_count ?? 0"
            :liked="Boolean(merchItem.liked)"
            :feature-label="t('merch.featureLike')"
            :toggle-href="route('merch-items.like.toggle', merchItem.slug)"
          />
          <a
            href="#comments"
            class="glass-panel rounded-full px-4 py-2 text-sm font-medium text-sky-700 hover:bg-white/55"
          >
            {{ t('pages.merch.jumpToComments') }}
          </a>
        </div>
      </div>

      <div v-if="merchItem.images.length" class="mt-6 grid gap-4 sm:grid-cols-2">
        <div v-for="(img, i) in merchItem.images" :key="i" class="overflow-hidden rounded-2xl border border-white/40 bg-white/45">
          <img :src="img.image_url" :alt="img.alt_text || merchItem.name" class="w-full object-cover" />
        </div>
      </div>
      <p v-if="merchItem.description" class="mt-6 text-slate-600">{{ merchItem.description }}</p>
    </article>

    <section class="mt-8">
      <div class="flex items-center justify-between gap-4">
        <h2 class="text-lg font-medium text-slate-800">{{ t('pages.merch.sameBandMerch') }}</h2>
        <Link :href="route('bands.show', merchItem.band.slug)" class="glass-link text-sm font-medium">{{ t('pages.merch.toBandDetail') }}</Link>
      </div>
      <ul v-if="(relatedMerchItems ?? []).length" class="mt-3 space-y-3">
        <li v-for="item in (relatedMerchItems ?? [])" :key="item.id" class="glass-surface p-4">
          <div class="flex items-start justify-between gap-3">
            <Link :href="route('merch-items.show', item.slug)" class="flex min-w-0 flex-1 items-center gap-4 hover:opacity-90">
              <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl border border-white/40 bg-white/45">
                <img v-if="item.cover_image" :src="item.cover_image.image_url" :alt="item.cover_image.alt_text || item.name" class="h-full w-full object-cover" />
              </div>
              <div class="min-w-0">
                <p class="font-medium text-slate-800">{{ item.name }}</p>
                <p class="mt-1 text-sm text-slate-500">
                  <span v-if="item.release_year">{{ item.release_year }}</span>
                  <span v-if="item.is_official"> · {{ t('search.official') }}</span>
                </p>
              </div>
            </Link>
            <div class="shrink-0 pt-0.5">
              <LikeToggleInline
                :likes-count="item.likes_count ?? 0"
                :liked="Boolean(item.liked)"
                :feature-label="t('merch.featureLike')"
                :toggle-href="route('merch-items.like.toggle', item.slug)"
                variant="compact"
              />
            </div>
          </div>
        </li>
      </ul>
      <div v-else class="glass-surface mt-3 p-6">
        <p class="text-sm text-slate-500">{{ t('pages.merch.emptyRelatedMerch') }}</p>
      </div>
    </section>

    <section id="comments" class="mt-8 scroll-mt-24">
      <div class="flex items-center justify-between gap-4">
        <h2 class="text-lg font-medium text-slate-800">{{ t('pages.merch.commentsHeading') }}</h2>
      </div>

      <div v-if="authUser" class="mt-4 glass-surface p-4">
        <form class="space-y-3" @submit.prevent="submitComment">
          <label class="block">
            <span class="sr-only">{{ t('pages.merch.commentBodyLabel') }}</span>
            <textarea
              v-model="commentForm.body"
              rows="4"
              required
              maxlength="5000"
              class="glass-panel block w-full rounded-2xl border-white/50 bg-white/45 px-4 py-3 text-base text-slate-800 placeholder:text-slate-400 md:text-sm"
              :placeholder="t('pages.merch.commentPlaceholder')"
            />
          </label>
          <p v-if="commentForm.errors.body" class="text-sm text-rose-600">{{ commentForm.errors.body }}</p>
          <p v-if="commentForm.errors.parent_id" class="text-sm text-rose-600">{{ commentForm.errors.parent_id }}</p>
          <div class="flex justify-end">
            <button
              type="submit"
              class="glass-panel rounded-full px-5 py-2.5 text-sm font-semibold text-sky-700 hover:bg-white/55 disabled:opacity-50"
              :disabled="commentForm.processing"
            >
              {{ t('pages.merch.commentSubmit') }}
            </button>
          </div>
        </form>
      </div>
      <div v-else class="mt-4 glass-surface p-4">
        <p class="text-sm text-slate-600">{{ t('pages.merch.commentLoginHint') }}</p>
        <GuestGateLink
          :href="route('home', { auth: 'login' })"
          :feature="t('pages.merch.commentLoginFeature')"
          content-class="glass-link mt-2 inline-flex text-sm font-medium"
        >
          {{ t('pages.merch.commentLoginLink') }}
        </GuestGateLink>
      </div>

      <ul v-if="comments.data.length" class="mt-4 space-y-4">
        <li v-for="c in comments.data" :key="c.id">
          <MerchCommentCard
            :comment="c"
            :merch-slug="merchItem.slug"
            :auth-user="authUser"
            :show-owner-menu="isOwnComment(c.user.id)"
            @destroy="destroyComment"
          />
        </li>
      </ul>
      <p v-else class="mt-4 text-sm text-slate-500">{{ t('pages.merch.emptyComments') }}</p>
      <CompactPagination v-if="comments.data.length" class="mt-6" :links="comments.links" />
    </section>
  </PublicLayout>
</template>
