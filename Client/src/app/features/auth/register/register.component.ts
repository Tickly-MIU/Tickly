import { Component, ElementRef, ViewChild,inject} from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router,RouterLink} from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';
import { UserSignUp } from '../../../core/models/user.interface';
@Component({
  selector: 'app-register',
  imports: [ReactiveFormsModule,RouterLink],
  templateUrl: './register.component.html',
  styleUrl: './register.component.css'
})
export class RegisterComponent {


  router=inject(Router);
  AuthService=inject(AuthService)
  loading:boolean=false;
  message:string='';

  login(value:UserSignUp){ 
    this.loading=true;
    this.AuthService.login(value).subscribe({
      next: (res) => {
        this.loading=false;
        console.log(res.message);
        this.message=res.message;
          if(res.success==true)
          this.router.navigate(['/home']);
      },
      error: (err) => {
        this.loading=false;
        this.message=err.message;
      }
    });
  }

 registerForm = new FormGroup({
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl('', [Validators.required, Validators.pattern(/^[A-Z][a-z0-9]{5,10}$/)]),
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
    this.login(values as UserSignUp);
  }

  get email() { return this.registerForm.get('email'); }
  get password() { return this.registerForm.get('password'); }
}
