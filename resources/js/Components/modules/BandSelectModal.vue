<script setup lang="ts">
import InputLabel from '@/Components/form/InputLabel.vue';
import Modal from '@/Components/container/Modal.vue';
import PrimaryButton from '@/Components/parts/PrimaryButton.vue';
import SecondaryButton from '@/Components/parts/SecondaryButton.vue';
import TextInput from '@/Components/form/TextInput.vue';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

type BandOption = {
  id: number;
  name: string;
};

const props = withDefaults(
  defineProps<{
    show: boolean;
    bands: BandOption[];
    /** 作成・編集（単一ラジオ） */
    selectedId: number | string;
    /** 一覧フィルタ（複数チェック） */
    selectedIds?: number[];
    filterMode?: boolean;
  }>(),
  {
    filterMode: false,
    selectedIds: () => [],
  },
);

const emit = defineEmits<{
  close: [];
  /** フィルタ: number[]（空=全バンド） / 作成・編集: number | null */
  apply: [value: number | null | number[]];
}>();

const keyword = ref('');
const draftId = ref<number | null>(null);
const draftIds = ref<number[]>([]);

watch(
  () => [props.show, props.selectedId, props.selectedIds, props.filterMode] as const,
  ([show]) => {
    if (!show) {
      return;
    }

    if (props.filterMode) {
      const raw = props.selectedIds ?? [];
      draftIds.value = [...raw].sort((a, b) => a - b);
      draftId.value = null;
    } else {
      draftId.value = props.selectedId === '' ? null : Number(props.selectedId);
    }
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

const selectedBandName = computed(() => {
  if (props.filterMode) {
    const ids = draftIds.value;
    if (ids.length === 0) {
      return t('merch.allBands');
    }
    if (ids.length === 1) {
      return props.bands.find((band) => band.id === ids[0])?.name ?? t('common.noneSelected');
    }
    return t('modals.bandSelect.selectedCount', { count: ids.length });
  }
  if (draftId.value === null || draftId.value === undefined) {
    return t('common.noneSelected');
  }
  return props.bands.find((band) => band.id === draftId.value)?.name ?? t('common.noneSelected');
});

function toggleBand(id: number) {
  const set = new Set(draftIds.value);
  if (set.has(id)) {
    set.delete(id);
  } else {
    set.add(id);
  }
  draftIds.value = [...set].sort((a, b) => a - b);
}

function isChecked(id: number) {
  return draftIds.value.includes(id);
}

function clearFilterSelection() {
  draftIds.value = [];
}

const apply = () => {
  if (props.filterMode) {
    emit('apply', [...draftIds.value]);
    emit('close');
    return;
  }

  if (draftId.value === null || draftId.value === undefined) {
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
          <h3 id="band-select-title" class="text-lg font-semibold text-slate-900">{{ t('modals.bandSelect.title') }}</h3>
          <p class="mt-1 text-sm text-slate-600">
            {{ filterMode ? t('modals.bandSelect.filterLead') : t('modals.bandSelect.lead') }}
          </p>
        </div>
        <div class="rounded-full bg-sky-100/70 px-3 py-1 text-sm font-medium text-sky-700">{{ selectedBandName }}</div>
      </div>

      <div class="mt-5">
        <InputLabel for="band-search" :value="t('modals.bandSelect.searchLabel')" />
        <TextInput id="band-search" v-model="keyword" class="mt-2 block w-full" :placeholder="t('modals.bandSelect.searchPlaceholder')" autofocus />
      </div>

      <div class="mt-5 max-h-[28rem] overflow-y-auto rounded-3xl border border-slate-200/80 bg-slate-50/85 p-3">
        <template v-if="filterMode">
          <label
            v-for="band in filteredBands"
            :key="band.id"
            class="flex cursor-pointer items-center gap-3 rounded-2xl px-3 py-3 transition hover:bg-white/45"
          >
            <input
              type="checkbox"
              class="h-5 w-5 rounded border-slate-300 bg-white text-sky-600 shadow-sm focus:ring-2 focus:ring-sky-200/70"
              :checked="isChecked(band.id)"
              @change="toggleBand(band.id)"
            />
            <span class="text-sm font-medium text-slate-800">{{ band.name }}</span>
          </label>
        </template>
        <template v-else>
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
        </template>
        <p v-if="filteredBands.length === 0" class="px-3 py-6 text-sm text-slate-500">{{ t('modals.bandSelect.empty') }}</p>
      </div>

      <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
        <SecondaryButton v-if="filterMode && draftIds.length > 0" type="button" @click="clearFilterSelection">
          {{ t('modals.bandSelect.clearSelection') }}
        </SecondaryButton>
        <SecondaryButton type="button" @click="emit('close')">{{ t('common.close') }}</SecondaryButton>
        <PrimaryButton
          type="button"
          :disabled="!filterMode && (draftId === null || draftId === undefined)"
          @click="apply"
        >
          {{ filterMode ? t('modals.bandSelect.applyFilter') : t('modals.bandSelect.apply') }}
        </PrimaryButton>
      </div>
    </div>
  </Modal>
</template>
