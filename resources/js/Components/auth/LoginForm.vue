<script setup lang="ts">
import Checkbox from '@/Components/form/Checkbox.vue';
import InputError from '@/Components/form/InputError.vue';
import InputLabel from '@/Components/form/InputLabel.vue';
import PrimaryButton from '@/Components/parts/PrimaryButton.vue';
import TextInput from '@/Components/form/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

defineProps<{
  canResetPassword?: boolean;
  status?: string;
  compact?: boolean;
}>();

const emit = defineEmits<{
  success: [];
  'request-forgot-password': [];
}>();

const { t } = useI18n();

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(route('login'), {
    preserveScroll: true,
    onFinish: () => {
      form.reset('password');
    },
    onSuccess: () => {
      emit('success');
    },
  });
};
</script>

<template>
  <div>
    <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <InputLabel for="login-email" :value="t('auth.email')" />

        <TextInput
          id="login-email"
          type="email"
          class="mt-1 block w-full"
          v-model="form.email"
          required
          autofocus
          autocomplete="username"
        />

        <InputError class="mt-2" :message="form.errors.email" />
      </div>

      <div>
        <InputLabel for="login-password" :value="t('auth.password')" />

        <TextInput
          id="login-password"
          type="password"
          class="mt-1 block w-full"
          v-model="form.password"
          required
          autocomplete="current-password"
        />

        <InputError class="mt-2" :message="form.errors.password" />
      </div>

      <div class="flex items-center">
        <label class="flex items-center">
          <Checkbox name="remember" v-model:checked="form.remember" />
          <span class="ms-2 text-sm text-gray-600">{{ t('auth.remember') }}</span>
        </label>
      </div>

      <div class="flex flex-wrap items-center justify-end gap-3 pt-1">
        <button
          v-if="canResetPassword"
          type="button"
          class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
          @click="$emit('request-forgot-password')"
        >
          {{ t('auth.forgotLink') }}
        </button>

        <PrimaryButton type="submit" class="ms-auto" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          {{ t('auth.loginButton') }}
        </PrimaryButton>
      </div>
    </form>
  </div>
</template>
