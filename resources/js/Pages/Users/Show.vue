<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

type PublicUser = {
  id: number;
  name: string;
  username: string;
  bio?: string | null;
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
      <h1 class="text-2xl font-semibold">{{ profileUser.name }}</h1>
      <p class="text-sm text-gray-500">@{{ profileUser.username }}</p>
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
