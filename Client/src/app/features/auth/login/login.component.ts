import { Component, ElementRef, ViewChild, inject, OnInit } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';
import { UserLogin } from '../../../core/models/user.interface';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-login',
  imports: [ReactiveFormsModule, RouterLink, CommonModule],
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})
export class LoginComponent implements OnInit {
  router = inject(Router);
  authService = inject(AuthService);

  loading = false;
  message = '';
  isSuccess = false;

  ngOnInit(): void {
    // Check if redirected due to session expiration
    const queryParams = new URLSearchParams(window.location.search);
    if (queryParams.get('expired') === 'true') {
      this.message = 'Your session has expired. Please login again.';
      this.isSuccess = false;
    }
  }

  loginForm = new FormGroup({
    email: new FormControl('', [
      Validators.required,
      Validators.email
    ]),
    password: new FormControl('', [
      Validators.required,
      Validators.minLength(6)
      // Removed pattern validation - backend handles password validation
      // Password can be any combination as long as it's verified against the hash
    ]),
  });

  @ViewChild('InvalidInput')
  firstInvalidControl: ElementRef | null = null;

  login(value: UserLogin) {
    this.loading = true;
    this.message = '';
    this.isSuccess = false;

    console.log('=== Login Component: Starting login process ===');
    console.log('Login attempt:', { email: value.email });
    console.log('Form values:', { email: value.email, passwordLength: value.password?.length || 0 });

    this.authService.login(value).subscribe({
      next: (res) => {
        console.log('=== Login Component: Received response ===');
        console.log('Login response:', res);
        console.log('Response structure:', {
          success: res.success,
          message: res.message,
          hasData: !!res.data,
          hasUser: !!res.data?.user
        });

        this.loading = false;
        this.message = res.message;
        this.isSuccess = res.success;

        if (res.success && res.data?.user) {
          console.log('✓ Login successful - User data received');
          // Store user data in localStorage
          localStorage.setItem('userRole', res.data.user.role);
          localStorage.setItem('userId', res.data.user.id.toString());
          localStorage.setItem('userName', res.data.user.name);
          localStorage.setItem('userEmail', res.data.user.email);

          console.log('User logged in:', {
            id: res.data.user.id,
            name: res.data.user.name,
            email: res.data.user.email,
            role: res.data.user.role
          });

          // Small delay before navigation for better UX
          console.log('Navigating to /home in 500ms...');
          setTimeout(() => {
            console.log('Navigating to /home now');
            this.router.navigate(['/home']);
          }, 500);
        } else {
          // Handle case where success is true but user data is missing
          console.warn('Login successful but user data missing:', res);
          this.message = res.message || 'Login successful but user data not received';
        }
      },
      error: (err) => {
        console.error('=== Login Component: Error occurred ===');
        console.error('Login error:', err);
        console.error('Error details:', {
          status: err.status,
          statusText: err.statusText,
          error: err.error,
          url: err.url,
          message: err.message
        });

        this.loading = false;
        this.isSuccess = false;

        // Handle specific error cases
        if (err.status === 401) {
          this.message = err.error?.message || 'Invalid email or password. Please try again.';
        } else if (err.status === 0) {
          this.message = 'Cannot connect to server. Please check if the server is running.';
        } else {
          this.message = err.error?.message || err.message || 'Login failed. Please check your credentials and try again.';
        }
      }
    });
  }

  handleSubmit() {
    if (this.loginForm.invalid) {
      this.loginForm.markAllAsTouched();
      if (this.firstInvalidControl) {
        this.firstInvalidControl.nativeElement.scrollIntoView({
          behavior: 'smooth',
          block: 'center'
        });
      }
      return;
    }

    const values = this.loginForm.value;
    this.login(values as UserLogin);
  }

  get email() {
    return this.loginForm.get('email');
  }

  get password() {
    return this.loginForm.get('password');
  }
}

