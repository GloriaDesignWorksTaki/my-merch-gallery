<script setup lang="ts">
import GuestGateLink from '@/Components/parts/GuestGateLink.vue';
import PageContextBar from '@/Components/container/PageContextBar.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

type MerchShow = {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  release_year: number | null;
  is_official: boolean;
  era_label?: string | null;
  band: { id: number; name: string; slug: string };
  category: { name: string; slug: string };
  images: { image_path: string; alt_text: string | null }[];
  creator?: { name: string; username: string } | null;
};

defineProps<{
  merchItem: MerchShow;
  canEdit: boolean;
  returnTo: string;
  relatedMerchItems: {
    id: number;
    name: string;
    slug: string;
    release_year?: number | null;
    is_official: boolean;
    cover_image?: { image_path: string; alt_text?: string | null } | null;
  }[];
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
    <Head :title="merchItem.name" />

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
            {{ merchItem.band.name }} · {{ merchItem.category.name }}
            <span v-if="merchItem.release_year"> · {{ merchItem.release_year }}</span>
            <span v-if="merchItem.era_label"> · {{ merchItem.era_label }}</span>
            <span v-if="merchItem.is_official"> · {{ t('search.official') }}</span>
          </p>
          <p v-if="merchItem.creator" class="mt-2 text-sm text-slate-500">{{ t('pages.merch.registeredBy', { username: merchItem.creator.username }) }}</p>
        </div>
        <GuestGateLink
          :href="route('posts.create')"
          :feature="t('posts.featureCreate')"
          content-class="glass-panel rounded-full px-4 py-2 text-sm font-medium text-sky-700 hover:bg-white/55"
        >
          {{ t('pages.merch.postWithMerch') }}
        </GuestGateLink>
      </div>

      <div v-if="merchItem.images.length" class="mt-6 grid gap-4 sm:grid-cols-2">
        <div v-for="(img, i) in merchItem.images" :key="i" class="overflow-hidden rounded-2xl border border-white/40 bg-white/45">
          <img :src="`/storage/${img.image_path}`" :alt="img.alt_text || merchItem.name" class="w-full object-cover" />
        </div>
      </div>
      <p v-if="merchItem.description" class="mt-6 text-slate-600">{{ merchItem.description }}</p>
    </article>

    <section class="mt-8">
      <div class="flex items-center justify-between gap-4">
        <h2 class="text-lg font-medium text-slate-800">{{ t('pages.merch.sameBandMerch') }}</h2>
        <Link :href="route('bands.show', merchItem.band.slug)" class="glass-link text-sm font-medium">{{ t('pages.merch.toBandDetail') }}</Link>
      </div>
      <ul v-if="relatedMerchItems.length" class="mt-3 space-y-3">
        <li v-for="item in relatedMerchItems" :key="item.id" class="glass-surface p-4">
          <Link :href="route('merch-items.show', item.slug)" class="flex items-center gap-4">
            <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl border border-white/40 bg-white/45">
              <img v-if="item.cover_image" :src="`/storage/${item.cover_image.image_path}`" :alt="item.cover_image.alt_text || item.name" class="h-full w-full object-cover" />
            </div>
            <div>
              <p class="font-medium text-slate-800">{{ item.name }}</p>
              <p class="mt-1 text-sm text-slate-500">
                <span v-if="item.release_year">{{ item.release_year }}</span>
                <span v-if="item.is_official"> · {{ t('search.official') }}</span>
              </p>
            </div>
          </Link>
        </li>
      </ul>
      <div v-else class="glass-surface mt-3 p-6">
        <p class="text-sm text-slate-500">{{ t('pages.merch.emptyRelatedMerch') }}</p>
      </div>
    </section>

    <section class="mt-8">
      <div class="flex items-center justify-between gap-4">
        <h2 class="text-lg font-medium text-slate-800">{{ t('pages.merch.relatedPostsHeading') }}</h2>
        <Link :href="route('posts.index')" class="glass-link text-sm font-medium">{{ t('layout.nav.postsIndex') }}</Link>
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
        <p class="text-sm text-slate-500">{{ t('pages.merch.emptyMerchPosts') }}</p>
      </div>
    </section>
  </PublicLayout>
</template>
