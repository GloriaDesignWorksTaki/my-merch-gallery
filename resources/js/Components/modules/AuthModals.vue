<script setup lang="ts">
import ForgotPasswordPanel from '@/Components/auth/ForgotPasswordPanel.vue';
import LoginForm from '@/Components/auth/LoginForm.vue';
import RegisterForm from '@/Components/auth/RegisterForm.vue';
import Modal from '@/Components/container/Modal.vue';
import type { PageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const showLogin = defineModel<boolean>('showLogin', { required: true });
const showRegister = defineModel<boolean>('showRegister', { required: true });

const page = usePage<
  PageProps<{ ui?: { canResetPassword?: boolean }; flash?: { status?: string | null } }>
>();
const canResetPassword = computed(() => page.props.ui?.canResetPassword ?? true);
const status = computed(() => page.props.flash?.status ?? undefined);

const { t } = useI18n();

const showForgot = ref(false);

const authOpen = computed(() => showLogin.value || showRegister.value || showForgot.value);

const authTitleId = computed(() => {
  if (showForgot.value) {
    return 'auth-forgot-title';
  }
  if (showRegister.value) {
    return 'auth-register-title';
  }
  return 'auth-login-title';
});

function closeAll() {
  showLogin.value = false;
  showRegister.value = false;
  showForgot.value = false;
}

function onLoginSuccess() {
  closeAll();
}

function onRegisterSuccess() {
  closeAll();
}

function switchToRegister() {
  showLogin.value = false;
  showRegister.value = true;
}

function switchToLogin() {
  showRegister.value = false;
  showLogin.value = true;
}

function openForgot() {
  showLogin.value = false;
  showForgot.value = true;
}

function backFromForgotToLogin() {
  showForgot.value = false;
  showLogin.value = true;
}
</script>

<template>
  <Modal :show="authOpen" max-width="md" :title-id="authTitleId" @close="closeAll">
    <div v-if="showLogin" class="max-h-[min(85vh,32rem)] overflow-y-auto p-6 sm:p-8">
      <h2 id="auth-login-title" class="text-lg font-semibold text-slate-800">{{ t('auth.loginTitle') }}</h2>
      <LoginForm
        class="mt-4"
        :can-reset-password="canResetPassword"
        :status="status"
        @success="onLoginSuccess"
        @request-forgot-password="openForgot"
      />
      <p class="mt-5 text-center text-sm text-slate-600">
        <button type="button" class="font-medium text-sky-700 hover:underline" @click="switchToRegister">
          {{ t('auth.noAccountYet') }}
        </button>
      </p>
    </div>

    <ForgotPasswordPanel v-else-if="showForgot" :open="showForgot" @back="backFromForgotToLogin" />

    <div v-else-if="showRegister" class="max-h-[min(85vh,40rem)] overflow-y-auto p-6 sm:p-8">
      <h2 id="auth-register-title" class="text-lg font-semibold text-slate-800">{{ t('auth.registerTitle') }}</h2>
      <RegisterForm class="mt-4" @success="onRegisterSuccess" @switch-to-login="switchToLogin" />
    </div>
  </Modal>
</template>
