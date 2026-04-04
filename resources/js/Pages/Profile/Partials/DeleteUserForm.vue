<script setup lang="ts">
import DangerButton from '@/Components/parts/DangerButton.vue';
import InputError from '@/Components/form/InputError.vue';
import InputLabel from '@/Components/form/InputLabel.vue';
import Modal from '@/Components/container/Modal.vue';
import SecondaryButton from '@/Components/parts/SecondaryButton.vue';
import TextInput from '@/Components/form/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const confirmingUserDeletion = ref(false);
const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
  password: '',
});

const confirmUserDeletion = () => {
  confirmingUserDeletion.value = true;

  nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
  form.delete(route('profile.destroy'), {
    preserveScroll: true,
    onSuccess: () => closeModal(),
    onError: () => passwordInput.value?.focus(),
    onFinish: () => {
      form.reset();
    },
  });
};

const closeModal = () => {
  confirmingUserDeletion.value = false;

  form.clearErrors();
  form.reset();
};
</script>

<template>
  <section class="space-y-6">
    <header>
      <h2 class="text-lg font-medium text-gray-900">
        {{ t('profile.deleteSectionTitle') }}
      </h2>

      <p class="mt-1 text-sm text-gray-600">
        {{ t('profile.deleteSectionLead') }}
      </p>
    </header>

    <DangerButton @click="confirmUserDeletion">{{ t('profile.deleteAccountCta') }}</DangerButton>

    <Modal :show="confirmingUserDeletion" :title-id="'delete-account-confirm-title'" @close="closeModal">
      <div class="p-6">
        <h2
          id="delete-account-confirm-title"
          class="text-lg font-medium text-gray-900"
        >
          {{ t('profile.deleteModalTitle') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
          {{ t('profile.deleteModalLead') }}
        </p>

        <div class="mt-6">
          <InputLabel
            for="password"
            :value="t('auth.password')"
            class="sr-only"
          />

          <TextInput
            id="password"
            ref="passwordInput"
            v-model="form.password"
            type="password"
            autofocus
            class="mt-1 block w-3/4"
            :placeholder="t('auth.password')"
            @keyup.enter="deleteUser"
          />

          <InputError :message="form.errors.password" class="mt-2" />
        </div>

        <div class="mt-6 flex justify-end">
          <SecondaryButton @click="closeModal">
            {{ t('common.cancel') }}
          </SecondaryButton>

          <DangerButton
            class="ms-3"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
            @click="deleteUser"
          >
            {{ t('profile.deleteModalConfirm') }}
          </DangerButton>
        </div>
      </div>
    </Modal>
  </section>
</template>
