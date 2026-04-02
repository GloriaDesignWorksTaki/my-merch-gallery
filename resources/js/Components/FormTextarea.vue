<script setup lang="ts">
const model = defineModel<string>({ required: true });
</script>

<template>
  <textarea
    v-model="model"
    class="glass-panel w-full rounded-2xl border-white/50 bg-white/45 px-4 py-3 text-slate-800 placeholder:text-slate-400 shadow-[0_10px_30px_rgba(125,166,214,0.12)] focus:border-sky-300/70 focus:ring-sky-200/60"
  />
</template>
