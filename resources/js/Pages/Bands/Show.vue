<script setup lang="ts">
import CompactPagination from '@/Components/parts/CompactPagination.vue';
import LikeToggleInline from '@/Components/parts/LikeToggleInline.vue';
import ExternalLinkConfirm from '@/Components/modules/ExternalLinkConfirm.vue';
import GuestGateLink from '@/Components/parts/GuestGateLink.vue';
import PageContextBar from '@/Components/container/PageContextBar.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import type { PaginatedList } from '@/types/inertia';
import SeoHead from '@/Components/seo/SeoHead.vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();

type MerchListItem = {
  id: number;
  name: string;
  slug: string;
  release_year?: number | null;
  is_official?: boolean;
  category?: { name: string };
  likes_count?: number;
  liked?: boolean;
};

type BandShow = {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  image_path?: string | null;
  country?: { name: string } | null;
  genres: { id: number; name: string }[];
  links: { id: number; url: string; sort_order: number }[];
  likes_count?: number;
  liked?: boolean;
};

defineProps<{
  band: BandShow;
  merchItems: PaginatedList<MerchListItem>;
  canEdit: boolean;
  canRequestEdit: boolean;
  hasPendingEditRequest: boolean;
  returnTo: string;
  editHistories: {
    id: number;
    created_at: string;
    user: { id: number; name: string; username: string };
    changes: Record<string, unknown>;
  }[];
}>();

function historyFieldLabels(changes: Record<string, unknown>): string {
  return Object.keys(changes)
    .map((k) => t(`pages.bands.historyFields.${k}`))
    .join(' · ');
}

function formatHistoryAt(iso: string) {
  try {
    return new Date(iso).toLocaleString(locale.value === 'ja' ? 'ja-JP' : 'en-US', {
      dateStyle: 'medium',
      timeStyle: 'short',
    });
  } catch {
    return iso;
  }
}
</script>

<template>
  <PublicLayout>
    <SeoHead page="bandsShow" :params="{ name: band.name }" />

    <PageContextBar
      :crumbs="[
        { label: t('pages.bands.listCrumb'), href: returnTo },
        { label: band.name },
      ]"
      :actions="[
        {
          label: t('pages.bands.registerMerchLong'),
          href: route('merch-items.create', { band: band.id }),
          loginRequired: true,
          featureLabel: t('pages.bands.featureRegisterMerch'),
        },
        ...(canEdit ? [{ label: t('pages.merch.editAction'), href: route('bands.edit', band.slug) }] : []),
        ...(canRequestEdit && !hasPendingEditRequest
          ? [{ label: t('pages.bands.requestEditAction'), href: route('bands.edit-request.create', band.slug) }]
          : []),
      ]"
    />

    <p
      v-if="hasPendingEditRequest"
      class="glass-panel mt-4 border border-amber-200/70 bg-amber-50/50 px-4 py-3 text-sm text-amber-900"
    >
      {{ t('pages.bands.pendingEditNotice') }}
    </p>

    <section class="glass-surface mt-4 overflow-hidden p-6">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex min-w-0 flex-1 flex-wrap items-start gap-4 sm:gap-5">
          <div
            v-if="band.image_path"
            class="shrink-0 overflow-hidden rounded-3xl border border-white/45 bg-white/30 shadow-sm"
          >
            <img
              :src="`/storage/${band.image_path}`"
              :alt="band.name"
              class="h-28 w-28 object-cover sm:h-32 sm:w-32"
            />
          </div>
          <div class="min-w-0">
            <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('pages.bands.detailEyebrow') }}</p>
            <h1 class="mt-2 min-w-0 break-words text-3xl font-semibold text-slate-800">{{ band.name }}</h1>
          </div>
        </div>
        <LikeToggleInline
          :likes-count="band.likes_count ?? 0"
          :liked="Boolean(band.liked)"
          :feature-label="t('bands.featureLike')"
          :toggle-href="route('bands.like.toggle', band.slug)"
        />
      </div>
      <div class="mt-3 flex min-w-0 flex-wrap gap-2 text-sm">
        <span v-if="band.country" class="glass-panel max-w-full rounded-full px-3 py-1 text-slate-600">{{ band.country.name }}</span>
        <span v-for="genre in band.genres" :key="genre.id" class="glass-panel max-w-full rounded-full px-3 py-1 text-slate-600">{{ genre.name }}</span>
      </div>
      <p v-if="band.description" class="mt-4 break-words text-slate-600">{{ band.description }}</p>

      <div v-if="band.links.length" class="mt-6 flex flex-col gap-3">
        <ExternalLinkConfirm v-for="link in band.links" :key="link.id" :href="link.url">
          {{ link.url }}
        </ExternalLinkConfirm>
      </div>
    </section>

    <div class="mt-8 flex min-w-0 flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
      <h2 class="min-w-0 text-lg font-medium text-slate-800">{{ t('pages.bands.merchSection') }}</h2>
      <GuestGateLink
        :href="route('merch-items.create', { band: band.id })"
        :feature="t('pages.bands.featureRegisterMerch')"
        content-class="glass-link shrink-0 text-sm font-medium"
      >
        {{ t('pages.bands.registerMerchInline') }}
      </GuestGateLink>
    </div>
    <ul class="glass-surface mt-3 divide-y divide-white/30 p-2">
      <li v-for="item in merchItems.data" :key="item.id" class="p-2">
        <div class="flex flex-col gap-3 rounded-2xl px-2 py-2">
          <Link :href="route('merch-items.show', item.slug)" class="flex items-center justify-between gap-4 px-2 hover:opacity-90">
            <div class="min-w-0">
              <span class="text-slate-800">{{ item.name }}</span>
              <p class="mt-1 text-sm text-slate-500">
                <span v-if="item.category">{{ item.category.name }}</span>
                <span v-if="item.release_year"> · {{ item.release_year }}</span>
                <span v-if="item.is_official"> · {{ t('search.official') }}</span>
              </p>
            </div>
            <span class="shrink-0 text-sm text-slate-500">{{ t('merch.detailLink') }}</span>
          </Link>
          <div class="flex items-center border-t border-white/35 pt-3">
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
    <CompactPagination :links="merchItems.links" />
    <div v-if="merchItems.data.length === 0" class="glass-surface mt-3 p-6">
      <p class="text-slate-500">{{ t('pages.bands.emptyMerchList') }}</p>
      <GuestGateLink
        :href="route('merch-items.create', { band: band.id })"
        :feature="t('pages.bands.featureRegisterMerch')"
        content-class="glass-link mt-3 inline-flex text-sm font-medium"
      >
        {{ t('pages.bands.registerFirstMerch') }}
      </GuestGateLink>
    </div>

    <section v-if="editHistories.length" class="mt-8">
      <h2 class="text-lg font-medium text-slate-800">{{ t('pages.bands.editHistoryHeading') }}</h2>
      <ul class="mt-3 space-y-3">
        <li v-for="h in editHistories" :key="h.id" class="glass-surface p-4 text-sm">
          <p class="text-xs text-slate-500">{{ formatHistoryAt(h.created_at) }}</p>
          <p class="mt-2 leading-relaxed text-slate-700">
            <Link :href="route('users.show', h.user.id)" class="font-medium text-sky-700 hover:underline">@{{ h.user.username }}</Link>
            <span class="text-slate-600"> · {{ historyFieldLabels(h.changes) }}</span>
          </p>
        </li>
      </ul>
    </section>
  </PublicLayout>
</template>
