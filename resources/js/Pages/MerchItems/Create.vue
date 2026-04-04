<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BandSelectModal from '@/Components/modules/BandSelectModal.vue';
import FormSelect from '@/Components/form/FormSelect.vue';
import FormTextarea from '@/Components/form/FormTextarea.vue';
import FormErrorSummary from '@/Components/form/FormErrorSummary.vue';
import InputError from '@/Components/form/InputError.vue';
import InputLabel from '@/Components/form/InputLabel.vue';
import PrimaryButton from '@/Components/parts/PrimaryButton.vue';
import TextInput from '@/Components/form/TextInput.vue';
import SeoHead from '@/Components/seo/SeoHead.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
  bands: { id: number; name: string }[];
  categories: { id: number; name: string }[];
  selectedBandId?: number | null;
}>();

const canSubmit = computed(() => props.bands.length > 0 && props.categories.length > 0);
const fileInput = ref<HTMLInputElement | null>(null);
const imagePreviews = ref<{ id: string; file: File; url: string }[]>([]);
const bandModalOpen = ref(false);

const form = useForm({
  band_id: props.selectedBandId ?? props.bands[0]?.id ?? '',
  merch_category_id: props.categories[0]?.id ?? '',
  name: '',
  description: '',
  release_year: '',
  size_note: '',
  is_official: true,
  source_type: 'user_created',
  images: [] as File[],
});

const imageErrors = computed(() =>
  Object.entries(form.errors)
    .filter(([key]) => key.startsWith('images.'))
    .map(([, message]) => String(message ?? '')),
);

const selectedBand = computed(() =>
  props.bands.find((band) => band.id === Number(form.band_id)) ?? null,
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
  <SeoHead page="merchCreate" />

  <AuthenticatedLayout>
    <template #header>
      <div>
        <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('profile.catalogEyebrow') }}</p>
        <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-800">{{ t('forms.merch.createTitle') }}</h2>
      </div>
    </template>

    <div class="mx-auto max-w-3xl">
      <div v-if="!canSubmit" class="glass-surface p-5 sm:p-6">
        <p class="text-base font-medium text-slate-800">{{ t('forms.merch.needSetup') }}</p>
        <div class="mt-4 flex flex-wrap gap-3">
          <Link :href="route('bands.create')" class="glass-link text-sm font-medium">{{ t('forms.merch.registerBand') }}</Link>
          <Link :href="route('merch-items.index')" class="glass-link text-sm font-medium">{{ t('forms.merch.backToMerchListNav') }}</Link>
        </div>
      </div>

      <form v-else @submit.prevent="submit" class="glass-surface space-y-6 p-5 sm:p-6">
        <FormErrorSummary :errors="form.errors" />
        <div class="flex flex-wrap items-center justify-between gap-3">
          <Link :href="route('merch-items.index')" class="glass-link text-sm font-medium">{{ t('forms.merch.backToMerchList') }}</Link>
          <Link :href="route('dashboard')" class="glass-link text-sm font-medium">{{ t('forms.merch.toDashboard') }}</Link>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
          <div>
            <InputLabel for="band_id" :value="t('forms.merch.band')" />
            <button
              id="band_id"
              type="button"
              class="glass-panel mt-1 flex w-full items-center justify-between rounded-2xl px-4 py-3 text-left text-slate-700 hover:bg-white/55"
              @click="bandModalOpen = true"
            >
              <span>{{ selectedBand?.name ?? t('forms.merch.chooseBand') }}</span>
              <span class="text-sm text-slate-500">{{ t('forms.merch.open') }}</span>
            </button>
            <InputError class="mt-2" :message="form.errors.band_id" />
          </div>
          <div>
            <InputLabel for="merch_category_id" :value="t('forms.merch.category')" />
            <FormSelect id="merch_category_id" v-model="form.merch_category_id" class="mt-1 block w-full">
              <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
            </FormSelect>
            <InputError class="mt-2" :message="form.errors.merch_category_id" />
          </div>
        </div>

        <div>
          <InputLabel for="name" :value="t('forms.merch.itemName')" />
          <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required autofocus />
          <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <div>
          <InputLabel for="description" :value="t('forms.merch.description')" />
          <FormTextarea id="description" v-model="form.description" rows="5" class="mt-1 block w-full" />
          <InputError class="mt-2" :message="form.errors.description" />
        </div>

        <div>
          <InputLabel for="images" :value="t('forms.merch.imagesMax')" />
          <input
            id="images"
            ref="fileInput"
            type="file"
            accept="image/png,image/jpeg,image/webp"
            multiple
            class="glass-panel mt-1 block w-full min-h-11 rounded-2xl px-4 py-2.5 text-sm text-slate-600 transition focus:border-sky-300/70 focus:outline-none focus:ring-2 focus:ring-sky-200/60 file:mr-4 file:rounded-2xl file:border-0 file:bg-white/70 file:px-4 file:py-2 file:text-sm file:font-medium file:text-slate-700"
            @change="onImagesSelected"
          />
          <p class="mt-2 text-sm text-slate-500">{{ t('forms.merch.imageHint') }}</p>
          <p v-if="imagePreviews.length" class="mt-2 text-sm text-slate-500">{{ t('forms.merch.selectedCount', { count: imagePreviews.length }) }}</p>
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
            <InputLabel for="release_year" :value="t('forms.merch.releaseYear')" />
            <TextInput id="release_year" v-model="form.release_year" type="number" class="mt-1 block w-full" min="1900" max="2100" />
            <InputError class="mt-2" :message="form.errors.release_year" />
          </div>
          <div>
            <InputLabel for="size_note" :value="t('forms.merch.sizeNote')" />
            <TextInput
              id="size_note"
              v-model="form.size_note"
              type="text"
              class="mt-1 block w-full"
              maxlength="255"
              list="merch-size-suggestions"
              autocomplete="off"
            />
            <datalist id="merch-size-suggestions">
              <option value="XS" />
              <option value="S" />
              <option value="M" />
              <option value="L" />
              <option value="XL" />
              <option value="XXL" />
              <option value="フリー" />
              <option value="S〜XL" />
              <option value="one size" />
            </datalist>
            <InputError class="mt-2" :message="form.errors.size_note" />
          </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
          <div>
            <InputLabel for="source_type" :value="t('forms.merch.sourceType')" />
            <FormSelect id="source_type" v-model="form.source_type" class="mt-1 block w-full">
              <option value="user_created">{{ t('forms.merch.sourceUser') }}</option>
              <option value="official">{{ t('forms.merch.sourceOfficialInfo') }}</option>
              <option value="imported">{{ t('forms.merch.sourceImported') }}</option>
            </FormSelect>
            <InputError class="mt-2" :message="form.errors.source_type" />
          </div>
          <label class="flex items-center gap-2 text-sm text-slate-600 sm:pt-8">
            <input v-model="form.is_official" type="checkbox" class="rounded border-white/40 text-sky-600 shadow-sm focus:ring-sky-200/60" />
            {{ t('forms.merch.officialMerch') }}
          </label>
        </div>

        <div class="flex items-center justify-end gap-3">
          <Link :href="route('merch-items.index')" class="glass-panel inline-flex min-h-11 items-center justify-center rounded-full px-5 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50/60 hover:text-rose-700">{{ t('forms.merch.cancel') }}</Link>
          <PrimaryButton type="submit" :disabled="form.processing || !canSubmit">{{ t('forms.merch.submitCreate') }}</PrimaryButton>
        </div>
      </form>
    </div>
    <BandSelectModal
      :show="bandModalOpen"
      :bands="bands"
      :selected-id="form.band_id"
      @close="bandModalOpen = false"
      @apply="(id) => { if (typeof id === 'number') form.band_id = id; }"
    />
  </AuthenticatedLayout>
</template>
