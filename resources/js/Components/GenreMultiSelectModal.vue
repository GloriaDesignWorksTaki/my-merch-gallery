<script setup lang="ts">
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { computed, ref, watch } from 'vue';

type GenreOption = {
  id: number;
  name: string;
};

const props = defineProps<{
  show: boolean;
  genres: GenreOption[];
  selectedIds: number[];
}>();

const emit = defineEmits<{
  close: [];
  apply: [value: number[]];
}>();

const keyword = ref('');
const draftIds = ref<number[]>([]);

watch(
  () => [props.show, props.selectedIds] as const,
  ([show]) => {
    if (!show) {
      return;
    }

    draftIds.value = [...props.selectedIds];
    keyword.value = '';
  },
  { immediate: true },
);

const filteredGenres = computed(() => {
  const normalized = keyword.value.trim().toLowerCase();

  if (normalized === '') {
    return props.genres;
  }

  return props.genres.filter((genre) => genre.name.toLowerCase().includes(normalized));
});

const toggleGenre = (id: number) => {
  draftIds.value = draftIds.value.includes(id)
    ? draftIds.value.filter((value) => value !== id)
    : [...draftIds.value, id];
};

const selectedLabels = computed(() =>
  props.genres
    .filter((genre) => draftIds.value.includes(genre.id))
    .map((genre) => genre.name),
);

const apply = () => {
  emit('apply', [...draftIds.value].sort((a, b) => a - b));
  emit('close');
};
</script>

<template>
  <Modal :show="show" max-width="2xl" :title-id="'genre-multi-select-title'" @close="emit('close')">
    <div class="p-6 sm:p-7">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h3 id="genre-multi-select-title" class="text-lg font-semibold text-slate-900">ジャンルを選択</h3>
          <p class="mt-1 text-sm text-slate-600">複数選択できます。チェックを付けたジャンルが保存されます。</p>
        </div>
        <div class="rounded-full bg-sky-100/70 px-3 py-1 text-sm font-medium text-sky-700">{{ draftIds.length }}件選択中</div>
      </div>

      <div class="mt-5">
        <InputLabel for="genre-search" value="ジャンル検索" />
        <TextInput
          id="genre-search"
          v-model="keyword"
          class="mt-2 block w-full"
          placeholder="emo / grunge / indie など"
          autofocus
        />
      </div>

      <div class="mt-4 min-h-[3.5rem] rounded-2xl border border-transparent px-1 py-1">
        <div v-if="selectedLabels.length" class="flex flex-wrap gap-2">
          <span
            v-for="label in selectedLabels"
            :key="label"
            class="rounded-full bg-white/70 px-3 py-1 text-sm text-slate-700 shadow-sm"
          >
            {{ label }}
          </span>
        </div>
      </div>

      <div class="mt-5 max-h-[28rem] overflow-y-auto rounded-3xl border border-slate-200/80 bg-slate-50/85 p-3">
        <label
          v-for="genre in filteredGenres"
          :key="genre.id"
          class="flex cursor-pointer items-center gap-3 rounded-2xl px-3 py-3 transition hover:bg-white/45"
        >
          <input
            :checked="draftIds.includes(genre.id)"
            type="checkbox"
            class="h-5 w-5 rounded border-slate-300 bg-white text-sky-600 shadow-sm focus:ring-2 focus:ring-sky-200/70"
            @change="toggleGenre(genre.id)"
          />
          <span class="text-sm font-medium text-slate-800">{{ genre.name }}</span>
        </label>
        <p v-if="filteredGenres.length === 0" class="px-3 py-6 text-sm text-slate-500">該当するジャンルがありません。</p>
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <SecondaryButton type="button" @click="emit('close')">閉じる</SecondaryButton>
        <PrimaryButton type="button" @click="apply">この内容で反映</PrimaryButton>
      </div>
    </div>
  </Modal>
</template>
