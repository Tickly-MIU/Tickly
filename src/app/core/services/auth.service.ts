import { Injectable,inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { UserSignUp, UserLogin,SignUpResponse,LoginResponse } from './../models/user.interface';


@Injectable({
  providedIn: 'root'
})
export class AuthService {
  http=inject(HttpClient)

  register(data: UserSignUp): Observable<SignUpResponse> {
    return this.http.post<SignUpResponse>('http://localhost:80/Tickly/public/api/register', data);
  }

  login(data: UserLogin): Observable<LoginResponse> {
    return this.http.post<LoginResponse>('http://localhost:80/Tickly/public/api/login', data);
  }
}
