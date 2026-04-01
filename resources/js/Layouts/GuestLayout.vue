<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div
    class="flex min-h-screen flex-col items-center pt-6 sm:justify-center sm:pt-0"
  >
    <div>
      <Link href="/">
        <ApplicationLogo class="h-20 w-20 fill-current text-sky-600" />
      </Link>
    </div>

    <div
      class="glass-surface mt-6 w-full px-6 py-5 sm:max-w-md"
    >
      <slot />
    </div>
  </div>
</template>
