<script setup lang="ts">
import InputError from '@/Components/form/InputError.vue';
import InputLabel from '@/Components/form/InputLabel.vue';
import PrimaryButton from '@/Components/parts/PrimaryButton.vue';
import TextInput from '@/Components/form/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const emit = defineEmits<{
  success: [];
  switchToLogin: [];
}>();

const { t } = useI18n();

const form = useForm({
  name: '',
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(route('register'), {
    preserveScroll: true,
    onFinish: () => {
      form.reset('password', 'password_confirmation');
    },
    onSuccess: () => {
      emit('success');
    },
  });
};
</script>

<template>
  <form @submit.prevent="submit" class="space-y-4">
    <div>
      <InputLabel for="register-name" :value="t('auth.name')" />

      <TextInput
        id="register-name"
        type="text"
        class="mt-1 block w-full"
        v-model="form.name"
        required
        autofocus
        autocomplete="name"
      />

      <InputError class="mt-2" :message="form.errors.name" />
    </div>

    <div>
      <InputLabel for="register-username" :value="t('auth.username')" />

      <TextInput
        id="register-username"
        type="text"
        class="mt-1 block w-full"
        v-model="form.username"
        required
        autocomplete="username"
      />

      <p class="mt-1 text-xs text-gray-500">{{ t('auth.usernameHint') }}</p>

      <InputError class="mt-2" :message="form.errors.username" />
    </div>

    <div>
      <InputLabel for="register-email" :value="t('auth.email')" />

      <TextInput
        id="register-email"
        type="email"
        class="mt-1 block w-full"
        v-model="form.email"
        required
        autocomplete="username"
      />

      <InputError class="mt-2" :message="form.errors.email" />
    </div>

    <div>
      <InputLabel for="register-password" :value="t('auth.password')" />

      <TextInput
        id="register-password"
        type="password"
        class="mt-1 block w-full"
        v-model="form.password"
        required
        autocomplete="new-password"
      />

      <InputError class="mt-2" :message="form.errors.password" />
    </div>

    <div>
      <InputLabel for="register-password_confirmation" :value="t('auth.confirmPassword')" />

      <TextInput
        id="register-password_confirmation"
        type="password"
        class="mt-1 block w-full"
        v-model="form.password_confirmation"
        required
        autocomplete="new-password"
      />

      <InputError class="mt-2" :message="form.errors.password_confirmation" />
    </div>

    <div class="flex flex-wrap items-center justify-end gap-3 pt-1">
      <button
        type="button"
        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        @click="emit('switchToLogin')"
      >
        {{ t('auth.alreadyRegistered') }}
      </button>

      <PrimaryButton type="submit" class="ms-auto" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
        {{ t('auth.registerButton') }}
      </PrimaryButton>
    </div>
  </form>
</template>
