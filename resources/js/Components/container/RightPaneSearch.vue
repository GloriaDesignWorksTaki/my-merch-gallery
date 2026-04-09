<script setup lang="ts">
import LocaleSwitcher from '@/Components/parts/LocaleSwitcher.vue';
import { IconSearch } from '@tabler/icons-vue';
import { router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

withDefaults(
  defineProps<{
    variant?: 'panel' | 'compact';
  }>(),
  { variant: 'panel' },
);

const page = usePage();
const searchInput = ref('');

function syncSearchFromUrl() {
  try {
    const url = new URL(page.url, 'http://rightpane.local');
    searchInput.value = url.searchParams.get('q') ?? '';
  } catch {
    searchInput.value = '';
  }
}

watch(() => page.url, syncSearchFromUrl, { immediate: true });

function submitSearch() {
  const q = searchInput.value.trim();
  if (q === '') {
    return;
  }
  let tab: 'bands' | 'merch' = 'bands';
  try {
    const url = new URL(page.url, 'http://rightpane.local');
    const t = url.searchParams.get('tab');
    if (t === 'merch' || t === 'bands') {
      tab = t;
    }
  } catch {
    /* noop */
  }
  router.get(route('search'), { q: q || undefined, tab }, { preserveScroll: true });
}
</script>

<template>
  <div :class="variant === 'panel' ? 'space-y-3' : 'space-y-2'">
    <div v-if="variant === 'panel'" class="flex justify-end">
      <LocaleSwitcher />
    </div>
    <section
      class="rounded-[2rem] border border-white/40 bg-white/35 backdrop-blur-xl theme-light:border-slate-300 theme-light:bg-white/95 theme-light:shadow-sm dark:border-slate-600/55 dark:bg-slate-900/55"
      :class="
        variant === 'panel'
          ? 'p-5'
          : 'border-white/30 bg-white/30 px-4 py-3 theme-light:border-slate-300 theme-light:bg-white/95 dark:border-slate-600/50 dark:bg-slate-900/50'
      "
    >
      <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-sky-700/70 theme-light:text-sky-800 dark:text-sky-300/90">
        {{ t('rightPane.heading') }}
      </p>
      <form class="mt-3 space-y-3" @submit.prevent="submitSearch">
        <div class="relative">
          <IconSearch
            class="pointer-events-none absolute top-1/2 -translate-y-1/2 text-slate-400 theme-light:text-slate-500 dark:text-slate-300"
            :class="variant === 'panel' ? 'left-4' : 'left-3'"
            :size="variant === 'panel' ? 20 : 18"
            aria-hidden="true"
          />
          <input
            v-model="searchInput"
            type="search"
            name="q"
            autocomplete="off"
            :placeholder="t('rightPane.placeholder')"
            class="glass-panel block w-full rounded-2xl border-white/50 bg-white/45 text-slate-800 placeholder:text-slate-500 shadow-[0_10px_30px_rgba(125,166,214,0.12)] focus:border-sky-300/70 focus:ring-sky-200/60 theme-light:border-slate-300 theme-light:bg-white theme-light:shadow-sm theme-light:focus:border-sky-500 theme-light:focus:ring-2 theme-light:focus:ring-sky-500/25 dark:border-slate-500/70 dark:bg-slate-800/90 dark:text-slate-100 dark:placeholder:text-slate-400 dark:shadow-[0_10px_30px_rgba(0,0,0,0.35)] dark:focus:border-sky-500/60 dark:focus:ring-sky-500/25"
            :class="variant === 'panel' ? 'py-3 pl-11 pr-4' : 'py-2 pl-10 pr-3 text-base md:text-sm'"
          />
        </div>
        <div class="flex justify-end">
          <button
            type="submit"
            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-white/50 bg-white/70 font-bold text-sky-700 backdrop-blur-xl transition hover:bg-white/80 theme-light:border-slate-300 theme-light:bg-white theme-light:text-sky-800 theme-light:shadow-sm theme-light:hover:border-sky-500 theme-light:hover:bg-sky-50 theme-light:active:bg-sky-100/90 dark:border-slate-500 dark:bg-slate-800 dark:text-sky-300 dark:hover:bg-slate-700 sm:w-auto"
            :class="variant === 'panel' ? 'px-6 py-3 text-sm' : 'px-4 py-2 text-xs'"
          >
            <IconSearch :size="variant === 'panel' ? 18 : 16" class="shrink-0" aria-hidden="true" />
            {{ t('rightPane.submit') }}
          </button>
        </div>
      </form>
    </section>
  </div>
</template>
