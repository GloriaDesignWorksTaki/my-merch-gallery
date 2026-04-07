<script setup lang="ts">
import SeoHead from '@/Components/seo/SeoHead.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
  summary: {
    bands: number;
    merchItems: number;
  };
  recentBands: { id: number; name: string; slug: string }[];
  recentMerchItems: { id: number; name: string; slug: string; band?: { name: string; slug: string } | null; cover_image?: { image_path: string; alt_text?: string | null } | null }[];
  profileHints: {
    bioMissing: boolean;
    avatarMissing: boolean;
  };
}>();

</script>

<template>
  <SeoHead page="dashboard" />
  <AuthenticatedLayout>
    <template #header>
      <div>
        <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('dashboard.eyebrow') }}</p>
        <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-800">{{ t('dashboard.title') }}</h2>
      </div>
    </template>

    <div class="mx-auto max-w-4xl space-y-6">
      <section class="space-y-6">
        <Link
          :href="route('dashboard.likes')"
          class="glass-surface block p-5 transition hover:bg-white/40"
        >
          <div class="flex items-center justify-between gap-4">
            <div>
              <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('dashboard.likesEyebrow') }}</p>
              <p class="mt-2 text-lg font-semibold text-slate-800">{{ t('dashboard.likesTitle') }}</p>
              <p class="mt-1 text-sm text-slate-600">{{ t('dashboard.likesHint') }}</p>
            </div>
            <span class="glass-link shrink-0 text-sm font-medium">{{ t('dashboard.toList') }}</span>
          </div>
        </Link>

        <div class="glass-surface p-6">
          <div class="flex items-center justify-between gap-4">
            <h3 class="text-lg font-semibold text-slate-800">{{ t('dashboard.recentBands') }}</h3>
            <Link :href="route('bands.index')" class="glass-link text-sm font-medium">{{ t('dashboard.toList') }}</Link>
          </div>
          <div class="mt-4 space-y-3">
            <Link v-for="band in recentBands" :key="band.id" :href="route('bands.show', band.slug)" class="glass-panel block rounded-2xl px-4 py-4 hover:bg-white/55">
              <p class="font-medium text-slate-800">{{ band.name }}</p>
            </Link>
            <p v-if="recentBands.length === 0" class="text-sm text-slate-500">{{ t('dashboard.emptyBands') }}</p>
          </div>
        </div>

        <div class="glass-surface p-6">
          <div class="flex items-center justify-between gap-4">
            <h3 class="text-lg font-semibold text-slate-800">{{ t('dashboard.recentMerch') }}</h3>
            <Link :href="route('merch-items.index')" class="glass-link text-sm font-medium">{{ t('dashboard.toList') }}</Link>
          </div>
          <div class="mt-4 space-y-3">
            <Link v-for="item in recentMerchItems" :key="item.id" :href="route('merch-items.show', item.slug)" class="glass-panel flex items-center gap-4 rounded-2xl px-4 py-4 hover:bg-white/55">
              <div class="h-14 w-14 shrink-0 overflow-hidden rounded-2xl border border-white/40 bg-white/45">
                <img v-if="item.cover_image" :src="`/storage/${item.cover_image.image_path}`" :alt="item.cover_image.alt_text || item.name" class="h-full w-full object-cover" />
              </div>
              <div class="min-w-0">
                <p class="font-medium text-slate-800">{{ item.name }}</p>
                <p v-if="item.band" class="mt-1 text-sm text-slate-500">{{ item.band.name }}</p>
              </div>
            </Link>
            <p v-if="recentMerchItems.length === 0" class="text-sm text-slate-500">{{ t('dashboard.emptyMerch') }}</p>
          </div>
        </div>

        <div class="glass-surface p-6">
          <div class="flex items-center justify-between gap-4">
            <h3 class="text-lg font-semibold text-slate-800">{{ t('dashboard.profileSection') }}</h3>
            <Link :href="route('profile.edit')" class="glass-link text-sm font-medium">{{ t('dashboard.toSettings') }}</Link>
          </div>
          <div class="mt-4 space-y-3 text-sm text-slate-600">
            <p :class="profileHints.bioMissing ? 'text-slate-800' : 'text-slate-500'">{{ profileHints.bioMissing ? t('dashboard.bioUnset') : t('dashboard.bioSet') }}</p>
            <p :class="profileHints.avatarMissing ? 'text-slate-800' : 'text-slate-500'">{{ profileHints.avatarMissing ? t('dashboard.avatarUnset') : t('dashboard.avatarSet') }}</p>
          </div>
        </div>
      </section>
    </div>
  </AuthenticatedLayout>
</template>
