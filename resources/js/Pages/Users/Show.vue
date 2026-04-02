<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

type PublicUser = {
  id: number;
  name: string;
  username: string;
  bio?: string | null;
  avatar_path?: string | null;
  avatar_focus_x?: number;
  avatar_focus_y?: number;
  avatar_zoom?: number;
  posts_count?: number;
};

defineProps<{
  profileUser: PublicUser;
}>();
</script>

<template>
  <PublicLayout>
    <Head :title="profileUser.username" />

    <section class="glass-surface p-6">
      <div class="flex items-start gap-4">
        <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-[1.75rem] border border-white/40 bg-white/55 text-2xl font-semibold text-slate-500">
          <img
            v-if="profileUser.avatar_path"
            :src="`/storage/${profileUser.avatar_path}`"
            alt=""
            class="h-full w-full object-cover"
            :style="{
              objectPosition: `${profileUser.avatar_focus_x ?? 50}% ${profileUser.avatar_focus_y ?? 50}%`,
              transform: `scale(${profileUser.avatar_zoom ?? 1})`,
            }"
          />
          <span v-else>{{ profileUser.name.slice(0, 1) }}</span>
        </div>
        <div class="min-w-0">
          <h1 class="text-2xl font-semibold">{{ profileUser.name }}</h1>
          <p class="text-sm text-gray-500">@{{ profileUser.username }}</p>
        </div>
      </div>
      <p v-if="profileUser.bio" class="mt-4 text-gray-700">{{ profileUser.bio }}</p>
      <p v-if="profileUser.posts_count !== undefined" class="mt-4 text-sm text-gray-600">
        公開投稿 {{ profileUser.posts_count }} 件
      </p>
      <div class="mt-6 flex flex-wrap gap-3">
        <Link :href="route('posts.index')" class="glass-link text-sm font-medium">投稿一覧へ</Link>
        <Link :href="route('home')" class="glass-link text-sm font-medium">ホームへ</Link>
      </div>
    </section>
  </PublicLayout>
</template>
