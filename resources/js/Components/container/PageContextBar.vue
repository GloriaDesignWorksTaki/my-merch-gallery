<script setup lang="ts">
import GuestGateLink from '@/Components/parts/GuestGateLink.vue';
import { Link } from '@inertiajs/vue3';

type Crumb = {
  label: string;
  href?: string;
};

type Action = {
  label: string;
  href: string;
  loginRequired?: boolean;
  featureLabel?: string;
};

const props = defineProps<{
  crumbs: Crumb[];
  actions?: Action[];
}>();

const isLastOddTile = (index: number) => {
  const list = props.actions;

  if (!list || list.length <= 1) {
    return false;
  }

  return index === list.length - 1 && list.length % 2 === 1;
};
</script>

<template>
  <div class="flex w-full min-w-0 flex-col gap-3">
    <nav class="min-w-0 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-slate-500">
      <template v-for="(crumb, index) in crumbs" :key="`${crumb.label}-${index}`">
        <span v-if="index > 0" class="shrink-0">/</span>
        <Link v-if="crumb.href" :href="crumb.href" class="glass-link min-w-0 break-words text-sm font-medium">{{ crumb.label }}</Link>
        <span v-else class="min-w-0 break-words font-medium text-slate-600">{{ crumb.label }}</span>
      </template>
    </nav>

    <div
      v-if="actions?.length"
      class="grid w-full min-w-0 gap-2"
      :class="actions.length === 1 ? 'grid-cols-1' : 'grid-cols-1 sm:grid-cols-2'"
    >
      <template v-for="(action, index) in actions" :key="`action-${index}`">
        <GuestGateLink
          v-if="action.loginRequired"
          :href="action.href"
          :feature="action.featureLabel ?? action.label"
          :content-class="`glass-panel flex min-h-[3rem] min-w-0 items-center justify-center rounded-2xl px-4 py-3 text-center text-sm font-medium leading-snug text-sky-700 hover:bg-white/55 ${isLastOddTile(index) ? 'sm:col-span-2' : ''}`"
        >
          <span class="break-words">{{ action.label }}</span>
        </GuestGateLink>
        <Link
          v-else
          :href="action.href"
          class="glass-panel flex min-h-[3rem] min-w-0 items-center justify-center rounded-2xl px-4 py-3 text-center text-sm font-medium leading-snug text-sky-700 hover:bg-white/55"
          :class="isLastOddTile(index) ? 'sm:col-span-2' : ''"
        >
          <span class="break-words">{{ action.label }}</span>
        </Link>
      </template>
    </div>
  </div>
</template>
