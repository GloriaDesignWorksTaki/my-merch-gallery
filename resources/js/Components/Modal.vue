<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const props = withDefaults(
  defineProps<{
    show?: boolean;
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
    closeable?: boolean;
    /**
     * ダイアログ見出し（aria-labelledby 用）
     * 例: 見出し要素に id="xxx" を付けて、この prop に title-id="xxx" を渡す。
     */
    titleId?: string;
    /** titleId 未指定時に使う代替（aria-label） */
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
      document.body.style.overflow = 'hidden';
      showSlot.value = true;

      lastActiveElement.value = document.activeElement as HTMLElement | null;

      // ダイアログを開いた後にフォーカスを当てる（アクセシビリティ向上）
      nextTick(() => {
        // 明示的な `autofocus` があればそれを優先
        const autoFocusEl = dialogRef.value?.querySelector<HTMLElement>('[autofocus]');
        if (autoFocusEl) {
          autoFocusEl.focus();
          return;
        }

        const focusable = getFocusableElements();
        (focusable[0] ?? dialogRef.value)?.focus?.();
      });
    } else {
      document.body.style.overflow = previousBodyOverflow.value;
      previousBodyOverflow.value = '';

      closeTimer = setTimeout(() => {
        showSlot.value = false;
        closeTimer = null;

        // 閉じた後に元のフォーカスへ戻す（UX）
        lastActiveElement.value?.focus?.();
        lastActiveElement.value = null;
      }, 200);
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

  document.body.style.overflow = '';
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
      class="fixed inset-0 z-50 overflow-y-auto px-4 py-8 sm:px-4 sm:py-10"
      scroll-region
    >
      <Transition
        enter-active-class="ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-show="show"
          class="fixed inset-0 transform transition-all"
          @click="close"
        >
          <div
            class="absolute inset-0 bg-gray-500 opacity-75"
          />
        </div>
      </Transition>

      <Transition
        enter-active-class="ease-out duration-300"
        enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        enter-to-class="opacity-100 translate-y-0 sm:scale-100"
        leave-active-class="ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0 sm:scale-100"
        leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
      >
        <div v-show="show" class="flex min-h-full items-center justify-center">
          <div
            ref="dialogRef"
            class="w-full transform overflow-hidden rounded-[2rem] border border-white/50 bg-white/95 shadow-[0_24px_80px_rgba(15,23,42,0.22)] transition-all"
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
      </Transition>
    </div>
  </Teleport>
</template>
