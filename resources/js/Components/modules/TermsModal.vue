<script setup lang="ts">
import Modal from '@/Components/container/Modal.vue';
import SecondaryButton from '@/Components/parts/SecondaryButton.vue';
import { nextTick, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineEmits<{
  (e: 'close'): void;
}>();

const props = defineProps<{
  show: boolean;
}>();

const scrollBodyRef = ref<HTMLElement | null>(null);

watch(
  () => props.show,
  async (show) => {
    if (!show) {
      return;
    }
    await nextTick();
    if (scrollBodyRef.value) {
      scrollBodyRef.value.scrollTop = 0;
    }
  },
);
</script>

<template>
  <Modal :show="props.show" max-width="xl" :title-id="'terms-title'" @close="$emit('close')">
    <div ref="scrollBodyRef" class="max-h-[80vh] overflow-y-auto p-6 sm:p-8">
      <h2 id="terms-title" class="text-xl font-semibold text-slate-800">
        {{ t('terms.title') }}
      </h2>
      <p class="mt-3 text-sm leading-relaxed text-slate-600">
        {{ t('terms.lastUpdated') }}
      </p>

      <section class="mt-6 space-y-2">
        <h3 class="text-base font-semibold text-slate-800">{{ t('terms.sections.scope.title') }}</h3>
        <p class="text-sm leading-relaxed text-slate-700">{{ t('terms.sections.scope.body') }}</p>
      </section>

      <section class="mt-6 space-y-2">
        <h3 class="text-base font-semibold text-slate-800">{{ t('terms.sections.account.title') }}</h3>
        <ul class="list-disc space-y-1 pl-5 text-sm leading-relaxed text-slate-700">
          <li>{{ t('terms.sections.account.items.0') }}</li>
          <li>{{ t('terms.sections.account.items.1') }}</li>
          <li>{{ t('terms.sections.account.items.2') }}</li>
        </ul>
      </section>

      <section class="mt-6 space-y-2">
        <h3 class="text-base font-semibold text-slate-800">{{ t('terms.sections.content.title') }}</h3>
        <ul class="list-disc space-y-1 pl-5 text-sm leading-relaxed text-slate-700">
          <li>{{ t('terms.sections.content.items.0') }}</li>
          <li>{{ t('terms.sections.content.items.1') }}</li>
          <li>{{ t('terms.sections.content.items.2') }}</li>
        </ul>
      </section>

      <section class="mt-6 space-y-2">
        <h3 class="text-base font-semibold text-slate-800">{{ t('terms.sections.images.title') }}</h3>
        <ul class="list-disc space-y-1 pl-5 text-sm leading-relaxed text-slate-700">
          <li>{{ t('terms.sections.images.items.0') }}</li>
          <li>{{ t('terms.sections.images.items.1') }}</li>
          <li>{{ t('terms.sections.images.items.2') }}</li>
        </ul>
      </section>

      <section class="mt-6 space-y-2">
        <h3 class="text-base font-semibold text-slate-800">{{ t('terms.sections.prohibited.title') }}</h3>
        <ul class="list-disc space-y-1 pl-5 text-sm leading-relaxed text-slate-700">
          <li>{{ t('terms.sections.prohibited.items.0') }}</li>
          <li>{{ t('terms.sections.prohibited.items.1') }}</li>
          <li>{{ t('terms.sections.prohibited.items.2') }}</li>
          <li>{{ t('terms.sections.prohibited.items.3') }}</li>
        </ul>
      </section>

      <section class="mt-6 space-y-2">
        <h3 class="text-base font-semibold text-slate-800">{{ t('terms.sections.ip.title') }}</h3>
        <p class="text-sm leading-relaxed text-slate-700">{{ t('terms.sections.ip.body') }}</p>
      </section>

      <section class="mt-6 space-y-2">
        <h3 class="text-base font-semibold text-slate-800">{{ t('terms.sections.disclaimer.title') }}</h3>
        <p class="text-sm leading-relaxed text-slate-700">{{ t('terms.sections.disclaimer.body') }}</p>
      </section>

      <section class="mt-6 space-y-2">
        <h3 class="text-base font-semibold text-slate-800">{{ t('terms.sections.changes.title') }}</h3>
        <p class="text-sm leading-relaxed text-slate-700">{{ t('terms.sections.changes.body') }}</p>
      </section>

      <section class="mt-6 space-y-2">
        <h3 class="text-base font-semibold text-slate-800">{{ t('terms.sections.contact.title') }}</h3>
        <p class="text-sm leading-relaxed text-slate-700">{{ t('terms.sections.contact.body') }}</p>
      </section>

      <div class="mt-8 flex justify-end">
        <SecondaryButton type="button" @click="$emit('close')">
          {{ t('common.close') }}
        </SecondaryButton>
      </div>
    </div>
  </Modal>
</template>
