


import { AbstractControl, FormControl, FormGroup, ReactiveFormsModule, ValidationErrors, Validators } from '@angular/forms';
import { Component, inject, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { ApiService } from '../../../api.service';

@Component({
  selector: 'app-reset-password',
  imports: [ReactiveFormsModule],
  templateUrl: './reset-password.component.html',
  styleUrl: './reset-password.component.css'
})
export class ResetPasswordComponent implements OnInit {
  router = inject(Router);
  route = inject(ActivatedRoute);
  apiService = inject(ApiService);
  step = 1;
  token: string | null = null;
  loading = false;

  completed: boolean[] = []

  // Custom password validator
  passwordValidator(control: AbstractControl): ValidationErrors | null {
    const value = control.value;
    if (!value) {
      return null;
    }

    const errors: any = {};

    // Check minimum length
    if (value.length < 8) {
      errors.minLength = true;
    }

    // Check for at least 1 capital letter
    if (!/[A-Z]/.test(value)) {
      errors.noCapital = true;
    }

    // Check for at least 1 number
    if (!/[0-9]/.test(value)) {
      errors.noNumber = true;
    }

    // Check for at least 1 special character
    if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/.test(value)) {
      errors.noSpecial = true;
    }

    return Object.keys(errors).length > 0 ? errors : null;
  }

  forgetPasswordGroup = new FormGroup({
    email: new FormControl('', [Validators.required, Validators.email])
  });

  resetPasswordGroup = new FormGroup({
    email: new FormControl('', [Validators.required, Validators.email]),
    token: new FormControl('', [Validators.required]),
    newPassword: new FormControl('', [Validators.required, this.passwordValidator.bind(this)]),
    rePassword: new FormControl('', [Validators.required])
  }, { validators: this.matchPassword });

  ngOnInit() {
    // Check for token and email in URL query params
    this.route.queryParams.subscribe(params => {
      const token = params['token'];
      const email = params['email'];
      
      if (token && email) {
        this.token = token;
        this.resetPasswordGroup.patchValue({
          email: email,
          token: token
        });
        this.step = 2;
        this.completed[0] = true;
      }
    });
  }

  matchPassword(control: AbstractControl) {
    const password = control.get('newPassword')?.value;
    const rePassword = control.get('rePassword')?.value;
    return password === rePassword ? null : { notMatching: true };
  }

  handleForgetPassword() {
    if (this.forgetPasswordGroup.invalid) {
      this.forgetPasswordGroup.markAllAsTouched();
      return;
    }
    
    this.loading = true;
    this.apiService.requestReset({ email: this.forgetPasswordGroup.value.email! }).subscribe({
      next: (response: any) => {
        this.loading = false;
        if (response.success) {
          alert('Password reset link has been sent to your email');
        } else {
          alert(response.message || 'Failed to send reset link');
        }
      },
      error: (error: any) => {
        this.loading = false;
        alert(error.error?.message || 'Failed to send reset link. Please try again.');
      }
    });
  }

  handleResetPassword() {
    if (this.resetPasswordGroup.invalid) {
      this.resetPasswordGroup.markAllAsTouched();
      return;
    }
    
    this.loading = true;
    const formValue = this.resetPasswordGroup.value;
    this.apiService.resetPassword({
      email: formValue.email!,
      token: formValue.token!,
      new_password: formValue.newPassword!
    }).subscribe({
      next: (response: any) => {
        this.loading = false;
        if (response.success) {
          alert('Password has been successfully reset');
          this.forgetPasswordGroup.reset();
          this.resetPasswordGroup.reset();
          this.router.navigate(['/login']);
        } else {
          alert(response.message || 'Failed to reset password');
        }
      },
      error: (error: any) => {
        this.loading = false;
        alert(error.error?.message || 'Failed to reset password. The link may have expired.');
      }
    });
  }

  get email() { return this.forgetPasswordGroup.get('email'); }
  get emailR() { return this.resetPasswordGroup.get('email'); } 
  get newPassword() { return this.resetPasswordGroup.get('newPassword'); }
  get rePassword() { return this.resetPasswordGroup.get('rePassword'); }
}
