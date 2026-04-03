<script setup lang="ts">
import { nextTick, onBeforeUnmount, watch } from 'vue';

const props = defineProps<{
  show: boolean;
}>();

type Part = {
  id: string;
  popIn: number; // ms
  fadeOut: number; // ms
  ox: number;
  oy: number;
  isCircle: boolean;
};

const CYCLE = 3000; // 全体の長さ (ms)
const POP_DUR = 540; // 出現時間 (ms)
const FADE_DUR = 200; // フェードアウト時間 (ms)

const parts: Part[] = [
  { id: 'p1g', popIn: 0, fadeOut: 1700, ox: 160, oy: 430, isCircle: false },
  { id: 'p3g', popIn: 340, fadeOut: 1900, ox: 385, oy: 430, isCircle: false },
  { id: 'p2g', popIn: 680, fadeOut: 2100, ox: 610, oy: 430, isCircle: false },
  { id: 'circg', popIn: 1080, fadeOut: 2300, ox: 410, oy: 61, isCircle: true },
];

let rafId: number | null = null;
let startTime: number | null = null;
let elements: Record<string, SVGElement | null> = {};

function easeInQuad(t: number) {
  return t * t;
}
function easeOutQuad(t: number) {
  return t * (2 - t);
}
function easeInOutQuad(t: number) {
  return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
}

function jellyBar(t: number) {
  if (t <= 0) return { sx: 1.4, sy: 0.0, ty: 60 };
  if (t >= 1) return { sx: 1, sy: 1, ty: 0 };
  if (t < 0.3) {
    const e = easeOutQuad(t / 0.3);
    return { sx: 1.4 - 0.55 * e, sy: e * 1.18, ty: 60 * (1 - e) };
  }
  if (t < 0.55) {
    const e = easeOutQuad((t - 0.3) / 0.25);
    return { sx: 0.85, sy: 1.18 + 0.14 * e, ty: 0 };
  }
  if (t < 0.72) {
    const e = easeOutQuad((t - 0.55) / 0.17);
    return { sx: 0.85 + 0.22 * e, sy: 1.32 - 0.22 * e, ty: 0 };
  }
  if (t < 0.86) {
    const e = easeInOutQuad((t - 0.72) / 0.14);
    return { sx: 1.07 - 0.1 * e, sy: 1.1 - 0.06 * e, ty: 0 };
  }
  const e = easeOutQuad((t - 0.86) / 0.14);
  return { sx: 0.97 + 0.03 * e, sy: 1.04 - 0.04 * e, ty: 0 };
}

function jellyCircle(t: number) {
  if (t <= 0) return { sx: 0.6, sy: 0.6, ty: -80 };
  if (t >= 1) return { sx: 1, sy: 1, ty: 0 };
  if (t < 0.28) {
    const e = easeInQuad(t / 0.28);
    return { sx: 0.6 + 0.45 * e, sy: 0.6 + 0.45 * e, ty: -80 * (1 - e) };
  }
  if (t < 0.44) {
    const e = easeOutQuad((t - 0.28) / 0.16);
    return { sx: 1.05 + 0.35 * e, sy: 1.05 - 0.32 * e, ty: 0 };
  }
  if (t < 0.6) {
    const e = easeOutQuad((t - 0.44) / 0.16);
    return { sx: 1.4 - 0.38 * e, sy: 0.73 + 0.34 * e, ty: 0 };
  }
  if (t < 0.74) {
    const e = easeInOutQuad((t - 0.6) / 0.14);
    return { sx: 1.02 - 0.08 * e, sy: 1.07 - 0.02 * e, ty: 0 };
  }
  if (t < 0.86) {
    const e = easeOutQuad((t - 0.74) / 0.12);
    return { sx: 0.94 + 0.08 * e, sy: 1.05 - 0.08 * e, ty: 0 };
  }
  const e = easeOutQuad((t - 0.86) / 0.14);
  return { sx: 1.02 - 0.02 * e, sy: 0.97 + 0.03 * e, ty: 0 };
}

const resetVisual = () => {
  for (const part of parts) {
    const el = elements[part.id];
    if (!el) continue;
    el.style.opacity = '0';
    el.style.transform = 'none';
  }
};

const stop = () => {
  if (rafId) {
    cancelAnimationFrame(rafId);
  }
  rafId = null;
  startTime = null;
  resetVisual();
};

const start = async () => {
  elements = {};
  for (const part of parts) {
    elements[part.id] = document.getElementById(part.id) as SVGElement | null;
  }

  startTime = null;
  await nextTick();
  resetVisual();

  const tick = (ts: number) => {
    if (!startTime) startTime = ts;
    const elapsed = ts - startTime;

    for (const part of parts) {
      const el = elements[part.id];
      if (!el) continue;

      const t = elapsed % CYCLE;

      if (t < part.popIn) {
        el.style.opacity = '0';
        el.style.transform = 'none';
        continue;
      }

      const sinceIn = t - part.popIn;
      if (sinceIn <= POP_DUR) {
        const norm = sinceIn / POP_DUR;
        const k = part.isCircle ? jellyCircle(norm) : jellyBar(norm);
        el.style.opacity = '1';
        el.style.transformOrigin = `${part.ox}px ${part.oy}px`;
        el.style.transform = `translate(0,${k.ty}px) scale(${k.sx},${k.sy})`;
        continue;
      }

      if (t < part.fadeOut) {
        el.style.opacity = '1';
        el.style.transformOrigin = `${part.ox}px ${part.oy}px`;
        el.style.transform = 'none';
        continue;
      }

      if (t < part.fadeOut + FADE_DUR) {
        const ft = 1 - (t - part.fadeOut) / FADE_DUR;
        el.style.opacity = String(Math.max(0, ft));
        el.style.transform = 'none';
        continue;
      }

      el.style.opacity = '0';
      el.style.transform = 'none';
    }

    rafId = requestAnimationFrame(tick);
  };

  rafId = requestAnimationFrame(tick);
};

watch(
  () => props.show,
  async (next) => {
    if (next) {
      await start();
    } else {
      stop();
    }
  },
  { immediate: true },
);

onBeforeUnmount(() => stop());
</script>

<template>
  <div v-if="show" class="loading-overlay" role="status" aria-live="polite" aria-busy="true">
    <svg
      class="logo-svg"
      viewBox="0 0 696.38 484.36"
      xmlns="http://www.w3.org/2000/svg"
    >
      <g id="p1g" style="opacity: 0">
        <path
          fill="#5896c2"
          d="M213.08,153.29h0c-34.69-20.03-79.05-8.14-99.08,26.55L9.73,360.43c-20.03,34.69-8.14,79.05,26.55,99.08h0c34.69,20.03,79.05,8.14,99.08-26.55l104.26-180.59c20.03-34.69-26.55-99.08-26.55-99.08Z"
        />
      </g>
      <g id="p3g" style="opacity: 0">
        <path
          fill="#5896c2"
          d="M436.59,168.4h0c-34.69-20.03-79.05-8.14-99.08,26.55l-104.26,180.59c-20.03,34.69-8.14,79.05,26.55,99.08h0c34.69,20.03,79.05,8.14,99.08-26.55l104.26-180.59c20.03-34.69-26.55-99.08-26.55-99.08Z"
        />
      </g>
      <g id="p2g" style="opacity: 0">
        <path
          fill="#5896c2"
          d="M660.1,168.4c-34.69-20.03-79.05-8.14-99.08,26.55l-104.26,180.59c-20.03,34.69-8.14,79.05,26.55,99.08h0c34.69,20.03,79.05,8.14,99.08-26.55l104.26-180.59c20.03-34.69-26.55-99.08-26.55-99.08Z"
        />
      </g>
      <g id="circg" style="opacity: 0">
        <circle fill="#5896c2" cx="409.82" cy="61.38" r="61.38" />
      </g>
    </svg>
  </div>
</template>

<style scoped>
.loading-overlay {
  position: fixed;
  inset: 0;
  background: #fff;
  pointer-events: none;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 2rem;
  z-index: 9999;
}

.logo-svg {
  width: 120px;
  height: auto;
  overflow: visible;
}
</style>

