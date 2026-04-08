<script setup lang="ts">
import SeoHead from '@/Components/seo/SeoHead.vue';
import LikeToggleInline from '@/Components/parts/LikeToggleInline.vue';
import CompactPagination from '@/Components/parts/CompactPagination.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import type { CoverImageJson } from '@/types/uploadAssets';
import type { PaginatedList } from '@/types/inertia';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

type PublicUser = {
  id: number;
  name: string;
  username: string;
  bio?: string | null;
  avatar_path?: string | null;
  avatar_url?: string | null;
  avatar_focus_x?: number;
  avatar_focus_y?: number;
  avatar_zoom?: number;
  created_merch_items_count?: number;
  liked_bands_count?: number;
  liked_merch_count?: number;
};

type MerchRow = {
  id: number;
  name: string;
  slug: string;
  release_year?: number | null;
  is_official: boolean;
  band: { name: string; slug: string };
  category?: { name: string } | null;
  cover_image?: CoverImageJson | null;
  likes_count?: number;
  liked?: boolean;
};

type BandRow = {
  id: number;
  name: string;
  slug: string;
  image_path?: string | null;
  image_url?: string | null;
  country?: { name: string } | null;
  merch_items_count?: number;
  likes_count?: number;
  liked?: boolean;
};

const props = defineProps<{
  profileUser: PublicUser;
  tab: 'posts' | 'likedBands' | 'likedMerch';
  postedMerchItems: (PaginatedList<MerchRow> & { current_page: number }) | null;
  likedBands: (PaginatedList<BandRow> & { current_page: number }) | null;
  likedMerchItems: (PaginatedList<MerchRow> & { current_page: number }) | null;
}>();

const tabItems = computed(() => [
  {
    id: 'posts' as const,
    label: t('pages.users.tabs.posts'),
    count: props.profileUser.created_merch_items_count ?? 0,
  },
  {
    id: 'likedBands' as const,
    label: t('pages.users.tabs.likedBands'),
    count: props.profileUser.liked_bands_count ?? 0,
  },
  {
    id: 'likedMerch' as const,
    label: t('pages.users.tabs.likedMerch'),
    count: props.profileUser.liked_merch_count ?? 0,
  },
]);
</script>

<template>
  <PublicLayout>
    <SeoHead page="usersShow" :params="{ username: profileUser.username }" />

    <section class="glass-surface p-6">
      <div class="flex items-start gap-4">
        <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-[1.75rem] border border-white/40 bg-white/55 text-2xl font-semibold text-slate-500">
          <img
            v-if="profileUser.avatar_url"
            :src="profileUser.avatar_url"
            alt=""
            class="h-full w-full object-cover"
            :style="{
              objectPosition: `${profileUser.avatar_focus_x ?? 50}% ${profileUser.avatar_focus_y ?? 50}%`,
              transform: `scale(${profileUser.avatar_zoom ?? 1})`,
            }"
          />
          <span v-else>{{ profileUser.name.slice(0, 1) }}</span>
        </div>
        <div class="min-w-0">
          <h1 class="text-2xl font-semibold">{{ profileUser.name }}</h1>
          <p class="text-sm text-gray-500">@{{ profileUser.username }}</p>
        </div>
      </div>
      <p v-if="profileUser.bio" class="mt-4 text-gray-700">{{ profileUser.bio }}</p>

      <nav class="mt-6 flex flex-wrap gap-2 border-b border-white/30 pb-1" :aria-label="t('pages.users.activityNav')">
        <Link
          v-for="item in tabItems"
          :key="item.id"
          :href="route('users.show', { user: profileUser.id, tab: item.id })"
          class="relative rounded-t-2xl px-4 py-3 text-sm font-semibold transition"
          :class="tab === item.id ? 'bg-white/70 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/40'"
          preserve-scroll
        >
          {{ item.label }}
          <span
            class="ml-1.5 rounded-full px-2 py-0.5 text-xs font-medium"
            :class="tab === item.id ? 'bg-sky-100 text-sky-800' : 'bg-white/50 text-slate-500'"
          >
            {{ item.count }}
          </span>
        </Link>
      </nav>

      <div class="mt-5">
        <template v-if="tab === 'posts'">
          <ul v-if="postedMerchItems?.data?.length" class="space-y-4">
            <li v-for="item in postedMerchItems.data" :key="item.id" class="glass-panel flex items-center gap-4 rounded-2xl p-4 hover:bg-white/55">
              <Link :href="route('merch-items.show', item.slug)" class="flex min-w-0 flex-1 items-center gap-4">
                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl border border-white/40 bg-white/45">
                  <img
                    v-if="item.cover_image"
                    :src="item.cover_image.image_url"
                    :alt="item.cover_image.alt_text || item.name"
                    class="h-full w-full object-cover"
                  />
                </div>
                <div class="min-w-0">
                  <p class="truncate font-medium text-slate-800">{{ item.name }}</p>
                  <p class="mt-1 truncate text-sm text-slate-500">
                    {{ item.band.name }}
                    <span v-if="item.category"> · {{ item.category.name }}</span>
                    <span v-if="item.release_year"> · {{ item.release_year }}</span>
                    <span v-if="item.is_official"> · {{ t('search.official') }}</span>
                  </p>
                </div>
              </Link>
              <LikeToggleInline
                :likes-count="item.likes_count ?? 0"
                :liked="Boolean(item.liked)"
                :feature-label="t('merch.featureLike')"
                :toggle-href="route('merch-items.like.toggle', item.slug)"
                variant="compact"
              />
            </li>
          </ul>
          <p v-else class="text-sm text-slate-500">{{ t('pages.users.empty.posts') }}</p>
          <CompactPagination v-if="postedMerchItems?.data?.length" class="mt-6" :links="postedMerchItems.links" />
        </template>

        <template v-else-if="tab === 'likedBands'">
          <ul v-if="likedBands?.data?.length" class="divide-y divide-white/30 overflow-hidden rounded-[1.25rem] border border-white/35 bg-white/25">
            <li v-for="b in likedBands.data" :key="b.id">
              <div class="flex items-center gap-2 px-4 py-5 sm:gap-4">
                <Link :href="route('bands.show', b.slug)" class="flex min-w-0 flex-1 items-center gap-3 sm:gap-4 hover:opacity-90">
                  <span v-if="b.image_url" class="relative shrink-0 overflow-hidden rounded-2xl border border-white/40 bg-white/40">
                    <img :src="b.image_url" alt="" class="h-12 w-12 object-cover sm:h-14 sm:w-14" />
                  </span>
                  <p class="min-w-0 flex-1 truncate text-sm font-medium text-slate-800 sm:text-base">
                    {{ b.name }}<template v-if="b.country"><span class="font-normal text-slate-500"> · {{ b.country.name }}</span></template>
                  </p>
                  <span
                    v-if="b.merch_items_count !== undefined"
                    class="shrink-0 whitespace-nowrap text-xs text-slate-500 tabular-nums sm:text-sm"
                  >{{ t('bands.merchCount', { count: b.merch_items_count }) }}</span>
                </Link>
                <LikeToggleInline
                  :likes-count="b.likes_count ?? 0"
                  :liked="Boolean(b.liked)"
                  :feature-label="t('bands.featureLike')"
                  :toggle-href="route('bands.like.toggle', b.slug)"
                  variant="compact"
                />
              </div>
            </li>
          </ul>
          <p v-else class="text-sm text-slate-500">{{ t('pages.users.empty.likedBands') }}</p>
          <CompactPagination v-if="likedBands?.data?.length" class="mt-6" :links="likedBands.links" />
        </template>

        <template v-else>
          <ul v-if="likedMerchItems?.data?.length" class="space-y-4">
            <li v-for="item in likedMerchItems.data" :key="item.id" class="glass-panel flex items-center gap-4 rounded-2xl p-4 hover:bg-white/55">
              <Link :href="route('merch-items.show', item.slug)" class="flex min-w-0 flex-1 items-center gap-4">
                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl border border-white/40 bg-white/45">
                  <img
                    v-if="item.cover_image"
                    :src="item.cover_image.image_url"
                    :alt="item.cover_image.alt_text || item.name"
                    class="h-full w-full object-cover"
                  />
                </div>
                <div class="min-w-0">
                  <p class="truncate font-medium text-slate-800">{{ item.name }}</p>
                  <p class="mt-1 truncate text-sm text-slate-500">
                    {{ item.band.name }}
                    <span v-if="item.category"> · {{ item.category.name }}</span>
                    <span v-if="item.release_year"> · {{ item.release_year }}</span>
                    <span v-if="item.is_official"> · {{ t('search.official') }}</span>
                  </p>
                </div>
              </Link>
              <LikeToggleInline
                :likes-count="item.likes_count ?? 0"
                :liked="Boolean(item.liked)"
                :feature-label="t('merch.featureLike')"
                :toggle-href="route('merch-items.like.toggle', item.slug)"
                variant="compact"
              />
            </li>
          </ul>
          <p v-else class="text-sm text-slate-500">{{ t('pages.users.empty.likedMerch') }}</p>
          <CompactPagination v-if="likedMerchItems?.data?.length" class="mt-6" :links="likedMerchItems.links" />
        </template>
      </div>

      <div class="mt-6 flex flex-wrap gap-3">
        <Link :href="route('merch-items.index')" class="glass-link text-sm font-medium">{{ t('pages.users.toMerchIndex') }}</Link>
        <Link :href="route('home')" class="glass-link text-sm font-medium">{{ t('pages.users.toHome') }}</Link>
      </div>
    </section>
  </PublicLayout>
</template>
