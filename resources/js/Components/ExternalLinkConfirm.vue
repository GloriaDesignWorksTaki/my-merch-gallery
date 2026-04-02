<script setup lang="ts">
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { isExternalHttpUrl, isSafeHttpUrl } from '@/utils/url';
import { computed, ref } from 'vue';

const props = withDefaults(
  defineProps<{
    href: string;
    /** トリガー（リンク／ボタン）に付与するクラス */
    linkClass?: string;
  }>(),
  {
    linkClass:
      'glass-panel block min-w-0 max-w-full break-all rounded-2xl px-4 py-3 text-left text-sm font-medium text-sky-700 hover:bg-white/55',
  },
);

const show = ref(false);

const safeUrl = computed(() => isSafeHttpUrl(props.href));
const needsExternalConfirm = computed(() => isExternalHttpUrl(props.href));

const confirmOpen = () => {
  window.open(props.href, '_blank', 'noopener,noreferrer');
  show.value = false;
};
</script>

<template>
  <!-- 不正なスキームはリンクにしない -->
  <span
    v-if="!safeUrl"
    :class="[linkClass, 'cursor-not-allowed opacity-60']"
    v-bind="$attrs"
    title="有効な http(s) の URL ではありません"
    :aria-disabled="true"
    tabindex="-1"
    role="text"
  >
    <slot />
  </span>
  <!-- 同一サイト内の http(s) -->
  <a
    v-else-if="!needsExternalConfirm"
    :href="href"
    target="_blank"
    rel="noopener noreferrer"
    :class="linkClass"
    v-bind="$attrs"
  >
    <slot />
  </a>
  <template v-else>
    <button
      type="button"
      :class="[linkClass, 'cursor-pointer']"
      @click="show = true"
      v-bind="$attrs"
    >
      <slot />
    </button>
    <Modal :show="show" max-width="md" :title-id="'external-link-confirm-title'" @close="show = false">
      <div class="p-6 sm:p-8">
        <h2 id="external-link-confirm-title" class="text-lg font-semibold text-slate-800">外部サイトを開きます</h2>
        <p class="mt-3 text-sm leading-relaxed text-slate-600">
          別のウェブサイトへ移動します（新しいタブで開きます）。それでも開きますか？
        </p>
        <p class="mt-4 break-all rounded-xl bg-slate-50/90 px-3 py-2 text-sm text-sky-800">{{ href }}</p>
        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
          <SecondaryButton type="button" autofocus @click="show = false">キャンセル</SecondaryButton>
          <PrimaryButton type="button" @click="confirmOpen">開く</PrimaryButton>
        </div>
      </div>
    </Modal>
  </template>
</template>
