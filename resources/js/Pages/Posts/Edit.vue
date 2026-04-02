<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FormSelect from '@/Components/FormSelect.vue';
import FormTextarea from '@/Components/FormTextarea.vue';
import FormErrorSummary from '@/Components/FormErrorSummary.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, toRef } from 'vue';
import { useMerchItemOptions } from '@/composables/useMerchItemOptions';

const props = defineProps<{
  post: {
    id: number;
    band_id: number;
    merch_item_id: number | null;
    body: string;
    visibility: string;
    images: { image_path: string }[];
  };
  bands: { id: number; name: string }[];
  merchItems: { id: number; band_id: number; name: string }[];
  returnTo: string;
}>();

const form = useForm({
  band_id: props.post.band_id,
  merch_item_id: props.post.merch_item_id ?? '',
  body: props.post.body,
  visibility: props.post.visibility,
  images: [] as File[],
});

const { merchItems, loading } = useMerchItemOptions({
  bandId: toRef(form, 'band_id'),
  merchItemId: toRef(form, 'merch_item_id'),
  initialItems: props.merchItems.map((m) => ({ id: m.id, name: m.name })),
  fetchOnMount: false,
});

const imageErrors = computed(() =>
  Object.entries(form.errors)
    .filter(([key]) => key.startsWith('images.'))
    .map(([, message]) => message),
);

const onImagesSelected = (event: Event) => {
  const input = event.target as HTMLInputElement;
  form.images = Array.from(input.files ?? []);
};

const submit = () =>
  form.patch(route('posts.update', props.post.id), {
    forceFormData: true,
  });
</script>

<template>
  <Head :title="`投稿 #${post.id} を編集`" />

  <AuthenticatedLayout>
    <template #header>
      <div>
        <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">Community</p>
        <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-800">投稿編集</h2>
      </div>
    </template>

    <div class="mx-auto max-w-3xl">
      <form @submit.prevent="submit" class="glass-surface space-y-6 p-5 sm:p-6">
        <FormErrorSummary :errors="form.errors" />
        <div class="flex flex-wrap items-center justify-between gap-3">
          <Link :href="returnTo" class="glass-link text-sm font-medium">← 投稿詳細へ戻る</Link>
          <Link :href="route('posts.index')" class="glass-link text-sm font-medium">投稿一覧へ</Link>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
          <div>
            <InputLabel for="band_id" value="バンド" />
            <FormSelect id="band_id" v-model="form.band_id" class="mt-1 block w-full">
              <option v-for="bandOption in bands" :key="bandOption.id" :value="bandOption.id">{{ bandOption.name }}</option>
            </FormSelect>
            <InputError class="mt-2" :message="form.errors.band_id" />
          </div>
          <div>
            <InputLabel for="merch_item_id" value="マーチ（任意）" />
            <FormSelect
              id="merch_item_id"
              v-model="form.merch_item_id"
              class="mt-1 block w-full"
              :disabled="loading"
            >
              <option value="">未選択</option>
              <option v-for="item in merchItems" :key="item.id" :value="item.id">{{ item.name }}</option>
            </FormSelect>
            <InputError class="mt-2" :message="form.errors.merch_item_id" />
          </div>
        </div>

        <div>
          <InputLabel for="body" value="本文" />
          <FormTextarea id="body" v-model="form.body" rows="8" class="mt-1 block w-full" required />
          <InputError class="mt-2" :message="form.errors.body" />
        </div>

        <div class="space-y-4">
          <div>
            <InputLabel for="images" value="画像を差し替え（任意）" />
            <input
              id="images"
              type="file"
              accept="image/png,image/jpeg,image/webp"
              multiple
              class="mt-1 block w-full text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-white/70 file:px-4 file:py-2 file:text-sm file:font-medium file:text-slate-700"
              @change="onImagesSelected"
            />
            <p class="mt-2 text-sm text-slate-500">新しい画像を選ぶと、今の画像をまとめて差し替えます。</p>
            <p v-if="form.images.length" class="mt-2 text-sm text-slate-500">{{ form.images.length }}枚選択中</p>
            <InputError class="mt-2" :message="form.errors.images" />
            <InputError
              v-for="(error, index) in imageErrors"
              :key="index"
              class="mt-2"
              :message="error"
            />
          </div>

          <div v-if="post.images.length">
            <p class="text-sm font-medium text-slate-700">現在の画像</p>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
              <img
                v-for="(image, index) in post.images"
                :key="index"
                :src="`/storage/${image.image_path}`"
                alt=""
                class="max-h-48 w-full rounded-2xl object-cover"
              />
            </div>
          </div>
        </div>

        <div>
          <InputLabel for="visibility" value="公開範囲" />
          <FormSelect id="visibility" v-model="form.visibility" class="mt-1 block w-full">
            <option value="public">公開</option>
            <option value="unlisted">限定公開</option>
            <option value="private">非公開</option>
          </FormSelect>
          <InputError class="mt-2" :message="form.errors.visibility" />
        </div>

        <div class="flex items-center justify-end gap-3">
          <Link :href="returnTo" class="glass-panel inline-flex min-h-11 items-center justify-center rounded-full px-5 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50/60 hover:text-rose-700">キャンセル</Link>
          <PrimaryButton type="submit" :disabled="form.processing">更新する</PrimaryButton>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
