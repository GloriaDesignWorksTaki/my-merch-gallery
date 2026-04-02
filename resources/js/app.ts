import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h, onUnmounted, ref } from 'vue';
import type { DefineComponent } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import LoadingLogoOverlay from '@/Components/LoadingLogoOverlay.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
    ),
  setup({ el, App, props, plugin }) {
    const isLoading = ref(false);
    let showTimer: ReturnType<typeof setTimeout> | null = null;

    const onStart = () => {
      if (showTimer) {
        clearTimeout(showTimer);
      }

      // レスポンスが速い遷移はローディングを出さない
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

    const offStart = Inertia.on('start', onStart);
    const offFinish = Inertia.on('finish', onFinish);
    const offCancel = Inertia.on('cancel', onFinish);
    const offError = Inertia.on('error', onFinish);

    const Root = {
      setup() {
        onUnmounted(() => {
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
      .use(ZiggyVue)
      .mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});
