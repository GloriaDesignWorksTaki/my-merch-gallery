<script setup lang="ts">
import SeoHead from '@/Components/seo/SeoHead.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
  featured: {
    bands: { id: number; name: string; slug: string }[];
    merchItems: { id: number; name: string; slug: string; band?: { name: string; slug: string } | null; cover_image?: { image_path: string; alt_text?: string | null } | null }[];
  };
}>();
</script>

<template>
  <PublicLayout>
    <SeoHead page="home" />

    <section class="glass-surface px-8 py-12">
      <p class="text-sm uppercase tracking-[0.35em] text-sky-600/70">{{ t('home.eyebrow') }}</p>
      <h1 class="mt-4 text-4xl font-semibold tracking-tight text-slate-800">{{ t('home.heroTitle') }}</h1>
      <p class="mt-4 max-w-2xl whitespace-pre-line text-sm leading-7 text-slate-600">{{ t('home.heroLead') }}</p>
      <div class="mt-6 overflow-hidden rounded-[2rem] border border-white/40 bg-white/35 shadow-[0_20px_60px_rgba(148,163,184,0.12)]">
        <img src="/images/main-visual.jpg" alt="My Merch Gallery main visual" class="h-[280px] w-full object-cover md:h-[360px]" />
      </div>
      <ul class="mt-8 flex flex-wrap gap-4 text-sm">
        <li><Link :href="route('bands.index')" class="glass-panel inline-flex rounded-full px-5 py-2.5 font-medium text-sky-700 hover:bg-white/55">{{ t('home.linkBands') }}</Link></li>
        <li><Link :href="route('merch-items.index')" class="glass-panel inline-flex rounded-full px-5 py-2.5 font-medium text-sky-700 hover:bg-white/55">{{ t('home.linkMerch') }}</Link></li>
        <li><Link :href="route('search')" class="glass-panel inline-flex rounded-full px-5 py-2.5 font-medium text-sky-700 hover:bg-white/55">{{ t('home.linkSearch') }}</Link></li>
      </ul>
    </section>

    <section class="mt-8">
      <div class="grid gap-6">
        <div class="glass-surface p-6">
          <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold text-slate-800">{{ t('home.recentMerch') }}</h2>
            <Link :href="route('merch-items.index')" class="glass-link text-sm font-medium">{{ t('home.more') }}</Link>
          </div>
          <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <Link v-for="item in featured.merchItems" :key="item.id" :href="route('merch-items.show', item.slug)" class="glass-panel flex items-center gap-4 rounded-2xl p-4 hover:bg-white/55">
              <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl border border-white/40 bg-white/45">
                <img v-if="item.cover_image" :src="`/storage/${item.cover_image.image_path}`" :alt="item.cover_image.alt_text || item.name" class="h-full w-full object-cover" />
              </div>
              <div>
                <p class="font-medium text-slate-800">{{ item.name }}</p>
                <p v-if="item.band" class="mt-1 text-sm text-slate-500">{{ item.band.name }}</p>
              </div>
            </Link>
          </div>
        </div>

        <div class="glass-surface p-6">
          <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold text-slate-800">{{ t('home.featuredBands') }}</h2>
            <Link :href="route('bands.index')" class="glass-link text-sm font-medium">{{ t('common.toList') }}</Link>
          </div>
          <div class="mt-4 space-y-3">
            <Link v-for="band in featured.bands" :key="band.id" :href="route('bands.show', band.slug)" class="glass-panel block rounded-2xl px-4 py-4 font-medium text-slate-800 hover:bg-white/55">
              {{ band.name }}
            </Link>
          </div>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>
