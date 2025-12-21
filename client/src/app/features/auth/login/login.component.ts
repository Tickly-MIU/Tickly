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

  ngOnInit() {
  this.email?.valueChanges.subscribe(() => this.message.set(''));
  this.password?.valueChanges.subscribe(() => this.message.set(''));
}

  login(value:UserLogin){ 
    this.loading.set(true);
    this.AuthService.login(value).pipe(delay(1000)).subscribe({
      next: (res) => {
        this.loading.set(false);
        this.message.set(res.message);
        if(res.success === true){
          localStorage.setItem('userRole', res.user.role);
          this.router.navigate(['/home']);
          console.log('User role stored in localStorage:', res.user.role);
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

  loginForm = new FormGroup({
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl('', [Validators.required, Validators.pattern(/^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/)]),
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

