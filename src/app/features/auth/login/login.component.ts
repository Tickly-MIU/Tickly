import { Component, ElementRef, ViewChild, inject, signal,OnInit } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router ,RouterLink} from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';
import { UserLogin } from '../../../core/models/user.interface';
import { delay } from 'rxjs/operators';


@Component({
  selector: 'app-login',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})
export class LoginComponent implements OnInit {

  router=inject(Router);
  AuthService=inject(AuthService)
  loading = signal(false);
  message = signal('');
  isSuccessMessage = signal(false);

  ngOnInit() {
  this.email?.valueChanges.subscribe(() => {
    this.message.set('');
    this.isSuccessMessage.set(false);
  });
  this.password?.valueChanges.subscribe(() => {
    this.message.set('');
    this.isSuccessMessage.set(false);
  });
}

  login(value:UserLogin){ 
    this.loading.set(true);
    this.AuthService.login(value).pipe(delay(1000)).subscribe({
      next: (res) => {
    this.loading.set(false);
        if (res.success && res.data?.user) {
          // Store user data in localStorage
          localStorage.setItem('userRole', res.data.user.role);
          localStorage.setItem('userId', res.data.user.id.toString());
          localStorage.setItem('userName', res.data.user.name);
          localStorage.setItem('userEmail', res.data.user.email);
          this.AuthService.logged.set(true);
          // Set success message
          this.message.set(res.message || 'Login successful!');
          this.isSuccessMessage.set(true);
          // Navigate after a short delay to show success message
          setTimeout(() => {
            this.router.navigate(['/tasks']);
          }, 500);
        } else {
          // Handle case where success is true but user data is missing
          this.message.set(res.message || 'Login successful but user data not received');
          this.isSuccessMessage.set(false);
        }
      },
      error: (err) => {        
  this.loading.set(false);
  this.isSuccessMessage.set(false);

  if (err.status === 401) {
    this.message.set(
      err.error?.message || 'Invalid email or password. Please try again.'
    );
  } else if (err.status === 0) {
    this.message.set(
      'Cannot connect to server. Please check if the server is running.'
    );
  } else {
    this.message.set(
      err.error?.message || err.message || 'Login failed. Please try again.'
    );
  }
}

    });
  }

  loginForm = new FormGroup({
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl('', [Validators.required]),
  });

  @ViewChild('InvalidInput')
  firstInvalidControl: ElementRef | null = null;
  
  handleSubmit() {
    if (this.loginForm.invalid) {
      this.loginForm.markAllAsTouched();
      if (this.firstInvalidControl) {
        this.firstInvalidControl.nativeElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      return;
    }
    const values = this.loginForm.value;
    this.login(values as UserLogin);
  }

  get email() { return this.loginForm.get('email'); }
  get password() { return this.loginForm.get('password'); }
}

