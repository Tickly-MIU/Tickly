import { Routes } from '@angular/router';
import { tokenGuard } from './core/guards/token-guard';
import { adminGuard } from './core/guards/admin-guard';

export const routes: Routes = [
    {path: '', loadComponent: () => import('./features/landing-page/landing-page.component').then(m => m.LandingPageComponent),pathMatch: 'full'},
    {path: 'home', loadComponent: () => import('./features/home/home.component').then(m => m.HomeComponent) , canActivate: [tokenGuard]},

    {path: 'profile', loadComponent: () => import('./features/profile/profile.component').then(m => m.ProfileComponent)     , canActivate: [tokenGuard]},
    {path: 'dashboard', loadComponent: () => import('./features/dashboard/dashboard.component').then(m => m.DashboardComponent), canActivate: [tokenGuard,adminGuard]},

    {path: 'login', loadComponent: () => import('./features/auth/login/login.component').then(m => m.LoginComponent) ,},
    {path: 'register', loadComponent: () => import('./features/auth/register/register.component').then(m => m.RegisterComponent) , },
    {path: 'reset-password', loadComponent: () => import('./features/auth/reset-password/reset-password.component').then(m => m.ResetPasswordComponent)},
    
    {path: 'landing-page', loadComponent: () => import('./features/landing-page/landing-page.component').then(m => m.LandingPageComponent)},
    {path: 'not-found', loadComponent: () => import('./features/not-found/not-found.component').then(m => m.NotFoundComponent)},
    {path: '**', redirectTo: 'not-found' }
];
