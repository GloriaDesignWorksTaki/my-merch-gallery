<script setup lang="ts">
import type { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
  page: string;
  params?: Record<string, unknown>;
}>();

const { t } = useI18n();
const inertiaPage = usePage<PageProps<{ appName?: string }>>();

const mergedParams = computed(() => ({
  siteName: inertiaPage.props.appName ?? 'My Merch Gallery',
  ...(props.params ?? {}),
}));

const title = computed(() => t(`seo.pages.${props.page}.title`, mergedParams.value));
const description = computed(() => t(`seo.pages.${props.page}.description`, mergedParams.value));
</script>

<template>
  <Head :title="title">
    <meta head-key="meta-description" name="description" :content="description" />
  </Head>
</template>
