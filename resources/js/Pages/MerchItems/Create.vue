<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FormErrorSummary from '@/Components/FormErrorSummary.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';

const props = defineProps<{
  bands: { id: number; name: string }[];
  categories: { id: number; name: string }[];
  selectedBandId?: number | null;
}>();

const canSubmit = computed(() => props.bands.length > 0 && props.categories.length > 0);
const fileInput = ref<HTMLInputElement | null>(null);
const imagePreviews = ref<{ id: string; file: File; url: string }[]>([]);

const form = useForm({
  band_id: props.selectedBandId ?? props.bands[0]?.id ?? '',
  merch_category_id: props.categories[0]?.id ?? '',
  name: '',
  description: '',
  release_year: '',
  era_label: '',
  is_official: true,
  source_type: 'user_created',
  images: [] as File[],
});

const imageErrors = computed(() =>
  Object.entries(form.errors)
    .filter(([key]) => key.startsWith('images.'))
    .map(([, message]) => message),
);

const onImagesSelected = (event: Event) => {
  const input = event.target as HTMLInputElement;
  const selectedFiles = Array.from(input.files ?? []);
  const remainingSlots = Math.max(0, 4 - imagePreviews.value.length);
  const filesToAdd = selectedFiles.slice(0, remainingSlots);

  filesToAdd.forEach((file, index) => {
    imagePreviews.value.push({
      id: `${file.name}-${file.lastModified}-${index}`,
      file,
      url: URL.createObjectURL(file),
    });
  });

  form.images = imagePreviews.value.map((preview) => preview.file);

  if (input) {
    input.value = '';
  }
};

const removeImage = (id: string) => {
  const preview = imagePreviews.value.find((item) => item.id === id);

  if (preview) {
    URL.revokeObjectURL(preview.url);
  }

  imagePreviews.value = imagePreviews.value.filter((item) => item.id !== id);
  form.images = imagePreviews.value.map((item) => item.file);
};

onBeforeUnmount(() => {
  imagePreviews.value.forEach((preview) => URL.revokeObjectURL(preview.url));
});

const submit = () =>
  form.post(route('merch-items.store'), {
    forceFormData: true,
  });
</script>

<template>
  <Head title="マーチ登録" />

  <AuthenticatedLayout>
    <template #header>
      <div>
        <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">Catalog</p>
        <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-800">マーチ登録</h2>
      </div>
    </template>

    <div class="mx-auto max-w-3xl">
      <div v-if="!canSubmit" class="glass-surface p-5 sm:p-6">
          <p class="text-base font-medium text-slate-800">マーチ登録にはバンドとカテゴリの準備が必要です。</p>
          <div class="mt-4 flex flex-wrap gap-3">
            <Link :href="route('bands.create')" class="glass-link text-sm font-medium">バンドを登録する</Link>
            <Link :href="route('merch-items.index')" class="glass-link text-sm font-medium">マーチ一覧へ戻る</Link>
          </div>
      </div>

      <form v-else @submit.prevent="submit" class="glass-surface space-y-6 p-5 sm:p-6">
          <FormErrorSummary :errors="form.errors" />
          <div class="flex flex-wrap items-center justify-between gap-3">
            <Link :href="route('merch-items.index')" class="glass-link text-sm font-medium">← マーチ一覧へ戻る</Link>
            <Link :href="route('dashboard')" class="glass-link text-sm font-medium">マイページへ</Link>
          </div>

          <div class="grid gap-6 sm:grid-cols-2">
            <div>
              <InputLabel for="band_id" value="バンド" />
              <select id="band_id" v-model="form.band_id" class="mt-1 block w-full rounded-md">
                <option v-for="bandOption in bands" :key="bandOption.id" :value="bandOption.id">{{ bandOption.name }}</option>
              </select>
              <InputError class="mt-2" :message="form.errors.band_id" />
            </div>
            <div>
              <InputLabel for="merch_category_id" value="カテゴリ" />
              <select id="merch_category_id" v-model="form.merch_category_id" class="mt-1 block w-full rounded-md">
                <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
              </select>
              <InputError class="mt-2" :message="form.errors.merch_category_id" />
            </div>
          </div>

          <div>
            <InputLabel for="name" value="アイテム名" />
            <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required autofocus />
            <InputError class="mt-2" :message="form.errors.name" />
          </div>

          <div>
            <InputLabel for="description" value="説明" />
            <textarea id="description" v-model="form.description" rows="5" class="mt-1 block w-full rounded-md" />
            <InputError class="mt-2" :message="form.errors.description" />
          </div>

          <div>
            <InputLabel for="images" value="画像（最大4枚）" />
            <input
              id="images"
              ref="fileInput"
              type="file"
              accept="image/png,image/jpeg,image/webp"
              multiple
              class="mt-1 block w-full text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-white/70 file:px-4 file:py-2 file:text-sm file:font-medium file:text-slate-700"
              @change="onImagesSelected"
            />
            <p class="mt-2 text-sm text-slate-500">JPEG / PNG / WebP を4枚までアップロードできます。</p>
            <p v-if="imagePreviews.length" class="mt-2 text-sm text-slate-500">{{ imagePreviews.length }}枚選択中</p>
            <div v-if="imagePreviews.length" class="mt-4 grid gap-3 sm:grid-cols-2">
              <div v-for="preview in imagePreviews" :key="preview.id" class="relative overflow-hidden rounded-2xl border border-white/40 bg-white/30">
                <img :src="preview.url" alt="" class="h-40 w-full object-cover" />
                <button
                  type="button"
                  class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-slate-900/70 text-lg text-white transition hover:bg-slate-900"
                  @click="removeImage(preview.id)"
                >
                  ×
                </button>
              </div>
            </div>
            <InputError class="mt-2" :message="form.errors.images" />
            <InputError
              v-for="(error, index) in imageErrors"
              :key="index"
              class="mt-2"
              :message="error"
            />
          </div>

          <div class="grid gap-6 sm:grid-cols-2">
            <div>
              <InputLabel for="release_year" value="リリース年" />
              <TextInput id="release_year" v-model="form.release_year" type="number" class="mt-1 block w-full" min="1900" max="2100" />
              <InputError class="mt-2" :message="form.errors.release_year" />
            </div>
            <div>
              <InputLabel for="era_label" value="時期ラベル" />
              <TextInput id="era_label" v-model="form.era_label" class="mt-1 block w-full" placeholder="AM Tour / 2010s など" />
              <InputError class="mt-2" :message="form.errors.era_label" />
            </div>
          </div>

          <div class="grid gap-6 sm:grid-cols-2">
            <div>
              <InputLabel for="source_type" value="登録ソース" />
              <select id="source_type" v-model="form.source_type" class="mt-1 block w-full rounded-md">
                <option value="user_created">ユーザー登録</option>
                <option value="official">公式情報</option>
                <option value="imported">外部取込</option>
              </select>
              <InputError class="mt-2" :message="form.errors.source_type" />
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600 sm:pt-8">
              <input v-model="form.is_official" type="checkbox" class="rounded border-white/40 text-sky-600 shadow-sm focus:ring-sky-200/60" />
              公式マーチ
            </label>
          </div>

          <div class="flex items-center justify-end gap-3">
            <Link :href="route('merch-items.index')" class="glass-panel inline-flex min-h-11 items-center justify-center rounded-full px-5 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50/60 hover:text-rose-700">キャンセル</Link>
            <PrimaryButton :disabled="form.processing || !canSubmit">登録する</PrimaryButton>
          </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
