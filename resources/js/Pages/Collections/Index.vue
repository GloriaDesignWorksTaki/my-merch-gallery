<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
  <Head title="コレクション" />

  <AuthenticatedLayout>
    <template #header>
      <div>
        <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">Archive</p>
        <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-800">コレクション</h2>
      </div>
    </template>

    <div class="mx-auto max-w-3xl">
      <div class="glass-surface p-6 text-slate-600">
        MVP では <code class="rounded bg-gray-100 px-1">collections</code> テーブルは未作成です。次フェーズで所有リストをここに接続します。
      </div>
    </div>
  </AuthenticatedLayout>
</template>
