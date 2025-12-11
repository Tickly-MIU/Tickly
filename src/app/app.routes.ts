import { Routes } from '@angular/router';
import { tokenGuard } from './core/guards/token-guard';

export const routes: Routes = [
    {path: '', redirectTo: 'home', pathMatch: 'full'},

    {path:"landing-page",loadComponent: () => import('./features/landing-page/landing-page.component').then(m => m.LandingPageComponent)},
    {path:"home",canActivate:[tokenGuard],loadComponent: () => import('./features/home/home.component').then(m => m.HomeComponent)}, 

    {path:"login",loadComponent: () => import('./features/auth/login/login.component').then(m => m.LoginComponent)},
    {path:"reset-password",loadComponent: () => import('./features/auth/reset-password/reset-password.component').then(m => m.ResetPasswordComponent)},
    {path:"register",loadComponent: () => import('./features/auth/register/register.component').then(m => m.RegisterComponent)},
    
    {path:"profile/:id",canActivate:[tokenGuard],loadComponent: () => import('./features/profile/profile.component').then(m => m.ProfileComponent)},
    
    {path:"**",loadComponent: () => import('./features/not-found/not-found.component').then(m => m.NotFoundComponent)},
];
