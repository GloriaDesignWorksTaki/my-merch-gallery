<script setup lang="ts">
import HeartLikeButton from '@/Components/parts/HeartLikeButton.vue';
import type { AuthUser } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { computed, inject } from 'vue';
import { useI18n } from 'vue-i18n';

const props = withDefaults(
  defineProps<{
    likesCount: number;
    liked: boolean;
    featureLabel: string;
    toggleHref: string;
    variant?: 'default' | 'compact';
  }>(),
  { variant: 'default' },
);

const { t } = useI18n();

const iconClass = computed(() =>
  props.variant === 'compact' ? 'h-5 w-5 sm:h-6 sm:w-6' : 'h-6 w-6 sm:h-7 sm:w-7',
);

const countClass = computed(() =>
  props.variant === 'compact'
    ? 'text-sm font-medium tabular-nums leading-none text-inherit'
    : 'text-sm font-semibold tabular-nums leading-none text-inherit',
);

const toneClass = computed(() =>
  props.liked ? 'text-pink-500 hover:text-pink-600' : 'text-slate-400 hover:text-pink-500',
);

const toggleAriaLabel = computed(() => {
  const verb = props.liked ? t('likes.ariaUnlike') : t('likes.ariaLike');
  return `${verb} (${props.likesCount})`;
});

const page = usePage<{ auth: { user: AuthUser | null } }>();
const user = computed(() => page.props.auth.user);

const openLoginRequired = inject<(feature: string) => void>('openLoginRequired', () => {});

function onToggleClick() {
  if (!user.value) {
    openLoginRequired(props.featureLabel);
    return;
  }
  router.post(
    props.toggleHref,
    {},
    { preserveScroll: true },
  );
}
</script>

<template>
  <button
    type="button"
    class="inline-flex max-w-full min-w-0 shrink-0 items-center gap-1 rounded-full py-0.5 pl-0.5 pr-1.5 text-left outline-none transition-colors duration-150 focus-visible:ring-2 focus-visible:ring-pink-300/60 focus-visible:ring-offset-2 focus-visible:ring-offset-transparent"
    :class="toneClass"
    :aria-pressed="liked"
    :aria-label="toggleAriaLabel"
    @click="onToggleClick"
  >
    <HeartLikeButton as-span :liked="liked" :icon-class="iconClass" />
    <span class="min-w-[1ch] tabular-nums" :class="countClass">{{ likesCount }}</span>
  </button>
</template>
