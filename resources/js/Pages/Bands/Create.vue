<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FormErrorSummary from '@/Components/FormErrorSummary.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
  countries: { id: number; name: string }[];
  genres: { id: number; name: string }[];
}>();

const form = useForm({
  name: '',
  country_id: '',
  genre_ids: [] as number[],
  links: ['', '', ''],
  description: '',
  formed_year: '',
  is_active: true,
});

const linkError = (index: number) => form.errors[`links.${index}` as keyof typeof form.errors] as string | undefined;

const submit = () => form.post(route('bands.store'));
</script>

<template>
  <Head title="バンド登録" />

  <AuthenticatedLayout>
    <template #header>
      <div>
        <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">Catalog</p>
        <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-800">バンド登録</h2>
      </div>
    </template>

    <div class="mx-auto max-w-4xl">
      <form @submit.prevent="submit" class="glass-surface space-y-6 p-5 sm:p-6">
          <FormErrorSummary :errors="form.errors" />
          <div class="flex flex-wrap items-center justify-between gap-3">
            <Link :href="route('bands.index')" class="glass-link text-sm font-medium">← バンド一覧へ戻る</Link>
            <Link :href="route('dashboard')" class="glass-link text-sm font-medium">マイページへ</Link>
          </div>

          <div>
            <InputLabel for="name" value="バンド名" />
            <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required autofocus />
            <InputError class="mt-2" :message="form.errors.name" />
          </div>

          <div class="grid gap-6 sm:grid-cols-2">
            <div>
              <InputLabel for="country_id" value="国" />
              <select id="country_id" v-model="form.country_id" class="mt-1 block w-full rounded-md">
                <option value="">未選択</option>
                <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
              </select>
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
            <select id="genre_ids" v-model="form.genre_ids" multiple class="mt-1 block min-h-40 w-full rounded-md">
              <option v-for="genre in genres" :key="genre.id" :value="genre.id">{{ genre.name }}</option>
            </select>
            <p class="mt-2 text-xs text-slate-500">Mac は Command キーで複数選択できます。</p>
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
            <textarea id="description" v-model="form.description" rows="5" class="mt-1 block w-full rounded-md" />
            <InputError class="mt-2" :message="form.errors.description" />
          </div>

          <label class="flex items-center gap-2 text-sm text-slate-600">
            <input v-model="form.is_active" type="checkbox" class="rounded border-white/40 text-sky-600 shadow-sm focus:ring-sky-200/60" />
            活動中として登録する
          </label>

          <div class="flex items-center justify-end gap-3">
            <Link :href="route('bands.index')" class="glass-panel inline-flex min-h-11 items-center justify-center rounded-full px-5 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50/60 hover:text-rose-700">キャンセル</Link>
          <PrimaryButton :disabled="form.processing">登録する</PrimaryButton>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
