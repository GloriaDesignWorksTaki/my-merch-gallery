<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import type { AppTheme, AuthUser } from '@/types';
import { isAppTheme } from '@/composables/useAppTheme';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const page = usePage<{ auth: { user: AuthUser | null } }>();
const user = page.props.auth.user;

const initialTheme = computed((): AppTheme =>
  user && isAppTheme(user.theme) ? user.theme : 'light',
);

const form = useForm({
  theme: initialTheme.value,
});

watch(initialTheme, (next) => {
  form.theme = next;
});

const options = computed((): { value: AppTheme; label: string }[] => [
  { value: 'light', label: t('profile.themeLight') },
  { value: 'dark', label: t('profile.themeDark') },
  { value: 'primary', label: t('profile.themePrimary') },
]);

function submit(theme: AppTheme) {
  if (!user) {
    return;
  }
  form.theme = theme;
  form.patch(route('profile.theme'), {
    preserveScroll: true,
  });
}
</script>

<template>
  <section v-if="user" class="max-w-xl">
    <header class="mb-5">
      <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70 dark:text-sky-400/80">{{ t('profile.themeEyebrow') }}</p>
      <h3 class="mt-2 text-lg font-semibold text-slate-800 dark:text-slate-100">{{ t('profile.themeTitle') }}</h3>
    </header>

    <div class="space-y-3" role="radiogroup" :aria-label="t('profile.themeTitle')">
      <label
        v-for="opt in options"
        :key="opt.value"
        class="flex cursor-pointer items-start gap-3 rounded-2xl border border-white/40 bg-white/25 px-4 py-3.5 transition hover:bg-white/40 theme-light:border-slate-300 theme-light:bg-white theme-light:hover:border-slate-400 theme-light:hover:bg-slate-50 dark:border-slate-600/50 dark:bg-slate-900/30 dark:hover:bg-slate-800/50"
        :class="
          form.theme === opt.value ? 'ring-2 ring-sky-400/50 theme-light:ring-sky-500/50 dark:ring-sky-500/40' : ''
        "
      >
        <input
          class="mt-1 h-4 w-4 shrink-0 border-slate-300 text-sky-600 focus:ring-sky-500 dark:border-slate-500 dark:bg-slate-800 dark:text-sky-500"
          type="radio"
          name="theme"
          :value="opt.value"
          :checked="form.theme === opt.value"
          @change="submit(opt.value)"
        />
        <span class="min-w-0 text-sm font-semibold text-slate-800 dark:text-slate-100">{{ opt.label }}</span>
      </label>
    </div>

    <p v-if="form.recentlySuccessful" class="mt-4 text-sm font-medium text-emerald-700 dark:text-emerald-400">
      {{ t('profile.saved') }}
    </p>
  </section>
</template>
