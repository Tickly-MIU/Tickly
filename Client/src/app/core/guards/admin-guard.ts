import { CanActivateFn, Router } from '@angular/router';
import { inject } from '@angular/core';

export const adminGuard: CanActivateFn = (route, state) => {
  const router = inject(Router);
  const userRole = localStorage.getItem('userRole');
  if (userRole !== 'admin') {
    router.navigate(['/tasks']);
    return false;
  }
  return true;
};
