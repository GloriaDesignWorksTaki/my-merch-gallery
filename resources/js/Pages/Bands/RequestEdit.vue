<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FormErrorSummary from '@/Components/form/FormErrorSummary.vue';
import FormSelect from '@/Components/form/FormSelect.vue';
import FormTextarea from '@/Components/form/FormTextarea.vue';
import GenreMultiSelectModal from '@/Components/modules/GenreMultiSelectModal.vue';
import InputError from '@/Components/form/InputError.vue';
import InputLabel from '@/Components/form/InputLabel.vue';
import PrimaryButton from '@/Components/parts/PrimaryButton.vue';
import TextInput from '@/Components/form/TextInput.vue';
import SeoHead from '@/Components/seo/SeoHead.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
  band: {
    id: number;
    name: string;
    country_id: number | null;
    genre_ids: number[];
    links: string[];
    description: string | null;
    formed_year: number | null;
    is_active: boolean;
    slug?: string;
  };
  countries: { id: number; name: string }[];
  genres: { id: number; name: string }[];
}>();

const form = useForm({
  name: props.band.name,
  country_id: props.band.country_id ?? '',
  genre_ids: props.band.genre_ids,
  links: [...props.band.links],
  description: props.band.description ?? '',
  formed_year: props.band.formed_year ? String(props.band.formed_year) : '',
  is_active: props.band.is_active,
});

const genreModalOpen = ref(false);

const linkError = (index: number) => form.errors[`links.${index}` as keyof typeof form.errors] as string | undefined;

const selectedGenres = computed(() =>
  props.genres.filter((genre) => form.genre_ids.includes(genre.id)),
);

const submit = () => form.post(route('bands.edit-request.store', props.band.slug ?? props.band.id));
</script>

<template>
  <SeoHead page="bandsRequestEdit" :params="{ name: band.name }" />

  <AuthenticatedLayout>
    <template #header>
      <div>
        <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('merch.catalogEyebrow') }}</p>
        <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-800">{{ t('forms.band.requestEditTitle') }}</h2>
        <p class="mt-2 text-sm text-slate-600">{{ t('forms.band.requestEditLead') }}</p>
      </div>
    </template>

    <div class="mx-auto max-w-4xl">
      <form @submit.prevent="submit" class="glass-surface space-y-6 p-5 sm:p-6">
        <FormErrorSummary :errors="form.errors" />
        <div class="flex flex-wrap items-center justify-between gap-3">
          <Link :href="route('bands.show', band.slug ?? band.id)" class="glass-link text-sm font-medium">{{ t('forms.band.backToBandDetail') }}</Link>
          <Link :href="route('bands.index')" class="glass-link text-sm font-medium">{{ t('forms.band.toBandsIndex') }}</Link>
        </div>

        <div>
          <InputLabel for="name" :value="t('forms.band.name')" />
          <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required autofocus />
          <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
          <div>
            <InputLabel for="country_id" :value="t('forms.band.country')" />
            <FormSelect id="country_id" v-model="form.country_id" class="mt-1 block w-full">
              <option value="">{{ t('forms.post.unselected') }}</option>
              <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
            </FormSelect>
            <InputError class="mt-2" :message="form.errors.country_id" />
          </div>
          <div>
            <InputLabel for="formed_year" :value="t('forms.band.formedYear')" />
            <TextInput id="formed_year" v-model="form.formed_year" type="number" class="mt-1 block w-full" min="1900" max="2100" />
            <InputError class="mt-2" :message="form.errors.formed_year" />
          </div>
        </div>

        <div>
          <InputLabel for="genre_ids" :value="t('forms.band.genreMulti')" />
          <button
            id="genre_ids"
            type="button"
            class="glass-panel mt-1 flex w-full items-center justify-between rounded-2xl px-4 py-3 text-left text-slate-700 hover:bg-white/55"
            @click="genreModalOpen = true"
          >
            <span>{{ selectedGenres.length ? t('forms.band.genresSelected', { count: selectedGenres.length }) : t('forms.band.chooseGenres') }}</span>
            <span class="text-sm text-slate-500">{{ t('forms.merch.open') }}</span>
          </button>
          <div v-if="selectedGenres.length" class="mt-3 flex flex-wrap gap-2">
            <span
              v-for="genre in selectedGenres"
              :key="genre.id"
              class="rounded-full bg-white/70 px-3 py-1 text-sm text-slate-700 shadow-sm"
            >
              {{ genre.name }}
            </span>
          </div>
          <InputError class="mt-2" :message="form.errors.genre_ids" />
        </div>

        <div class="grid gap-4">
          <div v-for="(_, index) in form.links" :key="index">
            <InputLabel :for="`link-${index}`" :value="t('forms.band.linkN', { n: index + 1 })" />
            <TextInput :id="`link-${index}`" v-model="form.links[index]" class="mt-1 block w-full" placeholder="https://" />
            <InputError class="mt-2" :message="linkError(index)" />
          </div>
          <p class="text-xs text-slate-500">{{ t('forms.band.linksHint') }}</p>
        </div>

        <div>
          <InputLabel for="description" :value="t('forms.band.description')" />
          <FormTextarea id="description" v-model="form.description" rows="5" class="mt-1 block w-full" />
          <InputError class="mt-2" :message="form.errors.description" />
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
          <input v-model="form.is_active" type="checkbox" class="rounded border-white/40 text-sky-600 shadow-sm focus:ring-sky-200/60" />
          {{ t('forms.band.activeDisplay') }}
        </label>

        <div class="flex items-center justify-end gap-3">
          <Link :href="route('bands.show', band.slug ?? band.id)" class="glass-panel inline-flex min-h-11 items-center justify-center rounded-full px-5 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50/60 hover:text-rose-700">{{ t('forms.band.cancel') }}</Link>
          <PrimaryButton type="submit" :disabled="form.processing">{{ t('forms.band.submitRequest') }}</PrimaryButton>
        </div>
      </form>
    </div>
    <GenreMultiSelectModal
      :show="genreModalOpen"
      :genres="genres"
      :selected-ids="form.genre_ids"
      @close="genreModalOpen = false"
      @apply="form.genre_ids = $event"
    />
  </AuthenticatedLayout>
</template>
