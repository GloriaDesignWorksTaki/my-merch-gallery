<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const page = usePage();
const { t } = useI18n();

withDefaults(
  defineProps<{
    /** ボタン群の横方向の寄せ（ヘッダー中央置きなど） */
    justify?: 'end' | 'center' | 'start';
  }>(),
  { justify: 'end' },
);

const locales = computed(() => page.props.locales ?? []);
const current = computed(() => page.props.locale ?? 'en');

function setLocale(code: string) {
  if (code === current.value) {
    return;
  }
  router.post(
    route('locale.update'),
    { locale: code },
    { preserveScroll: true, preserveState: true },
  );
}
</script>

<template>
  <div
    class="flex flex-wrap gap-1"
    :class="{
      'justify-end': justify === 'end',
      'justify-center': justify === 'center',
      'justify-start': justify === 'start',
    }"
    role="group"
    :aria-label="t('locale.switch')"
  >
    <button
      v-for="item in locales"
      :key="item.code"
      type="button"
      class="rounded-full px-2.5 py-1 text-xs font-semibold transition"
      :class="
        current === item.code
          ? 'bg-white/70 text-slate-900 shadow-sm theme-light:border theme-light:border-slate-300 theme-light:bg-white theme-light:shadow-md dark:bg-slate-700 dark:text-slate-100 dark:shadow-none'
          : 'text-slate-600 hover:bg-white/40 theme-light:text-slate-700 theme-light:hover:bg-slate-200 theme-light:hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/80'
      "
      @click="setLocale(item.code)"
    >
      {{ item.label }}
    </button>
  </div>
</template>
