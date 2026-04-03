<script setup lang="ts">
import type { AuthUser } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, inject } from 'vue';

const props = withDefaults(
  defineProps<{
    href: string;
    /** モーダル本文「〇〇を利用するには…」の〇〇 */
    feature: string;
    /** Link / button 共通のクラス */
    contentClass?: string;
  }>(),
  {},
);

const page = usePage<{ auth: { user: AuthUser | null } }>();
const user = computed(() => page.props.auth.user);

type OpenLoginRequired = (feature: string) => void;

function noopLoginRequired(_feature: string): void {}

const openLoginRequired = inject('openLoginRequired', noopLoginRequired) as OpenLoginRequired;

function onGuestClick(e: MouseEvent) {
  e.preventDefault();
  openLoginRequired(props.feature);
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
