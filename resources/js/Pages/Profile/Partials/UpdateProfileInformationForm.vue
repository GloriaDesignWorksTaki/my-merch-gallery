<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import FormTextarea from '@/Components/FormTextarea.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import type { AuthUser } from '@/types';
import { computed, onBeforeUnmount, ref } from 'vue';

const props = defineProps<{
  mustVerifyEmail?: boolean;
  status?: string;
  email: string;
  emailVerifiedAt: string | null;
}>();

const page = usePage<{ auth: { user: AuthUser | null } }>();
const user = page.props.auth.user;
if (!user) {
  throw new Error('Profile form requires authentication.');
}

const avatarInput = ref<HTMLInputElement | null>(null);
const avatarEditorOpen = ref(false);
const avatarPreviewUrl = ref<string | null>(user.avatar_path ? `/storage/${user.avatar_path}` : null);
const avatarDragArea = ref<HTMLElement | null>(null);
const isDraggingAvatar = ref(false);
let objectUrl: string | null = null;

const form = useForm({
  name: user.name,
  email: props.email,
  bio: user.bio ?? '',
  avatar: null as File | null,
  avatar_focus_x: user.avatar_focus_x ?? 50,
  avatar_focus_y: user.avatar_focus_y ?? 50,
  avatar_zoom: user.avatar_zoom ?? 1,
  remove_avatar: false,
});

const avatarPreviewStyle = computed(() => ({
  objectPosition: `${form.avatar_focus_x}% ${form.avatar_focus_y}%`,
  transform: `scale(${form.avatar_zoom})`,
}));

const clamp = (value: number, min: number, max: number) => Math.min(max, Math.max(min, value));

const updateAvatarFocusFromPointer = (clientX: number, clientY: number) => {
  const area = avatarDragArea.value;

  if (!area) {
    return;
  }

  const rect = area.getBoundingClientRect();
  const nextX = ((clientX - rect.left) / rect.width) * 100;
  const nextY = ((clientY - rect.top) / rect.height) * 100;

  form.avatar_focus_x = Math.round(clamp(nextX, 0, 100));
  form.avatar_focus_y = Math.round(clamp(nextY, 0, 100));
};

const revokeObjectUrl = () => {
  if (objectUrl) {
    URL.revokeObjectURL(objectUrl);
    objectUrl = null;
  }
};

const openAvatarPicker = () => {
  avatarInput.value?.click();
};

const onAvatarSelected = (event: Event) => {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];

  if (!file) {
    return;
  }

  revokeObjectUrl();
  objectUrl = URL.createObjectURL(file);
  avatarPreviewUrl.value = objectUrl;
  form.avatar = file;
  form.remove_avatar = false;
  avatarEditorOpen.value = true;
  input.value = '';
};

const removeAvatar = () => {
  revokeObjectUrl();
  avatarPreviewUrl.value = null;
  form.avatar = null;
  form.remove_avatar = true;
  form.avatar_focus_x = 50;
  form.avatar_focus_y = 50;
  form.avatar_zoom = 1;
};

const startAvatarDrag = (event: PointerEvent) => {
  if (!avatarPreviewUrl.value) {
    return;
  }

  isDraggingAvatar.value = true;
  updateAvatarFocusFromPointer(event.clientX, event.clientY);
  (event.currentTarget as HTMLElement | null)?.setPointerCapture?.(event.pointerId);
};

const moveAvatarDrag = (event: PointerEvent) => {
  if (!isDraggingAvatar.value) {
    return;
  }

  updateAvatarFocusFromPointer(event.clientX, event.clientY);
};

const endAvatarDrag = (event?: PointerEvent) => {
  if (event) {
    (event.currentTarget as HTMLElement | null)?.releasePointerCapture?.(event.pointerId);
  }

  isDraggingAvatar.value = false;
};

const submit = () => {
  form
    .transform((data) => ({
      ...data,
      _method: 'patch',
      remove_avatar: data.remove_avatar ? 1 : 0,
    }))
    .post(route('profile.update'), {
      forceFormData: true,
    });
};

onBeforeUnmount(() => {
  revokeObjectUrl();
});
</script>

<template>
  <section>
    <header>
      <h2 class="text-lg font-medium text-slate-900">
        プロフィール
      </h2>

      <p class="mt-1 text-sm text-slate-600">
        表示名、メール、自己紹介、プロフィール画像を更新できます。
      </p>
    </header>

    <form
      @submit.prevent="submit"
      class="mt-6 space-y-6"
    >
      <div>
        <InputLabel value="プロフィール画像" />
        <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-center">
          <div class="flex h-28 w-28 shrink-0 items-center justify-center overflow-hidden rounded-[1.75rem] border border-white/50 bg-white/55 text-2xl font-semibold text-slate-500 shadow-[0_10px_30px_rgba(125,166,214,0.12)]">
            <img
              v-if="avatarPreviewUrl"
              :src="avatarPreviewUrl"
              alt=""
              class="h-full w-full object-cover"
              :style="avatarPreviewStyle"
            />
            <span v-else>{{ user.name.slice(0, 1) }}</span>
          </div>
          <div class="space-y-3">
            <div class="flex flex-wrap gap-3">
              <button
                type="button"
                class="glass-panel rounded-full px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-white/70"
                @click="openAvatarPicker"
              >
                画像を選ぶ
              </button>
              <button
                v-if="avatarPreviewUrl"
                type="button"
                class="glass-panel rounded-full px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50/80"
                @click="avatarEditorOpen = true"
              >
                位置調整
              </button>
              <button
                v-if="avatarPreviewUrl"
                type="button"
                class="glass-panel rounded-full px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50/80"
                @click="removeAvatar"
              >
                削除
              </button>
            </div>
            <p class="text-sm text-slate-500">PNG / JPG / WEBP, 5MBまで。位置調整で見せたい部分を合わせられます。</p>
          </div>
        </div>
        <input
          ref="avatarInput"
          type="file"
          class="hidden"
          accept="image/png,image/jpeg,image/webp"
          @change="onAvatarSelected"
        />
        <InputError class="mt-2" :message="form.errors.avatar" />
      </div>

      <div>
        <InputLabel for="name" value="表示名" />

        <TextInput
          id="name"
          type="text"
          class="mt-1 block w-full"
          v-model="form.name"
          required
          autofocus
          autocomplete="name"
        />

        <InputError class="mt-2" :message="form.errors.name" />
      </div>

      <div>
        <InputLabel for="username" value="ユーザー名" />
        <input
          id="username"
          type="text"
          class="glass-panel mt-1 block w-full rounded-2xl border-white/50 bg-slate-100/70 px-4 py-3 text-slate-500 shadow-[0_10px_30px_rgba(125,166,214,0.12)]"
          :value="user.username"
          readonly
          autocomplete="username"
        />
        <p class="mt-2 text-sm text-slate-500">ユーザー名は固定です。変更できません。</p>
      </div>

      <div>
        <InputLabel for="email" value="メールアドレス" />

        <TextInput
          id="email"
          type="email"
          class="mt-1 block w-full"
          v-model="form.email"
          required
          autocomplete="username"
        />

        <InputError class="mt-2" :message="form.errors.email" />
      </div>

      <div>
        <InputLabel for="bio" value="自己紹介" />

        <FormTextarea
          id="bio"
          class="mt-1 block w-full"
          v-model="form.bio"
          rows="4"
        />

        <InputError class="mt-2" :message="form.errors.bio" />
      </div>

      <div v-if="mustVerifyEmail && emailVerifiedAt === null">
        <p class="mt-2 text-sm text-slate-700">
          メールアドレスが未認証です。
          <Link
            :href="route('verification.send')"
            method="post"
            as="button"
            class="rounded-md text-sm text-slate-600 underline hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-300 focus:ring-offset-2"
          >
            認証メールを再送する
          </Link>
        </p>

        <div
          v-show="status === 'verification-link-sent'"
          class="mt-2 text-sm font-medium text-emerald-600"
        >
          認証メールを再送しました。
        </div>
      </div>

      <div class="flex items-center gap-4">
        <PrimaryButton type="submit" :disabled="form.processing">更新する</PrimaryButton>

        <Transition
          enter-active-class="transition ease-in-out"
          enter-from-class="opacity-0"
          leave-active-class="transition ease-in-out"
          leave-to-class="opacity-0"
        >
          <p
            v-if="form.recentlySuccessful"
            class="text-sm text-slate-600"
          >
            保存しました。
          </p>
        </Transition>
      </div>
    </form>

    <Modal :show="avatarEditorOpen" max-width="lg" :title-id="'avatar-editor-title'" @close="avatarEditorOpen = false">
      <div class="p-6">
        <h3 id="avatar-editor-title" class="text-lg font-semibold text-slate-900">プロフィール画像の位置調整</h3>
        <p class="mt-1 text-sm text-slate-600">丸く切り抜かれる前提で、見せたい位置を合わせてください。</p>

        <div class="mt-5 flex justify-center">
          <div
            ref="avatarDragArea"
            class="relative flex h-64 w-64 items-center justify-center overflow-hidden rounded-full border border-white/50 bg-slate-100 select-none"
            :class="avatarPreviewUrl ? (isDraggingAvatar ? 'cursor-grabbing' : 'cursor-grab') : ''"
            @pointerdown="startAvatarDrag"
            @pointermove="moveAvatarDrag"
            @pointerup="endAvatarDrag"
            @pointercancel="endAvatarDrag"
            @pointerleave="endAvatarDrag"
          >
            <img
              v-if="avatarPreviewUrl"
              :src="avatarPreviewUrl"
              alt=""
              class="h-full w-full object-cover"
              :style="avatarPreviewStyle"
            />
          </div>
        </div>

        <div class="mt-6 space-y-5">
          <p class="text-sm text-slate-600">画像をドラッグして位置を調整してください。</p>
          <label class="block">
            <span class="text-sm font-medium text-slate-700">ズーム</span>
            <input v-model.number="form.avatar_zoom" type="range" min="1" max="2.5" step="0.05" class="mt-2 w-full" autofocus />
          </label>
        </div>

        <div class="mt-6 flex justify-end gap-3">
          <SecondaryButton @click="avatarEditorOpen = false">閉じる</SecondaryButton>
          <PrimaryButton type="button" @click="avatarEditorOpen = false">この位置で使う</PrimaryButton>
        </div>
      </div>
    </Modal>
  </section>
</template>
