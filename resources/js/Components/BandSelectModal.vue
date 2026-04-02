<script setup lang="ts">
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { computed, ref, watch } from 'vue';

type BandOption = {
  id: number;
  name: string;
};

const props = defineProps<{
  show: boolean;
  bands: BandOption[];
  selectedId: number | string;
}>();

const emit = defineEmits<{
  close: [];
  apply: [value: number];
}>();

const keyword = ref('');
const draftId = ref<number | null>(null);

watch(
  () => [props.show, props.selectedId] as const,
  ([show]) => {
    if (!show) {
      return;
    }

    draftId.value = props.selectedId === '' ? null : Number(props.selectedId);
    keyword.value = '';
  },
  { immediate: true },
);

const filteredBands = computed(() => {
  const normalized = keyword.value.trim().toLowerCase();

  if (normalized === '') {
    return props.bands;
  }

  return props.bands.filter((band) => band.name.toLowerCase().includes(normalized));
});

const selectedBandName = computed(
  () => props.bands.find((band) => band.id === draftId.value)?.name ?? '未選択',
);

const apply = () => {
  if (!draftId.value) {
    return;
  }

  emit('apply', draftId.value);
  emit('close');
};
</script>

<template>
  <Modal :show="show" max-width="2xl" :title-id="'band-select-title'" @close="emit('close')">
    <div class="p-6 sm:p-7">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h3 id="band-select-title" class="text-lg font-semibold text-slate-900">バンドを選択</h3>
          <p class="mt-1 text-sm text-slate-600">マーチに紐づけるバンドを1つ選んでください。</p>
        </div>
        <div class="rounded-full bg-sky-100/70 px-3 py-1 text-sm font-medium text-sky-700">{{ selectedBandName }}</div>
      </div>

      <div class="mt-5">
        <InputLabel for="band-search" value="バンド検索" />
        <TextInput id="band-search" v-model="keyword" class="mt-2 block w-full" placeholder="band name" autofocus />
      </div>

      <div class="mt-5 max-h-[28rem] overflow-y-auto rounded-3xl border border-slate-200/80 bg-slate-50/85 p-3">
        <label
          v-for="band in filteredBands"
          :key="band.id"
          class="flex cursor-pointer items-center gap-3 rounded-2xl px-3 py-3 transition hover:bg-white/45"
        >
          <input
            v-model="draftId"
            :value="band.id"
            type="radio"
            name="band-selection"
            class="h-5 w-5 border-slate-300 bg-white text-sky-600 shadow-sm focus:ring-2 focus:ring-sky-200/70"
          />
          <span class="text-sm font-medium text-slate-800">{{ band.name }}</span>
        </label>
        <p v-if="filteredBands.length === 0" class="px-3 py-6 text-sm text-slate-500">該当するバンドがありません。</p>
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <SecondaryButton type="button" @click="emit('close')">閉じる</SecondaryButton>
        <PrimaryButton type="button" :disabled="!draftId" @click="apply">このバンドにする</PrimaryButton>
      </div>
    </div>
  </Modal>
</template>
