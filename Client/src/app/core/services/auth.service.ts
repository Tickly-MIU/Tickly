import { Injectable,inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { UserSignUp, UserLogin,SignUpResponse,LoginResponse } from './../models/user.interface';
import { environment } from '../../environment/environment';

const API_BASE = environment.API_BASE;

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  http=inject(HttpClient)

  register(data: UserSignUp): Observable<SignUpResponse> {
    return this.http.post<SignUpResponse>(`${API_BASE}/register`, data, { withCredentials: true });
  }

  login(data: UserLogin): Observable<LoginResponse> {
    const url = `${API_BASE}/login`;
    console.log('AuthService.login() - Calling API:', url);
    console.log('Request data:', { email: data.email, password: '***' });
    console.log('Request options:', { withCredentials: true });
    
    return this.http.post<LoginResponse>(url, data, { withCredentials: true });
  }

  logout(): Observable<any> {
    return this.http.post(`${API_BASE}/logout`, {}, { withCredentials: true });
  }
}
