<script setup lang="ts">
import { HeartIcon as HeartOutlineIcon } from '@heroicons/vue/24/outline';
import { HeartIcon as HeartSolidIcon } from '@heroicons/vue/24/solid';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = withDefaults(
  defineProps<{
    liked: boolean;
    disabled?: boolean;
    label?: string;
    iconClass?: string;
    asSpan?: boolean;
  }>(),
  {
    disabled: false,
    iconClass: 'h-7 w-7',
    asSpan: false,
  },
);

const emit = defineEmits<{
  click: [event: MouseEvent];
}>();

const { t } = useI18n();

const BURST_COUNT = 10;

const punActive = ref(false);
const burstParticles = ref<{ tx: string; ty: string; delay: string; rot: string }[]>([]);
let burstToken = 0;

function triggerBurst() {
  burstToken += 1;
  const token = burstToken;
  burstParticles.value = Array.from({ length: BURST_COUNT }, (_, i) => {
    const jitter = Math.random() * 24 - 12;
    const baseDeg = (360 / BURST_COUNT) * i + jitter;
    const rad = (baseDeg * Math.PI) / 180;
    const dist = 22 + Math.random() * 16;
    const rot = `${Math.random() * 28 - 14}deg`;
    return {
      tx: `${Math.cos(rad) * dist}px`,
      ty: `${Math.sin(rad) * dist}px`,
      delay: `${i * 10}ms`,
      rot,
    };
  });
  window.setTimeout(() => {
    if (token === burstToken) {
      burstParticles.value = [];
    }
  }, 720);
}

watch(
  () => props.liked,
  (liked, prev) => {
    if (liked && prev === false) {
      punActive.value = true;
      window.setTimeout(() => {
        punActive.value = false;
      }, 480);
      triggerBurst();
    }
  },
);

const ariaLabelText = computed(() => {
  if (props.label) {
    return props.label;
  }
  return props.liked ? t('likes.ariaUnlike') : t('likes.ariaLike');
});

const toneClass = computed(() =>
  props.liked ? 'text-pink-500 hover:text-pink-600' : 'text-slate-400 hover:text-pink-500',
);

const rootClass = computed(() => {
  if (props.asSpan) {
    return 'relative inline-flex shrink-0 items-center justify-center rounded-full p-0 text-inherit transition-[color,transform] duration-150';
  }
  return 'relative inline-flex shrink-0 items-center justify-center rounded-full p-0 transition-[color,transform] duration-150 outline-none focus-visible:ring-2 focus-visible:ring-pink-300/60 focus-visible:ring-offset-2 focus-visible:ring-offset-transparent disabled:cursor-not-allowed disabled:opacity-45';
});

const iconClassMerged = computed(() =>
  props.asSpan ? [props.iconClass, 'text-current'] : props.iconClass,
);

function onClick(e: MouseEvent) {
  if (props.disabled || props.asSpan) {
    return;
  }
  emit('click', e);
}
</script>

<template>
  <span
    v-if="asSpan"
    class="pointer-events-none"
    :class="[rootClass]"
    aria-hidden="true"
  >
    <!-- 散るミニハート -->
    <span
      class="pointer-events-none absolute inset-0 flex items-center justify-center"
      aria-hidden="true"
    >
      <span
        v-for="(p, i) in burstParticles"
        :key="`b-${i}-${p.tx}`"
        class="heart-like-burst-particle"
        :style="{
          '--tx': p.tx,
          '--ty': p.ty,
          '--rot': p.rot,
          animationDelay: p.delay,
        }"
      >
        <HeartSolidIcon class="h-2.5 w-2.5 text-pink-400/95 drop-shadow-sm" />
      </span>
    </span>

    <span
      class="relative z-10 inline-flex"
      :class="{ 'heart-like-pun': punActive }"
      aria-hidden="true"
    >
      <HeartSolidIcon v-if="liked" :class="iconClassMerged" />
      <HeartOutlineIcon v-else :class="iconClassMerged" />
    </span>
  </span>

  <button
    v-else
    type="button"
    :class="[rootClass, toneClass]"
    :disabled="disabled"
    :aria-pressed="liked"
    :aria-label="ariaLabelText"
    @click="onClick"
  >
    <!-- 散るミニハート -->
    <span
      class="pointer-events-none absolute inset-0 flex items-center justify-center"
      aria-hidden="true"
    >
      <span
        v-for="(p, i) in burstParticles"
        :key="`b-${i}-${p.tx}`"
        class="heart-like-burst-particle"
        :style="{
          '--tx': p.tx,
          '--ty': p.ty,
          '--rot': p.rot,
          animationDelay: p.delay,
        }"
      >
        <HeartSolidIcon class="h-2.5 w-2.5 text-pink-400/95 drop-shadow-sm" />
      </span>
    </span>

    <!-- メインハート -->
    <span
      class="relative z-10 inline-flex"
      :class="{ 'heart-like-pun': punActive }"
      aria-hidden="true"
    >
      <HeartSolidIcon v-if="liked" :class="iconClassMerged" />
      <HeartOutlineIcon v-else :class="iconClassMerged" />
    </span>
  </button>
</template>

<style scoped>
@keyframes heart-like-pun {
  0% {
    transform: scale(1);
  }
  28% {
    transform: scale(0.82);
  }
  55% {
    transform: scale(1.14);
  }
  78% {
    transform: scale(0.96);
  }
  100% {
    transform: scale(1);
  }
}

.heart-like-pun {
  animation: heart-like-pun 0.48s cubic-bezier(0.34, 1.45, 0.64, 1);
}

@keyframes heart-burst-fly {
  0% {
    transform: translate(0, 0) rotate(var(--rot, 0deg)) scale(0.35);
    opacity: 1;
  }
  100% {
    transform: translate(var(--tx, 0), var(--ty, 0)) rotate(var(--rot, 0deg)) scale(0.75);
    opacity: 0;
  }
}

.heart-like-burst-particle {
  position: absolute;
  left: 50%;
  top: 50%;
  display: flex;
  width: 0.75rem;
  height: 0.75rem;
  align-items: center;
  justify-content: center;
  margin-left: -0.375rem;
  margin-top: -0.375rem;
  animation: heart-burst-fly 0.62s ease-out forwards;
  will-change: transform, opacity;
}
</style>
