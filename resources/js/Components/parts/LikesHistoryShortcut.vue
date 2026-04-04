<script setup lang="ts">
import { HeartIcon } from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { route } from 'ziggy-js';

const { t } = useI18n();

const active = computed(() => Boolean(route().current('dashboard.likes')));
</script>

<template>
  <Link
    :href="route('dashboard.likes')"
    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-white/40 bg-white/35 text-slate-600 transition hover:bg-white/55 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/50"
    :class="active ? 'text-rose-500 ring-1 ring-rose-200/60' : ''"
    :title="t('layout.nav.dashboardLikes')"
    :aria-label="t('layout.nav.dashboardLikes')"
    :aria-current="active ? 'page' : undefined"
  >
    <HeartIcon class="h-5 w-5" aria-hidden="true" />
  </Link>
</template>
