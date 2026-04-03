<script setup lang="ts">
import BandSelectModal from '@/Components/modules/BandSelectModal.vue';
import CompactPagination from '@/Components/parts/CompactPagination.vue';
import FormSelect from '@/Components/form/FormSelect.vue';
import GuestGateLink from '@/Components/parts/GuestGateLink.vue';
import TextInput from '@/Components/form/TextInput.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import type { PaginatedList } from '@/types/inertia';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

type PostRow = {
  id: number;
  body: string;
  visibility: string;
  user: { name: string; username: string };
  band: { name: string; slug: string };
  merch_item?: { name: string } | null;
  cover_image?: { image_path: string } | null;
};

const props = defineProps<{
  posts: PaginatedList<PostRow> & { current_page: number };
  filters: {
    search?: string | null;
    bands?: number[];
    visibility?: string | null;
    sort?: string | null;
  };
  bands: { id: number; name: string }[];
}>();

const filterForm = reactive({
  search: props.filters.search ?? '',
  bands: [...(props.filters.bands ?? [])],
  visibility: props.filters.visibility ?? '',
  sort: props.filters.sort ?? 'newest',
});

const applyFilters = () => {
  router.get(route('posts.index'), {
    search: filterForm.search || undefined,
    bands: filterForm.bands.length > 0 ? filterForm.bands : undefined,
    visibility: filterForm.visibility || undefined,
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
    return t('posts.allBands');
  }
  if (ids.length === 1) {
    return props.bands.find((b) => b.id === ids[0])?.name ?? t('posts.allBands');
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
    <Head :title="t('posts.indexTitle')" />

    <div class="flex items-center justify-between gap-4 px-1">
      <div>
        <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('posts.communityEyebrow') }}</p>
        <h1 class="mt-2 text-2xl font-semibold text-slate-800">{{ t('posts.listHead') }}</h1>
      </div>
      <GuestGateLink
        :href="route('posts.create')"
        :feature="t('posts.featureCreate')"
        content-class="glass-panel rounded-full px-4 py-2 text-sm font-medium text-sky-700 hover:bg-white/55"
      >
        {{ t('posts.createButton') }}
      </GuestGateLink>
    </div>
    <section class="glass-surface mt-7 p-5">
      <div class="space-y-4">
        <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-stretch">
          <TextInput
            v-model="filterForm.search"
            type="text"
            class="min-w-0 w-full flex-1"
            :placeholder="t('posts.filterSearchPlaceholder')"
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
              id="posts-filter-band"
              type="button"
              class="glass-panel flex w-full min-w-0 items-center justify-between rounded-2xl px-4 py-3 text-left text-sm text-slate-700 hover:bg-white/55"
              @click="bandModalOpen = true"
            >
              <span class="min-w-0 truncate">{{ selectedBandFilterLabel }}</span>
              <span class="shrink-0 pl-2 text-sm text-slate-500">{{ t('forms.merch.open') }}</span>
            </button>
          </div>
          <FormSelect v-model="filterForm.visibility" class="block w-full min-w-0" @change="applyFilters">
            <option value="">{{ t('posts.allVisibility') }}</option>
            <option value="public">{{ t('forms.post.visibilityPublic') }}</option>
            <option value="unlisted">{{ t('forms.post.visibilityUnlisted') }}</option>
          </FormSelect>
          <FormSelect v-model="filterForm.sort" class="block w-full min-w-0" @change="applyFilters">
            <option value="newest">{{ t('posts.sortNewest') }}</option>
            <option value="oldest">{{ t('posts.sortOldest') }}</option>
          </FormSelect>
        </div>
      </div>
    </section>
    <ul class="mt-7 space-y-5">
      <li v-for="p in posts.data" :key="p.id" class="glass-surface p-5">
        <Link
          :href="route('posts.show', {
            post: p.id,
            page: posts.current_page,
            search: filters.search || undefined,
            bands: filters.bands?.length ? filters.bands : undefined,
            visibility: filters.visibility || undefined,
            sort: filters.sort && filters.sort !== 'newest' ? filters.sort : undefined,
          })"
          class="block hover:opacity-90"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
              <p class="line-clamp-3 text-slate-800">{{ p.body }}</p>
              <p class="mt-3 text-sm text-slate-500">
                @{{ p.user.username }} · {{ p.band.name }}
                <span v-if="p.merch_item"> · {{ p.merch_item.name }}</span>
                <span> · {{
                  p.visibility === 'public'
                    ? t('forms.post.visibilityPublic')
                    : p.visibility === 'unlisted'
                      ? t('forms.post.visibilityUnlisted')
                      : t('forms.post.visibilityPrivate')
                }}</span>
              </p>
            </div>
            <div v-if="p.cover_image" class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl border border-white/40 bg-white/45">
              <img :src="`/storage/${p.cover_image.image_path}`" alt="" class="h-full w-full object-cover" />
            </div>
          </div>
        </Link>
      </li>
    </ul>
    <CompactPagination :links="posts.links" />
    <p v-if="posts.data.length === 0" class="mt-4 text-slate-500">{{ t('posts.emptyList') }}</p>

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
