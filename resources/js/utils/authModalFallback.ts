import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

export function fallbackVisitAuthLoginModal(): void {
  router.visit(route('home', { auth: 'login' }));
}

export function fallbackVisitAuthRegisterModal(): void {
  router.visit(route('home', { auth: 'register' }));
}
