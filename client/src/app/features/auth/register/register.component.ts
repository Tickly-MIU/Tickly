import { Component, ElementRef, ViewChild, inject, signal } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router,RouterLink} from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';
import { UserSignUp } from '../../../core/models/user.interface';
import { delay } from 'rxjs/operators';
@Component({
  selector: 'app-register',
  imports: [ReactiveFormsModule,RouterLink],
  templateUrl: './register.component.html',
  styleUrl: './register.component.css'
})
export class RegisterComponent {


  router=inject(Router);
  AuthService=inject(AuthService)
  loading = signal(false);
  message = signal('');

  register(value:UserSignUp){ 
    this.loading.set(true);
    this.AuthService.register(value).pipe(delay(1000)).subscribe({
      next: (res) => {
        this.loading.set(false);
        console.log(res.message);
        this.message.set(res.message);
        if (res.success === true) {
          this.router.navigate(['/home']);
        }
      },
      error: (err) => {
        this.loading.set(false);
        if (err && err.error && err.error.message) {
          this.message.set(err.error.message);
        } else if (err && err.message) {
          this.message.set(err.message);
        } else {
          this.message.set('An error occurred. Please try again.');
        }
      }
    });
  }

 registerForm = new FormGroup({
    full_name: new FormControl('', [Validators.required, Validators.minLength(8), Validators.maxLength(30)]),
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl('', [Validators.required, Validators.pattern(/^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/)]),
  });

  @ViewChild('InvalidInput')
  firstInvalidControl: ElementRef | null = null;
  
  handleSubmit() {
    if (this.registerForm.invalid) {
      this.registerForm.markAllAsTouched();
      if (this.firstInvalidControl) {
        this.firstInvalidControl.nativeElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      return;
    }
    const values = this.registerForm.value;
    this.register(values as UserSignUp);
  }

  get full_name() { return this.registerForm.get('full_name'); }
  get email() { return this.registerForm.get('email'); }
  get password() { return this.registerForm.get('password'); }
}
