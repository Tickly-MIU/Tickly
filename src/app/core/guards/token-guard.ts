import { CanActivateFn, Router } from '@angular/router';
import { inject } from '@angular/core';

export const tokenGuard: CanActivateFn = (route, state) => {
  const router = inject(Router);
  const isLoginedIn = localStorage.getItem('userId') !== null;
  if (!isLoginedIn) {
    router.navigate(['/login']);
    return false;
  }
  return true;
};
