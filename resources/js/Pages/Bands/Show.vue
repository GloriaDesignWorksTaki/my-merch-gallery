<script setup lang="ts">
import CompactPagination from '@/Components/parts/CompactPagination.vue';
import ExternalLinkConfirm from '@/Components/modules/ExternalLinkConfirm.vue';
import GuestGateLink from '@/Components/parts/GuestGateLink.vue';
import PageContextBar from '@/Components/container/PageContextBar.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import type { PaginatedList } from '@/types/inertia';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

type MerchListItem = {
  id: number;
  name: string;
  slug: string;
  release_year?: number | null;
  is_official?: boolean;
  category?: { name: string };
};

type BandShow = {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  country?: { name: string } | null;
  genres: { id: number; name: string }[];
  links: { id: number; url: string; sort_order: number }[];
};

defineProps<{
  band: BandShow;
  merchItems: PaginatedList<MerchListItem>;
  canEdit: boolean;
  returnTo: string;
  relatedPosts: {
    id: number;
    body: string;
    user: { username: string };
    cover_image?: { image_path: string } | null;
  }[];
}>();
</script>

<template>
  <PublicLayout>
    <Head :title="band.name" />

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
      ]"
    />

    <section class="glass-surface mt-4 overflow-hidden p-6">
      <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('pages.bands.detailEyebrow') }}</p>
      <h1 class="mt-2 min-w-0 break-words text-3xl font-semibold text-slate-800">{{ band.name }}</h1>
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
      <li v-for="item in merchItems.data" :key="item.id">
        <Link :href="route('merch-items.show', item.slug)" class="flex items-center justify-between rounded-2xl px-4 py-4 hover:bg-white/35">
          <div>
            <span class="text-slate-800">{{ item.name }}</span>
            <p class="mt-1 text-sm text-slate-500">
              <span v-if="item.category">{{ item.category.name }}</span>
              <span v-if="item.release_year"> · {{ item.release_year }}</span>
              <span v-if="item.is_official"> · {{ t('search.official') }}</span>
            </p>
          </div>
          <span class="text-sm text-slate-500">{{ t('merch.detailLink') }}</span>
        </Link>
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

    <section class="mt-8">
      <div class="flex min-w-0 flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <h2 class="min-w-0 text-lg font-medium text-slate-800">{{ t('pages.bands.relatedPostsHeading') }}</h2>
        <GuestGateLink
          :href="route('posts.create')"
          :feature="t('posts.featureCreate')"
          content-class="glass-link shrink-0 text-sm font-medium"
        >
          {{ t('pages.bands.postWithBand') }}
        </GuestGateLink>
      </div>
      <ul v-if="relatedPosts.length" class="mt-3 space-y-3">
        <li v-for="post in relatedPosts" :key="post.id" class="glass-surface p-4">
          <Link :href="route('posts.show', post.id)" class="flex items-start gap-4">
            <div class="min-w-0 flex-1">
              <p class="line-clamp-2 text-slate-800">{{ post.body }}</p>
              <p class="mt-2 text-sm text-slate-500">@{{ post.user.username }}</p>
            </div>
            <div v-if="post.cover_image" class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl border border-white/40 bg-white/45">
              <img :src="`/storage/${post.cover_image.image_path}`" alt="" class="h-full w-full object-cover" />
            </div>
          </Link>
        </li>
      </ul>
      <div v-else class="glass-surface mt-3 p-6">
        <p class="text-sm text-slate-500">{{ t('pages.bands.emptyBandPosts') }}</p>
      </div>
    </section>
  </PublicLayout>
</template>
