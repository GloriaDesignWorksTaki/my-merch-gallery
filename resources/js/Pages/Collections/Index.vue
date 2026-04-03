<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
  <Head :title="t('pages.collections.title')" />

  <AuthenticatedLayout>
    <template #header>
      <div>
        <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('pages.collections.eyebrow') }}</p>
        <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-800">{{ t('pages.collections.title') }}</h2>
      </div>
    </template>

    <div class="mx-auto max-w-3xl">
      <div class="glass-surface p-6 text-slate-600">
        {{ t('pages.collections.lead') }}
      </div>
    </div>
  </AuthenticatedLayout>
</template>
