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
    <div class="flex justify-end">
      <LocaleSwitcher :class="variant === 'compact' ? 'max-w-[min(100%,14rem)]' : ''" />
    </div>
    <section
      class="rounded-[2rem] border border-white/40 bg-white/35 backdrop-blur-xl"
      :class="variant === 'panel' ? 'p-5' : 'border-white/30 bg-white/30 px-4 py-3'"
    >
      <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-sky-700/70">
        {{ t('rightPane.heading') }}
      </p>
      <form class="mt-3 space-y-3" @submit.prevent="submitSearch">
        <div class="relative">
          <IconSearch
            class="pointer-events-none absolute top-1/2 -translate-y-1/2 text-slate-400"
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
            class="glass-panel block w-full rounded-2xl border-white/50 bg-white/45 text-slate-800 placeholder:text-slate-400 shadow-[0_10px_30px_rgba(125,166,214,0.12)] focus:border-sky-300/70 focus:ring-sky-200/60"
            :class="variant === 'panel' ? 'py-3 pl-11 pr-4' : 'py-2 pl-10 pr-3 text-sm'"
          />
        </div>
        <div class="flex justify-end">
          <button
            type="submit"
            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-white/50 bg-white/70 font-bold text-sky-700 backdrop-blur-xl transition hover:bg-white/80 sm:w-auto"
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
