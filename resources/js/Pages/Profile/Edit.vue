<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import SeoHead from '@/Components/seo/SeoHead.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
  mustVerifyEmail?: boolean;
  status?: string;
  email: string;
  emailVerifiedAt: string | null;
}>();
</script>

<template>
  <SeoHead page="profileEdit" />

  <AuthenticatedLayout>
    <template #header>
      <div>
        <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('profile.accountEyebrow') }}</p>
        <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-800">{{ t('profile.editTitle') }}</h2>
      </div>
    </template>

    <div class="mx-auto max-w-3xl space-y-6">
      <div class="glass-surface p-6 sm:p-8">
        <UpdateProfileInformationForm
          :must-verify-email="mustVerifyEmail"
          :status="status"
          :email="email"
          :email-verified-at="emailVerifiedAt"
          class="max-w-xl"
        />
      </div>

      <div class="glass-surface p-6 sm:p-8">
        <UpdatePasswordForm class="max-w-xl" />
      </div>

      <div class="glass-surface p-6 sm:p-8">
        <DeleteUserForm class="max-w-xl" />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
