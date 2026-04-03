<script setup lang="ts">
import LocaleSwitcher from '@/Components/parts/LocaleSwitcher.vue';
import ApplicationLogo from '@/Components/parts/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div
    class="flex min-h-screen flex-col items-center pt-6 sm:justify-center sm:pt-0"
  >
    <div class="flex w-full max-w-md flex-col items-stretch gap-4 px-4 sm:px-0">
      <div class="flex items-start justify-between gap-3">
        <Link href="/" class="shrink-0">
          <ApplicationLogo class="h-20 w-20 fill-current text-sky-600" />
        </Link>
        <LocaleSwitcher class="max-w-[min(100%,14rem)]" />
      </div>
    </div>

    <div
      class="glass-surface mt-6 w-full px-6 py-5 sm:max-w-md"
    >
      <slot />
    </div>
  </div>
</template>
