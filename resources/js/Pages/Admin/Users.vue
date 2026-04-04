<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CompactPagination from '@/Components/parts/CompactPagination.vue';
import TextInput from '@/Components/form/TextInput.vue';
import type { AuthUser } from '@/types';
import type { PaginatedList } from '@/types/inertia';
import SeoHead from '@/Components/seo/SeoHead.vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

type UserRow = {
  id: number;
  name: string;
  username: string;
  email: string;
  role: string;
  banned_at: string | null;
  created_at: string;
};

const props = defineProps<{
  users: PaginatedList<UserRow>;
  filters: { q: string };
}>();

const page = usePage<{ auth: { user: AuthUser | null } }>();
const me = computed(() => page.props.auth.user);

const searchForm = useForm({ q: props.filters.q });

function submitSearch() {
  searchForm.get(route('admin.users.index'), { preserveState: true });
}

function ban(id: number) {
  router.post(route('admin.users.ban', id));
}

function unban(id: number) {
  router.post(route('admin.users.unban', id));
}

function updateRole(id: number, role: string) {
  router.patch(route('admin.users.role', id), { role });
}

function roleLabel(role: string) {
  if (role === 'owner') {
    return t('pages.admin.usersRoleOwner');
  }
  if (role === 'admin') {
    return t('pages.admin.usersRoleAdmin');
  }

  return t('pages.admin.usersRoleUser');
}
</script>

<template>
  <SeoHead page="adminUsers" />

  <AuthenticatedLayout>
    <template #header>
      <div>
        <p class="text-xs uppercase tracking-[0.35em] text-sky-600/70">{{ t('pages.admin.eyebrow') }}</p>
        <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-800">{{ t('pages.admin.usersTitle') }}</h2>
      </div>
    </template>

    <div class="mx-auto max-w-5xl space-y-4">
      <Link :href="route('admin.dashboard')" class="glass-link text-sm font-medium">{{ t('pages.admin.title') }}</Link>

      <form class="glass-surface flex flex-wrap items-end gap-3 p-4" @submit.prevent="submitSearch">
        <div class="min-w-[200px] flex-1">
          <TextInput v-model="searchForm.q" type="search" class="w-full" :placeholder="t('pages.admin.usersSearchPlaceholder')" />
        </div>
        <button type="submit" class="glass-panel rounded-full px-5 py-2.5 text-sm font-semibold text-sky-800 hover:bg-white/55">
          {{ t('search.submit') }}
        </button>
      </form>

      <div class="overflow-x-auto rounded-3xl border border-white/40 bg-white/35">
        <table class="min-w-full text-left text-sm">
          <thead class="border-b border-white/40 text-xs uppercase tracking-wide text-slate-500">
            <tr>
              <th class="px-4 py-3">{{ t('pages.admin.usersRole') }}</th>
              <th class="px-4 py-3">@</th>
              <th class="px-4 py-3">email</th>
              <th class="px-4 py-3">{{ t('pages.admin.usersBanned') }}</th>
              <th class="px-4 py-3" />
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in users.data" :key="u.id" class="border-t border-white/30">
              <td class="px-4 py-3">
                <div v-if="me?.role === 'owner' && u.id !== me?.id" class="flex items-center gap-2">
                  <select
                    :value="u.role"
                    class="rounded-xl border border-white/40 bg-white/60 px-2 py-1 text-slate-800"
                    @change="updateRole(u.id, ($event.target as HTMLSelectElement).value)"
                  >
                    <option value="user">{{ t('pages.admin.usersRoleUser') }}</option>
                    <option value="admin">{{ t('pages.admin.usersRoleAdmin') }}</option>
                    <option value="owner">{{ t('pages.admin.usersRoleOwner') }}</option>
                  </select>
                </div>
                <span v-else class="font-medium text-slate-700">{{ roleLabel(u.role) }}</span>
              </td>
              <td class="px-4 py-3">
                <Link :href="route('users.show', u.id)" class="text-sky-700 hover:underline">@{{ u.username }}</Link>
              </td>
              <td class="px-4 py-3 text-slate-600">{{ u.email }}</td>
              <td class="px-4 py-3">
                <span v-if="u.banned_at" class="text-rose-600">{{ t('pages.admin.usersBanned') }}</span>
                <span v-else class="text-slate-500">{{ t('pages.admin.usersActive') }}</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button
                  v-if="!u.banned_at && u.id !== me?.id"
                  type="button"
                  class="text-xs font-semibold text-rose-600 hover:underline"
                  @click="ban(u.id)"
                >
                  {{ t('pages.admin.usersBan') }}
                </button>
                <button
                  v-else-if="u.banned_at && u.id !== me?.id"
                  type="button"
                  class="text-xs font-semibold text-sky-700 hover:underline"
                  @click="unban(u.id)"
                >
                  {{ t('pages.admin.usersUnban') }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <CompactPagination :links="users.links" />
    </div>
  </AuthenticatedLayout>
</template>
