<script setup lang="ts">
import InputError from '@/Components/form/InputError.vue';
import InputLabel from '@/Components/form/InputLabel.vue';
import PrimaryButton from '@/Components/parts/PrimaryButton.vue';
import TextInput from '@/Components/form/TextInput.vue';
import type { PageProps } from '@/types';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
  open: boolean;
}>();

const emit = defineEmits<{
  back: [];
}>();

const { t } = useI18n();

const page = usePage<PageProps<{ flash?: { status?: string | null } }>>();
const status = computed(() => page.props.flash?.status ?? null);

const form = useForm({
  email: '',
});

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      form.clearErrors();
      form.reset();
    }
  },
);

const submit = () => {
  form.post(route('password.email'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      form.reset();
    },
  });
};

function goBack() {
  emit('back');
}
</script>

<template>
  <div class="max-h-[min(85vh,32rem)] overflow-y-auto p-6 sm:p-8">
    <h2 id="auth-forgot-title" class="text-lg font-semibold text-slate-800">{{ t('auth.forgotTitle') }}</h2>

    <p class="mt-3 text-sm text-slate-600">
      {{ t('auth.forgotLead') }}
    </p>

    <div v-if="status" class="mt-4 text-sm font-medium text-green-600">
      {{ status }}
    </div>

    <form class="mt-4 space-y-4" @submit.prevent="submit">
      <div>
        <InputLabel for="forgot-email" :value="t('auth.email')" />

        <TextInput
          id="forgot-email"
          type="email"
          class="mt-1 block w-full"
          v-model="form.email"
          required
          autofocus
          autocomplete="username"
        />

        <InputError class="mt-2" :message="form.errors.email" />
      </div>

      <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
        <button type="button" class="text-sm font-medium text-sky-700 hover:underline" @click="goBack">
          {{ t('auth.backToLogin') }}
        </button>

        <PrimaryButton type="submit" class="ms-auto" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          {{ t('auth.forgotSubmitButton') }}
        </PrimaryButton>
      </div>
    </form>
  </div>
</template>
