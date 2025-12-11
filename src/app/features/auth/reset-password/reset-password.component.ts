import { AbstractControl, FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Component, inject } from '@angular/core';
import { Router } from '@angular/router';



@Component({
  selector: 'app-reset-password',
  imports: [ReactiveFormsModule],
  templateUrl: './reset-password.component.html',
  styleUrl: './reset-password.component.css'
})
export class ResetPasswordComponent {
  router=inject(Router);
  step=1;

  completed:boolean[]=[]

  forgetPasswordGroup=new FormGroup({
    email:new FormControl('',[Validators.required,Validators.email])
  });

  verifyResetCodeGroup=new FormGroup({
    resetCode:new FormControl('',[Validators.required,Validators.pattern(/^[0-9]{6}$/)])
  });

  resetPasswordGroup=new FormGroup({
    email:new FormControl('',[Validators.required,Validators.email]),
    newPassword: new FormControl('', [Validators.required, Validators.pattern(/^[A-Z][a-z0-9]{5,10}$/)]),
    rePassword:new FormControl('',[Validators.required])
  },{validators:this.matchPassword});

  matchPassword(control: AbstractControl) {
    const password = control.get('newPassword')?.value;
    const rePassword = control.get('rePassword')?.value;
    return password === rePassword ? null : { notMatching: true };
  }

  handleForgetPassword(){
    if(this.forgetPasswordGroup.invalid){
      this.forgetPasswordGroup.markAllAsTouched();
      return;
    }
  //   this.loading=true;
  //   this.AuthService.forgetPassword({email:this.forgetPasswordGroup.value.email!}).subscribe({
  //     next:(response)=>{
  //       this.ToastrService.info("Verification Code Sent to your Email");
  //       this.loading=false;
    this.step=2;
    this.completed[0]=true;
  // },
  //     error:(error)=>{
  //       this.loading=false;
  //       this.ToastrService.error(error.error.message);
  //     }
  //   })
  }

  handleVerifyResetCode(){
    if(this.verifyResetCodeGroup.invalid){
      this.verifyResetCodeGroup.markAllAsTouched();
      return;
    }
  //   this.loading=true;
  //   this.AuthService.verifyResetCode({resetCode:this.verifyResetCodeGroup.value.resetCode!}).subscribe({
  //     next:(response)=>{
  //       this.ToastrService.success("Code Verified Successfully");
  //       this.loading=false;
  //   this.resetPasswordGroup.patchValue({email:this.forgetPasswordGroup.value.email});
  this.step=3;
  this.completed[1]=true;
  // },
  //     error:(error)=>{
  //       this.loading=false;
  //       this.ToastrService.error(error.error.message);
  //     }
  //   })
  }

  handleResetPassword(){
    if(this.resetPasswordGroup.invalid){
      this.resetPasswordGroup.markAllAsTouched();
      return;
    }
    // this.loading=true;
    // this.AuthService.resetPassword({email:this.resetPasswordGroup.value.email!,newPassword:this.resetPasswordGroup.value.newPassword!}).subscribe({
    //   next:(response)=>{
    this.forgetPasswordGroup.reset();
    this.verifyResetCodeGroup.reset();
    this.resetPasswordGroup.reset();
    this.router.navigate(['/login']);
    }
    // ,
  //     error:(error)=>{
  //       this.loading=false;
  //       this.ToastrService.error(error.error.message);
  //     }
  //   })
  // }

  get email() { return this.forgetPasswordGroup.get('email'); }
  get resetCode() { return this.verifyResetCodeGroup.get('resetCode'); }
  get emailR() { return this.resetPasswordGroup.get('email'); } 
  get newPassword() { return this.resetPasswordGroup.get('newPassword'); }
  get rePassword() { return this.resetPasswordGroup.get('rePassword'); }
}
