<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
  <Head title="管理" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between gap-4">
        <div>
          <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">Admin</p>
          <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-800">管理ダッシュボード</h2>
        </div>
        <div class="glass-panel rounded-full px-4 py-2 text-sm text-slate-600">
          moderation preview
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="glass-surface p-6">
          <div class="space-y-4 text-slate-600">
            <p>管理者のみ表示。ここに一覧・モデレーション等を追加していきます。</p>
            <Link :href="route('dashboard')" class="glass-link text-sm font-medium">
              ユーザーダッシュボードへ
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
