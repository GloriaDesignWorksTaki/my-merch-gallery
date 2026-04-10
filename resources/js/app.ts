import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h, onUnmounted, ref } from 'vue';
import type { DefineComponent, Plugin } from 'vue';
import { ZiggyVue, type Config as ZiggyConfig } from 'ziggy-js';
import LoadingLogoOverlay from '@/Components/container/LoadingLogoOverlay.vue';
import { createAppI18n, isAppLocale, type AppLocale } from '@/i18n';
import { syncAppTheme } from '@/composables/useAppTheme';

let documentTitleAppName = 'My Merch Gallery';

function syncDocumentTitleAppName(pageProps: Record<string, unknown>): void {
  const n = pageProps.appName;
  if (typeof n === 'string' && n !== '') {
    documentTitleAppName = n;
  }
}

function syncDocumentHtmlLang(locale: AppLocale) {
  if (typeof document !== 'undefined') {
    document.documentElement.lang = locale;
  }
}

createInertiaApp({
  title: (title: string) => `${title} - ${documentTitleAppName}`,
  resolve: (name: string) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
    ),
  setup({
    el,
    App,
    props,
    plugin,
  }: {
    el: Element;
    App: DefineComponent;
    props: {
      initialPage: { props: Record<string, unknown> };
    };
    plugin: Plugin;
  }) {
    syncDocumentTitleAppName(props.initialPage.props);
    syncAppTheme(props.initialPage.props);

    const initialLocale = props.initialPage.props.locale;
    const i18n = createAppI18n(typeof initialLocale === 'string' ? initialLocale : undefined);
    syncDocumentHtmlLang(i18n.global.locale.value as AppLocale);

    const offI18nSuccess = router.on(
      'success',
      (event: { detail: { page: { props: Record<string, unknown> } } }) => {
        syncDocumentTitleAppName(event.detail.page.props);
        syncAppTheme(event.detail.page.props);
        const loc = event.detail.page.props.locale;
        if (isAppLocale(loc)) {
          i18n.global.locale.value = loc;
          syncDocumentHtmlLang(loc);
        }
      },
    );

    const isLoading = ref(false);
    let showTimer: ReturnType<typeof setTimeout> | null = null;
    let shownAt: number | null = null;
    let hideTimer: ReturnType<typeof setTimeout> | null = null;
    const SHOW_DELAY_MS = 2000;
    const MIN_VISIBLE_MS = 220;

    const onStart = () => {
      if (hideTimer) {
        clearTimeout(hideTimer);
        hideTimer = null;
      }
      if (showTimer) {
        clearTimeout(showTimer);
      }

      showTimer = setTimeout(() => {
        isLoading.value = true;
        shownAt = Date.now();
      }, SHOW_DELAY_MS);
    };
    const onFinish = () => {
      if (showTimer) {
        clearTimeout(showTimer);
        showTimer = null;
      }
      if (!isLoading.value) {
        return;
      }
      const elapsed = shownAt ? Date.now() - shownAt : MIN_VISIBLE_MS;
      const wait = Math.max(0, MIN_VISIBLE_MS - elapsed);
      if (wait === 0) {
        isLoading.value = false;
        shownAt = null;
        return;
      }
      hideTimer = setTimeout(() => {
        isLoading.value = false;
        shownAt = null;
        hideTimer = null;
      }, wait);
    };

    const offStart = router.on('start', onStart);
    const offFinish = router.on('finish', onFinish);
    const offCancel = router.on('cancel', onFinish);
    const offError = router.on('error', onFinish);

    const Root = {
      setup() {
        onUnmounted(() => {
          offI18nSuccess();
          offStart();
          offFinish();
          offCancel();
          offError();
          if (showTimer) {
            clearTimeout(showTimer);
          }
          if (hideTimer) {
            clearTimeout(hideTimer);
          }
        });
        return {};
      },
      render: () =>
        h('div', null, [
          h(LoadingLogoOverlay, { show: isLoading.value }),
          h(App, props),
        ]),
    };

    const ziggyConfig = (() => {
      const z = (window as unknown as { Ziggy?: ZiggyConfig }).Ziggy;
      if (!z) return null;
      return {
        ...z,
        url: window.location.origin,
      } satisfies ZiggyConfig;
    })();

    const app = createApp(Root).use(plugin).use(i18n);
    if (ziggyConfig) {
      app.use(ZiggyVue, ziggyConfig);
    } else {
      app.use(ZiggyVue);
    }
    app.mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});
