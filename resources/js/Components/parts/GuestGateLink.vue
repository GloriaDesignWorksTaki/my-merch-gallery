<script setup lang="ts">
import type { AuthUser } from '@/types';
import { fallbackVisitAuthLoginModal } from '@/utils/authModalFallback';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, inject } from 'vue';

withDefaults(
  defineProps<{
    href: string;
    feature?: string;
    contentClass?: string;
  }>(),
  {},
);

const page = usePage<{ auth: { user: AuthUser | null } }>();
const user = computed(() => page.props.auth.user);

const openAuthLogin = inject('openAuthLogin', fallbackVisitAuthLoginModal) as () => void;

function onGuestClick(e: MouseEvent) {
  e.preventDefault();
  openAuthLogin();
}
</script>

<template>
  <Link v-if="user" :href="href" :class="contentClass">
    <slot />
  </Link>
  <button
    v-else
    type="button"
    :class="contentClass"
    @click="onGuestClick"
  >
    <slot />
  </button>
</template>
