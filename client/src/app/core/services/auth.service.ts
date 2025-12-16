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
    return this.http.post<SignUpResponse>(`${API_BASE}/api/register`, data);
  }

  login(data: UserLogin): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${API_BASE}/api/login`, data);
  }

  logout() {
    return this.http.post(`${API_BASE}/api/logout`, {}, { withCredentials: true });
  }

  getTasks() {
    return this.http.get(`${API_BASE}/api/tasks`, { withCredentials: true });
  }

  getTask(task_id: number) {
    return this.http.post(
      `${API_BASE}/api/tasks/show`,
      { task_id },
      { withCredentials: true }
    );
  }

  createTask(body: any) {
    return this.http.post(`${API_BASE}/api/tasks/create`, body, { withCredentials: true });
  }

  updateTask(body: any) {
    return this.http.post(`${API_BASE}/api/tasks/update`, body, { withCredentials: true });
  }

  deleteTask(task_id: number) {
    return this.http.post(
      `${API_BASE}/api/tasks/delete`,
      { task_id },
      { withCredentials: true }
    );
  }
}


