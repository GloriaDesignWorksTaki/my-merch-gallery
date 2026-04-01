<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';

type NavItem = {
  label: string;
  href: string;
  active: boolean;
};

type NavSection = {
  title: string;
  items: NavItem[];
  compact?: boolean;
};

withDefaults(defineProps<{
  homeHref: string;
  mobileTitle: string;
  mobileActionLabel: string;
  mobileActionHref: string;
  primarySections: NavSection[];
  ctaLabel: string;
  ctaHref: string;
  footerTitle: string;
  footerSubtitle?: string;
  footerMeta?: string;
  showMobile?: boolean;
  showDesktop?: boolean;
}>(), {
  showMobile: true,
  showDesktop: true,
});
</script>

<template>
  <header v-if="showMobile" class="sticky top-0 z-30 border-b border-white/30 bg-white/25 backdrop-blur-2xl md:hidden">
    <div class="mx-auto flex max-w-md items-center justify-between px-4 py-3">
      <Link :href="homeHref" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/40 bg-white/40 p-1.5">
        <ApplicationLogo class="h-full w-full object-contain" />
      </Link>
      <div class="text-base font-semibold tracking-[0.18em] text-slate-800">{{ mobileTitle }}</div>
      <Link :href="mobileActionHref" class="rounded-full border border-white/50 bg-white/40 px-4 py-1.5 text-sm font-semibold text-slate-700">
        {{ mobileActionLabel }}
      </Link>
    </div>
  </header>

  <aside v-if="showDesktop" class="sticky top-0 hidden h-screen w-[290px] shrink-0 px-4 py-5 lg:block">
    <div class="flex h-full flex-col justify-between">
      <div>
        <Link :href="homeHref" class="flex h-14 w-14 items-center justify-center rounded-full p-2 transition hover:bg-white/35">
          <ApplicationLogo class="h-full w-full object-contain" />
        </Link>

        <div class="mt-4 rounded-[2rem] border border-white/40 bg-white/32 p-4 shadow-[0_20px_60px_rgba(148,163,184,0.12)] backdrop-blur-xl">
          <section
            v-for="section in primarySections"
            :key="section.title"
            :class="section === primarySections[0] ? '' : 'mt-6 rounded-3xl border border-white/35 bg-white/35 p-3'"
          >
            <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.28em] text-sky-700/70">{{ section.title }}</p>
            <nav class="mt-3 space-y-1.5">
              <Link
                v-for="item in section.items"
                :key="item.label"
                :href="item.href"
                class="flex items-center rounded-2xl px-4 py-3 font-medium transition"
                :class="[
                  section.compact ? 'text-base' : 'text-lg',
                  item.active ? 'bg-white/70 text-slate-900 shadow-sm' : 'text-slate-700 hover:bg-white/45',
                ]"
              >
                {{ item.label }}
              </Link>
            </nav>
          </section>

          <Link
            :href="ctaHref"
            class="mt-5 flex w-full items-center justify-center rounded-2xl border border-white/50 bg-white/70 px-5 py-3.5 text-base font-bold text-sky-700 backdrop-blur-xl transition hover:bg-white/80"
          >
            {{ ctaLabel }}
          </Link>
        </div>
      </div>

      <div class="rounded-[2rem] border border-white/40 bg-white/35 p-5 backdrop-blur-xl">
        <p class="text-sm font-semibold text-slate-800">{{ footerTitle }}</p>
        <p v-if="footerSubtitle" class="mt-1 text-sm text-slate-500">{{ footerSubtitle }}</p>
        <p v-if="footerMeta" class="mt-1 text-sm text-slate-500">{{ footerMeta }}</p>
      </div>
    </div>
  </aside>
</template>
