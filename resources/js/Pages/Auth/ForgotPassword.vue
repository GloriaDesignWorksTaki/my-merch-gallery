<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/form/InputError.vue';
import InputLabel from '@/Components/form/InputLabel.vue';
import PrimaryButton from '@/Components/parts/PrimaryButton.vue';
import TextInput from '@/Components/form/TextInput.vue';
import SeoHead from '@/Components/seo/SeoHead.vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
  status?: string;
}>();

const form = useForm({
  email: '',
});

const submit = () => {
  form.post(route('password.email'));
};
</script>

<template>
  <GuestLayout>
    <SeoHead page="authForgotPassword" />

    <div class="mb-4 text-sm text-gray-600">
      {{ t('auth.forgotLead') }}
    </div>

    <div
      v-if="status"
      class="mb-4 text-sm font-medium text-green-600"
    >
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <div>
        <InputLabel for="email" :value="t('auth.email')" />

        <TextInput
          id="email"
          type="email"
          class="mt-1 block w-full"
          v-model="form.email"
          required
          autofocus
          autocomplete="username"
        />

        <InputError class="mt-2" :message="form.errors.email" />
      </div>

      <div class="mt-4 flex items-center justify-end">
        <PrimaryButton
          type="submit"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          {{ t('auth.forgotSubmitButton') }}
        </PrimaryButton>
      </div>
    </form>
  </GuestLayout>
</template>
