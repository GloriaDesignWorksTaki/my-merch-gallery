<script setup lang="ts">
import AppButton from '@/Components/parts/AppButton.vue';
import Modal from '@/Components/container/Modal.vue';
import { useI18n } from 'vue-i18n';

defineProps<{
  show: boolean;
  /** モーダルに表示する機能名（例: 投稿作成） */
  feature: string;
}>();

const emit = defineEmits<{
  close: [];
}>();

const { t } = useI18n();
</script>

<template>
  <Modal
    :show="show"
    max-width="md"
    title-id="login-required-title"
    @close="emit('close')"
  >
    <div class="p-6 sm:p-8">
      <h2 id="login-required-title" class="text-lg font-semibold text-slate-800">
        {{ t('modals.loginRequired.title') }}
      </h2>
      <p class="mt-3 text-sm leading-relaxed text-slate-600">
        {{ t('modals.loginRequired.body', { feature }) }}
      </p>
      <div class="mt-6 flex flex-wrap items-center gap-3">
        <AppButton :href="route('login')" variant="secondary" size="md" radius="md">
          {{ t('modals.loginRequired.toLogin') }}
        </AppButton>
        <AppButton :href="route('register')" variant="signup" size="md" radius="md">
          {{ t('modals.loginRequired.signup') }}
        </AppButton>
        <AppButton
          variant="muted"
          size="sm"
          radius="md"
          native-type="button"
          extra-class="ml-auto"
          @click="emit('close')"
        >
          {{ t('common.close') }}
        </AppButton>
      </div>
    </div>
  </Modal>
</template>
