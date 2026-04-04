<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SeoHead from '@/Components/seo/SeoHead.vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
  <SeoHead page="adminDashboard" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-wrap items-start justify-between gap-3 sm:items-center">
        <div class="min-w-0">
          <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('pages.admin.eyebrow') }}</p>
          <h2 class="mt-1 text-xl font-semibold leading-snug text-slate-800 sm:text-2xl">{{ t('pages.admin.heading') }}</h2>
        </div>
        <div class="glass-panel shrink-0 rounded-full px-3 py-1.5 text-xs text-slate-600 sm:text-sm">
          {{ t('pages.admin.moderationPreview') }}
        </div>
      </div>
    </template>

    <div class="glass-surface rounded-2xl p-4 sm:p-5">
      <p class="text-sm leading-relaxed text-slate-600">{{ t('pages.admin.lead') }}</p>
      <ul class="mt-4 grid gap-3 sm:grid-cols-2">
        <li>
          <Link
            :href="route('admin.band-edit-requests.index')"
            class="glass-panel flex min-h-12 items-center rounded-xl px-4 py-3 text-sm font-medium text-sky-800 transition hover:bg-white/55 sm:min-h-14 sm:text-base"
          >
            {{ t('pages.admin.linkBandRequests') }}
          </Link>
        </li>
        <li>
          <Link
            :href="route('admin.users.index')"
            class="glass-panel flex min-h-12 items-center rounded-xl px-4 py-3 text-sm font-medium text-sky-800 transition hover:bg-white/55 sm:min-h-14 sm:text-base"
          >
            {{ t('pages.admin.linkUsers') }}
          </Link>
        </li>
      </ul>
      <Link :href="route('dashboard')" class="glass-link mt-4 inline-block text-sm font-medium">
        {{ t('pages.admin.toUserDashboard') }}
      </Link>
    </div>
  </AuthenticatedLayout>
</template>
