import { Component, ElementRef, ViewChild,inject} from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router ,RouterLink} from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';
import { UserLogin } from '../../../core/models/user.interface';


@Component({
  selector: 'app-login',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})
export class LoginComponent {

  router=inject(Router);
  AuthService=inject(AuthService)
  loading:boolean=false;
  message:string='';

  login(value:UserLogin){ 
    this.loading=true;
    this.AuthService.login(value).subscribe({
      next: (res) => {
        this.loading=false;
        this.message=res.message;
          if(res.success==true){
            localStorage.setItem('userRole', res.user.role);
            this.router.navigate(['/home']);
            console.log('User role stored in localStorage:', res.user.role);
          }
      },
      error: (err) => {
        this.loading=false;
        this.message=err.message;
      }
    });
  }

  loginForm = new FormGroup({
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl('', [Validators.required, Validators.pattern(/^[A-Z][a-z0-9]{5,10}$/)]),
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

