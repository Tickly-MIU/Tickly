import { Injectable,inject, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { UserSignUp, UserLogin,SignUpResponse,LoginResponse,User } from './../models/user.interface';
import { environment } from '../../environment/environment';

const API_BASE = environment.API_BASE;

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  http=inject(HttpClient)

  logged=signal(false);

  register(data: UserSignUp): Observable<SignUpResponse> {
    return this.http.post<SignUpResponse>(`${API_BASE}/register`, data, { withCredentials: true });
  }

  login(data: UserLogin): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${API_BASE}/login`, data, { withCredentials: true });
  }

   logout(): Observable<any> {
    return this.http.post(`${API_BASE}/logout`, {}, { withCredentials: true });
  }

  getProfile(): Observable<any>  {
    return this.http.get(`${API_BASE}/profile`, { withCredentials: true });
  }
  

  getUsers(): Observable<any> {
    return this.http.get(`${API_BASE}/admin/users`, { withCredentials: true });
  }
  checkSession(): Observable<any> {
    return this.http.get(`${API_BASE}/session-check`, { withCredentials: true });
  }

  // Admin routes
  getUserStatistics(): Observable<any> {
    return this.http.get(`${API_BASE}/admin/statistics`, { withCredentials: true });
  }

  getActivityLogs(): Observable<any> {
    return this.http.get(`${API_BASE}/admin/activity-logs`, { withCredentials: true });
  }

  getSystemOverview(): Observable<any> {
    return this.http.get(`${API_BASE}/admin/overview`, { withCredentials: true });
  }

  deleteUser(userId: number): Observable<any> {
    return this.http.post(`${API_BASE}/admin/user/delete`, { user_id: userId }, { withCredentials: true });
  }

  updateUserRole(userId: number, role: string): Observable<any> {
    return this.http.post(`${API_BASE}/admin/user/role`, { user_id: userId, role }, { withCredentials: true });
  }

  addNewAdmin(userData: any): Observable<any> {
    return this.http.post(`${API_BASE}/admin/add-admin`, userData, { withCredentials: true });
  }
}


