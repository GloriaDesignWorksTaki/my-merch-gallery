import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h, onUnmounted, ref } from 'vue';
import type { DefineComponent, Plugin } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
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

    const onStart = () => {
      if (showTimer) {
        clearTimeout(showTimer);
      }

      showTimer = setTimeout(() => {
        isLoading.value = true;
      }, 1000);
    };
    const onFinish = () => {
      if (showTimer) {
        clearTimeout(showTimer);
        showTimer = null;
      }
      isLoading.value = false;
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
        });
        return {};
      },
      render: () =>
        h('div', null, [
          h(LoadingLogoOverlay, { show: isLoading.value }),
          h(App, props),
        ]),
    };

    createApp(Root)
      .use(plugin)
      .use(i18n)
      .use(ZiggyVue)
      .mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});
