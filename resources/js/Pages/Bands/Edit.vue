<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FormErrorSummary from '@/Components/FormErrorSummary.vue';
import FormSelect from '@/Components/FormSelect.vue';
import FormTextarea from '@/Components/FormTextarea.vue';
import GenreMultiSelectModal from '@/Components/GenreMultiSelectModal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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

const submit = () => form.patch(route('bands.update', props.band.slug ?? props.band.id));
</script>

<template>
  <Head :title="`${band.name} を編集`" />

  <AuthenticatedLayout>
    <template #header>
      <div>
        <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">Catalog</p>
        <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-800">バンド編集</h2>
      </div>
    </template>

    <div class="mx-auto max-w-4xl">
      <form @submit.prevent="submit" class="glass-surface space-y-6 p-5 sm:p-6">
        <FormErrorSummary :errors="form.errors" />
        <div class="flex flex-wrap items-center justify-between gap-3">
          <Link :href="route('bands.show', band.slug ?? band.id)" class="glass-link text-sm font-medium">← バンド詳細へ戻る</Link>
          <Link :href="route('bands.index')" class="glass-link text-sm font-medium">バンド一覧へ</Link>
        </div>

        <div>
          <InputLabel for="name" value="バンド名" />
          <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required autofocus />
          <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
          <div>
            <InputLabel for="country_id" value="国" />
            <FormSelect id="country_id" v-model="form.country_id" class="mt-1 block w-full">
              <option value="">未選択</option>
              <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
            </FormSelect>
            <InputError class="mt-2" :message="form.errors.country_id" />
          </div>
          <div>
            <InputLabel for="formed_year" value="結成年" />
            <TextInput id="formed_year" v-model="form.formed_year" type="number" class="mt-1 block w-full" min="1900" max="2100" />
            <InputError class="mt-2" :message="form.errors.formed_year" />
          </div>
        </div>

        <div>
          <InputLabel for="genre_ids" value="ジャンル（複数選択可）" />
          <button
            id="genre_ids"
            type="button"
            class="glass-panel mt-1 flex w-full items-center justify-between rounded-2xl px-4 py-3 text-left text-slate-700 hover:bg-white/55"
            @click="genreModalOpen = true"
          >
            <span>{{ selectedGenres.length ? `${selectedGenres.length}件のジャンルを選択中` : 'ジャンルを選ぶ' }}</span>
            <span class="text-sm text-slate-500">開く</span>
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
            <InputLabel :for="`link-${index}`" :value="`リンク ${index + 1}`" />
            <TextInput :id="`link-${index}`" v-model="form.links[index]" class="mt-1 block w-full" placeholder="https://" />
            <InputError class="mt-2" :message="linkError(index)" />
          </div>
          <p class="text-xs text-slate-500">公式サイト、SNS、配信リンクなどを最大3件まで登録できます。</p>
        </div>

        <div>
          <InputLabel for="description" value="説明" />
          <FormTextarea id="description" v-model="form.description" rows="5" class="mt-1 block w-full" />
          <InputError class="mt-2" :message="form.errors.description" />
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
          <input v-model="form.is_active" type="checkbox" class="rounded border-white/40 text-sky-600 shadow-sm focus:ring-sky-200/60" />
          活動中として表示する
        </label>

        <div class="flex items-center justify-end gap-3">
          <Link :href="route('bands.show', band.slug ?? band.id)" class="glass-panel inline-flex min-h-11 items-center justify-center rounded-full px-5 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50/60 hover:text-rose-700">キャンセル</Link>
          <PrimaryButton type="submit" :disabled="form.processing">更新する</PrimaryButton>
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
