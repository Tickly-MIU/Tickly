import { Component, ElementRef, ViewChild, inject } from '@angular/core';
import { AbstractControl, FormControl, FormGroup, ReactiveFormsModule, ValidationErrors, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';
import { UserSignUp } from '../../../core/models/user.interface';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-register',
  imports: [ReactiveFormsModule, RouterLink, CommonModule],
  templateUrl: './register.component.html',
  styleUrl: './register.component.css'
})
export class RegisterComponent {
  router = inject(Router);
  authService = inject(AuthService);
  
  loading = false;
  message = '';
  isSuccess = false;

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

  registerForm = new FormGroup({
    full_name: new FormControl('', [
      Validators.required,
      Validators.minLength(2),
      Validators.maxLength(50)
    ]),
    email: new FormControl('', [
      Validators.required,
      Validators.email
    ]),
    password: new FormControl('', [
      Validators.required,
      this.passwordValidator.bind(this)
    ]),
  });

  @ViewChild('InvalidInput')
  firstInvalidControl: ElementRef | null = null;

  register(value: UserSignUp) {
    this.loading = true;
    this.message = '';
    this.isSuccess = false;

    this.authService.register(value).subscribe({
      next: (res) => {
        this.loading = false;
        this.message = res.message;
        this.isSuccess = res.success;
        
        if (res.success) {
          setTimeout(() => {
            this.router.navigate(['/login']);
          }, 1500);
        }
      },
      error: (err) => {
        this.loading = false;
        this.isSuccess = false;
        this.message = err.error?.message || err.message || 'Registration failed. Please try again.';
      }
    });
  }

  handleSubmit() {
    if (this.registerForm.invalid) {
      this.registerForm.markAllAsTouched();
      if (this.firstInvalidControl) {
        this.firstInvalidControl.nativeElement.scrollIntoView({ 
          behavior: 'smooth', 
          block: 'center' 
        });
      }
      return;
    }
    
    const values = this.registerForm.value;
    this.register(values as UserSignUp);
  }

  get full_name() { 
    return this.registerForm.get('full_name'); 
  }
  
  get email() { 
    return this.registerForm.get('email'); 
  }
  
  get password() { 
    return this.registerForm.get('password'); 
  }
}
