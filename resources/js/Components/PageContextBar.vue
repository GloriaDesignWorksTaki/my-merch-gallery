<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

type Crumb = {
  label: string;
  href?: string;
};

type Action = {
  label: string;
  href: string;
};

defineProps<{
  crumbs: Crumb[];
  actions?: Action[];
}>();
</script>

<template>
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
      <template v-for="(crumb, index) in crumbs" :key="`${crumb.label}-${index}`">
        <span v-if="index > 0">/</span>
        <Link v-if="crumb.href" :href="crumb.href" class="glass-link text-sm font-medium">{{ crumb.label }}</Link>
        <span v-else class="font-medium text-slate-600">{{ crumb.label }}</span>
      </template>
    </div>

    <div v-if="actions?.length" class="flex flex-wrap items-center gap-2">
      <Link
        v-for="action in actions"
        :key="action.label"
        :href="action.href"
        class="glass-panel rounded-full px-4 py-2 text-sm font-medium text-sky-700 hover:bg-white/55"
      >
        {{ action.label }}
      </Link>
    </div>
  </div>
</template>
