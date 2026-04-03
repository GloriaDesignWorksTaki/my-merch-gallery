<script setup lang="ts">
import type { PaginationLink } from '@/types/inertia';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

type CompactPaginationItem =
  | { type: 'link'; key: string; label: string; url: string | null; active: boolean; disabled: boolean }
  | { type: 'ellipsis'; key: string };

const props = defineProps<{
  links: PaginationLink[];
}>();

const items = computed<CompactPaginationItem[]>(() => {
  const links = props.links;

  if (links.length <= 3) {
    return [];
  }

  const previous = links[0];
  const next = links[links.length - 1];
  const pageLinks = links
    .slice(1, -1)
    .map((link) => ({
      ...link,
      page: Number.parseInt(String(link.label).replace(/[^0-9]/g, ''), 10),
    }))
    .filter((link) => Number.isFinite(link.page));

  const currentPage = pageLinks.find((link) => link.active)?.page ?? 1;
  const lastPage = pageLinks[pageLinks.length - 1]?.page ?? 1;
  const pages = new Set<number>([1, 2, currentPage - 1, currentPage, currentPage + 1, lastPage - 1, lastPage]);

  const visiblePages = [...pages]
    .filter((page) => page >= 1 && page <= lastPage)
    .sort((a, b) => a - b);

  const result: CompactPaginationItem[] = [
    {
      type: 'link',
      key: 'previous',
      label: '<',
      url: previous?.url ?? null,
      active: false,
      disabled: !previous?.url,
    },
  ];

  let lastVisiblePage = 0;

  visiblePages.forEach((page) => {
    if (lastVisiblePage > 0 && page - lastVisiblePage > 1) {
      result.push({ type: 'ellipsis', key: `ellipsis-${lastVisiblePage}-${page}` });
    }

    const link = pageLinks.find((item) => item.page === page);

    if (link) {
      result.push({
        type: 'link',
        key: `page-${page}`,
        label: String(page),
        url: link.url,
        active: link.active,
        disabled: !link.url,
      });
    }

    lastVisiblePage = page;
  });

  result.push({
    type: 'link',
    key: 'next',
    label: '>',
    url: next?.url ?? null,
    active: false,
    disabled: !next?.url,
  });

  return result;
});
</script>

<template>
  <nav v-if="items.length" class="mt-8 px-1">
    <div class="flex items-center justify-center gap-2 whitespace-nowrap">
      <template v-for="item in items" :key="item.key">
        <span v-if="item.type === 'ellipsis'" class="px-2 text-sm text-slate-500">...</span>
        <component
          :is="item.url && !item.disabled ? Link : 'span'"
          v-else
          :href="item.url ?? undefined"
          class="rounded-full px-4 py-2 text-sm transition"
          :class="[
            item.active
              ? 'bg-sky-600 text-white'
              : item.url && !item.disabled
                ? 'glass-link bg-white/20 text-slate-700 hover:bg-white/55'
                : 'bg-slate-200/70 text-slate-400',
          ]"
        >
          {{ item.label }}
        </component>
      </template>
    </div>
  </nav>
</template>
