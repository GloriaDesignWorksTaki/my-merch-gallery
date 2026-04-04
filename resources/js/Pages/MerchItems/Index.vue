<script setup lang="ts">
import BandSelectModal from '@/Components/modules/BandSelectModal.vue';
import LikeToggleInline from '@/Components/parts/LikeToggleInline.vue';
import CompactPagination from '@/Components/parts/CompactPagination.vue';
import FormSelect from '@/Components/form/FormSelect.vue';
import TextInput from '@/Components/form/TextInput.vue';
import SeoHead from '@/Components/seo/SeoHead.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import type { PaginatedList } from '@/types/inertia';
import { Link, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

type MerchRow = {
  id: number;
  name: string;
  slug: string;
  release_year?: number | null;
  is_official: boolean;
  band: { name: string; slug: string };
  category?: { name: string } | null;
  cover_image?: { image_path: string; alt_text?: string | null } | null;
  likes_count?: number;
  liked?: boolean;
};

const props = defineProps<{
  merchItems: PaginatedList<MerchRow> & { current_page: number };
  filters: {
    search?: string | null;
    bands?: number[];
    category?: number | null;
    sort?: string | null;
  };
  bands: { id: number; name: string }[];
  categories: { id: number; name: string }[];
}>();

const filterForm = reactive({
  search: props.filters.search ?? '',
  bands: [...(props.filters.bands ?? [])],
  category: props.filters.category ?? '',
  sort: props.filters.sort ?? 'newest',
});

const applyFilters = () => {
  router.get(route('merch-items.index'), {
    search: filterForm.search || undefined,
    bands: filterForm.bands.length > 0 ? filterForm.bands : undefined,
    category: filterForm.category || undefined,
    sort: filterForm.sort !== 'newest' ? filterForm.sort : undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};

const bandModalOpen = ref(false);

const selectedBandFilterLabel = computed(() => {
  const ids = filterForm.bands;
  if (ids.length === 0) {
    return t('merch.allBands');
  }
  if (ids.length === 1) {
    return props.bands.find((b) => b.id === ids[0])?.name ?? t('merch.allBands');
  }
  return t('modals.bandSelect.selectedCount', { count: ids.length });
});

function onBandFilterApply(value: number | null | number[]) {
  if (Array.isArray(value)) {
    filterForm.bands = value;
    bandModalOpen.value = false;
    applyFilters();
  }
}
</script>

<template>
  <PublicLayout>
    <SeoHead page="merchIndex" />

    <div class="flex items-center justify-between gap-4 px-1">
      <div>
        <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('merch.archiveEyebrow') }}</p>
        <h1 class="mt-2 text-2xl font-semibold text-slate-800">{{ t('merch.listHead') }}</h1>
      </div>
    </div>

    <section class="glass-surface mt-7 p-5">
      <div class="space-y-4">
        <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-stretch">
          <TextInput
            v-model="filterForm.search"
            type="text"
            class="min-w-0 w-full flex-1"
            :placeholder="t('merch.filterSearchPlaceholder')"
            @keyup.enter="applyFilters"
          />
          <button
            type="button"
            class="w-full shrink-0 rounded-2xl border border-white/50 bg-white/70 px-5 py-3 text-sm font-bold text-sky-700 backdrop-blur-xl transition hover:bg-white/80 sm:w-auto sm:px-6"
            @click="applyFilters"
          >
            {{ t('rightPane.submit') }}
          </button>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div class="min-w-0">
            <button
              id="merch-filter-band"
              type="button"
              class="glass-panel flex w-full min-w-0 items-center justify-between rounded-2xl px-4 py-3 text-left text-sm text-slate-700 hover:bg-white/55"
              @click="bandModalOpen = true"
            >
              <span class="min-w-0 truncate">{{ selectedBandFilterLabel }}</span>
              <span class="shrink-0 pl-2 text-sm text-slate-500">{{ t('forms.merch.open') }}</span>
            </button>
          </div>
          <FormSelect v-model="filterForm.category" class="block w-full min-w-0" @change="applyFilters">
            <option value="">{{ t('merch.allCategories') }}</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
          </FormSelect>
          <FormSelect v-model="filterForm.sort" class="block w-full min-w-0" @change="applyFilters">
            <option value="newest">{{ t('merch.sortNewest') }}</option>
            <option value="oldest">{{ t('merch.sortOldest') }}</option>
            <option value="name">{{ t('merch.sortName') }}</option>
          </FormSelect>
        </div>
      </div>
    </section>

    <ul class="mt-7 space-y-5">
      <li v-for="item in merchItems.data" :key="item.id" class="glass-surface p-5">
        <div class="flex flex-col gap-3">
          <Link
            :href="route('merch-items.show', {
              merchItem: item.slug,
              page: merchItems.current_page,
              search: filters.search || undefined,
              bands: filters.bands?.length ? filters.bands : undefined,
              category: filters.category || undefined,
              sort: filters.sort && filters.sort !== 'newest' ? filters.sort : undefined,
            })"
            class="block hover:opacity-90"
          >
            <div class="flex items-center justify-between gap-4">
              <div class="flex min-w-0 items-center gap-4">
                <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-white/50 bg-white/50 shadow-sm">
                  <img
                    v-if="item.cover_image"
                    :src="`/storage/${item.cover_image.image_path}`"
                    :alt="item.cover_image.alt_text || item.name"
                    class="h-full w-full object-cover"
                  />
                  <div v-else class="flex h-full w-full items-center justify-center text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                    {{ t('search.noImage') }}
                  </div>
                </div>

                <div class="min-w-0">
                  <p class="text-lg font-medium text-slate-800">{{ item.name }}</p>
                  <p class="mt-1 text-sm text-slate-500">{{ item.band.name }}</p>
                  <p class="mt-2 text-sm text-slate-500">
                    <span v-if="item.category">{{ item.category.name }}</span>
                    <span v-if="item.release_year"> · {{ item.release_year }}</span>
                    <span v-if="item.is_official"> · {{ t('search.official') }}</span>
                  </p>
                </div>
              </div>
              <span class="glass-link shrink-0 text-sm font-medium">{{ t('merch.detailLink') }}</span>
            </div>
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

    <p v-if="merchItems.data.length === 0" class="mt-4 text-slate-500">{{ t('merch.emptyList') }}</p>

    <BandSelectModal
      :show="bandModalOpen"
      :bands="bands"
      :selected-id="''"
      :selected-ids="filterForm.bands"
      filter-mode
      @close="bandModalOpen = false"
      @apply="onBandFilterApply"
    />
  </PublicLayout>
</template>
