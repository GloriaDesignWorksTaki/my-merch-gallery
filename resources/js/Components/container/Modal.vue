<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const props = withDefaults(
  defineProps<{
    show?: boolean;
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
    closeable?: boolean;
    titleId?: string;
    ariaLabel?: string;
  }>(),
  {
    show: false,
    maxWidth: '2xl',
    closeable: true,
  },
);

const emit = defineEmits(['close']);
const showSlot = ref(props.show);
let closeTimer: ReturnType<typeof setTimeout> | null = null;
const dialogRef = ref<HTMLElement | null>(null);
const lastActiveElement = ref<HTMLElement | null>(null);
const previousBodyOverflow = ref<string>('');
const previousHtmlOverflow = ref<string>('');
const previousBodyPosition = ref<string>('');
const previousBodyTop = ref<string>('');
const previousBodyLeft = ref<string>('');
const previousBodyWidth = ref<string>('');
const previousScrollY = ref<number>(0);
const previousHtmlOverscrollBehavior = ref<string>('');
const previousBodyOverscrollBehavior = ref<string>('');

/** 閉じるアニメーションと一致させる（CSS .mmg-modal-leave-active の時間） */
const LEAVE_MS = 300;

const getFocusableElements = () => {
  if (!dialogRef.value) {
    return [];
  }

  const nodes = dialogRef.value.querySelectorAll<HTMLElement>(
    [
      'a[href]',
      'button:not([disabled])',
      'textarea:not([disabled])',
      'input:not([disabled])',
      'select:not([disabled])',
      '[tabindex]:not([tabindex="-1"])',
    ].join(','),
  );

  return Array.from(nodes).filter((el) => !el.hasAttribute('disabled'));
};

const trapFocus = (e: KeyboardEvent) => {
  if (!props.show || e.key !== 'Tab') {
    return;
  }

  const focusable = getFocusableElements();

  if (focusable.length === 0) {
    return;
  }

  const active = document.activeElement;
  const first = focusable[0];
  const last = focusable[focusable.length - 1];

  const isActiveFirst = active === first;
  const isActiveLast = active === last;

  if (e.shiftKey && isActiveFirst) {
    e.preventDefault();
    last.focus();
  } else if (!e.shiftKey && isActiveLast) {
    e.preventDefault();
    first.focus();
  } else if (active && dialogRef.value && !dialogRef.value.contains(active)) {
    e.preventDefault();
    first.focus();
  }
};

watch(
  () => props.show,
  () => {
    if (closeTimer) {
      clearTimeout(closeTimer);
      closeTimer = null;
    }

    if (props.show) {
      previousBodyOverflow.value = document.body.style.overflow;
      previousHtmlOverflow.value = document.documentElement.style.overflow;
      previousBodyPosition.value = document.body.style.position;
      previousBodyTop.value = document.body.style.top;
      previousBodyLeft.value = document.body.style.left;
      previousBodyWidth.value = document.body.style.width;

      previousScrollY.value = window.scrollY || document.documentElement.scrollTop || 0;

      document.body.style.overflow = 'hidden';
      document.documentElement.style.overflow = 'hidden';
      previousHtmlOverscrollBehavior.value = document.documentElement.style.overscrollBehavior;
      previousBodyOverscrollBehavior.value = document.body.style.overscrollBehavior;
      document.documentElement.style.overscrollBehavior = 'none';
      document.body.style.overscrollBehavior = 'none';
      document.body.style.position = 'fixed';
      document.body.style.top = `-${previousScrollY.value}px`;
      document.body.style.left = '0';
      document.body.style.width = '100%';
      showSlot.value = true;

      lastActiveElement.value = document.activeElement as HTMLElement | null;

      nextTick(() => {
        const autoFocusEl = dialogRef.value?.querySelector<HTMLElement>('[autofocus]');
        if (autoFocusEl) {
          autoFocusEl.focus();
          return;
        }

        const focusable = getFocusableElements();
        (focusable[0] ?? dialogRef.value)?.focus?.();
      });
    } else {
      closeTimer = setTimeout(() => {
        showSlot.value = false;
        closeTimer = null;

        document.body.style.overflow = previousBodyOverflow.value;
        document.documentElement.style.overflow = previousHtmlOverflow.value;
        document.documentElement.style.overscrollBehavior = previousHtmlOverscrollBehavior.value;
        document.body.style.overscrollBehavior = previousBodyOverscrollBehavior.value;
        document.body.style.position = previousBodyPosition.value;
        document.body.style.top = previousBodyTop.value;
        document.body.style.left = previousBodyLeft.value;
        document.body.style.width = previousBodyWidth.value;

        previousBodyOverflow.value = '';
        previousHtmlOverflow.value = '';
        previousHtmlOverscrollBehavior.value = '';
        previousBodyOverscrollBehavior.value = '';
        previousBodyPosition.value = '';
        previousBodyTop.value = '';
        previousBodyLeft.value = '';
        previousBodyWidth.value = '';

        window.scrollTo(0, previousScrollY.value);

        previousScrollY.value = 0;

        lastActiveElement.value?.focus?.();
        lastActiveElement.value = null;
      }, LEAVE_MS);
    }
  },
  { immediate: true },
);

const close = () => {
  if (props.closeable) {
    emit('close');
  }
};

const closeOnEscape = (e: KeyboardEvent) => {
  if (e.key === 'Escape') {
    e.preventDefault();

    if (props.show) {
      close();
    }
  }
};

onMounted(() => {
  document.addEventListener('keydown', closeOnEscape);
  document.addEventListener('keydown', trapFocus);
});

onUnmounted(() => {
  document.removeEventListener('keydown', closeOnEscape);
  document.removeEventListener('keydown', trapFocus);

  document.body.style.overflow = previousBodyOverflow.value;
  document.documentElement.style.overflow = previousHtmlOverflow.value;
  document.documentElement.style.overscrollBehavior = previousHtmlOverscrollBehavior.value;
  document.body.style.overscrollBehavior = previousBodyOverscrollBehavior.value;
  document.body.style.position = previousBodyPosition.value;
  document.body.style.top = previousBodyTop.value;
  document.body.style.left = previousBodyLeft.value;
  document.body.style.width = previousBodyWidth.value;
  previousBodyOverflow.value = '';
  previousHtmlOverflow.value = '';
  previousHtmlOverscrollBehavior.value = '';
  previousBodyOverscrollBehavior.value = '';
  previousBodyPosition.value = '';
  previousBodyTop.value = '';
  previousBodyLeft.value = '';
  previousBodyWidth.value = '';
  if (previousScrollY.value) {
    window.scrollTo(0, previousScrollY.value);
    previousScrollY.value = 0;
  }
  if (closeTimer) {
    clearTimeout(closeTimer);
  }
});

const maxWidthClass = computed(() => {
  return {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
  }[props.maxWidth];
});
</script>

<template>
  <Teleport to="body">
    <div
      v-if="show || showSlot"
      class="fixed inset-0 z-50 overflow-y-hidden overscroll-contain"
      scroll-region
    >
      <Transition name="mmg-modal" appear>
        <div
          v-if="show"
          class="fixed inset-0 z-50 flex min-h-full flex-col items-center justify-center bg-gray-500/75 px-4 py-8 sm:px-4 sm:py-10"
          @click="close"
        >
          <div class="relative flex w-full min-h-0 max-h-full items-center justify-center">
            <div
              ref="dialogRef"
              class="mmg-modal-dialog w-full overflow-hidden rounded-[2rem] border border-white/50 bg-white/95 shadow-[0_24px_80px_rgba(15,23,42,0.22)]"
              :class="[maxWidthClass, 'max-h-[85vh]']"
              role="dialog"
              aria-modal="true"
              tabindex="-1"
              :aria-labelledby="titleId"
              :aria-label="ariaLabel"
              @click.stop
            >
              <slot v-if="showSlot" />
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </Teleport>
</template>

<style scoped>
/* オーバーレイ全体のフェード */
.mmg-modal-enter-active,
.mmg-modal-leave-active {
  transition: opacity 0.3s cubic-bezier(0, 0, 0.2, 1);
}

.mmg-modal-enter-from,
.mmg-modal-leave-to {
  opacity: 0;
}

/* パネル：開くときは下から＋少し縮小 → 開いた状態へ（閉じるときも同様に戻す） */
.mmg-modal-enter-active .mmg-modal-dialog,
.mmg-modal-leave-active .mmg-modal-dialog {
  transition:
    opacity 0.3s cubic-bezier(0, 0, 0.2, 1),
    transform 0.3s cubic-bezier(0, 0, 0.2, 1);
}

.mmg-modal-enter-from .mmg-modal-dialog,
.mmg-modal-leave-to .mmg-modal-dialog {
  opacity: 0;
  transform: translateY(1rem) scale(0.96);
}

@media (min-width: 640px) {
  .mmg-modal-enter-from .mmg-modal-dialog,
  .mmg-modal-leave-to .mmg-modal-dialog {
    transform: scale(0.96);
  }
}

.mmg-modal-enter-to .mmg-modal-dialog,
.mmg-modal-leave-from .mmg-modal-dialog {
  opacity: 1;
  transform: translateY(0) scale(1);
}
</style>
