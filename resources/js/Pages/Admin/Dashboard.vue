<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
  <Head :title="t('pages.admin.title')" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between gap-4">
        <div>
          <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('pages.admin.eyebrow') }}</p>
          <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-800">{{ t('pages.admin.heading') }}</h2>
        </div>
        <div class="glass-panel rounded-full px-4 py-2 text-sm text-slate-600">
          {{ t('pages.admin.moderationPreview') }}
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="glass-surface p-6">
          <div class="space-y-4 text-slate-600">
            <p>{{ t('pages.admin.lead') }}</p>
            <Link :href="route('dashboard')" class="glass-link text-sm font-medium">
              {{ t('pages.admin.toUserDashboard') }}
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
