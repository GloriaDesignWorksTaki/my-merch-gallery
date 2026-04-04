<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const page = usePage();
const { t } = useI18n();

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
  <div class="flex flex-wrap justify-end gap-1" role="group" :aria-label="t('locale.switch')">
    <button
      v-for="item in locales"
      :key="item.code"
      type="button"
      class="rounded-full px-2.5 py-1 text-xs font-semibold transition"
      :class="
        current === item.code
          ? 'bg-white/70 text-slate-900 shadow-sm'
          : 'text-slate-600 hover:bg-white/40'
      "
      @click="setLocale(item.code)"
    >
      {{ item.label }}
    </button>
  </div>
</template>
